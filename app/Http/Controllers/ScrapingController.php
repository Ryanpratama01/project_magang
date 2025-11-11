<?php

namespace App\Http\Controllers;

use App\Jobs\ScrapeNewsLinksJob;
use App\Jobs\ScrapeYoutubeLinksJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\NewsQuote;
use Illuminate\Support\Carbon;   
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\SentimentController;



class ScrapingController extends Controller
{
    
    // [ADD] Analisis sentimen untuk item terpilih (news / youtube) (aline1)
public function analyzeSelected(Request $request)
{
    $data = $request->validate([
        'ids'  => 'required|array|min:1',
        'ids.*'=> 'integer',
        'type' => 'required|in:news,youtube',
    ]);

    $ids  = $data['ids'];
    $type = $data['type'];

    // Ambil sumber data dari DB scraping
    $conn = DB::connection('scrapingdb');

    if ($type === 'news') {
        $rows = $conn->table('news_quotes')
            ->whereIn('id', $ids)
            ->get(['id','original_text','cleaned_text','news_url']);
    } else {
        $rows = $conn->table('youtube_comments1')
            ->whereIn('id', $ids)
            ->get(['id','comment_text','cleaned_text','video_url']);
    }

    $sentCtrl = app(SentimentController::class);

    $ok = 0; $skip = 0; $details = [];
    foreach ($rows as $r) {
        // pilih teks yang paling bersih tersedia
        $text = null;
        if ($type === 'news') {
            $text = trim((string)($r->cleaned_text ?: $r->original_text));
        } else {
            $text = trim((string)($r->cleaned_text ?: $r->comment_text));
        }

        if ($text === '' || mb_strlen($text) < 3) { $skip++; continue; }

        // Panggil analyzer yang sudah menyimpan ke tabel
        // (SentimentController@analyze memang akan INSERT ke news_article_sentiments)
        $res = $sentCtrl->analyze(new Request(['text' => $text]));
        $payload = method_exists($res, 'getData') ? $res->getData(true) : (json_decode($res->getContent(), true) ?? []);

        if (!empty($payload['success'])) {
            $ok++;
            // kumpulkan ringkasan untuk pop-up
            $d = $payload['data'] ?? [];
            $details[] = [
                'id'        => $r->id,
                'label'     => $d['sentiment'] ?? '-',
                'confidence'=> $d['confidence'] ?? null,
                'pos'       => $d['scores']['positive'] ?? null,
                'neg'       => $d['scores']['negative'] ?? null,
                'neu'       => $d['scores']['neutral'] ?? null,
                'preview'   => mb_substr($text, 0, 120),
            ];
        } else {
            $skip++;
        }
    }

    return response()->json([
        'success'  => true,
        'message'  => "Analisis selesai: {$ok} berhasil, {$skip} dilewati.",
        'analyzed' => $ok,
        'skipped'  => $skip,
        'details'  => $details,
        // info: hasil sudah tersimpan oleh SentimentController@analyze
        // ke tabel news_article_sentiments
    ]);
}

/**
 * [ADD] Skor sentimen sederhana (Bahasa Indonesia).
 * Output: -1..+1 (negatif..positif)
 */
private function scoreSentimentId(string $text): float
{
    static $pos = [
        'bagus','baik','puas','memuaskan','senang','bahagia','mantap','top','cepat','murah','hebat',
        'terbaik','suka','rekomendasi','ramah','bersih','nyaman','lancar','stabil','keren','mantul',
        'menarik','cukup baik','memadai','solutif','terbantu','mantap','recommended'
    ];
    static $neg = [
        'buruk','jelek','kecewa','mengecewakan','lambat','mahal','parah','macet','error','rusak','gagal',
        'tidak puas','kurang','benci','lemot','kotor','bising','bermasalah','menyedihkan','menakutkan',
        'hancur','abal','tidak layak','menurun','fatal','payah'
    ];

    $t = mb_strtolower($text);
    $t = preg_replace('/[^a-z0-9\s]/u', ' ', $t);
    $words = preg_split('/\s+/', $t, -1, PREG_SPLIT_NO_EMPTY);

    if (!$words) return 0.0;

    $p = 0; $n = 0;
    foreach ($words as $w) {
        if (in_array($w, $pos, true)) $p++;
        if (in_array($w, $neg, true)) $n++;
    }

    // bobot sederhana untuk beberapa frasa 2 kata
    for ($i=0; $i<count($words)-1; $i++) {
        $bg = $words[$i].' '.$words[$i+1];
        if (in_array($bg, ['sangat baik','sangat puas','luar biasa','sangat membantu','cukup baik'], true)) $p+=2;
        if (in_array($bg, ['sangat buruk','tidak puas','sangat lambat','sangat mahal','kurang baik'], true)) $n+=2;
    }

    $total = max(1, $p + $n);
    return ($p - $n) / $total;
}

    public function index()
    {
        return view('scraping');
    }
    // public function scrapeNewsByLinks(Request $r) {
    //     $data = $r->validate(['links'=>'required|string']);
    //     $urls = collect(preg_split('/\r?\n/', trim($data['links'])))->filter()->unique();
    //     foreach ($urls as $url) ScrapeNewsLinksJob::dispatch((string)$url, auth()->id() ?? 0);
    //     return back()->with('ok','Scraping berita dimulai ('.count($urls).' URL).');
    // }
    public function scrapeNewsByLinks(Request $r) {
        // Terima dua-duanya: 'links' atau 'newsLinks'
        Log::info('HIT scrapeNewsByLinks', ['all'=>$r->all()]);
        
        // alias: terima 'links' ATAU nama lama 'newsLinks'
        $r->merge(['links' => $r->input('links') ?? $r->input('newsLinks')]);

        $data = $r->validate([
            'links'      => 'nullable|string|required_without:newsLinks',
            'newsLinks'  => 'nullable|string|required_without:links',
        ]);

        // Ambil salah satu yang ada
        $raw = $r->input('links') ?? $r->input('newsLinks');

        $urls = collect(preg_split('/\r?\n/', trim($raw)))->filter()->unique();
        
        // ✅ TAMBAHKAN: Generate unique job ID
        $jobId = uniqid('scrape_news_', true);
        $total = $urls->count();
        
        Log::info('DISPATCH news', [
            'count' => $total, 
            'job_id' => $jobId
        ]);
        
        // ✅ TAMBAHKAN: Initialize progress di cache
        Cache::put("scraping_progress_{$jobId}", [
            'current' => 0,
            'total' => $total,
            'completed' => 0,
            'failed' => 0,
            'status' => 'started',
            'message' => "Memulai scraping {$total} berita...",
            'details' => []
        ], now()->addMinutes(10));
        
        // ✅ UPDATE: Dispatch jobs dengan job ID dan index
        foreach ($urls as $index => $url) {
            ScrapeNewsLinksJob::dispatch(
                (string)$url, 
                (int) (Auth::id() ?? 0),
                $jobId,        // Pass job ID
                $index,        // Pass current index
                $total         // Pass total URLs
            );
        }

        // ✅ TAMBAHKAN: Return JSON jika AJAX request
        if ($r->expectsJson() || $r->wantsJson()) {
            return response()->json([
                'success' => true,
                'job_id' => $jobId,
                'total' => $total,
                'message' => "Scraping dimulai untuk {$total} berita"
            ]);
        }

        // Redirect biasa jika bukan AJAX
        return back()->with('ok', "Scraping berita dimulai ({$total} URL).");
    }

    public function startScraping(Request $request)
    {
        $request->validate([
            'links' => 'required|string'
        ]);
        
        // Process scraping logic here
        $links = explode("\n", $request->links);
        $results = [];
        
        foreach ($links as $link) {
            $link = trim($link);
            if (!empty($link)) {
                // Simulate scraping result
                $results[] = [
                    'url' => $link,
                    'title' => 'Sample News Title from ' . parse_url($link, PHP_URL_HOST),
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...',
                    'published_at' => now(),
                    'status' => 'success'
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Scraping completed successfully',
            'data' => $results,
            'total' => count($results)
        ]);
    }
    public function results(Request $r)
    {
        $page    = max(1, (int) $r->input('page', 1));
        $perPage = max(1, min(200, (int) $r->input('per_page', 50)));
        $search  = trim((string) $r->input('search', ''));
        $source  = trim((string) $r->input('source', ''));

        $q = DB::connection('scrapingdb')
            ->table('news_quotes')
            ->orderByDesc('scraped_at')
            ->orderByDesc('id');

        if ($source !== '') {
            $q->where(function ($qq) use ($source) {
                $qq->where('news_source', 'LIKE', "%{$source}%")
                ->orWhere('news_url', 'LIKE', "%{$source}%");
            });
        }

        if ($search !== '') {
            $like = "%{$search}%";
            $q->where(function ($qq) use ($like) {
                $qq->where('original_text', 'LIKE', $like)
                ->orWhere('cleaned_text', 'LIKE', $like)
                ->orWhere('speaker', 'LIKE', $like)
                ->orWhere('context', 'LIKE', $like)
                ->orWhere('news_url', 'LIKE', $like)
                ->orWhere('news_source', 'LIKE', $like);
            });
        }

        $p = $q->paginate(
            $perPage,
            [
                'id','original_text','cleaned_text','speaker',
                'news_url','news_source','context',
                'published_at','scraped_at','processing_status','created_at'
            ],
            'page',
            $page
        );

        return response()->json([
            'data' => $p->items(),
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
                'per_page'     => $p->perPage(),
                'total'        => $p->total(),
            ],
        ]);
    }

    public function youtubeResults(Request $r)
    {
        $page    = max(1, (int)$r->input('page', 1));
        $perPage = max(1, min(200, (int)$r->input('per_page', 50)));
        $q       = trim((string)$r->input('q', ''));
        $from    = $r->input('date_from');
        $to      = $r->input('date_to');

        $tbl = DB::connection('scrapingdb')
            ->table('youtube_comments1')
            ->orderByDesc('scraped_at')
            ->orderByDesc('id');

        if ($q !== '') {
            $like = "%{$q}%";
            $tbl->where(function ($w) use ($like) {
                $w->where('comment_text',  'like', $like)
                ->orWhere('cleaned_text','like', $like)
                ->orWhere('video_title', 'like', $like)
                ->orWhere('channel_name','like', $like)
                ->orWhere('author_name', 'like', $like);
            });
        }
        if ($from) $tbl->whereDate('published_at', '>=', $from);
        if ($to)   $tbl->whereDate('published_at', '<=', $to);

        $p = $tbl->paginate(
            $perPage,
            ['id','video_title','video_url','channel_name','comment_text','cleaned_text','author_name','likes_count','replies_count','published_at','scraped_at','processing_status'],
            'page',
            $page
        );

        return response()->json([
            'data' => $p->items(),
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
                'per_page'     => $p->perPage(),
                'total'        => $p->total(),
            ],
        ]);
    }



    public function searchNews(Request $request)
    {
        $request->validate([
            'keywords' => 'required|string',
            'source' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date'
        ]);
        
        // Simulate search and scraping
        $results = [];
        for ($i = 1; $i <= 10; $i++) {
            $results[] = [
                'id' => $i,
                'title' => 'Search Result ' . $i . ' - ' . $request->keywords,
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'source' => $request->source ?? 'detik.com',
                'url' => 'https://example.com/news/' . $i,
                'published_at' => now()->subDays(rand(1, 7)),
                'status' => 'success'
            ];
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Search completed successfully',
            'data' => $results,
            'total' => count($results)
        ]);
    }

    public function searchAll(Request $r)
    {
        $q         = trim((string) $r->input('q', ''));
        $dateFrom  = $r->input('date_from'); // format 'YYYY-MM-DD'
        $dateTo    = $r->input('date_to');   // format 'YYYY-MM-DD'

        Log::info('HIT searchAll', ['q' => $q, 'date_from' => $dateFrom, 'date_to' => $dateTo]);

        // Kalau SEMUANYA kosong (tidak ada q dan tidak pilih tanggal), kembalikan kosong agar UI balik ke mode normal
        if ($q === '' && !$dateFrom && !$dateTo) {
            return response()->json([
                'data' => [],
                'meta' => ['total' => 0]
            ]);
        }

        $HARD_LIMIT = (int) $r->input('limit', 2000);

        $rows = DB::connection('scrapingdb')
            ->table('news_quotes')
            // filter kata kunci (opsional)
            ->when($q !== '', function ($w) use ($q) {
                $like = '%'.$q.'%';
                $w->where(function ($x) use ($like) {
                    $x->where('original_text', 'like', $like)
                    ->orWhere('cleaned_text',  'like', $like)
                    ->orWhere('speaker',       'like', $like)
                    ->orWhere('context',       'like', $like)
                    ->orWhere('news_source',   'like', $like)
                    ->orWhere('news_url',      'like', $like);
                });
            })
            // filter rentang tanggal (opsional) pakai published_at
            ->when($dateFrom || $dateTo, function ($w) use ($dateFrom, $dateTo) {
                $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
                $to   = $dateTo   ? Carbon::parse($dateTo)->endOfDay()   : null;

                if ($from && $to) {
                    $w->whereBetween('published_at', [$from, $to]);
                } elseif ($from) {
                    $w->where('published_at', '>=', $from);
                } elseif ($to) {
                    $w->where('published_at', '<=', $to);
                }
            })
            ->orderByDesc('scraped_at')
            ->orderByDesc('id')
            ->limit($HARD_LIMIT)
            ->get([
                'id','original_text','cleaned_text','speaker',
                'news_url','news_source','context',
                'published_at','scraped_at','processing_status','created_at'
            ]);

        return response()->json([
            'data' => $rows,
            'meta' => ['total' => $rows->count()]
        ]);
    }



//     public function searchAll(Request $r)
//     {
//         // q boleh kosong, tapi kalau kosong kita balikan kosong (biar UI balik ke mode normal)
//         $q = trim((string) $r->input('q', ''));

//         \Log::info('HIT searchAll', ['q' => $q]);


//         if ($q === '') {
//             return response()->json([
//                 'data' => [],
//                 'meta' => ['total' => 0]
//             ]);
//         }

//     // BATAS AMAN agar tidak jebol browser (silakan ganti sesuai kebutuhan)
//     $HARD_LIMIT = (int) $r->input('limit', 2000);

//     $rows = \DB::table('news_quotes')
//         ->when($q, function ($w) use ($q) {
//             $like = '%'.$q.'%';
//             $w->where(function ($x) use ($like) {
//                 $x->where('original_text', 'like', $like)
//                   ->orWhere('cleaned_text',  'like', $like)
//                   ->orWhere('speaker',       'like', $like)
//                   ->orWhere('context',       'like', $like)
//                   ->orWhere('news_source',   'like', $like)
//                   ->orWhere('news_url',      'like', $like);
//             });
//         })
//         ->orderByDesc('scraped_at')   // urut terbaru scrapped di atas
//         ->orderByDesc('id')
//         ->limit($HARD_LIMIT)
//         ->get([
//             'id','original_text','cleaned_text','speaker',
//             'news_url','news_source','context',
//             'published_at','scraped_at','processing_status','created_at'
//         ]);

//     return response()->json([
//         'data' => $rows,
//         'meta' => ['total' => $rows->count()]
//     ]);
// }
    

    public function item($id)
    {
        $row = DB::table('news_quotes')->where('id', $id)->first();

        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Kalau context kamu simpan JSON, boleh decode pelan-pelan.
        $title = null;
        if (!empty($row->context)) {
            $decoded = json_decode($row->context, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['title'])) {
                $title = $decoded['title'];
            } else {
                // kalau context string judul biasa (bukan JSON), pakai langsung
                $title = $row->context;
            }
        }

        return response()->json([
            'data' => [
                'id'                => $row->id,
                'original_text'     => $row->original_text,
                'cleaned_text'      => $row->cleaned_text,
                'speaker'           => $row->speaker,
                'news_url'          => $row->news_url,
                'news_source'       => $row->news_source,
                'title'             => $title,
                'published_at'      => $row->published_at,
                'scraped_at'        => $row->scraped_at,
                'processing_status' => $row->processing_status,
            ]
        ]);
    }

public function destroy($id)
{
    try {
        $deleted = DB::table('news_quotes')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan'], 404);
        }
        return response()->json(['success' => true], 200);
    } catch (\Throwable $e) {
        Log::error('newsDestroy error', ['e' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Server error'], 500);
    }
}

public function bulkDestroy(Request $request)
{
    $data = $request->validate([
        'ids'   => 'required|array|min:1',
        'ids.*' => 'integer'
    ]);

    try {
        $deleted = DB::table('news_quotes')->whereIn('id', $data['ids'])->delete();
        return response()->json(['success' => true, 'deleted' => $deleted], 200);
    } catch (\Throwable $e) {
        Log::error('newsBulkDestroy error', ['e' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Server error'], 500);
    }
}

//YOUTUBE
    // public function scrapeYoutubeByLinks(Request $r) {
    //     $data = $r->validate(['links'=>'required|string']);
    //     $urls = collect(preg_split('/\r?\n/', trim($data['links'])))->filter()->unique();
    //     foreach ($urls as $url) ScrapeYoutubeLinksJob::dispatch((string)$url, auth()->id() ?? 0);
    //     return back()->with('ok','Scraping komentar YouTube dimulai ('.count($urls).' URL).');
    // }
    public function scrapeYoutubeByLinks(Request $r) {
        // Terima banyak alias: links | youtubeLinks | youtube_urls
        $r->merge([
            'links' => $r->input('links')
                ?? $r->input('youtubeLinks')
                ?? $r->input('youtube_urls')
        ]);

        $data = $r->validate([
            'links' => 'required|string'
        ]);

        $urls = collect(preg_split('/\r?\n/', trim($data['links'])))->filter()->unique();

        foreach ($urls as $url) {
       ScrapeYoutubeLinksJob::dispatch((string) $url, Auth::id() ?? 0);
      }


        return back()->with('ok', 'Scraping komentar YouTube dimulai ('.count($urls).' URL).');
    }

public function youtubeDestroy($id)
{
    try {
        $deleted = DB::connection('scrapingdb')
            ->table('youtube_comments1')
            ->where('id', $id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false, 
                'message' => 'Item tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil dihapus'
        ], 200);
        
    } catch (\Throwable $e) {
        Log::error('youtubeDestroy error', ['id' => $id, 'error' => $e->getMessage()]);
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus: ' . $e->getMessage()
        ], 500);
    }
}

public function youtubeBulkDestroy(Request $request)
{
    try {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer'
        ]);
        $deleted = DB::table('youtube_comments1')->whereIn('id', $data['ids'])->delete();

        return response()->json(['success'=>true,'deleted'=>$deleted], 200);
    } catch (\Throwable $e) {
        Log::error('youtubeBulkDestroy error', ['e'=>$e->getMessage()]);
        return response()->json(['success'=>false,'message'=>'Server error'], 500);
    }
}


    public function youtubeItem($id)
    {
        $row = DB::table('youtube_comments1')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // siapkan host dari URL (aman jika URL kosong)
        $host = null;
        try { if (!empty($row->video_url)) $host = parse_url($row->video_url, PHP_URL_HOST); } catch (\Throwable $e) {}

        return response()->json([
            'data' => [
                'id'               => $row->id,
                'video_title'      => $row->video_title,
                'video_url'        => $row->video_url,
                'channel_name'     => $row->channel_name,
                'author_name'      => $row->author_name,        // ← ini author, bukan channel
                'comment_text'     => $row->comment_text,
                'cleaned_text'     => $row->cleaned_text,
                'likes_count'      => $row->likes_count,
                'replies_count'    => $row->replies_count,
                'published_at'     => $row->published_at,
                'scraped_at'       => $row->scraped_at,
                'processing_status'=> $row->processing_status,
                'host'             => $host,
            ]
        ]);
    }

    // app/Http/Controllers/ScrapingController.php

   public function checkProgress(Request $request)
    {
        $jobId = $request->input('job_id');
        
        if (!$jobId) {
            return response()->json(['error' => 'Job ID required'], 400);
        }

        $progress = Cache::get("scraping_progress_{$jobId}", [
            'current' => 0,
            'total' => 0,
            'completed' => 0,
            'failed' => 0,
            'status' => 'unknown',
            'message' => 'Data tidak ditemukan',
            'details' => []
        ]);

        return response()->json($progress);
    }

    

}
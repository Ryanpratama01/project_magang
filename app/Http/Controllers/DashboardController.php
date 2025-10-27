<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) ($request->input('year') ?: $this->availableYears()[0] ?? now()->year);
        $q    = trim((string) $request->input('q', ''));
        $src  = in_array($request->input('source'), ['news', 'youtube']) ? $request->input('source') : 'news';

        // paket data utama
        $stats         = $this->buildStats();
        $monthlyTrend  = $this->buildMonthlyTrend($year);
        $youtubeVideos = $this->getYoutubeVideos();
        $newsData      = $this->getNewsList($q, 10); // tabel berita (dengan search kalau ada $q)
        $totalAll  = $this->countAllNews();

        return view('dashboard', [
            'stats'         => $stats,
            'monthlyTrend'  => $monthlyTrend,
            'youtubeVideos' => $youtubeVideos,
            'newsData'      => $newsData,
            'totalAll'      => $totalAll, // ← penting
            'q'             => $q,
            'source'        => $src,
        ]);
    }




    // ===== AJAX data (tanpa reload halaman) =====
    public function data(Request $request)
    {
        $year = (int) ($request->input('year') ?: $this->availableYears()[0] ?? now()->year);
        $q    = trim((string) $request->input('q', ''));
        $src  = in_array($request->input('source'), ['news', 'youtube']) ? $request->input('source') : 'news';

        // Mode tampil
        $show  = (string) $request->input('show', ''); // '', 'all', 'top10'
        $limit = match ($show) {
            'all'   => 200,  // bisa dinaikkan sesuai kebutuhan
            'top10' => 10,
            default => 50,
        };

        $newsData   = $this->getNewsList($q, $limit);
        $totalAll   = $this->countAllNews();         // total keseluruhan tabel (tanpa filter)
        $returned   = $newsData->count();

        $newsTableHtml = view('partials._news_table_dashboard', [
            'newsData' => $newsData,
            'q'        => $q,
        ])->render();

        $stats        = $this->buildStats();
        $monthlyTrend = $this->buildMonthlyTrend($year);

        return response()->json([
            'newsTableHtml' => $newsTableHtml,
            'q'             => $q,
            'year'          => $year,
            'source'        => $src,
            'summary'       => $stats,
            'trend'         => $monthlyTrend,
            'pie'           => $stats['distribution'] ?? null,

            // ⬇️ tambahan untuk counter
            'total_all'     => (int) $totalAll,
            'returned'      => (int) $returned,
            'mode'          => $show === 'all' ? 'all' : ($show === 'top10' ? 'top10' : 'default'),
        ]);
    }




    // ================== Helpers ==================

    /** Tahun gabungan (news & youtube), desc */
    private function availableYears(): array
    {
        $yearsNews = DB::table('news_articles')
            ->selectRaw('DISTINCT YEAR(COALESCE(published_at, created_at)) AS y')->pluck('y')->filter()->all();

        $yearsYt = DB::table('youtube_comments')
            ->selectRaw('DISTINCT YEAR(published_at) AS y')->pluck('y')->filter()->all();

        return collect(array_unique(array_merge($yearsNews, $yearsYt)))
            ->sortDesc()->values()->all();
    }

    private function availableYearsNews(): array
    {
        return DB::table('news_articles')
            ->selectRaw('DISTINCT YEAR(COALESCE(published_at, created_at)) AS y')
            ->pluck('y')->filter()->sortDesc()->values()->all();
    }

    private function availableYearsYoutube(): array
    {
        return DB::table('youtube_comments')
            ->selectRaw('DISTINCT YEAR(published_at) AS y')
            ->pluck('y')->filter()->sortDesc()->values()->all();
    }


    /** Kartu ringkasan + distribusi pie (news & youtube) */
    private function buildStats(): array
    {
        // --- YouTube summary (pakai COUNTS) ---
        $latestSub = DB::table('comment_sentiments')
            ->select('comment_fk', DB::raw('MAX(analyzed_at) AS max_at'))
            ->groupBy('comment_fk');

        $ytTotal = (int) DB::table('youtube_comments')->count();
        $ytLabelCounts = DB::table('comment_sentiments AS cs')
            ->joinSub($latestSub, 'latest', function ($j) {
                $j->on('cs.comment_fk', '=', 'latest.comment_fk')
                    ->on('cs.analyzed_at', '=', 'latest.max_at');
            })
            ->select('cs.label', DB::raw('COUNT(*) AS n'))
            ->groupBy('cs.label')
            ->pluck('n', 'label');

        $ytPos = (int) ($ytLabelCounts['positive'] ?? 0);
        $ytNeu = (int) ($ytLabelCounts['neutral']  ?? 0);
        $ytNeg = (int) ($ytLabelCounts['negative'] ?? 0);

        $youtubeSummary = [
            'total'    => $ytTotal,
            'positive' => $ytPos,
            'neutral'  => $ytNeu,
            'negative' => $ytNeg,
        ];
        $distYoutube = [
            'pos' => $ytPos,
            'neu' => $ytNeu,
            'neg' => $ytNeg,
        ];

        // --- News summary (pakai COUNTS) ---
        $newsDistRaw = DB::table('v_news_distribution_all')
            ->select('label', DB::raw('SUM(n) AS n'))
            ->groupBy('label')
            ->pluck('n', 'label');

        $newsPos = (int) ($newsDistRaw['positive'] ?? 0);
        $newsNeu = (int) ($newsDistRaw['neutral']  ?? 0);
        $newsNeg = (int) ($newsDistRaw['negative'] ?? 0);
        $newsTotal = $newsPos + $newsNeu + $newsNeg;

        $newsSummary = [
            'total'    => $newsTotal,
            'positive' => $newsPos,
            'neutral'  => $newsNeu,
            'negative' => $newsNeg,
        ];

        return [
            'news'    => $newsSummary,
            'youtube' => $youtubeSummary,
            'distribution' => [
                'news'    => ['pos' => $newsPos, 'neu' => $newsNeu, 'neg' => $newsNeg],
                'youtube' => $distYoutube,
            ],
        ];
    }

    /** Data bulanan per sumber untuk bar chart (berdasarkan tahun pilih) */
    private function buildMonthlyTrend(int $year): array
    {
        // daftar tahun per sumber (untuk dropdown dinamis)
        $yearsNews = DB::table('news_articles')
            ->selectRaw('DISTINCT YEAR(COALESCE(published_at, created_at)) AS y')
            ->pluck('y')->filter()->sortDesc()->values()->all();

        $yearsYt = DB::table('youtube_comments')
            ->selectRaw('DISTINCT YEAR(published_at) AS y')
            ->pluck('y')->filter()->sortDesc()->values()->all();

        // label 12 bulan (YYYY-MM)
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = sprintf('%04d-%02d', $year, $m);
        }

        // NEWS (agregasi per artikel x label join view kutipan)
        $rowsNews = DB::table('news_articles as a')
            ->leftJoin('v_news_quotes_with_pubdate as q', 'q.article_fk', '=', 'a.id')
            ->whereYear(DB::raw('COALESCE(a.published_at, a.created_at)'), $year)
            ->selectRaw("
            DATE_FORMAT(COALESCE(a.published_at, a.created_at), '%Y-%m') AS ym,
            COALESCE(q.label, 'neutral') AS label,
            COUNT(*) AS n
        ")
            ->groupBy('ym', 'label')
            ->get();

        $mapNews = [];
        foreach ($rowsNews as $r) {
            $mapNews[$r->ym][$r->label] = (int) $r->n;
        }
        $monthlyTrendNews = [
            'labels'   => $months,
            'positive' => array_map(fn ($m) => $mapNews[$m]['positive'] ?? 0, $months),
            'neutral'  => array_map(fn ($m) => $mapNews[$m]['neutral']  ?? 0, $months),
            'negative' => array_map(fn ($m) => $mapNews[$m]['negative'] ?? 0, $months),
        ];

        // YOUTUBE (label terbaru per komentar)
        $rowsYt = DB::table('comment_sentiments AS cs')
            ->join(
                DB::raw("(SELECT comment_fk, MAX(analyzed_at) max_at FROM comment_sentiments GROUP BY comment_fk) latest"),
                fn ($j) => $j
                    ->on('cs.comment_fk', '=', 'latest.comment_fk')
                    ->on('cs.analyzed_at', '=', 'latest.max_at')
            )
            ->join('youtube_comments AS yc', 'yc.id', '=', 'cs.comment_fk')
            ->whereYear('yc.published_at', $year)
            ->selectRaw("DATE_FORMAT(yc.published_at, '%Y-%m') AS ym, cs.label, COUNT(*) AS n")
            ->groupBy('ym', 'cs.label')->get();

        $mapYt = [];
        foreach ($rowsYt as $r) {
            $mapYt[$r->ym][$r->label] = (int) $r->n;
        }
        $monthlyTrendYoutube = [
            'labels'   => $months,
            'positive' => array_map(fn ($m) => $mapYt[$m]['positive'] ?? 0, $months),
            'neutral'  => array_map(fn ($m) => $mapYt[$m]['neutral']  ?? 0, $months),
            'negative' => array_map(fn ($m) => $mapYt[$m]['negative'] ?? 0, $months),
        ];

        return [
            'labels'        => $months,
            'news'          => $monthlyTrendNews,
            'youtube'       => $monthlyTrendYoutube,
            'year'          => $year,
            // fallback gabungan (optional)
            'years'         => array_values(array_unique(array_merge($yearsNews, $yearsYt))),
            // ⬇️ dipakai JS untuk dropdown per sumber
            'years_news'    => $yearsNews,
            'years_youtube' => $yearsYt,
        ];
    }



    /** 5 video + 5 komentar per video (ringkas untuk tabel YouTube) */
    private function getYoutubeVideos(): array
    {
        $latestSub = DB::table('comment_sentiments')
            ->select('comment_fk', DB::raw('MAX(analyzed_at) AS max_at'))
            ->groupBy('comment_fk');

        $videos = DB::table('youtube_videos AS v')
            ->join('youtube_channels AS c', 'c.id', '=', 'v.channel_id_fk')
            ->select('v.id', 'v.video_id', 'v.title', 'c.name AS channel_title')
            ->orderBy('v.published_at', 'desc')
            ->limit(5)->get();

        $out = [];
        foreach ($videos as $v) {
            $comments = DB::table('youtube_comments AS yc')
                ->leftJoinSub($latestSub, 'latest', fn ($j) => $j->on('yc.id', '=', 'latest.comment_fk'))
                ->leftJoin('comment_sentiments AS cs', fn ($j) => $j->on('yc.id', '=', 'cs.comment_fk')->on('cs.analyzed_at', '=', 'latest.max_at'))
                ->where('yc.video_fk', $v->id)
                ->orderBy('yc.published_at', 'desc')->limit(5)
                ->get([
                    'yc.comment_text AS text',
                    DB::raw("COALESCE(cs.label, 'unknown') AS sentiment"),
                ]);

            $out[] = [
                'id'       => $v->id,
                'title'    => $v->title,
                'channel'  => $v->channel_title,
                'comments' => $comments->map(fn ($r) => ['text' => $r->text, 'sentiment' => $r->sentiment])->all(),
            ];
        }
        return $out;
    }

    private function countAllNews(): int
    {
        return (int) DB::table('news_articles')->count();
    }


    private function getNewsList(string $q, int $limit = 50)
    {
        if ($q === '') {
            return DB::table('news_articles')
                ->select('id', 'url', 'title', 'summary_clean', 'published_at', 'created_at')
                ->orderByRaw('COALESCE(published_at, created_at) DESC')
                ->limit($limit)->get();
        }

        $coll = 'utf8mb4_unicode_ci';
        $like = "%{$q}%";

        if (mb_strlen($q) < 3) {
            return DB::table('news_articles')
                ->select('id', 'url', 'title', 'summary_clean', 'published_at', 'created_at')
                ->where(function ($w) use ($like, $coll) {
                    $w->whereRaw("title COLLATE {$coll} LIKE ?", [$like])
                        ->orWhereRaw("summary_clean COLLATE {$coll} LIKE ?", [$like]);
                })
                ->orderByRaw('COALESCE(published_at, created_at) DESC')
                ->limit($limit)->get();
        }

        return DB::table('news_articles')
            ->selectRaw("
                id, url, title, summary_clean, published_at, created_at,
                (MATCH(title) AGAINST(? IN NATURAL LANGUAGE MODE) * 2 +
                 MATCH(summary_clean) AGAINST(? IN NATURAL LANGUAGE MODE)) AS score,
                (title COLLATE {$coll} LIKE ?) AS title_exact
            ", [$q, $q, $like])
            ->whereRaw("MATCH(title, summary_clean) AGAINST(? IN NATURAL LANGUAGE MODE)", [$q])
            ->orderByDesc('title_exact')
            ->orderByDesc('score')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit($limit)->get();
    }
}

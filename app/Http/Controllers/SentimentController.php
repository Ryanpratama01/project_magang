<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class SentimentController extends Controller
{
    public function index()
    {
        // Stats
        $totalBerita = DB::table('news_articles')->count();
        $sudahDianalisis = DB::table('news_article_sentiments')->count();
        $articlesWithSentiment = DB::table('news_article_sentiments')
            ->distinct('article_fk')
            ->whereNotNull('article_fk')
            ->count('article_fk');
        $menunggu = max(0, $totalBerita - $articlesWithSentiment);

        $batchStats = [
            'total'    => $totalBerita,
            'analyzed' => $sudahDianalisis,
            'pending'  => $menunggu,
        ];

        // Data tabel + PAGINATION
        $newsSentiments = DB::table('news_article_sentiments')
            ->select(
                'id',
                'article_idx',
                Schema::hasColumn('news_article_sentiments', 'article_fk')
                    ? 'article_fk'
                    : DB::raw('NULL as article_fk'),
                'quote_text',
                'label',
                'score_positive',
                'score_negative',
                'score_neutral',
                'created_at',
                'updated_at'
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('sentiment', compact('batchStats', 'newsSentiments'));
    }

    public function analyze(Request $request)
    {
        try {
            $request->validate(['text' => 'required|string|min:10']);
            $text = trim($request->input('text'));

            $p = $this->predict($text);
            $confidence = (int) round(max([$p['pos'], $p['neg'], $p['neu']]) * 100);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => true,
                'message' => 'ok.',
                'data' => [
                    'text' => $text,
                    'sentiment' => $p['label'],
                    'confidence' => $confidence,
                    'keywords' => $this->extractKeywords($text),
                    'processed_at' => now()->toIso8601String(),
                    'model' => 'rule-stub',
                ],
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $rawText = trim($request->input('text'));
            $normalized = $this->normalizeText($rawText);

            $useHash = Schema::hasColumn('news_article_sentiments', 'quote_hash');
            $hash = $useHash ? hash('sha256', $normalized) : null;

            $orderCol = Schema::hasColumn('news_article_sentiments', 'id') ? 'id'
                : (Schema::hasColumn('news_article_sentiments', 'analyzed_at') ? 'analyzed_at'
                    : (Schema::hasColumn('news_article_sentiments', 'created_at') ? 'created_at' : null));

            $q = DB::table('news_article_sentiments');
            $useHash ? $q->where('quote_hash', $hash) : $q->where('quote_text', $rawText);
            $row = $orderCol ? $q->orderByDesc($orderCol)->first() : $q->first();

            if (!$row) {
                $pred = $this->predict($rawText);
                if (!$pred || !isset($pred['label'])) {
                    return response()->json(['success' => false, 'message' => 'Gagal melakukan prediksi.'], 500);
                }

                $insert = [
                    'quote_text' => $rawText,
                    'label' => $pred['label'],
                    'score_positive' => (float) $pred['pos'],
                    'score_negative' => (float) $pred['neg'],
                    'score_neutral' => (float) $pred['neu'],
                ];

                if ($useHash) $insert['quote_hash'] = $hash;
                if (Schema::hasColumn('news_article_sentiments', 'model')) $insert['model'] = $pred['model'] ?? 'rule-stub';
                if (Schema::hasColumn('news_article_sentiments', 'analyzed_at')) $insert['analyzed_at'] = Carbon::now();
                if (Schema::hasColumn('news_article_sentiments', 'created_at')) $insert['created_at'] = Carbon::now();
                if (Schema::hasColumn('news_article_sentiments', 'updated_at')) $insert['updated_at'] = Carbon::now();
                if (Schema::hasColumn('news_article_sentiments', 'article_fk')) $insert['article_fk'] = $insert['article_fk'] ?? 0;

                $table = DB::table('news_article_sentiments');

                try {
                    if (Schema::hasColumn('news_article_sentiments', 'id')) {
                        $id = $table->insertGetId($insert);
                        $row = $table->where('id', $id)->first();
                    } else {
                        $ok = $table->insert($insert);
                        if (!$ok) throw new \RuntimeException('Insert gagal.');
                        $row = $orderCol ? $table->orderByDesc($orderCol)->first() : (object) $insert;
                    }
                } catch (QueryException $qe) {
                    Log::warning('Insert failed (mungkin VIEW/non-insertable). Mengembalikan hasil tanpa save.', [
                        'error' => $qe->getMessage()
                    ]);
                    $row = (object) $insert;
                }
            }

            $scores = [
                (float)($row->score_positive ?? 0),
                (float)($row->score_negative ?? 0),
                (float)($row->score_neutral ?? 0),
            ];
            $confidence = (int) round(max($scores) * 100);

            return response()->json([
                'success' => true,
                'message' => 'Analysis completed successfully',
                'data' => [
                    'text' => $row->quote_text ?? $rawText,
                    'sentiment' => $row->label ?? 'neutral',
                    'confidence' => $confidence,
                    'keywords' => $this->extractKeywords($row->quote_text ?? $rawText),
                    'processed_at' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Analyze error', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat analisis.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** Alias */
    public function analyzeManual(Request $request) { return $this->analyze($request); }

    /* ========================= Batch YouTube ========================= */

    public function analyzeBatchYoutube(Request $request)
    {
        $request->validate([
            'data_source' => 'required|in:uploaded,scraped,database',
            'file' => 'required_if:data_source,uploaded|file|mimes:csv,txt,xlsx,xls|max:20480',
            'batch_size' => 'nullable|integer|min:1|max:1000',
            'priority' => 'nullable|in:low,normal,high',
        ]);

        if ($request->input('data_source') !== 'uploaded') {
            return response()->json([
                'success' => false,
                'message' => 'Sumber data bukan file upload.',
            ], 422);
        }

        $file = $request->file('file');
        if (!$file) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan.'], 422);
        }

        try {
            [$items, $meta] = $this->parseUploadedFileToTexts($file);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage(),
            ], 422);
        }

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada baris teks valid yang ditemukan pada file.',
            ], 422);
        }

        [$inserted, $skipped] = $this->insertBatchYoutubeSentiments($items);

        return response()->json([
            'success' => true,
            'message' => 'File batch YouTube diproses.',
            'data' => [
                'filename' => $meta['filename'],
                'size_kb' => $meta['size_kb'],
                'rows' => $meta['rows'],
                'inserted' => $inserted,
                'skipped' => $skipped,
            ],
        ]);
    }

    private function insertBatchYoutubeSentiments(array $items): array
    {
        $useHash = Schema::hasColumn('youtube_comment_sentiments', 'comment_hash');
        $now = Carbon::now();
        $inserted = 0; $skipped = 0;

        foreach ($items as $item) {
            $commentText = trim((string)($item['text'] ?? ''));
            $url = isset($item['url']) ? trim((string)$item['url']) : null;

            if ($commentText === '' || mb_strlen($commentText) < 3) { $skipped++; continue; }

            $normalized = $this->normalizeText($commentText);
            $hash = $useHash ? hash('sha256', $normalized) : null;

            $q = DB::table('youtube_comment_sentiments');
            if ($useHash && $hash) $q->where('comment_hash', $hash);
            else $q->where('comment_text', $commentText);

            if ($q->exists()) { $skipped++; continue; }

            $pred = $this->predict($commentText);
            if (!$pred || !isset($pred['label'])) { $skipped++; continue; }

            $videoFk = null;
            if ($url) {
                $video = DB::table('youtube_videos')->where('video_url', $url)->first();
                if ($video) $videoFk = $video->id;
            }
            if (!$videoFk) $videoFk = DB::table('youtube_videos')->orderBy('id')->value('id');

            $row = [
                'comment_text' => $commentText,
                'label' => $pred['label'],
                'score_positive' => (float) $pred['pos'],
                'score_negative' => (float) $pred['neg'],
                'score_neutral'  => (float) $pred['neu'],
            ];

            if ($useHash) $row['comment_hash'] = $hash;
            if (Schema::hasColumn('youtube_comment_sentiments', 'video_fk'))  $row['video_fk'] = $videoFk;
            if (Schema::hasColumn('youtube_comment_sentiments', 'comment_fk')) $row['comment_fk'] = null;
            if (Schema::hasColumn('youtube_comment_sentiments', 'model'))      $row['model'] = $pred['model'] ?? 'rule-stub';
            if (Schema::hasColumn('youtube_comment_sentiments', 'analyzed_at')) $row['analyzed_at'] = $now;
            if (Schema::hasColumn('youtube_comment_sentiments', 'created_at'))  $row['created_at'] = $now;
            if (Schema::hasColumn('youtube_comment_sentiments', 'updated_at'))  $row['updated_at'] = $now;

            try {
                DB::table('youtube_comment_sentiments')->insert($row);
                $inserted++;
            } catch (\Exception $e) {
                Log::error('❌ Insert YouTube failed', ['error' => $e->getMessage(), 'text' => substr($commentText, 0, 30)]);
                $skipped++;
            }
        }

        return [$inserted, $skipped];
    }

    public function getYoutubeSentiments()
    {
        $sentiments = DB::table('youtube_comment_sentiments')
            ->select('id','video_fk','comment_text','label','score_positive','score_negative','score_neutral','created_at')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $sentiments]);
    }

    /* ========================= Batch News ========================= */

    public function analyzeBatch(Request $request)
    {
        $request->validate([
            'data_source' => 'required|in:uploaded,scraped,database',
            'file' => 'required_if:data_source,uploaded|file|mimes:csv,txt,xlsx,xls|max:20480',
            'batch_size' => 'nullable|integer|min:1|max:1000',
            'priority' => 'nullable|in:low,normal,high',
        ]);

        if ($request->input('data_source') !== 'uploaded') {
            return response()->json([
                'success' => false,
                'message' => 'Sumber data bukan file upload.',
            ], 422);
        }

        $file = $request->file('file');
        if (!$file) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan.'], 422);
        }

        try { [$items, $meta] = $this->parseUploadedFileToTexts($file); }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage(),
            ], 422);
        }

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada baris teks valid yang ditemukan pada file.',
            ], 422);
        }

        [$inserted, $skipped] = $this->insertBatchSentiments($items);

        return response()->json([
            'success' => true,
            'message' => 'File batch diproses.',
            'data' => [
                'filename' => $meta['filename'],
                'size_kb' => $meta['size_kb'],
                'rows' => $meta['rows'],
                'inserted' => $inserted,
                'skipped' => $skipped,
            ],
        ]);
    }

    public function getSentiments()
    {
        $sentiments = DB::table('news_article_sentiments')
            ->select(
                'id',
                'article_idx',
                Schema::hasColumn('news_article_sentiments', 'article_fk')
                    ? 'article_fk'
                    : DB::raw('NULL as article_fk'),
                'quote_text',
                'label',
                'score_positive',
                'score_negative',
                'score_neutral',
                'created_at'
            )
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $sentiments]);
    }

    private function parseUploadedFileToTexts(\Illuminate\Http\UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $sizeKb = (int) round($file->getSize() / 1024);
        $name = $file->getClientOriginalName();

        $texts = []; $rows = 0; $urls = [];

        if (in_array($ext, ['csv', 'txt'])) {
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle === false) throw new \RuntimeException('Tidak dapat membuka file.');

            $firstLine = fgetcsv($handle);
            if ($firstLine === false) {
                fclose($handle);
                return [[], ['filename' => $name, 'size_kb' => $sizeKb, 'rows' => 0]];
            }

            $possibleHeaders = [
                'text','teks','content','konten','isi','isi_berita','body',
                'article','article_text','quote_text','quote','kalimat','berita',
                'deskripsi','description'
            ];

            $hasHeader = false;
            if (is_array($firstLine)) {
                $lower = array_map(fn($v) => strtolower(trim((string)$v)), $firstLine);
                $hasHeader = count(array_intersect($lower, $possibleHeaders)) > 0;
            }

            $urlIdx = false;
            if ($hasHeader) {
                $urlHeaderCandidates = ['url','link','source_url','article_url'];
                foreach ($urlHeaderCandidates as $cand) {
                    $pos = array_search($cand, $lower, true);
                    if ($pos !== false) { $urlIdx = $pos; break; }
                }
            }

            $textIdx = 0;
            if ($hasHeader) {
                $map = array_flip(array_map(fn($v) => strtolower(trim((string)$v)), $firstLine));
                foreach ($possibleHeaders as $h) {
                    if (isset($map[$h])) { $textIdx = (int)$map[$h]; break; }
                }
            } else {
                $val = is_array($firstLine) ? trim((string)($firstLine[0] ?? '')) : trim((string)$firstLine);
                if ($val !== '') { $texts[] = $val; $urls[] = null; $rows++; }
            }

            if (isset($lower) && $hasHeader && $textIdx === 0
                && !in_array('text', $lower) && !in_array('quote_text', $lower)
                && !in_array('quote', $lower) && !in_array('content', $lower)
                && !in_array('konten', $lower) && !in_array('isi', $lower)
                && !in_array('isi_berita', $lower) && !in_array('body', $lower)
                && !in_array('kalimat', $lower) && !in_array('berita', $lower)
                && !in_array('deskripsi', $lower) && !in_array('description', $lower)
            ) {
                foreach ($lower as $idx => $name2) {
                    if (strpos($name2, 'content') !== false || strpos($name2, 'isi') !== false || strpos($name2, 'teks') !== false) {
                        $textIdx = (int)$idx; break;
                    }
                }
            }

            while (($row = fgetcsv($handle)) !== false) {
                $val = trim((string)($row[$textIdx] ?? ''));
                if ($val !== '') {
                    $texts[] = $val;
                    $urls[] = ($hasHeader && $urlIdx !== false) ? trim((string)($row[$urlIdx] ?? '')) : null;
                }
                $rows++;
            }

            fclose($handle);
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                throw new \RuntimeException('Parser Excel tidak tersedia. Instal phpoffice/phpspreadsheet atau gunakan CSV.');
            }

            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $firstRow = $sheet->rangeToArray('A1:Z1', null, true, true, true)[1] ?? [];
            $lower = array_map(fn($v) => strtolower(trim((string)$v)), array_values($firstRow));
            $map = array_flip($lower);
            $possibleHeaders = ['text','quote_text','quote','content','kalimat','berita'];

            $textColLetter = null;
            foreach ($possibleHeaders as $h) {
                if (isset($map[$h])) {
                    $keys = array_keys($firstRow);
                    $textColLetter = $keys[$map[$h]] ?? 'A';
                    break;
                }
            }
            $startRow = $textColLetter ? 2 : 1;

            $highestRow = $sheet->getHighestRow();
            $textCol = $textColLetter ?: 'A';

            for ($r = $startRow; $r <= $highestRow; $r++) {
                $val = trim((string)$sheet->getCell($textCol.$r)->getValue());
                if ($val !== '') { $texts[] = $val; }
                $rows++;
            }
        } else {
            throw new \RuntimeException('Ekstensi tidak didukung.');
        }

        $items = [];
        foreach ($texts as $i => $t) { $items[] = ['text' => $t, 'url' => $urls[$i] ?? null]; }

        return [$items, ['filename' => $name, 'size_kb' => $sizeKb, 'rows' => $rows]];
    }

    private function insertBatchSentiments(array $items): array
    {
        $useHash = Schema::hasColumn('news_article_sentiments', 'quote_hash');
        $now = Carbon::now();
        $inserted = 0; $skipped = 0;

        foreach ($items as $item) {
            $rawText = trim((string)($item['text'] ?? ''));
            $url = isset($item['url']) ? trim((string)$item['url']) : null;

            if ($rawText === '' || mb_strlen($rawText) < 3) { $skipped++; continue; }

            $normalized = $this->normalizeText($rawText);
            $hash = $useHash ? hash('sha256', $normalized) : null;

            $q = DB::table('news_article_sentiments');
            if ($useHash && $hash) $q->where('quote_hash', $hash);
            else $q->where('quote_text', $rawText);

            if ($q->exists()) { $skipped++; continue; }

            $pred = $this->predict($rawText);
            if (!$pred || !isset($pred['label'])) { $skipped++; continue; }

            $articleFk = null;
            if ($url) {
                $article = DB::table('news_articles')->where('url', $url)->first();
                if ($article) $articleFk = $article->id;
            }
            if (!$articleFk) $articleFk = DB::table('news_articles')->orderBy('id')->value('id');

            $row = [
                'quote_text' => $rawText,
                'label' => $pred['label'],
                'score_positive' => (float)$pred['pos'],
                'score_negative' => (float)$pred['neg'],
                'score_neutral'  => (float)$pred['neu'],
            ];

            if ($useHash) $row['quote_hash'] = $hash;
            if (Schema::hasColumn('news_article_sentiments', 'article_fk')) $row['article_fk'] = $articleFk ?: 0;
            if (Schema::hasColumn('news_article_sentiments', 'quote_type'))   $row['quote_type'] = 'direct';
            if (Schema::hasColumn('news_article_sentiments', 'speaker'))      $row['speaker'] = null;
            if (Schema::hasColumn('news_article_sentiments', 'paragraph_idx'))$row['paragraph_idx'] = null;
            if (Schema::hasColumn('news_article_sentiments', 'article_idx'))  $row['article_idx'] = null;
            if (Schema::hasColumn('news_article_sentiments', 'model'))        $row['model'] = $pred['model'] ?? 'rule-stub';
            if (Schema::hasColumn('news_article_sentiments', 'analyzed_at'))  $row['analyzed_at'] = $now;
            if (Schema::hasColumn('news_article_sentiments', 'created_at'))   $row['created_at'] = $now;
            if (Schema::hasColumn('news_article_sentiments', 'updated_at'))   $row['updated_at'] = $now;

            try {
                DB::table('news_article_sentiments')->insert($row);
                $inserted++;
            } catch (\Exception $e) {
                Log::error('❌ Insert failed', ['error' => $e->getMessage(), 'text' => substr($rawText, 0, 30), 'row' => $row]);
                $skipped++;
            }
        }

        return [$inserted, $skipped];
    }

    private function normalizeText(string $t): string
    {
        $t = Str::lower($t);
        $t = preg_replace('/\s+/', ' ', $t);
        return trim($t);
    }

    private function predict(string $text): array
    {
        $lx = Str::lower($text);
        $neg = ['rusak','macet','buruk','parah','bahaya','masalah','banjir','lambat'];
        $pos = ['bagus','baik','mantap','puas','bersih','lancar','top','sukses'];

        $negHits = collect($neg)->filter(fn($w) => Str::contains($lx, $w))->count();
        $posHits = collect($pos)->filter(fn($w) => Str::contains($lx, $w))->count();

        if ($negHits > $posHits) return ['label'=>'negative','pos'=>0.05,'neg'=>0.90,'neu'=>0.05,'model'=>'rule-stub'];
        if ($posHits > $negHits) return ['label'=>'positive','pos'=>0.90,'neg'=>0.05,'neu'=>0.05,'model'=>'rule-stub'];
        return ['label'=>'neutral','pos'=>0.10,'neg'=>0.10,'neu'=>0.80,'model'=>'rule-stub'];
    }

    private function extractKeywords(string $text): array
    {
        $t = Str::lower($text);
        $t = preg_replace('/[^a-z0-9\s-]/u', ' ', $t);
        $stop = ['yang','dan','di','ke','dari','atau','untuk','pada','ini','itu','ada','akan','karena','dengan','sudah','belum','banyak','lebih'];

        return collect(explode(' ', $t))
            ->filter()
            ->reject(fn($w) => in_array($w, $stop))
            ->unique()
            ->take(6)
            ->values()
            ->all();
    }

    public function showSentiment($id)
    {
        $row = DB::table('news_article_sentiments')->where('id', $id)->first();
        if (!$row) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);

        $scores = [
            (float)($row->score_positive ?? 0),
            (float)($row->score_negative ?? 0),
            (float)($row->score_neutral ?? 0),
        ];
        $confidence = (int) round(max($scores) * 100);

        return response()->json([
            'success' => true,
            'data' => [
                'id'            => $row->id,
                'article_fk'    => $row->article_fk ?? null,
                'article_idx'   => $row->article_idx ?? null,
                'quote_text'    => $row->quote_text,
                'label'         => $row->label,
                'confidence'    => $confidence,
                'score_positive'=> (float)($row->score_positive ?? 0),
                'score_negative'=> (float)($row->score_negative ?? 0),
                'score_neutral' => (float)($row->score_neutral ?? 0),
                'created_at'    => $row->created_at ?? null,
                'updated_at'    => $row->updated_at ?? null,
            ]
        ]);
    }

    /** ===== Tambahan: Hapus 1 baris sentimen ===== */
    public function destroy($id)
    {
        try {
            $row = DB::table('news_article_sentiments')->where('id', $id)->first();
            if (!$row) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);

            DB::table('news_article_sentiments')->where('id', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus.']);
        } catch (\Throwable $e) {
            Log::error('Delete sentiment error', ['id' => $id, 'msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data.', 'error' => $e->getMessage()], 500);
        }
    }
}

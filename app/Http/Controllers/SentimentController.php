<?php
// app/Http/Controllers/SentimentController.php

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
    // Expanded keyword dictionary with weights
    private $sentimentKeywords = [
        'positive' => [
            // Sangat positif (weight: 1.0)
            'sangat bagus' => 1.0, 'luar biasa' => 1.0, 'sempurna' => 1.0, 'excellent' => 1.0,
            'terbaik' => 1.0, 'hebat' => 1.0, 'fantastis' => 1.0, 'spektakuler' => 1.0,
            
            // Positif kuat (weight: 0.8)
            'bagus' => 0.8, 'baik' => 0.8, 'mantap' => 0.8, 'puas' => 0.8, 'bersih' => 0.8,
            'lancar' => 0.8, 'top' => 0.8, 'sukses' => 0.8, 'berhasil' => 0.8, 'memuaskan' => 0.8,
            'efektif' => 0.8, 'efisien' => 0.8, 'canggih' => 0.8, 'modern' => 0.8,
            
            // Positif sedang (weight: 0.6)
            'perbaikan' => 0.6, 'membaik' => 0.6, 'meningkat' => 0.6, 'berkembang' => 0.6,
            'maju' => 0.6, 'progres' => 0.6, 'inovasi' => 0.6, 'kreatif' => 0.6,
            'solutif' => 0.6, 'responsif' => 0.6, 'cepat tanggap' => 0.6,
            
            // Positif ringan (weight: 0.4)
            'lumayan' => 0.4, 'oke' => 0.4, 'cukup baik' => 0.4, 'tidak buruk' => 0.4,
            'layak' => 0.4, 'memadai' => 0.4, 'stabil' => 0.4,
        ],
        
        'negative' => [
            // Sangat negatif (weight: 1.0)
            'sangat buruk' => 1.0, 'parah sekali' => 1.0, 'mengerikan' => 1.0, 'bencana' => 1.0,
            'kacau balau' => 1.0, 'hancur' => 1.0, 'fatal' => 1.0, 'berbahaya' => 1.0,
            
            // Negatif kuat (weight: 0.8)
            'rusak' => 0.8, 'macet' => 0.8, 'buruk' => 0.8, 'parah' => 0.8, 'bahaya' => 0.8,
            'masalah' => 0.8, 'banjir' => 0.8, 'lambat' => 0.8, 'jelek' => 0.8, 'gagal' => 0.8,
            'error' => 0.8, 'bermasalah' => 0.8, 'kecewa' => 0.8, 'mengecewakan' => 0.8,
            
            // Negatif sedang (weight: 0.6)
            'kurang' => 0.6, 'lemah' => 0.6, 'minim' => 0.6, 'terbatas' => 0.6,
            'menurun' => 0.6, 'mundur' => 0.6, 'terhambat' => 0.6, 'terlambat' => 0.6,
            'tidak efisien' => 0.6, 'boros' => 0.6, 'tidak efektif' => 0.6,
            
            // Negatif ringan (weight: 0.4)
            'kurang baik' => 0.4, 'biasa saja' => 0.4, 'tidak istimewa' => 0.4,
            'perlu perbaikan' => 0.4, 'belum optimal' => 0.4, 'kurang memuaskan' => 0.4,
        ],
    ];

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

            $analysis = $this->predictAdvanced($text);
            
            // Save to database
            $this->saveSentimentToDatabase($text, $analysis);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Analyze error', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat analisis.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Analysis completed successfully',
            'data' => [
                'text' => $text,
                'sentiment' => $analysis['label'],
                'confidence' => $analysis['confidence'],
                'scores' => [
                    'positive' => $analysis['pos'],
                    'negative' => $analysis['neg'],
                    'neutral' => $analysis['neu'],
                ],
                'detected_keywords' => $analysis['detected_keywords'],
                'reasoning' => $analysis['reasoning'],
                'keywords' => $this->extractKeywords($text),
                'processed_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function analyzeManual(Request $request)
    {
        return $this->analyze($request);
    }

    private function predictAdvanced(string $text): array
    {
        $lowerText = Str::lower($text);
        
        $positiveScore = 0;
        $negativeScore = 0;
        $detectedPositive = [];
        $detectedNegative = [];
        
        // Detect positive keywords
        foreach ($this->sentimentKeywords['positive'] as $keyword => $weight) {
            if (Str::contains($lowerText, $keyword)) {
                $positiveScore += $weight;
                $detectedPositive[] = [
                    'keyword' => $keyword,
                    'weight' => $weight,
                    'score' => $weight,
                ];
            }
        }
        
        // Detect negative keywords
        foreach ($this->sentimentKeywords['negative'] as $keyword => $weight) {
            if (Str::contains($lowerText, $keyword)) {
                $negativeScore += $weight;
                $detectedNegative[] = [
                    'keyword' => $keyword,
                    'weight' => $weight,
                    'score' => $weight,
                ];
            }
        }
        
        // Calculate total and normalize
        $totalScore = $positiveScore + $negativeScore;
        
        if ($totalScore == 0) {
            return [
                'label' => 'neutral',
                'pos' => 0.10,
                'neg' => 0.10,
                'neu' => 0.80,
                'confidence' => 80,
                'detected_keywords' => [
                    'positive' => [],
                    'negative' => [],
                ],
                'reasoning' => 'Tidak ditemukan kata kunci sentimen yang signifikan. Teks cenderung netral atau informatif.',
                'model' => 'advanced-rule-based',
            ];
        }
        
        // Normalize scores
        $posNormalized = $totalScore > 0 ? $positiveScore / $totalScore : 0;
        $negNormalized = $totalScore > 0 ? $negativeScore / $totalScore : 0;
        
        // Determine sentiment label with 5 categories
        $label = '';
        $reasoning = '';
        
        $diff = abs($posNormalized - $negNormalized);
        
        if ($diff < 0.15) {
            if ($posNormalized > $negNormalized) {
                $label = 'positive-neutral';
                $reasoning = 'Teks mengandung sentimen positif dan netral yang seimbang. ';
            } else {
                $label = 'negative-neutral';
                $reasoning = 'Teks mengandung sentimen negatif dan netral yang seimbang. ';
            }
        } else if ($posNormalized > 0.65) {
            $label = 'positive';
            $reasoning = 'Teks didominasi oleh kata-kata positif. ';
        } else if ($negNormalized > 0.65) {
            $label = 'negative';
            $reasoning = 'Teks didominasi oleh kata-kata negatif. ';
        } else if ($posNormalized > $negNormalized) {
            $label = 'positive-neutral';
            $reasoning = 'Teks cenderung positif namun masih mengandung unsur netral. ';
        } else {
            $label = 'negative-neutral';
            $reasoning = 'Teks cenderung negatif namun masih mengandung unsur netral. ';
        }
        
        // Add detected keywords to reasoning
        if (count($detectedPositive) > 0) {
            $posWords = implode(', ', array_column($detectedPositive, 'keyword'));
            $reasoning .= "Kata positif terdeteksi: {$posWords}. ";
        }
        if (count($detectedNegative) > 0) {
            $negWords = implode(', ', array_column($detectedNegative, 'keyword'));
            $reasoning .= "Kata negatif terdeteksi: {$negWords}.";
        }
        
        // Calculate final scores
        $neuScore = max(0.05, 1 - $posNormalized - $negNormalized);
        $posScore = $posNormalized * 0.95;
        $negScore = $negNormalized * 0.95;
        
        // Ensure scores sum to ~1.0
        $sum = $posScore + $negScore + $neuScore;
        $posScore = $posScore / $sum;
        $negScore = $negScore / $sum;
        $neuScore = $neuScore / $sum;
        
        $confidence = (int) round(max([$posScore, $negScore, $neuScore]) * 100);
        
        return [
            'label' => $label,
            'pos' => round($posScore, 4),
            'neg' => round($negScore, 4),
            'neu' => round($neuScore, 4),
            'confidence' => $confidence,
            'detected_keywords' => [
                'positive' => $detectedPositive,
                'negative' => $detectedNegative,
            ],
            'reasoning' => trim($reasoning),
            'model' => 'advanced-rule-based',
        ];
    }
    
    private function saveSentimentToDatabase(string $text, array $analysis): void
    {
        $normalized = $this->normalizeText($text);
        $useHash = Schema::hasColumn('news_article_sentiments', 'quote_hash');
        $hash = $useHash ? hash('sha256', $normalized) : null;
        
        $q = DB::table('news_article_sentiments');
        if ($useHash && $hash) {
            $q->where('quote_hash', $hash);
        } else {
            $q->where('quote_text', $text);
        }
        
        if ($q->exists()) {
            return;
        }
        
        $now = Carbon::now();
        $row = [
            'quote_text' => $text,
            'label' => $analysis['label'],
            'score_positive' => (float) $analysis['pos'],
            'score_negative' => (float) $analysis['neg'],
            'score_neutral' => (float) $analysis['neu'],
        ];
        
        if ($useHash) $row['quote_hash'] = $hash;
        if (Schema::hasColumn('news_article_sentiments', 'model')) $row['model'] = $analysis['model'];
        if (Schema::hasColumn('news_article_sentiments', 'analyzed_at')) $row['analyzed_at'] = $now;
        if (Schema::hasColumn('news_article_sentiments', 'created_at')) $row['created_at'] = $now;
        if (Schema::hasColumn('news_article_sentiments', 'updated_at')) $row['updated_at'] = $now;
        if (Schema::hasColumn('news_article_sentiments', 'article_fk')) $row['article_fk'] = 0;
        
        try {
            DB::table('news_article_sentiments')->insert($row);
        } catch (\Exception $e) {
            Log::warning('Insert sentiment failed', ['error' => $e->getMessage()]);
        }
    }

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
            list($items, $meta) = $this->parseUploadedFileToTexts($file);
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

        list($inserted, $skipped) = $this->insertBatchYoutubeSentiments($items);

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
        $inserted = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $commentText = trim((string)($item['text'] ?? ''));
            $url = isset($item['url']) ? trim((string)$item['url']) : null;

            if ($commentText === '' || mb_strlen($commentText) < 3) {
                $skipped++;
                continue;
            }

            $normalized = $this->normalizeText($commentText);
            $hash = $useHash ? hash('sha256', $normalized) : null;

            $q = DB::table('youtube_comment_sentiments');
            if ($useHash && $hash) {
                $q->where('comment_hash', $hash);
            } else {
                $q->where('comment_text', $commentText);
            }

            if ($q->exists()) {
                $skipped++;
                continue;
            }

            $pred = $this->predictAdvanced($commentText);
            if (!$pred || !isset($pred['label'])) {
                $skipped++;
                continue;
            }

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
            if (Schema::hasColumn('youtube_comment_sentiments', 'model'))      $row['model'] = $pred['model'] ?? 'advanced-rule-based';
            if (Schema::hasColumn('youtube_comment_sentiments', 'analyzed_at')) $row['analyzed_at'] = $now;
            if (Schema::hasColumn('youtube_comment_sentiments', 'created_at'))  $row['created_at'] = $now;
            if (Schema::hasColumn('youtube_comment_sentiments', 'updated_at'))  $row['updated_at'] = $now;

            try {
                DB::table('youtube_comment_sentiments')->insert($row);
                $inserted++;
            } catch (\Exception $e) {
                Log::error('Insert YouTube failed', ['error' => $e->getMessage(), 'text' => substr($commentText, 0, 30)]);
                $skipped++;
            }
        }

        return [$inserted, $skipped];
    }

    public function analyzeBatch(Request $request)
    {
        $request->validate([
            'data_source' => 'required|in:uploaded,scraped,database',
            'file' => 'required_if:data_source,uploaded|file|mimes:csv,txt,xlsx,xls|max:20480',
            'batch_size' => 'nullable|integer|min:1|max:1000',
            'priority' => 'nullable|in:low,normal,high',
            'analysis_type' => 'nullable|in:news,youtube',
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
            list($items, $meta) = $this->parseUploadedFileToTexts($file);
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

        $analysisType = $request->input('analysis_type', 'news');
        
        if ($analysisType === 'youtube') {
            list($inserted, $skipped) = $this->insertBatchYoutubeSentiments($items);
            $message = 'File batch YouTube diproses.';
        } else {
            list($inserted, $skipped) = $this->insertBatchSentiments($items);
            $message = 'File batch News diproses.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'filename' => $meta['filename'],
                'size_kb' => $meta['size_kb'],
                'rows' => $meta['rows'],
                'inserted' => $inserted,
                'skipped' => $skipped],
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

    private function parseUploadedFileToTexts($file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $sizeKb = (int) round($file->getSize() / 1024);
        $name = $file->getClientOriginalName();

        $texts = [];
        $rows = 0;
        $urls = [];

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
                $lower = array_map(function($v) { return strtolower(trim((string)$v)); }, $firstLine);
                $hasHeader = count(array_intersect($lower, $possibleHeaders)) > 0;
            }

            $urlIdx = false;
            if ($hasHeader) {
                $urlHeaderCandidates = ['url','link','source_url','article_url'];
                foreach ($urlHeaderCandidates as $cand) {
                    $pos = array_search($cand, $lower, true);
                    if ($pos !== false) {
                        $urlIdx = $pos;
                        break;
                    }
                }
            }

            $textIdx = 0;
            if ($hasHeader) {
                $map = array_flip(array_map(function($v) { return strtolower(trim((string)$v)); }, $firstLine));
                foreach ($possibleHeaders as $h) {
                    if (isset($map[$h])) {
                        $textIdx = (int)$map[$h];
                        break;
                    }
                }
            } else {
                $val = is_array($firstLine) ? trim((string)($firstLine[0] ?? '')) : trim((string)$firstLine);
                if ($val !== '') {
                    $texts[] = $val;
                    $urls[] = null;
                    $rows++;
                }
            }

            if (isset($lower) && $hasHeader && $textIdx === 0) {
                $found = false;
                foreach ($possibleHeaders as $ph) {
                    if (in_array($ph, $lower)) {
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    foreach ($lower as $idx => $name2) {
                        if (
                            strpos($name2, 'content') !== false ||
                            strpos($name2, 'isi') !== false ||
                            strpos($name2, 'teks') !== false
                        ) {
                            $textIdx = (int) $idx;
                            break;
                        }
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
                throw new \RuntimeException('Parser Excel tidak tersedia.');
            }

            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $firstRow = $sheet->rangeToArray('A1:Z1', null, true, true, true)[1] ?? [];
            $lower = array_map(function($v) { return strtolower(trim((string)$v)); }, array_values($firstRow));
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
                if ($val !== '') {
                    $texts[] = $val;
                }
                $rows++;
            }
        } else {
            throw new \RuntimeException('Ekstensi tidak didukung.');
        }

        $items = [];
        foreach ($texts as $i => $t) {
            $items[] = ['text' => $t, 'url' => $urls[$i] ?? null];
        }

        return [$items, ['filename' => $name, 'size_kb' => $sizeKb, 'rows' => $rows]];
    }

    private function insertBatchSentiments(array $items): array
    {
        $useHash = Schema::hasColumn('news_article_sentiments', 'quote_hash');
        $now = Carbon::now();
        $inserted = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $rawText = trim((string)($item['text'] ?? ''));
            $url = isset($item['url']) ? trim((string)$item['url']) : null;

            if ($rawText === '' || mb_strlen($rawText) < 3) {
                $skipped++;
                continue;
            }

            $normalized = $this->normalizeText($rawText);
            $hash = $useHash ? hash('sha256', $normalized) : null;

            $q = DB::table('news_article_sentiments');
            if ($useHash && $hash) {
                $q->where('quote_hash', $hash);
            } else {
                $q->where('quote_text', $rawText);
            }

            if ($q->exists()) {
                $skipped++;
                continue;
            }

            $pred = $this->predictAdvanced($rawText);
            if (!$pred || !isset($pred['label'])) {
                $skipped++;
                continue;
            }

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
            if (Schema::hasColumn('news_article_sentiments', 'model'))        $row['model'] = $pred['model'] ?? 'advanced-rule-based';
            if (Schema::hasColumn('news_article_sentiments', 'analyzed_at'))  $row['analyzed_at'] = $now;
            if (Schema::hasColumn('news_article_sentiments', 'created_at'))   $row['created_at'] = $now;
            if (Schema::hasColumn('news_article_sentiments', 'updated_at'))   $row['updated_at'] = $now;

            try {
                DB::table('news_article_sentiments')->insert($row);
                $inserted++;
            } catch (\Exception $e) {
                Log::error('Insert failed', ['error' => $e->getMessage(), 'text' => substr($rawText, 0, 30), 'row' => $row]);
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
        return $this->predictAdvanced($text);
    }

    private function extractKeywords(string $text): array
    {
        $t = Str::lower($text);
        $t = preg_replace('/[^a-z0-9\s-]/u', ' ', $t);
        $stop = ['yang','dan','di','ke','dari','atau','untuk','pada','ini','itu','ada','akan','karena','dengan','sudah','belum','banyak','lebih'];

        return collect(explode(' ', $t))
            ->filter()
            ->reject(function($w) use ($stop) { return in_array($w, $stop); }) // <-- fix di sini
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
        
        // Re-analyze to get detailed info
        $analysis = $this->predictAdvanced($row->quote_text);

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
                'detected_keywords' => $analysis['detected_keywords'] ?? [],
                'reasoning'     => $analysis['reasoning'] ?? 'Tidak ada penjelasan tersedia.',
                'created_at'    => $row->created_at ?? null,
                'updated_at'    => $row->updated_at ?? null,
            ]
        ]);
    }

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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SentimentController extends Controller
{
    public function index()
    {
        $batchStats = [
            'total'    => 24,
            'analyzed' => 18,
            'pending'  => 6,
        ];

        $newsSentiments = DB::table('news_article_sentiments')
            ->select('article_idx', 'article_fk', 'quote_text', 'label',
                     'score_positive', 'score_negative', 'score_neutral')
            ->get();

        return view('sentiment', compact('batchStats', 'newsSentiments'));
    }

    public function analyze(Request $request)
    {
        // 1) Validasi
        $request->validate([
            'text' => 'required|string|min:10',
        ]);

        // 2) Ambil teks & buat hash sebagai "memori"
        $rawText   = trim($request->input('text'));
        $normalized = $this->normalizeText($rawText);
        $hash       = hash('sha256', $normalized);

        // 3) Cek apakah sudah ada di DB
        $row = DB::table('news_article_sentiments')
            ->where('quote_hash', $hash)
            ->orderByDesc('id')
            ->first();

        // 4) Jika belum ada → prediksi & simpan
        if (!$row) {
            $pred = $this->predict($rawText); // ['label','pos','neg','neu','model']

            $id = DB::table('news_article_sentiments')->insertGetId([
                'quote_text'     => $rawText,
                'quote_hash'     => $hash,
                'label'          => $pred['label'],
                'score_positive' => $pred['pos'],
                'score_negative' => $pred['neg'],
                'score_neutral'  => $pred['neu'],
                'model'          => $pred['model'] ?? 'indo-bert-sentiment',
                'analyzed_at'    => Carbon::now(),
                'created_at'     => Carbon::now(),
                'updated_at'     => Carbon::now(),
            ]);

            $row = DB::table('news_article_sentiments')->where('id', $id)->first();
        }

        // 5) Bentuk respons untuk front-end
        $scores = [
            (float) $row->score_positive,
            (float) $row->score_negative,
            (float) $row->score_neutral,
        ];
        $confidence = (int) round(max($scores) * 100);

        return response()->json([
            'success' => true,
            'message' => 'Analysis completed successfully',
            'data'    => [
                'text'            => $row->quote_text,
                'sentiment'       => $row->label,     // positive|negative|neutral
                'confidence'      => $confidence,     // 0..100
                'keywords'        => $this->extractKeywords($row->quote_text),
                'processed_at'    => now()->toIso8601String(),
                'processing_time' => '—',
            ],
        ]);
    }

    /* ========================= Helpers ========================= */

    private function normalizeText(string $t): string
    {
        $t = Str::lower($t);
        $t = preg_replace('/\s+/', ' ', $t);
        return trim($t);
    }

    /**
     * Ganti isi fungsi ini jika Miss punya model/servis ML beneran.
     * Untuk start, rule sederhana agar endpoint langsung jalan.
     */
    private function predict(string $text): array
    {
        $lx  = Str::lower($text);
        $neg = ['rusak','macet','buruk','parah','bahaya','masalah','banjir','lambat'];
        $pos = ['bagus','baik','mantap','puas','bersih','lancar','top','sukses'];

        $negHits = collect($neg)->filter(fn($w) => Str::contains($lx, $w))->count();
        $posHits = collect($pos)->filter(fn($w) => Str::contains($lx, $w))->count();

        if ($negHits > $posHits) {
            return ['label'=>'negative','pos'=>0.05,'neg'=>0.90,'neu'=>0.05,'model'=>'rule-stub'];
        } elseif ($posHits > $negHits) {
            return ['label'=>'positive','pos'=>0.90,'neg'=>0.05,'neu'=>0.05,'model'=>'rule-stub'];
        }
        return ['label'=>'neutral','pos'=>0.10,'neg'=>0.10,'neu'=>0.80,'model'=>'rule-stub'];
    }

    private function extractKeywords(string $text): array
    {
        $t = Str::lower($text);
        $t = preg_replace('/[^a-z0-9\s-]/u', ' ', $t);

        $stop = [
            'yang','dan','di','ke','dari','atau','untuk','pada','ini','itu',
            'ada','akan','karena','dengan','sudah','belum','banyak','lebih'
        ];

        return collect(explode(' ', $t))
            ->filter()
            ->reject(fn($w) => in_array($w, $stop))
            ->unique()
            ->take(6)
            ->values()
            ->all();
    }
}

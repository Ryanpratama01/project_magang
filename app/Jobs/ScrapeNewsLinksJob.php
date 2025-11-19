<?php

namespace App\Jobs;

use App\Services\NewsScraper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ScrapeNewsLinksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

     // ✅ TAMBAHKAN: Property untuk tracking
    public string $url;
    public int $userId;
     public ?string $jobId = null;
    public int $currentIndex = 0;
    public int $totalUrls = 1;

    public function __construct(
        string $url,
        int $userId = 0,
        ?string $jobId = null,
        int $currentIndex = 0,
        int $totalUrls = 1
    ) {
        $this->url = $url;
        $this->userId = $userId;
        $this->jobId = $jobId;
        $this->currentIndex = $currentIndex;
        $this->totalUrls = $totalUrls;
    }

    public function handle(NewsScraper $scraper): void
    {
        Log::info('JOB HANDLE START', [
            'url' => $this->url,
            'job_id' => $this->jobId,
            'current_index' => $this->currentIndex,
            'total_urls' => $this->totalUrls
        ]);

         // ✅ TAMBAHKAN: Update progress di awal
        if ($this->jobId) {
            $this->updateProgress('processing', 'Mengambil berita dari ' . parse_url($this->url, PHP_URL_HOST) . '...');
        }
        Log::info('JOB: start', [
            'url' => $this->url,
            'db'  => DB::connection()->getDatabaseName(),
        ]);

        try {
            $data   = $scraper->fetch($this->url);
            $quotes = collect($data['quotes'] ?? [])->filter()->values();

            Log::info('JOB: fetched', [
                'url'          => $this->url,
                'title'        => $data['title'] ?? null,
                'date'         => $data['date'] ?? null,
                'quotes_count' => $quotes->count(),
            ]);

            $publishedAt = $this->normalizePublishedAt($data['date'] ?? null);
            $now         = now('Asia/Jakarta');

            if ($quotes->isEmpty()) {
                // Simpan marker minimal + payload debug sebagai JSON di context,
                // JANGAN isi kolom string dengan array lagi.
                DB::table('news_quotes')->insert([
                    'original_text'     => '[DEBUG] quotes empty',
                    'cleaned_text'      => null,
                    'speaker'           => null,
                    'news_url'          => $this->url,
                    'news_source'       => $data['source'] ?? parse_url($this->url, PHP_URL_HOST),
                    'context'           => json_encode([
                        'title' => $data['title'] ?? null,
                        'date'  => $data['date'] ?? null,
                    ], JSON_UNESCAPED_UNICODE),
                    'published_at'      => $publishedAt,
                    'scraped_at'        => $now,
                    'created_at'        => $publishedAt, // rapi ideal
                    'updated_at'        => $now,
                    'processing_status' => 'scraped',
                ]);

                Log::warning('JOB: quotes empty, inserted debug marker', ['url' => $this->url]);

                // ✅ TAMBAHKAN: Update progress setelah selesai
                if ($this->jobId) {
                    $this->updateProgress('completed', 'Berita dari ' . parse_url($this->url, PHP_URL_HOST) . ' selesai (0 kutipan)');
                }

                return;
            }

            $rows = [];
            foreach ($quotes as $q) {
                // quotes bisa string atau array normalized dari scraper
                if (is_string($q)) {
                    $original = $q;
                    $clean    = $this->cleanQuote($q);
                    $speaker  = null;
                } elseif (is_array($q)) {
                    $original = (string)($q['original_text'] ?? ($q['text'] ?? ''));
                    $clean    = $q['cleaned_text'] ?? $this->cleanQuote($original);
                    $speaker  = $q['speaker'] ?? null;
                } else {
                    Log::warning('JOB: skip non-string/array quote', ['type' => gettype($q)]);
                    continue;
                }

                if ($original === '') {
                    continue;
                }

                $rows[] = [
                    'original_text'     => $original,
                    'cleaned_text'      => $clean,
                    'speaker'           => $speaker,
                    'news_url'          => $this->url,
                    'news_source'       => $data['source'] ?? parse_url($this->url, PHP_URL_HOST),
                    'context'           => $data['title'] ?? null,
                    'published_at'      => $publishedAt,
                    'scraped_at'        => $now,
                    'created_at'        => $publishedAt, // rapi ideal
                    'updated_at'        => $now,
                    'processing_status' => 'scraped',
                ];
            }

            if (empty($rows)) {
                Log::warning('JOB: no valid rows built', ['url' => $this->url]);

                // ✅ TAMBAHKAN: Update progress
                if ($this->jobId) {
                    $this->updateProgress('completed', 'Tidak ada kutipan valid');
                }

                return;
            }

            // Insert (boleh batch)
            DB::table('news_quotes')->insert($rows);

            Log::info('JOB: done', ['url' => $this->url, 'inserted' => count($rows)]);

            // ✅ TAMBAHKAN: Update progress setelah berhasil
            if ($this->jobId) {
                $this->updateProgress('completed', 'Berhasil menyimpan ' . count($rows) . ' kutipan');
            }

        } catch (QueryException $e) {
            Log::error('JOB: DB error', ['url' => $this->url, 'err' => $e->getMessage()]);

            // ✅ TAMBAHKAN: Update progress saat error
            if ($this->jobId) {
                $this->updateProgress('failed', 'Gagal: Database error');
            }

            throw $e; // biar job FAIL kalau DB bermasalah
        } catch (Throwable $e) {
            Log::error('JOB: failed', ['url' => $this->url, 'err' => $e->getMessage()]);
            // ✅ TAMBAHKAN: Update progress saat error
            if ($this->jobId) {
                $this->updateProgress('failed', 'Gagal: ' . $e->getMessage());
            }

            throw $e;
        }
    }

    // ✅ TAMBAHKAN: Method untuk update progress
    private function updateProgress(string $status, string $message): void
    {
        if (!$this->jobId) {
            Log::warning('updateProgress called but jobId is null');
            return;
        }

        Log::info('UPDATE PROGRESS', [
            'job_id' => $this->jobId,
            'status' => $status,
            'message' => $message,
            'current_index' => $this->currentIndex,
            'total' => $this->totalUrls
        ]);

        $progress = Cache::get("scraping_progress_{$this->jobId}", [
            'current' => 0,
            'total' => $this->totalUrls,
            'completed' => 0,
            'failed' => 0,
            'status' => 'waiting',
            'message' => 'Menunggu...',
            'details' => []
        ]);

        if ($status === 'completed') {
            $progress['completed']++;
        } elseif ($status === 'failed') {
            $progress['failed']++;
        }

        $progress['current'] = $progress['completed'] + $progress['failed'];

        if ($progress['current'] >= $this->totalUrls) {
            $progress['status'] = 'completed';
            $progress['message'] = "Selesai! {$progress['completed']} berhasil, {$progress['failed']} gagal";
        } else {
            $progress['status'] = 'processing';
            $progress['message'] = "Memproses berita {$progress['current']}/{$this->totalUrls}...";
        }

        $progress['details'][$this->currentIndex] = [
            'url' => $this->url,
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->toDateTimeString()
        ];

        Cache::put("scraping_progress_{$this->jobId}", $progress, now()->addMinutes(10));
        
        Log::info('PROGRESS SAVED', ['progress' => $progress]);
    }

    
    // ===== Helpers =====

    private function normalizePublishedAt(?string $raw): Carbon
    {
        // fallback: sekarang (WIB)
        $nowJakarta = Carbon::now('Asia/Jakarta');
        if (!$raw) return $nowJakarta;

        // Standarkan label zona (WIB/WITA/WIT) → asumsikan +07:00 (WIB) jika ada
        $clean = preg_replace('/\b(WIB|WITA|WIT)\b/i', '+07:00', $raw);

        // Bulan Indonesia → Inggris
        $bulan = [
            'Januari'=>'January','Februari'=>'February','Maret'=>'March','April'=>'April',
            'Mei'=>'May','Juni'=>'June','Juli'=>'July','Agustus'=>'August',
            'September'=>'September','Oktober'=>'October','November'=>'November','Desember'=>'December'
        ];
        $clean = str_ireplace(array_keys($bulan), array_values($bulan), $clean);

        try {
            return Carbon::parse($clean, 'Asia/Jakarta');
        } catch (Throwable $e) {
        }

        $formats = [
            'd F Y H:i', 'd F Y H:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s',
            'd-m-Y H:i', 'd/m/Y H:i', 'l, d F Y H:i', 'l, d F Y H:i:s',
        ];
        foreach ($formats as $f) {
            try {
                return Carbon::createFromFormat($f, $clean, 'Asia/Jakarta');
            } catch (Throwable $e) {
            }
        }

        return $nowJakarta;
    }

    private function cleanQuote(string $text): string
    {
        // sederhanakan whitespace
        $text = preg_replace('/\s+/u', ' ', $text);
        // buang karakter aneh yang bukan huruf/angka/punct dasar
        $text = preg_replace('/[^a-zA-Z0-9\s\p{L}\p{N}\.,\?!:“”"\'\-–—]/u', '', $text);
        return trim($text);
    }
}

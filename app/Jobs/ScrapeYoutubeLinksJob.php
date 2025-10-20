<?php

namespace App\Jobs;

use App\Services\YoutubeScraper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class ScrapeYoutubeLinksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $backoff = 10;

    public function __construct(public string $videoUrl, public int $userId) {}

    public function handle(YoutubeScraper $yt): void
    {
        try {
            $videoId = $yt->videoId($this->videoUrl);
            if (!$videoId) {
                Log::warning('YT: gagal ekstrak videoId', ['url' => $this->videoUrl]);
                return;
            }

            $meta = $yt->meta($videoId) ?? [];

            Log::info('YT: ambil komentar', ['videoId' => $videoId, 'title' => $meta['title'] ?? null]);

            $batch = [];
            foreach ($yt->comments($videoId) as $c) {
                $rawText = $c['text'] ?? null;
                
                $batch[] = [
                    'comment_id'    => $c['id'] ?? null,
                    'video_id'      => $videoId,
                    'video_title'   => $meta['title']   ?? null,
                    'video_url'     => $this->videoUrl,
                    'channel_name'  => $meta['channel'] ?? null,

                    'comment_text'  => $rawText,
                    'cleaned_text'  => $this->cleanText($rawText), // ✅ Clean langsung
                    'author_name'   => $c['author'] ?? null,
                    'likes_count'   => (int)($c['likes'] ?? 0),
                    'replies_count' => (int)($c['replies_count'] ?? 0),

                    'published_at'  => isset($c['published_at'])
                        ? Carbon::parse($c['published_at'])->format('Y-m-d H:i:s')
                        : null,

                    'scraped_at'        => now(),
                    'processing_status' => 'cleaned', // ✅ Status 'cleaned'
                    'updated_at'        => now(),
                ];

                // Flush tiap 500
                if (count($batch) >= 500) {
                    $this->upsertComments($batch);
                    $batch = [];
                }
            }

            if ($batch) {
                $this->upsertComments($batch);
            }

            Log::info('YT: selesai simpan komentar', ['videoId' => $videoId]);

        } catch (\Throwable $e) {
            Log::error('YT Job error: '.$e->getMessage(), [
                'url' => $this->videoUrl,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            $this->fail($e);
        }
    }

    protected function upsertComments(array $rows): void
    {
        DB::connection('scrapingdb')
            ->table('youtube_comments1')->upsert(
                $rows,
                ['comment_id'], // conflict key
                [
                    'likes_count', 'replies_count', 'comment_text', 'cleaned_text',
                    'published_at', 'video_title', 'channel_name',
                    'processing_status', 'updated_at',
                ]
            );
    }

    /**
     * Fungsi cleaning text (copy dari CleanYoutubeCommentsJob)
     */
    private function cleanText(?string $text): ?string
    {
        if ($text === null) return null;

        $t = $text;

        // Hapus URL
        $t = preg_replace('~https?://\S+~u', ' ', $t);

        // Hilangkan mention, hashtag, dan @/#
        $t = preg_replace('~[@#][\p{L}\p{N}_.-]+~u', ' ', $t);

        // Ganti line breaks jadi spasi
        $t = preg_replace("~\R+~u", ' ', $t);

        // Buang emoji/simbol non-teks umum
        $t = preg_replace('~[\x{1F100}-\x{1FFFF}]~u', ' ', $t);

        // Buang karakter non-alfanumerik berlebihan (sisakan tanda baca dasar)
        $t = preg_replace('~[^\p{L}\p{N}\s\.,!?\'"-]+~u', ' ', $t);

        // Normalisasi whitespace
        preg_replace('~\s+~', ' ', $text);  // ✅ tidak ada modifier tambahan

        // Trim
        $t = trim($t);

        return $t !== '' ? $t : null;
    }
}
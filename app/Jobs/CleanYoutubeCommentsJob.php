<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CleanYoutubeCommentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function handle(): void
    {
        // ambil batch kecil agar ringan
        $rows = DB::connection('scrapingdb')
            ->table('youtube_comments1')
            ->select('id', 'comment_text')
            ->whereNull('cleaned_text')
            ->where('processing_status', 'scraped')
            ->limit(1000)
            ->get();

        if ($rows->isEmpty()) return;

        $updates = [];
        foreach ($rows as $r) {
            $clean = $this->clean($r->comment_text);
            $updates[] = [
                'id' => $r->id,
                'cleaned_text' => $clean,
                'processing_status' => 'cleaned',
                'updated_at' => now(),
            ];
        }

        // update per 500 baris
        foreach (array_chunk($updates, 500) as $chunk) {
            DB::connection('scrapingdb')
            ->table('youtube_comments1')->upsert(
                $chunk,
                ['id'],
                ['cleaned_text', 'processing_status', 'updated_at']
            );
        }
    }

    private function clean(?string $text): ?string
    {
        if ($text === null) return null;

        $t = $text;

        // hapus URL
        $t = preg_replace('~https?://\S+~u', ' ', $t);

        // hilangkan mention, hashtag, dan @/#
        $t = preg_replace('~[@#][\p{L}\p{N}_.-]+~u', ' ', $t);

        // ganti line breaks jadi spasi
        $t = preg_replace("~\R+~u", ' ', $t);

        // buang emoji/simbol non-teks umum (opsional)
        $t = preg_replace('~[\x{1F100}-\x{1FFFF}]~u', ' ', $t);

        // buang karakter non-alfanumerik berlebihan (sisakan tanda baca dasar)
        $t = preg_replace('~[^\p{L}\p{N}\s\.,!?\'"-]+~u', ' ', $t);

        // normalisasi whitespace
        $t = preg_replace('~\s+~', ' ', $t);

        // trim & lowercase ringan
        $t = trim($t);
        // $t = mb_strtolower($t); // aktifkan kalau ingin semuanya huruf kecil

        return $t !== '' ? $t : null;
    }
}
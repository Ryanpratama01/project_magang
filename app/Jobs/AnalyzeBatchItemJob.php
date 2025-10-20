<?php

namespace App\Jobs;

use App\Models\BatchItem;
use App\Models\Batch;
use App\Services\NewsScraper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AnalyzeBatchItemJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $tries = 2;
  public function __construct(public int $batchItemId) {}

  public function handle(NewsScraper $scraper): void
  {
    /** @var BatchItem $item */
    $item = BatchItem::find($this->batchItemId);
    if (!$item) return;

    $item->update(['status'=>'processing']);

    try {
      $payload = trim((string)$item->payload);  // diasumsikan URL
      $data    = $scraper->fetch($payload);     // {title, source, date, content, quotes[], speaker}

      // Simpan ringkas ke result
      $result = [
        'title'   => $data['title']   ?? null,
        'source'  => $data['source']  ?? null,
        'date'    => $data['date']    ?? null,
        'speaker' => $data['speaker'] ?? null,
        'quotes'  => array_map(fn($q) => [
          'original_text' => $q['original_text'] ?? null,
          'cleaned_text'  => $q['cleaned_text'] ?? null,
          'speaker'       => $q['speaker'] ?? null,
        ], $data['quotes'] ?? []),
      ];

      // (opsional) tulis ke tabel news_quotes seperti alur Miss sebelumnya
      // ... (boleh di-skip kalau sudah ditangani job lain)

      DB::transaction(function () use ($item, $result) {
        $item->update([
          'status'       => 'success',
          'result'       => $result,
          'processed_at' => now(),
        ]);

        // incremen processed_items di parent batch tanpa hitung ulang
        $item->batch()->update(['processed_items' => DB::raw('processed_items + 1')]);

        // kalau sudah semua, tandai batch done
        $batch = $item->batch()->first();
        if ($batch && $batch->processed_items + 1 >= $batch->total_items) {
          $batch->update(['status' => 'done']);
        }
      });
    } catch (\Throwable $e) {
      $item->update([
        'status'        => 'failed',
        'error_message' => substr($e->getMessage(), 0, 2000),
        'processed_at'  => now(),
      ]);
    }
  }
}

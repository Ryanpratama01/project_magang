<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Services\BatchFileParser;
use App\Jobs\AnalyzeBatchItemJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BatchAnalysisController extends Controller
{
  // 4.1 Upload file → create batch + batch_items
  public function upload(Request $r) {
    $data = $r->validate([
      'file'       => 'required|file|mimes:csv,xlsx,xls,txt',
      'batch_size' => 'nullable|integer|min:1|max:500',
      'priority'   => 'nullable|in:high,default,low',
    ]);

    $userId   = auth()->id();
    $file     = $data['file'];
    $path     = $file->store('uploads', 'local'); // storage/app/uploads/...
    $original = $file->getClientOriginalName();

    // Parse file → array of URLs/teks
    $rows = app(BatchFileParser::class)->parse(storage_path("app/".$path)); // return ['https://..', ...]
    $rows  = collect($rows)->filter()->unique()->values();

    DB::transaction(function () use ($rows, $userId, $path, $original, $data, &$batch) {
      $batch = Batch::create([
        'user_id'          => $userId,
        'source_type'      => 'file_upload',
        'file_path'        => $path,
        'original_filename'=> $original,
        'batch_size'       => $data['batch_size'] ?? 25,
        'priority'         => $data['priority'] ?? 'default',
        'total_items'      => $rows->count(),
        'status'           => 'created',
      ]);

      // insert items
      $items = $rows->map(function($val) use ($batch) {
        $payload = is_string($val) ? trim($val) : json_encode($val);
        $hash    = sha1((string)$payload);
        return [
          'batch_id' => $batch->id,
          'payload'  => $payload,
          'hash'     => $hash,
          'status'   => 'pending',
          'created_at'=> now(),
          'updated_at'=> now(),
        ];
      })->all();

      // chunk insert (lebih cepat)
      foreach (array_chunk($items, 1000) as $chunk) {
        BatchItem::insert($chunk);
      }
    });

    return response()->json([
      'success' => true,
      'batch_id'=> $batch->id,
      'total'   => $batch->total_items,
      'message' => 'Batch dibuat, siap dianalisis.',
    ]);
  }

  // 4.2 Mulai Analisis → enqueue N item sesuai batch_size & priority
  public function start(Batch $batch) {
    abort_if($batch->status === 'done', 400, 'Batch sudah selesai.');
    $limit   = $batch->batch_size;
    $queue   = $batch->priority; // 'high'|'default'|'low' → mapping ke queue

    $items = $batch->items()
      ->whereIn('status', ['pending','failed'])
      ->limit($limit)
      ->get();

    foreach ($items as $item) {
      $item->update(['status'=>'queued','queued_at'=>now()]);
      AnalyzeBatchItemJob::dispatch($item->id)->onQueue($queue);
    }

    $batch->update(['status' => 'running']);

    return response()->json([
      'success'=> true,
      'enqueued'=> $items->count(),
      'remaining'=> $batch->items()->whereIn('status',['pending','failed'])->count(),
      'message'=> 'Item dikirim ke antrian.',
    ]);
  }

  // 4.3 Stats untuk kartu di UI
  public function stats(Batch $batch) {
    $total = $batch->total_items;
    $done  = $batch->items()->where('status','success')->count();
    $waiting = $total - $done;

    return response()->json([
      'total'   => $total,
      'done'    => $done,
      'waiting' => max(0,$waiting),
      'status'  => $batch->status,
    ]);
  }

  // 4.4 (opsional) daftar item
  public function items(Batch $batch, Request $r) {
    $status = $r->query('status'); // pending|queued|processing|success|failed|null
    $q = $batch->items()->orderByDesc('id');
    if ($status) $q->where('status',$status);
    return response()->json($q->paginate(50));
  }
}

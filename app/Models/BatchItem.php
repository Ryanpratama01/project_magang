<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchItem extends Model {
  protected $fillable = ['batch_id','payload','hash','status','result','error_message','queued_at','processed_at'];
  protected $casts = ['result' => 'array'];
  public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
}

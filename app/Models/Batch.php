<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model {
  protected $fillable = [
    'user_id','source_type','file_path','original_filename',
    'batch_size','priority','total_items','processed_items','status'
  ];
  public function items(): HasMany { return $this->hasMany(BatchItem::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsArticleSentiment extends Model
{
    protected $table = 'news_article_sentiments';

    protected $fillable = [
        'quote_text',
        'quote_hash',
        'label',
        'score_positive',
        'score_negative',
        'score_neutral',
        'model',
        'analyzed_at',
        // kolom lain di tabel ini biarkan null/default saja:
        'quote_type','speaker','paragraph_idx','article_idx'
    ];

    protected $casts = [
        'analyzed_at' => 'datetime',
    ];

    public $timestamps = true; // untuk created_at / updated_at
}

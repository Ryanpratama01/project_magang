<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsArticleSentiment extends Model
{
    protected $table = 'news_article_sentiments';

    // tambahkan article_fk ke fillable agar bisa diisi saat seeding/import
    protected $fillable = [
        'article_fk',        // <— penting
        'quote_text',
        'quote_hash',
        'quote_type',
        'speaker',
        'paragraph_idx',
        'label',
        'score_positive',
        'score_neutral',
        'score_negative',
        'model',
        'analyzed_at',
    ];

    protected $casts = [
        'analyzed_at'    => 'datetime',
        // opsional, tapi berguna kalau nanti pakai agregasi skor
        'score_positive' => 'float',
        'score_neutral'  => 'float',
        'score_negative' => 'float',
    ];

    public $timestamps = true; // tabelmu punya created_at/updated_at

    /**
     * Relasi balik ke artikel.
     * FK: article_fk -> news_articles.id
     */
    public function article()
    {
        return $this->belongsTo(NewsArticle::class, 'article_fk', 'id');
    }
}

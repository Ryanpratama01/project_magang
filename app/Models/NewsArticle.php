<?php

// app/Models/NewsArticle.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
    use HasFactory;

    protected $table = 'news_articles';

    protected $fillable = [
        'title', 'content', 'publisher_id', 'url', 'published_at', 'status'
    ];

    public $timestamps = true;

    public function sentiments()
    {
        return $this->hasMany(NewsArticleSentiment::class, 'article_fk', 'id');
    }
}

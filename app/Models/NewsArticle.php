<?php

// app/Models/NewsArticle.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
    use HasFactory;

    // Jika nama tabel di database tidak sesuai dengan format default (jamak), deklarasikan nama tabelnya
    protected $table = 'news_articles';

    // Kolom-kolom yang dapat diisi (mass assignable)
    protected $fillable = [
        'title', 'content', 'publisher_id', 'url', 'published_at', 'status'
    ];

    // Jika menggunakan timestamp di tabel, bisa deklarasikan
    public $timestamps = true;
}
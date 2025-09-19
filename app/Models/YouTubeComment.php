<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class YoutubeComment extends Model
// {
//     use HasFactory;

//     protected $table = 'yt_comment_cleaning'; // Sesuaikan dengan nama tabel Anda

//     protected $fillable = [
//         'video_id',
//         'video_url',
//         'comment_id',
//         'text',
//         'author',
//         'author_channel',
//         'like_count',
//         'text_clean',
//         'text_clean_strong'
//     ];

//     protected $casts = [
//         'published_at' => 'datetime',
//         'likes_count' => 'integer'
//     ];

//     // Scope untuk filter berdasarkan video
//     public function scopeByVideo($query, $videoId)
//     {
//         if ($videoId) {
//             return $query->where('video_id', $videoId);
//         }
//         return $query;
//     }

//     // Scope untuk pencarian
//     public function scopeSearch($query, $search)
//     {
//         if ($search) {
//             return $query->where(function($q) use ($search) {
//                 $q->where('text', 'like', '%' . $search . '%')
//                   ->orWhere('author', 'like', '%' . $search . '%');
//             });
//         }
//         return $query;
//     }

//     // Accessor untuk format tanggal
//     public function getFormattedDateAttribute()
//     {
//         return $this->published_at ? $this->published_at->format('d/m/Y H:i') : '-';
//     }

//     // Accessor untuk excerpt comment
//     public function getExcerptAttribute()
//     {
//         return \Str::limit($this->text, 100);
//     }
// }
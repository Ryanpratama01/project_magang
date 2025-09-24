<!-- <?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Carbon\Carbon;

// class News extends Model
// {
//     use HasFactory;

//     protected $table = 'news'; // Sesuaikan dengan nama tabel Anda
    
//     protected $fillable = [
//         'title',
//         'content', 
//         'url',
//         'source',
//         'published_at',
//         'scraped_at',
//         'status',
//         'category',
//         'sentiment', // jika sudah ada kolom sentiment
//         'confidence' // jika sudah ada kolom confidence
//     ];

    // protected $casts = [
    //     'published_at' => 'datetime',
    //     'scraped_at' => 'datetime',
    // ];

    // Scope untuk filter berdasarkan sumber
    // public function scopeBySource($query, $source)
    // {
    //     if ($source) {
    //         return $query->where('source', 'like', '%' . $source . '%');
    //     }
    //     return $query;
    // }

    // Scope untuk filter berdasarkan tanggal
    // public function scopeByDateRange($query, $startDate, $endDate)
    // {
    //     if ($startDate && $endDate) {
    //         return $query->whereBetween('published_at', [$startDate, $endDate]);
    //     }
    //     return $query;
    // }

    // Scope untuk pencarian
    // public function scopeSearch($query, $search)
    // {
    //     if ($search) {
    //         return $query->where(function($q) use ($search) {
    //             $q->where('title', 'like', '%' . $search . '%')
    //               ->orWhere('content', 'like', '%' . $search . '%');
    //         });
    //     }
    //     return $query;
    // }

    // Accessor untuk format tanggal Indonesia
    // public function getFormattedDateAttribute()
    // {
    //     return $this->published_at ? $this->published_at->format('d/m/Y') : '-';
    // }

//     // Accessor untuk excerpt content
//     public function getExcerptAttribute()
//     {
//         return \Str::limit(strip_tags($this->content), 100);
//     }
// } -->

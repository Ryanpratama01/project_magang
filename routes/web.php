<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScrapingController;
use App\Http\Controllers\SentimentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/profile/data', [ProfileController::class, 'data'])->name('profile.data');
    Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
// Register
Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forget-password', [AuthController::class, 'showForgetPasswordForm'])
    ->name('password.request');

Route::post('/forget-password', [AuthController::class, 'sendResetLinkEmail'])
    ->name('password.email');

Route::get('/verify-otp', [AuthController::class, 'showVerifyOtpForm'])->name('password.verify.otp');

Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('password.verify.otp.post');

Route::post('/resend-otp', [AuthController::class, 'resendOtp'])
    ->name('password.resend.otp');

Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reset-password', [AuthController::class, 'reset'])
    ->name('password.update');


Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [\App\Http\Controllers\DashboardController::class, 'data'])
    ->name('dashboard.data');

    Route::get('/scraping', [ScrapingController::class, 'index'])->name('scraping');
    Route::post('/scraping/start', [ScrapingController::class, 'startScraping'])->name('scraping.start');
    Route::post('/scraping/search', [ScrapingController::class, 'searchNews'])->name('scraping.search');

     /** Scraping (page) */
    Route::get('/scraping', [ScrapingController::class, 'index'])->name('scraping');

    /** NEWS */
    // kirim link berita (dipakai form Blade kamu)
    Route::post('/scraping/news/links', [ScrapingController::class,'scrapeNewsByLinks'])->name('scrape.news.links');
    // list hasil (dipakai tabel bawah)
    Route::get('/scraping/results', [ScrapingController::class, 'results'])->name('scraping.results');
    // pencarian (kata kunci + optional tanggal) — dipakai JS kamu
    Route::get('/scraping/search', [ScrapingController::class, 'searchAll'])->name('scraping.search');
    // detail 1 kutipan
    Route::get('/scraping/item/{id}', [ScrapingController::class, 'item'])->whereNumber('id')->name('scraping.item');
    // hapus 1 kutipan
    Route::delete('/scraping/item/{id}', [ScrapingController::class, 'destroy'])->whereNumber('id')->name('scraping.destroy');
    // hapus banyak kutipan
    Route::post('/scraping/bulk-delete', [ScrapingController::class, 'bulkDestroy'])->name('scraping.bulkDestroy');

    Route::get('/scraping/progress', [ScrapingController::class, 'checkProgress'])
    ->name('scraping.progress');
    
    /** YOUTUBE */
    // kirim link youtube
    Route::post('/scraping/youtube/links', [ScrapingController::class,'scrapeYoutubeByLinks'])->name('scrape.youtube.links');
    // list komentar youtube (dipakai tabel bawah)
    Route::get('/scraping/youtube/results', [ScrapingController::class,'youtubeResults'])->name('scraping.youtube.results');
    // detail 1 komentar youtube
    Route::get('/scraping/youtube/{id}', [ScrapingController::class, 'youtubeItem'])->whereNumber('id')->name('scraping.youtube.item');
    // hapus 1 komentar youtube
    Route::delete('/scraping/youtube/{id}', [ScrapingController::class, 'youtubeDestroy'])->whereNumber('id')->name('scraping.youtube.destroy');
    // hapus banyak komentar youtube
    Route::post('/scraping/youtube/bulk-destroy', [ScrapingController::class, 'youtubeBulkDestroy'])->name('scraping.youtube.bulkDestroy');

    // ==== [ADD] Analisis sentimen untuk item yang dipilih di halaman Scraping ==== (aline1)
Route::post('/scraping/analyze', [\App\Http\Controllers\ScrapingController::class, 'analyzeSelected'])
    ->name('scraping.analyze');

    
    Route::get('/analisis-sentimen', [SentimentController::class, 'index'])->name('sentiment');
    // Route untuk menerima analisis teks
    Route::post('/analyze-sentiment', [SentimentController::class, 'analyze'])->name('analyze.sentiment');
    
    Route::post('/analyze/batch', [SentimentController::class, 'analyzeBatch'])
     ->name('analyze.sentiment.batch');
    
    Route::get('/analisis-sentimen/data', [SentimentController::class, 'getSentiments'])->name('sentiment.data');

    // 🔥 Routes BARU untuk YouTube Sentiment
    Route::post('/analyze-sentiment-youtube', [SentimentController::class, 'analyzeBatchYoutube'])->name('analyze.sentiment.youtube');
    Route::get('/sentiment/youtube/data', [SentimentController::class, 'getYoutubeSentiments'])->name('sentiment.youtube.data');

    // Detail by id (jika tabel punya kolom id)
    Route::get('/sentiment/{id}', [SentimentController::class, 'showSentiment'])->name('sentiment.show');

    // Fallback detail by text/hash (kalau id tidak ada)
    Route::post('/sentiment/lookup', [SentimentController::class, 'lookupSentiment'])->name('sentiment.lookup');

    // ===== Tambahan: Export CSV & Hapus baris =====
    Route::get('/analisis-sentimen/export', [SentimentController::class, 'export'])->name('sentiment.export');
    Route::delete('/sentiment/{id}', [SentimentController::class, 'destroy'])->name('sentiment.destroy');

    Route::get('/sentiment/{id}', [\App\Http\Controllers\SentimentController::class,'showSentiment'])
     ->name('sentiment.show');


    // ==============================================

    // Route::get('/news-distribution', [SentimentController::class, 'index']);
    
    Route::get('/cetak-laporan', [ReportController::class, 'index'])->name('report');
    Route::post('/cetak-laporan/generate', [ReportController::class, 'generate'])->name('report.generate');
    Route::get('/cetak-laporan/download/{type}', [ReportController::class, 'download'])->name('report.download');
});

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

/**
 * Profile (harus login)
 * (ini dari master, tetap dipakai)
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/data', [ProfileController::class, 'data'])->name('profile.data');
    Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');
});

/**
 * Auth
 */
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


/**
 * Semua route yang butuh login
 */
Route::middleware(['auth'])->group(function () {

    /** Dashboard */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get(
        '/dashboard/data',
        [DashboardController::class, 'data']
    )->name('dashboard.data');

    /** Scraping (dasar dari master) */
    Route::get('/scraping', [ScrapingController::class, 'index'])->name('scraping');
    Route::post('/scraping/start', [ScrapingController::class, 'startScraping'])->name('scraping.start');
    Route::post('/scraping/search', [ScrapingController::class, 'searchNews'])->name('scraping.search');

    /** Scraping (page) – tetap ada seperti di master (duplikat GET /scraping, tapi sama saja) */
    Route::get('/scraping', [ScrapingController::class, 'index'])->name('scraping');

    /** NEWS */
    // kirim link berita (dipakai form Blade)
    Route::post('/scraping/news/links', [ScrapingController::class, 'scrapeNewsByLinks'])->name('scrape.news.links');
    // list hasil (dipakai tabel bawah)
    Route::get('/scraping/results', [ScrapingController::class, 'results'])->name('scraping.results');
    // pencarian (kata kunci + optional tanggal) — dipakai JS
    Route::get('/scraping/search', [ScrapingController::class, 'searchAll'])->name('scraping.search');
    // detail 1 kutipan
    Route::get('/scraping/item/{id}', [ScrapingController::class, 'item'])->whereNumber('id')->name('scraping.item');
    // hapus 1 kutipan
    Route::delete('/scraping/item/{id}', [ScrapingController::class, 'destroy'])->whereNumber('id')->name('scraping.destroy');
    // hapus banyak kutipan
    Route::post('/scraping/bulk-delete', [ScrapingController::class, 'bulkDestroy'])->name('scraping.bulkDestroy');

    // progress scraping (dipakai polling JS)
    Route::get('/scraping/progress', [ScrapingController::class, 'checkProgress'])
        ->name('scraping.progress');

    // ✅ Tambahan dari scraping_page: export hasil scraping
    Route::get('/scraping/export/excel', [ScrapingController::class, 'exportExcel'])->name('scraping.export.excel');
    Route::get('/scraping/export/csv', [ScrapingController::class, 'exportCsv'])->name('scraping.export.csv');

    /** YOUTUBE */
    // kirim link youtube
    Route::post('/scraping/youtube/links', [ScrapingController::class, 'scrapeYoutubeByLinks'])->name('scrape.youtube.links');
    // list komentar youtube (dipakai tabel bawah)
    Route::get('/scraping/youtube/results', [ScrapingController::class, 'youtubeResults'])->name('scraping.youtube.results');
    // detail 1 komentar youtube
    Route::get('/scraping/youtube/{id}', [ScrapingController::class, 'youtubeItem'])->whereNumber('id')->name('scraping.youtube.item');
    // hapus 1 komentar youtube
    Route::delete('/scraping/youtube/{id}', [ScrapingController::class, 'youtubeDestroy'])->whereNumber('id')->name('scraping.youtube.destroy');
    // hapus banyak komentar youtube
    Route::post('/scraping/youtube/bulk-destroy', [ScrapingController::class, 'youtubeBulkDestroy'])->name('scraping.youtube.bulkDestroy');

    // Analisis sentimen untuk item yang dipilih di halaman Scraping (dari master)
    Route::post(
        '/scraping/analyze',
        [ScrapingController::class, 'analyzeSelected']
    )->name('scraping.analyze');


    /** ANALISIS SENTIMEN (gabungan master + alias lama) */

    // route utama (master)
    Route::get('/analisis-sentimen', [SentimentController::class, 'index'])->name('sentiment');

    // master: analisis satu teks
    Route::post('/analyze-sentiment', [SentimentController::class, 'analyze'])->name('analyze.sentiment');
    // master: batch analisis
    Route::post('/analyze/batch', [SentimentController::class, 'analyzeBatch'])->name('analyze.sentiment.batch');
    // master: data tabel sentimen
    Route::get('/analisis-sentimen/data', [SentimentController::class, 'getSentiments'])->name('sentiment.data');

    // 🔥 Routes YouTube Sentiment (master)
    Route::post('/analyze-sentiment-youtube', [SentimentController::class, 'analyzeBatchYoutube'])->name('analyze.sentiment.youtube');
    Route::get('/sentiment/youtube/data', [SentimentController::class, 'getYoutubeSentiments'])->name('sentiment.youtube.data');

    // Detail by id (jika tabel punya kolom id)
    Route::get('/sentiment/{id}', [SentimentController::class, 'showSentiment'])->name('sentiment.show');

    // Fallback detail by text/hash (kalau id tidak ada)
    Route::post('/sentiment/lookup', [SentimentController::class, 'lookupSentiment'])->name('sentiment.lookup');

    // Export CSV & Hapus baris (master)
    Route::get('/analisis-sentimen/export', [SentimentController::class, 'export'])->name('sentiment.export');
    Route::delete('/sentiment/{id}', [SentimentController::class, 'destroy'])->name('sentiment.destroy');

    // Di master ada dua route sentiment.show, dibiarkan supaya tidak mengubah logic
    Route::get(
        '/sentiment/{id}',
        [SentimentController::class, 'showSentiment']
    )->name('sentiment.show');

    /**
     * ✅ Alias lama dari branch laporan_page
     * supaya form lama yang pakai {{ route('sentiment.analyze') }}
     * dan endpoint /analisis-sentimen/analyze tetap jalan
     */
    Route::post(
        '/analisis-sentimen/analyze',
        [SentimentController::class, 'analyze']
    )->name('sentiment.analyze');


    /** LAPORAN – tabel & grafik (dari laporan_page) */

    // TABEL LAPORAN LAMA
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    Route::post('/reports/count', [ReportController::class, 'count'])->name('reports.count');
    Route::get('/reports/preview/{filename}', [ReportController::class, 'preview'])->name('reports.preview');
    Route::get('/reports/download/{filename}', [ReportController::class, 'download'])->name('reports.download');
    Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');

    // Halaman UI grafik (nama route lama, supaya kompatibel)
    Route::get('/reports/charts', [ReportController::class, 'chartsPage'])
        ->name('reports.charts');

    // API data grafik (JSON)
    Route::post('/reports/charts/data', [ReportController::class, 'charts'])
        ->name('reports.charts.data');

    // Generate PDF khusus grafik bulanan
    Route::post('/reports/generate-charts', [ReportController::class, 'generateCharts'])
        ->name('reports.generate.charts');

    // API tabel (JSON) untuk halaman grafik
    Route::post('/reports/charts/table', [ReportController::class, 'chartsDetails'])
        ->name('reports.charts.table');

    // ==============================================
    // Route laporan versi master (cetak-laporan)
    Route::get('/cetak-laporan', [ReportController::class, 'index'])->name('report');
    Route::post('/cetak-laporan/generate', [ReportController::class, 'generate'])->name('report.generate');
    Route::get('/cetak-laporan/download/{type}', [ReportController::class, 'download'])->name('report.download');
});

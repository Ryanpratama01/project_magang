<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScrapingController;
use App\Http\Controllers\SentimentController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Semua rute butuh login
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Halaman scraping (list + form)
    Route::get('/scraping', [ScrapingController::class, 'index'])->name('scraping');

    // === Rute BARU untuk scraping ===
    Route::post('/scraping/news/links',   [ScrapingController::class, 'storeNewsLinks'])->name('scraping.news.links');
    Route::post('/scraping/youtube/urls', [ScrapingController::class, 'storeYoutubeUrls'])->name('scraping.youtube.urls');
    Route::delete('/scraping/news/{scraping}', [ScrapingController::class, 'destroyNews'])->name('scraping.news.destroy');

    // (Opsional) rute LAMA—hapus jika sudah tidak dipakai di Blade/JS kamu
    // Route::post('/scraping/start',  [ScrapingController::class, 'startScraping'])->name('scraping.start');
    // Route::post('/scraping/search', [ScrapingController::class, 'searchNews'])->name('scraping.search');

    // Sentimen (index + jalankan analisis untuk item terpilih)
    Route::get('/analisis-sentimen',              [SentimentController::class, 'index'])->name('sentiment');
    Route::post('/analisis-sentimen/analyze',     [SentimentController::class, 'analyze'])->name('sentiment.analyze');

    // Atau kalau kamu pakai rute analisis yang baru:
    // Route::post('/analysis/run', [SentimentController::class,'analyzeSelected'])->name('analysis.run');

    // (opsional) Report
    // Route::get('/report', [ReportController::class, 'index'])->name('report.index');
});

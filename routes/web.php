<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScrapingController;
use App\Http\Controllers\SentimentController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/scraping', [ScrapingController::class, 'index'])->name('scraping');
    Route::post('/scraping/start', [ScrapingController::class, 'startScraping'])->name('scraping.start');
    Route::post('/scraping/search', [ScrapingController::class, 'searchNews'])->name('scraping.search');
    
    Route::get('/analisis-sentimen', [SentimentController::class, 'index'])->name('sentiment');
    Route::post('/analisis-sentimen/analyze', [SentimentController::class, 'analyze'])->name('sentiment.analyze');
    
    Route::get('/cetak-laporan', [ReportController::class, 'index'])->name('report');
    Route::post('/cetak-laporan/generate', [ReportController::class, 'generate'])->name('report.generate');
    Route::get('/cetak-laporan/download/{type}', [ReportController::class, 'download'])->name('report.download');
});
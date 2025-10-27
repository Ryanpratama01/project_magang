<?php


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
    return redirect()->route('dashboard');
});

// alias lama agar {{ route('report') }} tetap bekerja
Route::get('/cetak-laporan', [ReportController::class, 'index'])->name('report');

/** Auth (kalau memang ada di project, kalau tidak ada boleh dihapus bagian ini) */
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forget-password', [AuthController::class, 'showForgetPasswordForm'])->name('password.request');
Route::post('/forget-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/verify-otp', [AuthController::class, 'showVerifyOtpForm'])->name('password.verify.otp');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('password.verify.otp.post');
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('password.resend.otp');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile/data', [ProfileController::class, 'data'])->name('profile.data');
    Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');
});

Route::post('/resend-otp', [AuthController::class, 'resendOtp'])
    ->name('password.resend.otp');

Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reset-password', [AuthController::class, 'reset'])
    ->name('password.update');




Route::middleware(['auth'])->group(function () {
    /** Dashboard */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    /** Scraping */
    Route::get('/scraping', [ScrapingController::class, 'index'])->name('scraping');
    Route::post('/scraping/start',  [ScrapingController::class, 'startScraping'])->name('scraping.start');
    Route::post('/scraping/search', [ScrapingController::class, 'searchNews'])->name('scraping.search');

    /** Analisis Sentimen */
    Route::get('/analisis-sentimen', [SentimentController::class, 'index'])->name('sentiment');
    Route::post('/analisis-sentimen/analyze', [SentimentController::class, 'analyze'])->name('sentiment.analyze');

    /** Laporan – tabel (yang lama) */
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    Route::post('/reports/count', [ReportController::class, 'count'])->name('reports.count');
    Route::get('/reports/preview/{filename}', [ReportController::class, 'preview'])->name('reports.preview');
    Route::get('/reports/download/{filename}', [ReportController::class, 'download'])->name('reports.download');
    Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');

    // Halaman UI grafik (PAKAI NAMA LAMA biar kompatibel)
    Route::get('/reports/charts', [ReportController::class, 'chartsPage'])
        ->name('reports.charts');

    // API data grafik (JSON) untuk menampilkan gambar di halaman
    Route::post('/reports/charts/data', [ReportController::class, 'charts'])
        ->name('reports.charts.data');

    // Generate PDF khusus grafik bulanan
    Route::post('/reports/generate-charts', [ReportController::class, 'generateCharts'])
        ->name('reports.generate.charts');

    // API tabel (JSON) untuk halaman grafik
    Route::post('/reports/charts/table', [ReportController::class, 'chartsDetails'])->name('reports.charts.table');
});

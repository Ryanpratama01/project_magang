@extends('layouts.app')

@section('title', 'Scraping - Sistem Analisis Sentimen')
@section('page-title', 'Scraping')
@section('page-description', 'Ambil data dari berbagai sumber untuk analisis sentimen')

<style>
/* ✅ Shake animation untuk warning */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.animate-shake {
    animation: shake 0.5s ease-in-out;
}

/* ✅ Smooth transition untuk modal */
#scrapingProgress.flex {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* ✅ Mini progress hover effect */
#miniProgress:hover {
    transform: scale(1.05);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>

@section('content')
<div class="space-y-8">
    <!-- Category Selection -->
    <div class="bg-white rounded-2xl p-6 card-shadow">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-layer-group text-blue-600"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-800">Pilih Kategori Scraping</h3>
                <p class="text-gray-600 text-sm">Pilih jenis data yang ingin Anda scrape</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div id="categoryNews" class="category-card cursor-pointer p-6 border-2 border-blue-200 bg-blue-50 rounded-xl hover:border-blue-400 transition-all duration-200 category-active">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-newspaper text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-blue-800 mb-1">Scraping Berita</h4>
                        <p class="text-blue-600 text-sm">Ambil data berita dari website portal berita</p>
                    </div>
                </div>
            </div>
            
            <div id="categoryYoutube" class="category-card cursor-pointer p-6 border-2 border-gray-200 bg-gray-50 rounded-xl hover:border-red-400 hover:bg-red-50 transition-all duration-200">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fab fa-youtube text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-red-800 mb-1">Scraping YouTube</h4>
                        <p class="text-red-600 text-sm">Ambil data komentar dari video YouTube</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- News Scraping Section -->
    <div id="newsSection" class="scraping-section">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Input Link Berita -->
            <div class="bg-white rounded-2xl p-6 card-shadow">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-link text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Masukkan Link Berita</h3>
                        <p class="text-gray-600 text-sm">Tempelkan link satu per baris</p>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('scrape.news.links') }}" id="linkScrapingForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Daftar Link Berita</label>
                        <textarea 
                            id="newsLinks"
                            name="links" 
                            rows="8" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            placeholder="https://example.com/berita-1
https://example.com/berita-2
https://example.com/berita-3"></textarea>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <button type="button" id="clearLinks" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                            <i class="fas fa-trash mr-2"></i>Bersihkan
                        </button>
                        <button type="submit" id="startLinkScraping" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-medium">
                            <i class="fas fa-play mr-2"></i>Mulai Scraping
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Cari Berita -->
            <div class="bg-white rounded-2xl p-6 card-shadow">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-search text-blue-700"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Cari Berita</h3>
                        <p class="text-gray-600 text-sm">Cari berita berdasarkan kata kunci</p>
                    </div>
                </div>
                
                <form id="searchScrapingForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Masukkan Kata Kunci</label>
                        <input 
                            type="text" 
                            name="keywords"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="politik, ekonomi, teknologi">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rentang Tanggal</label>
                            <div class="flex space-x-2">
                                <input 
                                    type="date" 
                                    name="date_from"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                    value="{{ date('Y-m-d', strtotime('-7 days')) }}">
                                <input 
                                    type="date" 
                                    name="date_to"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" id="startSearchScraping" class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-medium">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- YouTube Scraping Section -->
    <div id="youtubeSection" class="scraping-section hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Input YouTube URL -->
            <div class="bg-white rounded-2xl p-6 card-shadow">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fab fa-youtube text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Masukkan URL Video</h3>
                        <p class="text-gray-600 text-sm">URL video YouTube yang ingin di-scrape</p>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('scrape.youtube.links') }}" id="youtubeUrlForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL Video YouTube</label>
                        <textarea 
                            id="youtubeUrls"
                            name="youtube_urls" 
                            rows="6" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"
                            placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ
https://www.youtube.com/watch?v=VIDEO_ID_2
https://youtu.be/SHORT_URL"></textarea>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <button type="button" id="clearYoutubeUrls" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                            <i class="fas fa-trash mr-2"></i>Bersihkan
                        </button>
                        <button type="submit" id="startYoutubeScraping" class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-200 font-medium">
                            <i class="fas fa-play mr-2"></i>Mulai Scraping
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- YouTube Search -->
            <div class="bg-white rounded-2xl p-6 card-shadow">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-search text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Cari Video YouTube</h3>
                        <p class="text-gray-600 text-sm">Cari dan scrape video berdasarkan kata kunci</p>
                    </div>
                </div>
                
                <form id="youtubeSearchForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kata Kunci Pencarian</label>
                        <input 
                            type="text" 
                            name="search_query"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="politik indonesia, review produk, tutorial">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Tanggal Upload</label>
                        <select name="upload_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Semua Waktu</option>
                            <option value="hour">1 Jam Terakhir</option>
                            <option value="today">Hari Ini</option>
                            <option value="week">Minggu Ini</option>
                            <option value="month">Bulan Ini</option>
                            <option value="year">Tahun Ini</option>
                        </select>
                    </div>
                    
                    <button type="submit" id="startYoutubeSearch" class="w-full px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-200 font-medium">
                        <i class="fas fa-search mr-2"></i>Cari Komentar
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Scraping Progress -->
    <div id="scrapingProgress" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 m-4 max-w-md w-full shadow-2xl transform transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-sync fa-spin text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Progress Scraping</h3>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                            <i class="fas fa-circle text-yellow-500 mr-1"></i>
                            Sedang Berjalan
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-medium">Progress</span>
                    <span id="progressText" class="font-bold text-blue-600">0/0</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                    <div id="progressBar" class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full transition-all duration-300 flex items-center justify-center text-white text-xs font-bold" style="width: 0%">
                        <span id="progressPercent" class="drop-shadow-md">0%</span>
                    </div>
                </div>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded">
                    <p id="currentTask" class="text-sm text-gray-700 font-medium">Menunggu...</p>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button id="stopScraping" class="flex-1 px-4 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors font-medium">
                    <i class="fas fa-stop mr-2"></i>Stop Scraping
                </button>
                <button id="minimizeProgress" class="px-4 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mini Progress Indicator (saat di-minimize) -->
    <div id="miniProgress" class="fixed bottom-6 right-6 bg-white rounded-xl shadow-2xl p-4 hidden z-40 cursor-pointer hover:shadow-3xl transition-all">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-sync fa-spin text-blue-600"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-800">Scraping...</p>
                <p id="miniProgressText" class="text-xs text-gray-600">0/0</p>
            </div>
        </div>
    </div>
    
    <!-- Results Table -->
    <div id="resultsSection" class="bg-white rounded-2xl card-shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Hasil Scraping</h3>
                    {{-- <p class="text-gray-600 text-sm" id="resultsDescription">Daftar data yang berhasil diambil</p> --}}
                </div>
                <div class="flex space-x-3">
                    <!-- Export Dropdown -->
                    <div class="relative" id="exportDropdown">
                        <button id="exportButton" class="px-4 py-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-download mr-2"></i>Export
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div id="exportMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                            <button id="exportExcelAll" class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center">
                                <i class="fas fa-file-excel mr-2 text-green-600"></i>
                                Export Semua (Excel)
                            </button>
                            <button id="exportCSVAll" class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center">
                                <i class="fas fa-file-csv mr-2 text-blue-600"></i>
                                Export Semua (CSV)
                            </button>
                            <div class="border-t border-gray-200 my-1"></div>
                            <button id="exportExcelSelected" class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center text-gray-700">
                                <i class="fas fa-check-square mr-2 text-green-600"></i>
                                Export Terpilih (Excel)
                            </button>
                            <button id="exportCSVSelected" class="w-full px-4 py-2 text-left hover:bg-gray-50 flex items-center text-gray-700">
                                <i class="fas fa-check-square mr-2 text-blue-600"></i>
                                Export Terpilih (CSV)
                            </button>
                        </div>
                    </div>
                    <button id="selectAll" class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        <i class="fas fa-check-square mr-2"></i>Pilih Semua
                    </button>
                    <button id="deleteSelected" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i>Hapus Terpilih
                    </button>
                    <button id="analyzeSelected" class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200">
                        <i class="fas fa-brain mr-2"></i>Analisis Sentimen
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Filter Controls -->
            <div class="mb-6 flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Filter:</label>
                    <select id="sourceFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Sumber</option>
                        <option value="detik.com">Detik.com</option>
                        <option value="kompas.com">Kompas.com</option>
                        <option value="youtube.com">YouTube</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="text" id="searchFilter" placeholder="Cari konten..." 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="ml-auto text-sm text-gray-600">
                    Total: <span id="totalCount">0</span> item
                </div>
            </div>
            
            <!-- Dynamic Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead id="tableHeader">
                        <!-- Headers will be populated dynamically -->
                    </thead>
                    <tbody id="dynamicTableBody">
                        <!-- Content will be populated dynamically -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Menampilkan <span id="showingRange">0-0</span> dari <span id="totalItems">0</span> hasil
                </div>
                <div class="flex space-x-2" id="paginationControls">
                    <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors disabled:opacity-50" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="px-3 py-2 bg-blue-500 text-white rounded-lg text-sm">1</button>
                    <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Modal -->
<div id="dynamicModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 m-4 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800" id="modalTitle">Detail</h3>
            <button onclick="closeDynamicModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="modalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Modal proses analisis sentimen -->
<div id="sentimentProcessing" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-2xl p-6 m-4 max-w-sm w-full shadow-2xl">
    <div class="flex items-center space-x-3">
      <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
        <i class="fas fa-brain fa-spin text-blue-600 text-xl"></i>
      </div>
      <div>
        <h3 class="text-lg font-bold text-gray-800">Menganalisis Sentimen</h3>
        <p class="text-gray-600 text-sm">Mohon tunggu sebentar…</p>
      </div>
    </div>
    <div class="mt-4 text-sm text-gray-500" id="sentimentNote">Mengirim data ke server…</div>
  </div>
</div>

@endsection

@push('scripts')
<script>
// ✅ PERBAIKAN: Semua variabel global di sini
window.currentCategory = 'news';
let currentPage = 1;
const perPage = 50;
const ytFilter = { q: '', from: '', to: '' };

// Status scraping global
var isScrapingActive = false;
var scrapingInterval = null;

$(document).ready(function() {

    // Peringatan saat mau keluar kalau scraping masih jalan
    window.addEventListener('beforeunload', function (e) {
        if (isScrapingActive) {
            const message = 'Proses scraping masih berjalan. Yakin ingin meninggalkan halaman?';
            e.preventDefault();
            e.returnValue = message;
            return message;
        }
    });

    // Category Selection
    $('.category-card').on('click', function() {
        // Cegah switch kategori saat scraping
        if (isScrapingActive) {
            alert('Tunggu proses scraping selesai terlebih dahulu!');
            return false;
        }
        
        $('.category-card').removeClass('category-active border-blue-500 bg-blue-50 border-red-500 bg-red-50');
        
        if ($(this).is('#categoryNews')) {
            window.currentCategory = 'news';
            $(this).addClass('category-active border-blue-500 bg-blue-50');
            $('#newsSection').removeClass('hidden');
            $('#youtubeSection').addClass('hidden');
        } else {
            window.currentCategory = 'youtube';
            $(this).addClass('category-active border-red-500 bg-red-50');
            $('#newsSection').addClass('hidden');
            $('#youtubeSection').removeClass('hidden');
        }
        
        updateTableHeaders();
        loadResults(1);
    });
    
    // Update table headers based on category
    function updateTableHeaders() {
        let headers = '';
        if (window.currentCategory === 'news') {
            headers = `
                <tr class="border-b border-gray-200">
                    <th class="text-left py-4 px-2 font-medium text-gray-700">
                        <input type="checkbox" id="masterCheckbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Kutipan</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Berita URL</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Tanggal Publish</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Speaker</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Aksi</th>
                </tr>
            `;
        } else {
            headers = `
                <tr class="border-b border-gray-200">
                    <th class="text-left py-4 px-2 font-medium text-gray-700">
                        <input type="checkbox" id="masterCheckbox" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                    </th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Komentar</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Komentar Bersih</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Video URL</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Author</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Tanggal</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Aksi</th>
                </tr>
            `;
        }
        $('#tableHeader').html(headers);
        loadResults(1);
    }

    // Loader utama hasil scraping
    window.loadResults = async function(page = 1) {
        currentPage = page;

        const isYT = (window.currentCategory === 'youtube');
        const q    = isYT ? (ytFilter.q    || '') : ($('#searchFilter').val() || '').trim();
        const from = isYT ? (ytFilter.from || '') : ($('input[name="date_from"]').val() || '');
        const to   = isYT ? (ytFilter.to   || '') : ($('input[name="date_to"]').val()   || '');

        const base = (window.currentCategory === 'youtube')
          ? "{{ route('scraping.youtube.results') }}"
          : "{{ route('scraping.results') }}";

        const params = new URLSearchParams({ page, per_page: perPage });
        if (q)    params.set('q', q);
        if (from) params.set('date_from', from);
        if (to)   params.set('date_to', to);

        try {
          const res  = await fetch(`${base}?${params.toString()}`, { headers: { "Accept": "application/json" }});
          const json = await res.json();
          const rows = Array.isArray(json.data) ? json.data : [];
          const meta = json.meta || { current_page:1, last_page:1, per_page: perPage, total: rows.length };

          const start = rows.length ? (meta.current_page - 1) * meta.per_page + 1 : 0;
          const end   = rows.length ? start + rows.length - 1 : 0;
          $('#showingRange').text(`${start}-${end}`);
          $('#totalItems').text(meta.total);
          $('#totalCount').text(meta.total);

          let tbodyHtml = '';

          if (window.currentCategory === 'youtube') {
            tbodyHtml = rows.map((row, i) => {
              const title   = row.video_title || '-';
              const author  = row.author_name || '-';
              const url     = row.video_url || '#';
              const comment = (row.comment_text || '').toString();
              const clean   = (row.cleaned_text || '').toString();
              const tanggal = row.published_at || '-';

              return `
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                  <td class="py-4 px-2">
                    <input type="checkbox" name="selected_items[]" value="${row.id || i}" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                  </td>
                  <td class="py-4 px-4"><div class="text-sm text-gray-800">${escapeHtml(comment.slice(0,200))}</div></td>
                  <td class="py-4 px-4">
                    <div class="text-sm text-gray-800">
                      ${ clean ? escapeHtml(clean.slice(0,200)) : '<span class="text-gray-400">-</span>' }
                    </div>
                  </td>
                  <td class="py-4 px-4">
                    <a href="${url}" target="_blank" class="text-sm text-blue-700 underline">${escapeHtml(title)}</a>
                  </td>
                  <td class="py-4 px-4"><span class="text-sm text-gray-700">${escapeHtml(author)}</span></td>
                  <td class="py-4 px-4"><span class="text-sm text-gray-600">${escapeHtml(tanggal)}</span></td>
                  <td class="py-4 px-4">
                    <div class="flex space-x-2">
                      <button class="text-red-600 hover:text-red-800 text-sm" onclick="viewItem(${row.id}, 'youtube')">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button type="button" class="text-red-600 hover:text-red-800 text-sm" onclick="deleteItem(${row.id})">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>`;
            }).join('');
          } 
          else {
            // render NEWS
            tbodyHtml = rows.map((row, i) => {
              const host = (() => { try { return new URL(row.news_url).host } catch { return row.news_source || '-' }})();
              const kutipan = (row.original_text || '').toString().slice(0, 160);
              const tanggal = row.published_at || '-';
              const speaker = row.speaker || '-';
              const status  = row.processing_status || '-';
              return `
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                  <td class="py-4 px-2">
                    <input type="checkbox" name="selected_items[]" value="${row.id || i}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  </td>
                  <td class="py-4 px-4">
                    <div>
                      <p class="text-gray-800 text-sm">${escapeHtml(kutipan)}</p>
                      ${row.context ? `<p class="text-gray-500 text-xs mt-1">${escapeHtml(row.context)}</p>` : ''}
                      <p class="text-gray-400 text-xs mt-1">Status: ${escapeHtml(status)}</p>
                    </div>
                  </td>
                  <td class="py-4 px-4">
                    <a href="${row.news_url}" target="_blank" class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">${escapeHtml(host)}</a>
                  </td>
                  <td class="py-4 px-4">
                    <span class="text-gray-600 text-sm">${escapeHtml(tanggal)}</span>
                  </td>
                  <td class="py-4 px-4">
                    <span class="text-gray-700 text-sm">${escapeHtml(speaker)}</span>
                  </td>
                  <td class="py-4 px-4">
                    <div class="flex space-x-2">
                      <button class="text-blue-600 hover:text-blue-800 text-sm" onclick="viewItem(${row.id}, 'news')">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button type="button" class="text-red-600 hover:text-red-800 text-sm" onclick="deleteItem(${row.id})">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>`;
            }).join('');
          }

          $('#dynamicTableBody').html(tbodyHtml || `<tr><td colspan="7" class="py-6 text-center text-gray-500">Belum ada data</td></tr>`);
          renderPagination(meta);
        } catch (e) {
          console.error(e);
          $('#dynamicTableBody').html(`<tr><td colspan="7" class="py-6 text-center text-red-600">Gagal memuat data</td></tr>`);
        }
    }; // end window.loadResults

    function renderPagination(meta) {
        const prevDisabled = meta.current_page <= 1 ? 'disabled opacity-50' : '';
        const nextDisabled = meta.current_page >= meta.last_page ? 'disabled opacity-50' : '';

        const html = `
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 ${prevDisabled}"
                    data-page="${meta.current_page - 1}">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="px-3 py-2 bg-blue-500 text-white rounded-lg text-sm">${meta.current_page}</button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 ${nextDisabled}"
                    data-page="${meta.current_page + 1}">
                <i class="fas fa-chevron-right"></i>
            </button>
        `;

        $('#paginationControls').html(html);
    }

    // delegasi klik pagination
    $(document).on('click', '#paginationControls button[data-page]', function () {
        const to = parseInt($(this).attr('data-page'), 10);
        if (!isNaN(to) && to > 0) { 
            loadResults(to);
            $('html,body').animate({ scrollTop: $('#resultsSection').offset().top - 80 }, 200);
        }
    });

    function debounce(fn, delay = 350) {
      let t;
      return function (...args) {
        const ctx = this;
        clearTimeout(t);
        t = setTimeout(() => fn.apply(ctx, args), delay);
      };
    }

    window.escapeHtml = function (s) {
        return String(s).replace(/[&<>"']/g, m => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[m]));
    };

    function highlight(text, q) {
        if (!q) return escapeHtml(String(text ?? ''));
        const esc = escapeHtml(String(text ?? ''));
        const safe = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const re = new RegExp(safe, 'gi');
        return esc.replace(re, (m) => `<mark class="bg-yellow-200">${m}</mark>`);
    }

    // Init awal
    updateTableHeaders();

    // FORM SEARCH NEWS (filter di DB & highlight)
    $('#searchScrapingForm').on('submit', async function (e) {
        e.preventDefault();

        const q     = ($('input[name="keywords"]').val() || '').toString().trim();
        const from  = $('input[name="date_from"]').val() || '';
        const to    = $('input[name="date_to"]').val() || '';

        // kalau semua kosong -> balik mode normal
        if (q === '' && from === '' && to === '') {
            $('#paginationControls').show();
            await loadResults(1);
            return;
        }

        $('#paginationControls').hide();

        try {
            const params = new URLSearchParams();
            if (q)    params.set('q', q);
            if (from) params.set('date_from', from);
            if (to)   params.set('date_to', to);

            const url = "{{ route('scraping.search') }}" + '?' + params.toString();
            const res = await fetch(url, { headers: { "Accept": "application/json" }});
            const json = await res.json();

            const rows  = Array.isArray(json.data) ? json.data : [];
            const total = (json.meta && typeof json.meta.total === 'number') ? json.meta.total : rows.length;

            $('#totalCount').text(total);
            $('#showingRange').text(rows.length ? `1-${rows.length}` : '0-0');
            $('#totalItems').text(total);

            const tbodyHtml = rows.map((row, i) => {
                const host    = (() => { try { return new URL(row.news_url).host } catch { return row.news_source || '-' }})();
                const kutipan = (row.original_text || '').toString().slice(0, 160);
                const tanggal = row.published_at || '-';
                const speaker = row.speaker || '-';
                const status  = row.processing_status || '-';

                return `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-2">
                            <input type="checkbox" name="selected_items[]" value="${row.id || i}"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="py-4 px-4">
                            <div>
                                <p class="text-gray-800 text-sm">${highlight(kutipan, q)}</p>
                                ${row.context ? `<p class="text-gray-500 text-xs mt-1">${highlight(row.context, q)}</p>` : ''}
                                <p class="text-gray-400 text-xs mt-1">Status: ${escapeHtml(status)}</p>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <a href="${row.news_url}" target="_blank"
                                class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                ${highlight(host, q)}
                            </a>
                        </td>
                        <td class="py-4 px-4"><span class="text-gray-600 text-sm">${escapeHtml(tanggal)}</span></td>
                        <td class="py-4 px-4"><span class="text-gray-700 text-sm">${highlight(speaker, q)}</span></td>
                        <td class="py-4 px-4">
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-800 text-sm" onclick="viewItem(${row.id || i}, 'news')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="text-red-600 hover:text-red-800 text-sm" onclick="deleteItem(${row.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
            }).join('');

            $('#dynamicTableBody').html(
                tbodyHtml || `<tr><td colspan="6" class="py-6 text-center text-gray-500">
                    Tidak ada hasil untuk "${escapeHtml(q)}"
                </td></tr>`
            );

        } catch (err) {
            console.error(err);
            $('#dynamicTableBody').html(
                `<tr><td colspan="6" class="py-6 text-center text-red-600">Gagal memuat hasil pencarian</td></tr>`
            );
        }
    });

    // Helper tanggal YT
    function pad2(n){ return String(n).padStart(2,'0'); }
    function formatYMD(d){ return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; }
    function computeUploadRange(key) {
      if (!key) return { from:'', to:'' };
      const now = new Date();
      if (key === 'hour' || key === 'today') {
        const ymd = formatYMD(now); return { from: ymd, to: ymd };
      }
      if (key === 'week')  { const f=new Date(now); f.setDate(now.getDate()-7);  return { from:formatYMD(f), to:formatYMD(now) }; }
      if (key === 'month') { const f=new Date(now); f.setMonth(now.getMonth()-1); return { from:formatYMD(f), to:formatYMD(now) }; }
      if (key === 'year')  { const f=new Date(now); f.setFullYear(now.getFullYear()-1); return { from:formatYMD(f), to:formatYMD(now) }; }
      return { from:'', to:'' };
    }

    // Cari komentar YouTube dengan preset
    $('#youtubeSearchForm').on('submit', async function (e) {
      e.preventDefault();

      const q      = ($('input[name="search_query"]').val() || '').trim();
      const preset = ($('select[name="upload_date"]').val() || '').trim();

      if (!q) { alert('Masukkan kata kunci pencarian!'); return; }

      const { from, to } = computeUploadRange(preset);

      ytFilter.q    = q;
      ytFilter.from = from;
      ytFilter.to   = to;

      if (window.currentCategory !== 'youtube') $('#categoryYoutube').click();

      $('#paginationControls').show();
      await loadResults(1);
    });

    // Clear buttons
    $('#clearLinks').on('click', function() {
        $('#newsLinks').val('');
    });
    
    $('#clearYoutubeUrls').on('click', function() {
        $('#youtubeUrls').val('');
    });
    
    // Master Checkbox & Select All
    $(document).on('change', '#masterCheckbox', function() {
        $('input[name="selected_items[]"]').prop('checked', this.checked);
    });
    
    $('#selectAll').on('click', function() {
        $('input[name="selected_items[]"]').prop('checked', true);
        $('#masterCheckbox').prop('checked', true);
    });

    // cegah Enter di field cari memicu submit
    $(document).on('keydown', '#searchFilter', function (e) {
      if (e.key === 'Enter') e.preventDefault();
    });

    // pencarian live (news & youtube)
    $(document).on('input', '#searchFilter', debounce(async function () {
      const q = ($(this).val() || '').toString().trim();

      if (q === '') {
        $('#paginationControls').show();
        await loadResults(1);
        return;
      }

      $('#paginationControls').hide();

      try {
        let url;
        if (window.currentCategory === 'youtube') {
          const params = new URLSearchParams({ q, page: 1, per_page: perPage });
          url = "{{ route('scraping.youtube.results') }}" + "?" + params.toString();
        } else {
          url = "{{ route('scraping.search') }}" + `?q=${encodeURIComponent(q)}`;
        }

        const res  = await fetch(url, { headers: { "Accept": "application/json" }});
        const json = await res.json();
        const rows = Array.isArray(json.data) ? json.data : [];
        const total = (json.meta && typeof json.meta.total === 'number') ? json.meta.total : rows.length;

        $('#totalCount').text(total);
        $('#showingRange').text(rows.length ? `1-${rows.length}` : '0-0');
        $('#totalItems').text(total);

        let tbodyHtml = '';

        if (window.currentCategory === 'youtube') {
          tbodyHtml = rows.map((row, i) => {
            const comment = (row.comment_text || '').toString();
            const clean   = (row.cleaned_text || '').toString();
            const url     = row.video_url || '#';
            const title   = row.video_title || '-';
            const author  = row.author_name   || '-';
            const tanggal = row.published_at || '-';

            return `
              <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                <td class="py-4 px-2">
                  <input type="checkbox" name="selected_items[]" value="${row.id || i}" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                </td>
                <td class="py-4 px-4"><div class="text-sm text-gray-800">${highlight(comment.slice(0,200), q)}</div></td>
                <td class="py-4 px-4">
                  <div class="text-sm text-gray-800">
                    ${ clean ? escapeHtml(clean.slice(0,200)) : '<span class="text-gray-400">-</span>' }
                  </div>
                </td>
                <td class="py-4 px-4"><a href="${url}" target="_blank" class="text-sm text-blue-700 underline">${escapeHtml(title)}</a></td>
                <td class="py-4 px-4"><span class="text-sm text-gray-700">${escapeHtml(author)}</span></td>
                <td class="py-4 px-4"><span class="text-sm text-gray-600">${escapeHtml(tanggal)}</span></td>
                <td class="py-4 px-4">
                  <div class="flex space-x-2">
                    <button class="text-red-600 hover:text-red-800 text-sm" onclick="viewItem(${row.id}, 'youtube')"><i class="fas fa-eye"></i></button>
                    <button type="button" class="text-red-600 hover:text-red-800 text-sm" onclick="deleteItem(${row.id})"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>`;
          }).join('');
        } else {
          tbodyHtml = rows.map((row, i) => {
            const host    = (() => { try { return new URL(row.news_url).host } catch { return row.news_source || '-' }})();
            const kutipan = (row.original_text || '').toString().slice(0, 160);
            const tanggal = row.published_at || '-';
            const speaker = row.speaker || '-';
            const status  = row.processing_status || '-';

            return `
              <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                <td class="py-4 px-2">
                  <input type="checkbox" name="selected_items[]" value="${row.id || i}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </td>
                <td class="py-4 px-4">
                  <div>
                    <p class="text-gray-800 text-sm">${highlight(kutipan, q)}</p>
                    ${row.context ? `<p class="text-gray-500 text-xs mt-1">${highlight(row.context, q)}</p>` : ''}
                    <p class="text-gray-400 text-xs mt-1">Status: ${escapeHtml(status)}</p>
                  </div>
                </td>
                <td class="py-4 px-4"><a href="${row.news_url}" target="_blank" class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">${highlight(host, q)}</a></td>
                <td class="py-4 px-4"><span class="text-gray-600 text-sm">${escapeHtml(tanggal)}</span></td>
                <td class="py-4 px-4"><span class="text-gray-700 text-sm">${highlight(speaker, q)}</span></td>
                <td class="py-4 px-4">
                  <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-800 text-sm" onclick="viewItem(${row.id}, 'news')"><i class="fas fa-eye"></i></button>
                    <button type="button" class="text-red-600 hover:text-red-800 text-sm" onclick="deleteItem(${row.id})"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>`;
          }).join('');
        }

        $('#dynamicTableBody').html(
          tbodyHtml || `<tr><td colspan="7" class="py-6 text-center text-gray-500">Tidak ada hasil untuk "${escapeHtml(q)}"</td></tr>`
        );

      } catch (e) {
        console.error(e);
        $('#dynamicTableBody').html(`<tr><td colspan="7" class="py-6 text-center text-red-600">Gagal memuat hasil pencarian</td></tr>`);
      }
    }, 350));

    // Source Filter (client-side sederhana)
    $('#sourceFilter').on('change', function() {
        const value = $(this).val();
        if (value === '') {
            $('#dynamicTableBody tr').show();
        } else {
            $('#dynamicTableBody tr').each(function() {
                $(this).toggle($(this).text().indexOf(value) > -1);
            });
        }
    });

}); // end document.ready

// =========================
// View Item (detail modal)
// =========================
async function viewItem(id, type) {
  try {
    if (type === 'news') {
      const url = "{{ route('scraping.item', ['id' => '__ID__']) }}".replace('__ID__', encodeURIComponent(id));
      const res = await fetch(url, { headers: { "Accept": "application/json" }});
      if (!res.ok) { alert('Gagal memuat detail item.'); return; }
      const json = await res.json();
      const d = json?.data;
      if (!d) { alert('Gagal memuat detail item.'); return; }

      const title   = d.title || '(Tanpa judul)';
      const source  = d.news_source || (d.news_url ? new URL(d.news_url).host : '-');
      const tgl     = d.published_at || d.scraped_at || '-';
      const speaker = d.speaker || '-';
      const quote   = d.original_text || '';
      const clean   = d.cleaned_text || '';

      const linkBtn = d.news_url
        ? `<a href="${d.news_url}" target="_blank" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
             <i class="fas fa-external-link-alt mr-2"></i>Buka Link Asli
           </a>`
        : '';

      const cleanedBlock = clean && clean !== quote
        ? `<div class="bg-gray-50 p-3 rounded-lg mt-3">
             <div class="text-xs text-gray-500 mb-1">Teks Bersih:</div>
             <p class="text-gray-800">${escapeHtml(clean)}</p>
           </div>`
        : '';

      const modalHtml = `
        <div class="space-y-4">
          <div>
            <h4 class="font-bold text-lg mb-2">${escapeHtml(title)}</h4>
            <div class="flex items-center flex-wrap gap-4 text-sm text-gray-600 mb-2">
              <span><i class="fas fa-calendar mr-1"></i>${escapeHtml(tgl)}</span>
              <span><i class="fas fa-globe mr-1"></i>${escapeHtml(source)}</span>
              <span><i class="fas fa-user mr-1"></i>${escapeHtml(speaker)}</span>
            </div>
          </div>

          <div class="bg-white border rounded-lg p-4">
            <div class="text-xs text-gray-500 mb-1">Kutipan Asli:</div>
            <p class="text-gray-900">${escapeHtml(quote)}</p>
            ${cleanedBlock}
          </div>

          <div class="flex space-x-3 pt-4 border-t">
            <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
              <i class="fas fa-brain mr-2"></i>Analisis Sentimen
            </button>
            ${linkBtn}
          </div>
        </div>
      `;

      $('#modalTitle').text('Detail Kutipan');
      $('#modalContent').html(modalHtml);
      $('#dynamicModal').removeClass('hidden').addClass('flex');
      return;
    }

    if (type === 'youtube') {
      const url = "{{ route('scraping.youtube.item', ['id' => '__ID__']) }}".replace('__ID__', encodeURIComponent(id));
      const res = await fetch(url, { headers: { "Accept": "application/json" }});
      if (!res.ok) { alert('Gagal memuat detail komentar.'); return; }
      const json = await res.json();
      const d = json?.data; if (!d) { alert('Gagal memuat detail komentar.'); return; }

      const title   = d.video_title || '(Tanpa judul)';
      const host    = d.host || (d.video_url ? new URL(d.video_url).host : 'youtube.com');
      const tgl     = d.published_at || d.scraped_at || '-';
      const channel = d.channel_name || '-';
      const author  = d.author_name ? '@'+d.author_name : '-';
      const likes   = (d.likes_count ?? 0);
      const replies = (d.replies_count ?? 0);
      const comment = d.comment_text || '';
      const clean   = (d.cleaned_text || '').trim();

      const linkBtn = d.video_url
        ? `<a href="${d.video_url}" target="_blank"
             class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
             <i class="fas fa-external-link-alt mr-2"></i>Buka Video
           </a>` : '';

      const cleanedBlock = clean && clean !== comment
        ? `<div class="bg-gray-50 p-3 rounded-lg mt-3">
             <div class="text-xs text-gray-500 mb-1">Komentar Bersih:</div>
             <p class="text-gray-800">${escapeHtml(clean)}</p>
           </div>` : '';

      const metaRight = `
        <span class="ml-3 text-xs text-gray-500">❤ ${likes}</span>
        <span class="ml-3 text-xs text-gray-500">↩ ${replies}</span>
      `;

      const modalHtml = `
        <div class="space-y-4">
          <div>
            <h4 class="font-bold text-lg mb-2">${escapeHtml(title)}</h4>
            <div class="flex items-center flex-wrap gap-4 text-sm text-gray-600 mb-2">
              <span><i class="fas fa-calendar mr-1"></i>${escapeHtml(tgl)}</span>
              <span><i class="fas fa-globe mr-1"></i>${escapeHtml(host)}</span>
              <span><i class="fab fa-youtube mr-1"></i>${escapeHtml(channel)}</span>
              <span><i class="fas fa-user mr-1"></i>${escapeHtml(author)}</span>
              ${metaRight}
            </div>
            <div class="text-xs text-gray-500">Status: ${escapeHtml(d.processing_status || '-')}</div>
          </div>

          <div class="bg-white border rounded-lg p-4">
            <div class="text-xs text-gray-500 mb-1">Komentar Asli:</div>
            <p class="text-gray-900 whitespace-pre-wrap">${escapeHtml(comment)}</p>
            ${cleanedBlock}
          </div>

          <div class="flex space-x-3 pt-4 border-t">
            <button class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
              <i class="fas fa-brain mr-2"></i>Analisis Sentimen
            </button>
            ${linkBtn}
          </div>
        </div>
      `;

      $('#modalTitle').text('Detail Komentar');
      $('#modalContent').html(modalHtml);
      $('#dynamicModal').removeClass('hidden').addClass('flex');
      return;
    }
  } catch (e) {
    console.error(e);
    alert('Gagal memuat detail item.');
  }
}

// DELETE satu item (news/youtube sesuai kategori aktif)
async function deleteItem(id) {
  if (!confirm('Hapus item ini?')) return;

  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (!token) {
    alert('CSRF token tidak ditemukan');
    return;
  }

  const isYoutube = (window.currentCategory === 'youtube');
  const urlTpl = isYoutube
    ? "{{ route('scraping.youtube.destroy', ['id' => '__ID__']) }}"
    : "{{ route('scraping.destroy', ['id' => '__ID__']) }}";
  const url = urlTpl.replace('__ID__', encodeURIComponent(id));

  try {
    const res = await fetch(url, {
      method: 'DELETE',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });

    const textResponse = await res.text();
    let json = null;
    if (textResponse) {
      try { json = JSON.parse(textResponse); } catch {}
    }

    if (res.ok && (json?.success === true || json === null)) {
      await loadResults(window.currentPage || 1);
      return;
    }

    const errorMsg = json?.message || textResponse.substring(0, 100) || `Error HTTP ${res.status}`;
    alert('Gagal menghapus: ' + errorMsg);

  } catch (e) {
    console.error(e);
    alert('Terjadi kesalahan koneksi saat menghapus: ' + e.message);
  }
}

// Bulk delete
$(document).on('click', '#deleteSelected', async function () {
  const ids = $('input[name="selected_items[]"]:checked')
    .map(function(){ return parseInt($(this).val(), 10); })
    .get()
    .filter(Number.isInteger);

  if (ids.length === 0) { 
    alert('Pilih minimal satu item yang akan dihapus.'); 
    return; 
  }
  
  if (!confirm(`Hapus ${ids.length} item terpilih?`)) return;

  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const bulkUrl = (window.currentCategory === 'youtube')
    ? "{{ route('scraping.youtube.bulkDestroy') }}"
    : "{{ route('scraping.bulkDestroy') }}";

  try {
    const res = await fetch(bulkUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ ids })
    });

    let json = null;
    const contentType = res.headers.get('content-type');
    
    if (contentType && contentType.includes('application/json')) {
      try {
        json = await res.json();
      } catch (e) {
        console.error('Gagal parse JSON:', e);
      }
    }

    if (res.ok) {
      await loadResults(window.currentPage || 1);
      return;
    }

    const errorMsg = json?.message || `Error HTTP ${res.status}`;
    alert('Gagal menghapus: ' + errorMsg);

  } catch (e) {
    console.error('Error bulk delete:', e);
    alert('Terjadi kesalahan koneksi saat menghapus');
  }
});

// Close Modal
function closeDynamicModal() {
    $('#dynamicModal').addClass('hidden').removeClass('flex');
}

// ============================================
// PROGRESS BAR MANAGER (dari master)
// ============================================
const ProgressBar = {
    show() {
        $('#scrapingProgress').removeClass('hidden').addClass('flex');
        $('#miniProgress').addClass('hidden');
        isScrapingActive = true;
    },
    
    hide() {
        $('#scrapingProgress').removeClass('flex').addClass('hidden');
        $('#miniProgress').addClass('hidden');
        this.reset();
        isScrapingActive = false;
    },
    
    minimize() {
        $('#scrapingProgress').removeClass('flex').addClass('hidden');
        $('#miniProgress').removeClass('hidden');
    },
    
    maximize() {
        $('#scrapingProgress').removeClass('hidden').addClass('flex');
        $('#miniProgress').addClass('hidden');
    },
    
    reset() {
        $('#progressBar').css('width', '0%');
        $('#progressPercent').text('0%');
        $('#progressText').text('0/0');
        $('#miniProgressText').text('0/0');
        $('#currentTask').text('Menunggu...');
    },
    
    update(current, total, taskName = '') {
        const percentage = total > 0 ? Math.round((current / total) * 100) : 0;
        
        $('#progressBar').css('width', percentage + '%');
        $('#progressPercent').text(percentage + '%');
        $('#progressText').text(`${current}/${total}`);
        $('#miniProgressText').text(`${current}/${total} (${percentage}%)`);
        
        if (taskName) {
            $('#currentTask').text(taskName);
        }
    },
    
    setTask(text) {
        $('#currentTask').text(text);
    }
};

// Handler minimize/maximize progress
$('#minimizeProgress').on('click', function() {
    ProgressBar.minimize();
});

$('#miniProgress').on('click', function() {
    ProgressBar.maximize();
});

// Klik backdrop -> shake modal
$('#scrapingProgress').on('click', function(e) {
    if (e.target === this) {
        const modal = $(this).find('.bg-white').first();
        modal.addClass('animate-shake');
        setTimeout(() => modal.removeClass('animate-shake'), 500);
    }
});

// ============================================
// SUBMIT SCRAPING NEWS (REAL PROGRESS + JOB ID)
// ============================================
$('#linkScrapingForm').off('submit').on('submit', async function(e) {
    e.preventDefault();
    
    const links = $('#newsLinks').val().trim();
    if (!links) {
        alert('Masukkan minimal satu link berita!');
        return;
    }
    
    const urlArray = links.split('\n').filter(l => l.trim());
    const total = urlArray.length;
    
    isScrapingActive = true;
    ProgressBar.show();
    ProgressBar.update(0, total, 'Memulai scraping berita...');
    
    const $btn = $('#startLinkScraping');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Scraping...');
    
    try {
        const formData = new FormData(this);
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const contentType = response.headers.get('content-type');
        let result;
        
        if (contentType && contentType.includes('application/json')) {
            result = await response.json();
        } else {
            throw new Error('Response bukan JSON');
        }
        
        const jobId = result.job_id;
        if (!jobId) throw new Error('Job ID tidak ditemukan');
        
        if (scrapingInterval) clearInterval(scrapingInterval);
        
        scrapingInterval = setInterval(async () => {
            try {
                const progressUrl = "{{ route('scraping.progress') }}";
                const progressRes = await fetch(`${progressUrl}?job_id=${jobId}`);
                const progressData = await progressRes.json();
                
                ProgressBar.update(
                    progressData.current,
                    progressData.total,
                    progressData.message
                );
                
                if (progressData.status === 'completed') {
                    clearInterval(scrapingInterval);
                    scrapingInterval = null;
                    
                    ProgressBar.setTask(`✅ Selesai! ${progressData.completed} berhasil, ${progressData.failed} gagal`);
                    
                    setTimeout(() => {
                        ProgressBar.hide();
                        isScrapingActive = false;
                        $btn.prop('disabled', false).html('<i class="fas fa-play mr-2"></i>Mulai Scraping');
                        $('#newsLinks').val('');
                        loadResults(1);
                    }, 2000);
                }
            } catch (error) {
                console.error('Error checking progress:', error);
            }
        }, 1000);
        
    } catch (error) {
        console.error('Error scraping:', error);
        
        if (scrapingInterval) {
            clearInterval(scrapingInterval);
            scrapingInterval = null;
        }
        
        ProgressBar.setTask('❌ Scraping gagal');
        
        setTimeout(() => {
            ProgressBar.hide();
            isScrapingActive = false;
            $btn.prop('disabled', false).html('<i class="fas fa-play mr-2"></i>Mulai Scraping');
        }, 2000);
        
        alert('Gagal melakukan scraping: ' + error.message);
    }
});

// ============================================
// SUBMIT SCRAPING YOUTUBE
// ============================================
$('#youtubeUrlForm').off('submit').on('submit', async function(e) {
    e.preventDefault();
    
    const urls = $('#youtubeUrls').val().trim();
    if (!urls) {
        alert('Masukkan minimal satu URL YouTube!');
        return;
    }
    
    const urlArray = urls.split('\n').filter(l => l.trim());
    const total = urlArray.length;
    
    isScrapingActive = true;
    ProgressBar.show();
    ProgressBar.update(0, total, 'Memulai scraping YouTube...');
    
    const $btn = $('#startYoutubeScraping');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Scraping...');
    
    let progress = 0;
    scrapingInterval = setInterval(() => {
        progress++;
        ProgressBar.update(progress, total, `Mengambil komentar video ${progress}/${total}...`);
        
        if (progress >= total) {
            clearInterval(scrapingInterval);
            ProgressBar.setTask('Scraping YouTube selesai! ✅');
        }
    }, 1000);
    
    try {
        const formData = new FormData(this);
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const contentType = response.headers.get('content-type');
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Server error:', errorText.substring(0, 200));
            throw new Error(`Server error: ${response.status}`);
        }
        
        let result;
        if (contentType && contentType.includes('application/json')) {
            result = await response.json();
            console.log('YouTube scraping result:', result);
        } else {
            console.log('Response bukan JSON, tapi request sukses');
            result = { success: true };
        }
        
        setTimeout(() => {
            ProgressBar.hide();
            isScrapingActive = false;
            $btn.prop('disabled', false).html('<i class="fas fa-play mr-2"></i>Mulai Scraping');
            $('#youtubeUrls').val('');
            
            if (window.currentCategory !== 'youtube') {
                $('#categoryYoutube').click();
            } else {
                loadResults(1);
            }
        }, 2000);
        
    } catch (error) {
        console.error('Error scraping YouTube:', error);
        clearInterval(scrapingInterval);
        ProgressBar.setTask('Scraping gagal ❌');
        
        setTimeout(() => {
            ProgressBar.hide();
            isScrapingActive = false;
            $btn.prop('disabled', false).html('<i class="fas fa-play mr-2"></i>Mulai Scraping');
        }, 2000);
        
        alert('Gagal melakukan scraping YouTube. Cek console untuk detail.');
    }
});

// ============================================
// STOP BUTTON
// ============================================
$('#stopScraping').off('click').on('click', function() {
    if (confirm('Hentikan proses scraping?')) {
        if (scrapingInterval) {
            clearInterval(scrapingInterval);
            scrapingInterval = null;
        }
        ProgressBar.hide();
        isScrapingActive = false;
        $('#startLinkScraping').prop('disabled', false).html('<i class="fas fa-play mr-2"></i>Mulai Scraping');
        $('#startYoutubeScraping').prop('disabled', false).html('<i class="fas fa-play mr-2"></i>Mulai Scraping');
    }
});

// ==========================
// Sentiment Modal helper
// ==========================
const SentimentModal = {
  show(msg='Memproses…'){ 
    document.getElementById('sentimentNote').textContent = msg;
    $('#sentimentProcessing').removeClass('hidden').addClass('flex');
  },
  set(msg){ document.getElementById('sentimentNote').textContent = msg; },
  hide(){ $('#sentimentProcessing').removeClass('flex').addClass('hidden'); }
};

// ==========================
// ANALISIS SENTIMEN TERPILIH (AJAX ke route scraping.analyze)
// ==========================
$('#analyzeSelected').off('click.sentiment').on('click.sentiment', async function(e){
  e.preventDefault();

  if (typeof isScrapingActive !== 'undefined' && isScrapingActive) {
    const ok = confirm('Proses scraping masih berjalan. Lanjut analisis sekarang?');
    if (!ok) return;
  }

  const ids = $('input[name="selected_items[]"]:checked')
    .map(function(){ return parseInt($(this).val(),10); })
    .get()
    .filter(Number.isInteger);

  if (!ids.length) { alert('Pilih minimal satu item untuk dianalisis!'); return; }

  const type = (window.currentCategory === 'youtube') ? 'youtube' : 'news';

  SentimentModal.show('Mengirim daftar item terpilih…');

  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const res = await fetch(`{{ route('scraping.analyze') }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ ids, type })
    });

    SentimentModal.set('Menunggu hasil dari server…');

    if (!res.ok) {
      const t = await res.text();
      throw new Error(t || ('HTTP ' + res.status));
    }
    const out = await res.json();

    SentimentModal.set(`Selesai. ${out.analyzed ?? ids.length} item dianalisis.`);
    setTimeout(()=>{
      SentimentModal.hide();
      // tampilkan ringkasan di modal hasil (sekalian pakai dynamicModal)
      const rows = (out.details || []).slice(0, 10).map(d => `
        <tr class="border-b border-gray-100">
          <td class="py-2 px-3 text-xs text-gray-500">${d.id}</td>
          <td class="py-2 px-3 text-sm">${escapeHtml(d.preview || '')}</td>
          <td class="py-2 px-3">
            <span class="px-2 py-1 rounded-full text-xs
              ${d.label==='positive'?'bg-green-100 text-green-700':
                d.label==='negative'?'bg-red-100 text-red-700':
                d.label==='positive-neutral'?'bg-blue-100 text-blue-700':
                d.label==='negative-neutral'?'bg-orange-100 text-orange-700':
                'bg-yellow-100 text-yellow-700'}">${escapeHtml(d.label||'-')}</span>
          </td>
          <td class="py-2 px-3 text-xs text-gray-600">
            P:${(d.pos??0).toFixed?.(3)??'-'} /
            N:${(d.neg??0).toFixed?.(3)??'-'} /
            U:${(d.neu??0).toFixed?.(3)??'-'}
          </td>
        </tr>
      `).join('');

      const modalHtml = `
        <div class="space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
              <i class="fas fa-brain text-purple-600"></i>
            </div>
            <div>
              <div class="font-bold text-gray-800">Analisis Selesai</div>
              <div class="text-sm text-gray-600">${escapeHtml(out.message || '')}</div>
            </div>
          </div>
          <div class="overflow-x-auto border rounded-xl">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-200 bg-gray-50">
                  <th class="py-2 px-3 text-left text-gray-700">ID</th>
                  <th class="py-2 px-3 text-left text-gray-700">Preview Teks</th>
                  <th class="py-2 px-3 text-left text-gray-700">Label</th>
                  <th class="py-2 px-3 text-left text-gray-700">Skor</th>
                </tr>
              </thead>
              <tbody>${rows || '<tr><td colspan="4" class="py-4 text-center text-gray-500">Tidak ada ringkasan.</td></tr>'}</tbody>
            </table>
          </div>
          <div class="flex gap-3">
            <a href="{{ route('sentiment') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              <i class="fas fa-table mr-2"></i>Lihat Tabel Analisis
            </a>
            <button onclick="closeDynamicModal()" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">
              Tutup
            </button>
          </div>
        </div>
      `;
      $('#modalTitle').text('Ringkasan Analisis Sentimen');
      $('#modalContent').html(modalHtml);
      $('#dynamicModal').removeClass('hidden').addClass('flex');

      try { loadResults(window.currentPage || 1); } catch (_) {}
    }, 600);

  } catch (err) {
    console.error(err);
    SentimentModal.set('Gagal memproses analisis. Coba lagi ya.');
    setTimeout(()=> SentimentModal.hide(), 1200);
    alert('Terjadi kesalahan saat analisis: ' + (err.message || err));
  }
});

// ==========================
// EXPORT BUTTON (dari scraping_page)
// ==========================

// Toggle Export Dropdown
document.getElementById('exportButton').addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('exportMenu').classList.toggle('hidden');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('exportDropdown');
    if (!dropdown.contains(e.target)) {
        document.getElementById('exportMenu').classList.add('hidden');
    }
});

// Ambil selected IDs
function getSelectedIds() {
    const ids = [];
    document.querySelectorAll('input[name="selected_items[]"]:checked').forEach(checkbox => {
        const value = parseInt(checkbox.value);
        if (!isNaN(value)) ids.push(value);
    });
    return ids;
}

// Generate export URL
function generateExportUrl(baseUrl, category, ids = null) {
    let url = baseUrl + '?category=' + category;
    if (ids && ids.length > 0) {
        url += '&ids=' + encodeURIComponent(JSON.stringify(ids));
    }
    return url;
}

// Export Semua - Excel
document.getElementById('exportExcelAll').addEventListener('click', function() {
    const category = window.currentCategory || 'news';
    const url = generateExportUrl("{{ route('scraping.export.excel') }}", category);
    window.location.href = url;
    document.getElementById('exportMenu').classList.add('hidden');
});

// Export Semua - CSV
document.getElementById('exportCSVAll').addEventListener('click', function() {
    const category = window.currentCategory || 'news';
    const url = generateExportUrl("{{ route('scraping.export.csv') }}", category);
    window.location.href = url;
    document.getElementById('exportMenu').classList.add('hidden');
});

// Export Terpilih - Excel
document.getElementById('exportExcelSelected').addEventListener('click', function() {
    const ids = getSelectedIds();
    
    if (ids.length === 0) {
        alert('Pilih minimal satu item untuk di-export!');
        return;
    }
    
    const category = window.currentCategory || 'news';
    const itemType = category === 'news' ? 'berita' : 'komentar';
    
    if (confirm(`Export ${ids.length} ${itemType} yang dipilih ke Excel?`)) {
        const url = generateExportUrl("{{ route('scraping.export.excel') }}", category, ids);
        window.location.href = url;
        document.getElementById('exportMenu').classList.add('hidden');
    }
});

// Export Terpilih - CSV
document.getElementById('exportCSVSelected').addEventListener('click', function() {
    const ids = getSelectedIds();
    
    if (ids.length === 0) {
        alert('Pilih minimal satu item untuk di-export!');
        return;
    }
    
    const category = window.currentCategory || 'news';
    const itemType = category === 'news' ? 'berita' : 'komentar';
    
    if (confirm(`Export ${ids.length} ${itemType} yang dipilih ke CSV?`)) {
        const url = generateExportUrl("{{ route('scraping.export.csv') }}", category, ids);
        window.location.href = url;
        document.getElementById('exportMenu').classList.add('hidden');
    }
});

// Update style export selected (opsional)
$(document).on('change', 'input[name="selected_items[]"]', function() {
    const selectedCount = $('input[name="selected_items[]"]:checked').length;
    
    if (selectedCount > 0) {
        $('#exportExcelSelected').removeClass('text-gray-400').addClass('text-gray-700');
        $('#exportCSVSelected').removeClass('text-gray-400').addClass('text-gray-700');
    } else {
        $('#exportExcelSelected').removeClass('text-gray-700').addClass('text-gray-400');
        $('#exportCSVSelected').removeClass('text-gray-700').addClass('text-gray-400');
    }
});
</script>

<style>
#exportMenu {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

#exportMenu button {
    transition: all 0.2s ease;
    text-decoration: none;
    color: inherit;
}

#exportMenu button:hover {
    background-color: #f9fafb;
}

#exportMenu button:first-child {
    border-radius: 0.5rem 0.5rem 0 0;
}

#exportMenu button:last-child {
    border-radius: 0 0 0.5rem 0.5rem;
}

#exportMenu button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
@endpush

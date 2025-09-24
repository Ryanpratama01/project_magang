@extends('layouts.app')

@section('title', 'Scraping - Sistem Analisis Sentimen')
@section('page-title', 'Scraping')
@section('page-description', 'Ambil data dari berbagai sumber untuk analisis sentimen')

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
                        <div class="flex items-center mt-2 space-x-2">

                        </div>
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
                        <div class="flex items-center mt-2 space-x-2">
                        </div>
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
                
                <form id="linkScrapingForm" class="space-y-4">
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
                        <i class="fas fa-search mr-2"></i>Cari & Scrape
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
                
                <form id="youtubeUrlForm" class="space-y-4">
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
                    
                    <button type="submit" id="startYoutubeSearch" class="w-full px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red -700 transition-all duration-200 font-medium">
                        <i class="fas fa-search mr-2"></i>Cari & Scrape Komentar
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Scraping Progress -->
    <div id="scrapingProgress" class="bg-white rounded-2xl p-6 card-shadow hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Progress Scraping</h3>
            <button id="stopScraping" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                <i class="fas fa-stop mr-2"></i>Stop
            </button>
        </div>
        <div class="space-y-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Progress</span>
                <span id="progressText" class="font-medium">0/0</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div id="progressBar" class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div id="currentTask" class="text-sm text-gray-600">Menunggu...</div>
        </div>
    </div>
    
    <!-- Results Table -->
    <div id="resultsSection" class="bg-white rounded-2xl card-shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Hasil Scraping</h3>
                    <p class="text-gray-600 text-sm" id="resultsDescription">Daftar data yang berhasil diambil</p>
                </div>
                <div class="flex space-x-3">
                    <button id="selectAll" class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        <i class="fas fa-check-square mr-2"></i>Pilih Semua
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let isScrapingActive = false;
    let currentCategory = 'news';
    
    // Category Selection
    $('.category-card').on('click', function() {
        $('.category-card').removeClass('category-active border-blue-500 bg-blue-50 border-red-500 bg-red-50')
                           .addClass('border-gray-200 bg-gray-50');
        
        if ($(this).is('#categoryNews')) {
            currentCategory = 'news';
            $(this).addClass('category-active border-blue-500 bg-blue-50');
            $('#newsSection').removeClass('hidden');
            $('#youtubeSection').addClass('hidden');
            $('#resultsDescription').text('Daftar berita yang berhasil diambil');
        } else {
            currentCategory = 'youtube';
            $(this).addClass('category-active border-red-500 bg-red-50');
            $('#newsSection').addClass('hidden');
            $('#youtubeSection').removeClass('hidden');
            $('#resultsDescription').text('Daftar komentar YouTube yang berhasil diambil');
        }
        
        updateTableHeaders();
    });
    
    // Update table headers based on category
    function updateTableHeaders() {
        let headers = '';
        if (currentCategory === 'news') {
            headers = `
                <tr class="border-b border-gray-200">
                    <th class="text-left py-4 px-2 font-medium text-gray-700">
                        <input type="checkbox" id="masterCheckbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Kutipan</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Berita URL</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Tanggal</th>
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
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Komentar Bersih</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Video URL</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Author</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Tanggal</th>
                    <th class="text-left py-4 px-4 font-medium text-gray-700">Aksi</th>
                </tr>
            `;
        }
        $('#tableHeader').html(headers);
        loadSampleData();
    }
    
    // Load sample data based on category
    function loadSampleData() {
        let tbody = '';
        $('#totalCount').text(currentCategory === 'news' ? '10' : '15');
        
        if (currentCategory === 'news') {
            for (let i = 1; i <= 10; i++) {
                tbody += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-2">
                            <input type="checkbox" name="selected_items[]" value="${i}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="py-4 px-4">
                            <div>
                                <h4 class="font-medium text-gray-800 mb-1">Berita Sample ${i} - Lorem ipsum dolor sit amet</h4>
                                <p class="text-gray-600 text-sm">Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">detik.com</span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="text-gray-600 text-sm">${new Date(Date.now() - i * 24 * 60 * 60 * 1000).toLocaleDateString('id-ID')}</span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="text-gray-700 text-sm">@user${i}</span>
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-800 text-sm" onclick="viewItem(${i}, 'news')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-800 text-sm" onclick="deleteItem(${i})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }
        } else {
            for (let i = 1; i <= 15; i++) {
                tbody += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-2">
                            <input type="checkbox" name="selected_items[]" value="${i}" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        </td>
                        <td class="py-4 px-4">
                            <div class="max-w-md">
                                <p class="text-gray-800 text-sm">Komentar YouTube sample ${i}: Videonya bagus banget! Sangat membantu untuk belajar...</p>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <div class="max-w-md">
                                <p class="text-gray-800 text-sm">Komentar YouTube sample ${i}: Videonya bagus banget! Sangat membantu untuk belajar...</p>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <div class="max-w-md">
                                <p class="text-gray-800 text-sm">Komentar YouTube sample ${i}: Videonya bagus banget! Sangat membantu untuk belajar...</p>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <div class="max-w-xs">
                                <p class="font-medium text-gray-800 text-sm">Tutorial ${i}</p>
                                <p class="text-gray-600 text-xs">Channel Name</p>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <span class="text-gray-700 text-sm">@user${i}</span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="text-gray-600 text-sm">${new Date(Date.now() - i * 60 * 60 * 1000).toLocaleDateString('id-ID')}</span>
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex space-x-2">
                                <button class="text-red-600 hover:text-red-800 text-sm" onclick="viewItem(${i}, 'youtube')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-800 text-sm" onclick="deleteItem(${i})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }
        $('#dynamicTableBody').html(tbody);
        $('#showingRange').text(`1-${currentCategory === 'news' ? '10' : '15'}`);
        $('#totalItems').text(currentCategory === 'news' ? '10' : '15');
    }
    
    // Initialize with news data
    updateTableHeaders();
    
    // News Scraping Forms
    $('#linkScrapingForm').on('submit', function(e) {
        e.preventDefault();
        const links = $('#newsLinks').val().trim();
        
        if (!links) {
            alert('Masukkan minimal satu link berita!');
            return;
        }
        
        startScraping('news_links', { links: links });
    });
    
    $('#searchScrapingForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            source: $('input[name="source"]').val(),
            keywords: $('input[name="keywords"]').val(),
            date_from: $('input[name="date_from"]').val(),
            date_to: $('input[name="date_to"]').val()
        };
        
        if (!formData.keywords) {
            alert('Masukkan kata kunci pencarian!');
            return;
        }
        
        startScraping('news_search', formData);
    });
    
    // YouTube Scraping Forms
    $('#youtubeUrlForm').on('submit', function(e) {
        e.preventDefault();
        const urls = $('#youtubeUrls').val().trim();
        const scrapeTypes = $('input[name="scrape_types[]"]:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (!urls) {
            alert('Masukkan minimal satu URL YouTube!');
            return;
        }
        
        if (scrapeTypes.length === 0) {
            alert('Pilih minimal satu jenis data untuk di-scrape!');
            return;
        }
        
        startScraping('youtube_urls', { urls: urls, types: scrapeTypes });
    });
    
    $('#youtubeSearchForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            search_query: $('input[name="search_query"]').val(),
            video_limit: $('select[name="video_limit"]').val(),
            duration_filter: $('select[name="duration_filter"]').val(),
            upload_date: $('select[name="upload_date"]').val()
        };
        
        if (!formData.search_query) {
            alert('Masukkan kata kunci pencarian!');
            return;
        }
        
        startScraping('youtube_search', formData);
    });
    
    // Clear buttons
    $('#clearLinks').on('click', function() {
        $('#newsLinks').val('');
    });
    
    $('#clearYoutubeUrls').on('click', function() {
        $('#youtubeUrls').val('');
    });
    
    // Start Scraping
    function startScraping(type, data) {
        if (isScrapingActive) return;
        
        isScrapingActive = true;
        $('#scrapingProgress').removeClass('hidden');
        $('button[type="submit"]').prop('disabled', true);
        
        // Simulate scraping progress
        let progress = 0;
        let total = 0;
        let taskName = '';
        
        switch(type) {
            case 'news_links':
                total = data.links.split('\n').length;
                taskName = 'berita';
                break;
            case 'news_search':
                total = 10;
                taskName = 'berita';
                break;
            case 'youtube_urls':
                total = data.urls.split('\n').length;
                taskName = 'video YouTube';
                break;
            case 'youtube_search':
                total = parseInt(data.video_limit);
                taskName = 'video YouTube';
                break;
        }
        
        const interval = setInterval(() => {
            progress++;
            const percentage = (progress / total) * 100;
            
            $('#progressBar').css('width', percentage + '%');
            $('#progressText').text(progress + '/' + total);
            $('#currentTask').text(`Mengambil ${taskName} ${progress}...`);
            
            if (progress >= total) {
                clearInterval(interval);
                completeScraping();
            }
        }, 500);
    }
    
    // Complete Scraping
    function completeScraping() {
        isScrapingActive = false;
        $('#currentTask').text('Scraping selesai!');
        $('button[type="submit"]').prop('disabled', false);
        
        setTimeout(() => {
            $('#scrapingProgress').addClass('hidden');
            $('#progressBar').css('width', '0%');
            $('#progressText').text('0/0');
            loadSampleData(); // Reload data to show new results
        }, 2000);
    }
    
    // Stop Scraping
    $('#stopScraping').on('click', function() {
        isScrapingActive = false;
        $('#scrapingProgress').addClass('hidden');
        $('button[type="submit"]').prop('disabled', false);
    });
    
    // Master Checkbox
    $(document).on('change', '#masterCheckbox', function() {
        $('input[name="selected_items[]"]').prop('checked', this.checked);
    });
    
    // Select All
    $('#selectAll').on('click', function() {
        $('input[name="selected_items[]"]').prop('checked', true);
        $('#masterCheckbox').prop('checked', true);
    });
    
    // Analyze Selected
    $('#analyzeSelected').on('click', function() {
        const selected = $('input[name="selected_items[]"]:checked').length;
        if (selected === 0) {
            alert('Pilih minimal satu item untuk dianalisis!');
            return;
        }
        
        const itemType = currentCategory === 'news' ? 'berita' : 'komentar';
        if (confirm(`Analisis ${selected} ${itemType} yang dipilih?`)) {
            window.location.href = '{{ route("sentiment") }}';
        }
    });
    
    // Search Filter
    $('#searchFilter').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#dynamicTableBody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Source Filter
    $('#sourceFilter').on('change', function() {
        const value = $(this).val();
        if (value === '') {
            $('#dynamicTableBody tr').show();
        } else {
            $('#dynamicTableBody tr').filter(function() {
                $(this).toggle($(this).text().indexOf(value) > -1);
            });
        }
    });
});

// View Item Function
function viewItem(id, type) {
    let modalTitle = '';
    let modalContent = '';
    
    if (type === 'news') {
        modalTitle = 'Detail Kutipan';
        modalContent = `
            <div class="space-y-4">
                <div>
                    <h4 class="font-bold text-lg mb-2">Berita Sample ${id}</h4>
                    <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4">
                        <span><i class="fas fa-calendar mr-1"></i>16 September 2025</span>
                        <span><i class="fas fa-globe mr-1"></i>detik.com</span>
                    </div>
                </div>
                <div class="prose max-w-none">
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                    <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                </div>
                <div class="flex space-x-3 pt-4 border-t">
                    <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        <i class="fas fa-brain mr-2"></i>Analisis Sentimen
                    </button>
                    <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-external-link-alt mr-2"></i>Buka Link Asli
                    </button>
                </div>
            </div>
        `;
    } else {
        modalTitle = 'Detail Komentar YouTube';
        modalContent = `
            <div class="space-y-4">
                <div>
                    <h4 class="font-bold text-lg mb-2">Komentar dari @user${id}</h4>
                    <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4">
                        <span><i class="far fa-calendar mr-1"></i>16 September 2025</span>
                        <span><i class="far fa-thumbs-up mr-1"></i>${Math.floor(Math.random() * 100) + 1} likes</span>
                        <span><i class="far fa-comment mr-1"></i>${Math.floor(Math.random() * 10) + 1} replies</span>
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-800">Komentar YouTube sample ${id}: Videonya bagus banget! Sangat membantu untuk belajar. Terima kasih sudah berbagi ilmu yang bermanfaat. Semoga channelnya terus berkembang dan bisa memberikan konten-konten edukatif lainnya. Keep up the good work!</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <h5 class="font-medium text-red-800 mb-2">Video Information:</h5>
                    <p class="text-red-700 text-sm"><strong>Title:</strong> Tutorial Sample ${id}</p>
                    <p class="text-red-700 text-sm"><strong>Channel:</strong> Channel Name</p>
                    <p class="text-red-700 text-sm"><strong>URL:</strong> https://youtube.com/watch?v=sample${id}</p>
                </div>
                <div class="flex space-x-3 pt-4 border-t">
                    <button class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        <i class="fas fa-brain mr-2"></i>Analisis Sentimen
                    </button>
                    <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fab fa-youtube mr-2"></i>Lihat Video
                    </button>
                </div>
            </div>
        `;
    }
    
    $('#modalTitle').text(modalTitle);
    $('#modalContent').html(modalContent);
    $('#dynamicModal').removeClass('hidden').addClass('flex');
}

// Delete Item Function
function deleteItem(id) {
    const itemType = currentCategory === 'news' ? 'berita' : 'komentar';
    if (confirm(`Hapus ${itemType} ini?`)) {
        alert(`${itemType.charAt(0).toUpperCase() + itemType.slice(1)} berhasil dihapus!`);
    }
}

// Close Modal Function
function closeDynamicModal() {
    $('#dynamicModal').addClass('hidden').removeClass('flex');
}
</script>
@endpush
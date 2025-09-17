@extends('layouts.app')

@section('title', 'Analisis Sentimen - Sistem Analisis Sentimen')
@section('page-title', 'Analisis Sentimen')
@section('page-description', 'Analisis sentimen berita dan teks menggunakan AI')

@section('content')
<div class="space-y-8">
    <!-- Analysis Controls -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Text Input Analysis -->
        <div class="bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-keyboard text-purple-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Analisis Teks Manual</h3>
                    <p class="text-gray-600 text-sm">Masukkan teks untuk dianalisis sentimennya</p>
                </div>
            </div>
            
            <form id="textAnalysisForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Teks untuk Dianalisis</label>
                    <textarea 
                        id="analysisText"
                        name="text" 
                        rows="8" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                        placeholder="Masukkan teks berita atau kalimat yang ingin dianalisis sentimennya..."></textarea>
                    <div class="mt-2 text-sm text-gray-500">
                        <span id="charCount">0</span> karakter | Min. 10 karakter
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Model Analisis</label>
                        <select name="model" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="bert">BERT (Akurat)</option>
                            <option value="lstm">LSTM (Cepat)</option>
                            <option value="naive_bayes">Naive Bayes (Ringan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bahasa</label>
                        <select name="language" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="id">Bahasa Indonesia</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" id="analyzeTextBtn" class="w-full px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl hover:from-purple-600 hover:to-purple-700 transition-all duration-200 font-medium">
                    <i class="fas fa-brain mr-2"></i>Analisis Sekarang
                </button>
            </form>
        </div>
        
        <!-- Batch Analysis -->
        <div class="bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-list text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Analisis Batch</h3>
                    <p class="text-gray-600 text-sm">Analisis multiple berita sekaligus</p>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="text-center p-3 bg-gray-50 rounded-xl">
                    <div class="text-2xl font-bold text-gray-800">{{ $batchStats['total'] ?? '24' }}</div>
                    <div class="text-sm text-gray-600">Total Berita</div>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-xl">
                    <div class="text-2xl font-bold text-green-600">{{ $batchStats['analyzed'] ?? '18' }}</div>
                    <div class="text-sm text-gray-600">Sudah Dianalisis</div>
                </div>
                <div class="text-center p-3 bg-yellow-50 rounded-xl">
                    <div class="text-2xl font-bold text-yellow-600">{{ $batchStats['pending'] ?? '6' }}</div>
                    <div class="text-sm text-gray-600">Menunggu</div>
                </div>
            </div>
            
            <form id="batchAnalysisForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Sumber Data</label>
                    <select name="data_source" id="dataSource" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="scraped">Berita dari Scraping</option>
                        <option value="uploaded">File Upload (CSV/Excel)</option>
                        <option value="database">Data dari Database</option>
                    </select>
                </div>
                
                <div id="fileUploadSection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload File</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors">
                        <input type="file" id="fileUpload" name="file" accept=".csv,.xlsx,.xls" class="hidden">
                        <label for="fileUpload" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Klik untuk upload atau drag & drop file</p>
                            <p class="text-sm text-gray-500">CSV, Excel (.xlsx, .xls)</p>
                        </label>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Batch Size</label>
                        <select name="batch_size" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="10">10 item</option>
                            <option value="25" selected>25 item</option>
                            <option value="50">50 item</option>
                            <option value="100">100 item</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="low">Rendah</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">Tinggi</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" id="startBatchAnalysis" class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-medium">
                    <i class="fas fa-play mr-2"></i>Mulai Analisis Batch
                </button>
            </form>
        </div>
    </div>
    
    <!-- Analysis Result -->
    <div id="analysisResult" class="bg-white rounded-2xl p-6 card-shadow hidden">
        <h3 class="text-xl font-bold text-gray-800 mb-6">Hasil Analisis</h3>
        <div id="resultContent">
            <!-- Result will be populated here -->
        </div>
    </div>
    
    <!-- Batch Progress -->
    <div id="batchProgress" class="bg-white rounded-2xl p-6 card-shadow hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Progress Analisis Batch</h3>
            <button id="stopBatchAnalysis" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                <i class="fas fa-stop mr-2"></i>Stop
            </button>
        </div>
        <div class="space-y-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Progress</span>
                <span id="batchProgressText" class="font-medium">0/0</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div id="batchProgressBar" class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div id="currentAnalysis" class="text-sm text-gray-600">Menunggu...</div>
        </div>
    </div>
    
    <!-- Recent Analysis Results -->
    <div class="bg-white rounded-2xl card-shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Hasil Analisis Terbaru</h3>
                    <p class="text-gray-600 text-sm">Daftar analisis yang telah dilakukan</p>
                </div>
                <div class="flex space-x-3">
                    <button class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <button class="px-4 py-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Filter Row -->
            <div class="mb-6 flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Sentimen:</label>
                    <select id="sentimentFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Semua</option>
                        <option value="positive">Positif</option>
                        <option value="neutral">Netral</option>
                        <option value="negative">Negatif</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Akurasi:</label>
                    <select id="accuracyFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Semua</option>
                        <option value="high">> 90%</option>
                        <option value="medium">80-90%</option>
                        <option value="low">< 80%</option>
                    </select>
                </div>
                <div class="ml-auto text-sm text-gray-600">
                    Total: <span id="totalAnalysisCount">150</span> analisis
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Teks/Berita</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Sentimen</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Confidence</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Model</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Waktu</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="analysisTableBody">
                        @php
                        $sampleResults = [
                            ['text' => 'Ekonomi Indonesia menunjukkan pertumbuhan yang positif...', 'sentiment' => 'positive', 'confidence' => 92.5, 'model' => 'BERT'],
                            ['text' => 'Keadaan politik saat ini cukup mengkhawatirkan...', 'sentiment' => 'negative', 'confidence' => 87.3, 'model' => 'LSTM'],
                            ['text' => 'Pemerintah mengumumkan kebijakan baru tentang...', 'sentiment' => 'neutral', 'confidence' => 78.9, 'model' => 'BERT'],
                            ['text' => 'Teknologi AI berkembang pesat dan memberikan dampak...', 'sentiment' => 'positive', 'confidence' => 95.2, 'model' => 'BERT'],
                            ['text' => 'Situasi pandemi masih memerlukan perhatian khusus...', 'sentiment' => 'negative', 'confidence' => 89.7, 'model' => 'LSTM']
                        ];
                        @endphp
                        
                        @foreach($sampleResults as $index => $result)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="max-w-md">
                                    <p class="text-gray-800 text-sm">{{ Str::limit($result['text'], 80) }}</p>
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">politik</span>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">ekonomi</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                @if($result['sentiment'] === 'positive')
                                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full font-medium">
                                        <i class="fas fa-smile mr-1"></i>Positif
                                    </span>
                                @elseif($result['sentiment'] === 'negative')
                                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm rounded-full font-medium">
                                        <i class="fas fa-frown mr-1"></i>Negatif
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full font-medium">
                                        <i class="fas fa-meh mr-1"></i>Netral
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $result['confidence'] > 90 ? 'bg-green-500' : ($result['confidence'] > 80 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                             style="width: {{ $result['confidence'] }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ $result['confidence'] }}%</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ $result['model'] }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-gray-600 text-sm">{{ now()->subMinutes($index * 15)->format('H:i, d/m') }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-800 text-sm" onclick="viewAnalysisDetail({{ $index + 1 }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="text-green-600 hover:text-green-800 text-sm" onclick="reAnalyze({{ $index + 1 }})">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-800 text-sm" onclick="deleteAnalysis({{ $index + 1 }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Menampilkan 1-5 dari 150 hasil
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors disabled:opacity-50" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="px-3 py-2 bg-purple-500 text-white rounded-lg text-sm">1</button>
                    <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">2</button>
                    <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">3</button>
                    <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analysis Detail Modal -->
<div id="analysisDetailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 m-4 max-w-4xl w-full max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">Detail Analisis Sentimen</h3>
            <button onclick="closeAnalysisModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="analysisDetailContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let isAnalyzing = false;
    
    // Character count for text input
    $('#analysisText').on('input', function() {
        const length = $(this).val().length;
        $('#charCount').text(length);
        
        const btn = $('#analyzeTextBtn');
        if (length >= 10) {
            btn.prop('disabled', false).removeClass('opacity-50');
        } else {
            btn.prop('disabled', true).addClass('opacity-50');
        }
    });
    
    // Data source change handler
    $('#dataSource').on('change', function() {
        if ($(this).val() === 'uploaded') {
            $('#fileUploadSection').removeClass('hidden');
        } else {
            $('#fileUploadSection').addClass('hidden');
        }
    });
    
    // Text Analysis Form
    $('#textAnalysisForm').on('submit', function(e) {
        e.preventDefault();
        
        const text = $('#analysisText').val().trim();
        if (text.length < 10) {
            alert('Masukkan minimal 10 karakter untuk dianalisis!');
            return;
        }
        
        analyzeText(text);
    });
    
    // Batch Analysis Form
    $('#batchAnalysisForm').on('submit', function(e) {
        e.preventDefault();
        
        if (isAnalyzing) return;
        
        const dataSource = $('#dataSource').val();
        if (dataSource === 'uploaded') {
            const fileInput = $('#fileUpload')[0];
            if (!fileInput.files.length) {
                alert('Pilih file untuk diupload!');
                return;
            }
        }
        
        startBatchAnalysis();
    });
    
    // Analyze Text Function
    function analyzeText(text) {
        if (isAnalyzing) return;
        
        isAnalyzing = true;
        $('#analyzeTextBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Menganalisis...');
        
        // Simulate API call
        setTimeout(() => {
            const sentiments = ['positive', 'negative', 'neutral'];
            const randomSentiment = sentiments[Math.floor(Math.random() * sentiments.length)];
            const confidence = Math.floor(Math.random() * 30) + 70; // 70-100%
            
            showAnalysisResult({
                text: text,
                sentiment: randomSentiment,
                confidence: confidence,
                model: 'BERT',
                keywords: ['teknologi', 'positif', 'berkembang'],
                timestamp: new Date().toLocaleString('id-ID')
            });
            
            isAnalyzing = false;
            $('#analyzeTextBtn').prop('disabled', false).html('<i class="fas fa-brain mr-2"></i>Analisis Sekarang');
        }, 2000);
    }
    
    // Show Analysis Result
    function showAnalysisResult(result) {
        const sentimentClass = result.sentiment === 'positive' ? 'green' : (result.sentiment === 'negative' ? 'red' : 'yellow');
        const sentimentIcon = result.sentiment === 'positive' ? 'smile' : (result.sentiment === 'negative' ? 'frown' : 'meh');
        const sentimentText = result.sentiment === 'positive' ? 'Positif' : (result.sentiment === 'negative' ? 'Negatif' : 'Netral');
        
        const html = `
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium text-gray-800 mb-2">Teks yang Dianalisis</h4>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-700">${result.text}</p>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-800 mb-2">Kata Kunci</h4>
                        <div class="flex flex-wrap gap-2">
                            ${result.keywords.map(keyword => `<span class="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded">${keyword}</span>`).join('')}
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="text-center p-6 bg-${sentimentClass}-50 rounded-xl">
                        <div class="w-16 h-16 bg-${sentimentClass}-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-${sentimentIcon} text-2xl text-${sentimentClass}-600"></i>
                        </div>
                        <h4 class="text-xl font-bold text-${sentimentClass}-800 mb-2">${sentimentText}</h4>
                        <p class="text-${sentimentClass}-600">Confidence: ${result.confidence}%</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="font-semibold text-gray-800">${result.model}</div>
                            <div class="text-sm text-gray-600">Model</div>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="font-semibold text-gray-800">${result.timestamp}</div>
                            <div class="text-sm text-gray-600">Waktu</div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan Hasil
                        </button>
                        <button class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-share mr-2"></i>Bagikan
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#resultContent').html(html);
        $('#analysisResult').removeClass('hidden');
    }
    
    // Start Batch Analysis
    function startBatchAnalysis() {
        if (isAnalyzing) return;
        
        isAnalyzing = true;
        $('#batchProgress').removeClass('hidden');
        $('#startBatchAnalysis').prop('disabled', true);
        
        // Simulate batch analysis
        let progress = 0;
        const total = 25;
        
        const interval = setInterval(() => {
            progress++;
            const percentage = (progress / total) * 100;
            
            $('#batchProgressBar').css('width', percentage + '%');
            $('#batchProgressText').text(progress + '/' + total);
            $('#currentAnalysis').text('Menganalisis item ' + progress + '...');
            
            if (progress >= total) {
                clearInterval(interval);
                completeBatchAnalysis();
            }
        }, 300);
    }
    
    // Complete Batch Analysis
    function completeBatchAnalysis() {
        isAnalyzing = false;
        $('#currentAnalysis').text('Analisis batch selesai!');
        $('#startBatchAnalysis').prop('disabled', false);
        
        setTimeout(() => {
            $('#batchProgress').addClass('hidden');
            $('#batchProgressBar').css('width', '0%');
            $('#batchProgressText').text('0/0');
            
            // Show success message
            alert('Analisis batch berhasil diselesaikan! 25 item telah dianalisis.');
        }, 2000);
    }
    
    // Stop Batch Analysis
    $('#stopBatchAnalysis').on('click', function() {
        isAnalyzing = false;
        $('#batchProgress').addClass('hidden');
        $('#startBatchAnalysis').prop('disabled', false);
    });
    
    // Filter handlers
    $('#sentimentFilter, #accuracyFilter').on('change', function() {
        // Implement filtering logic here
        console.log('Filter applied');
    });
});

// View Analysis Detail Function
function viewAnalysisDetail(id) {
    const detailHtml = `
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <div>
                    <h4 class="font-medium text-gray-800 mb-2">Teks Lengkap</h4>
                    <div class="p-4 bg-gray-50 rounded-lg max-h-64 overflow-y-auto">
                        <p class="text-gray-700">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-800 mb-3">Analisis Kata</h4>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <span class="font-medium text-green-800">positif</span>
                            <span class="text-green-600">+0.8</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <span class="font-medium text-blue-800">berkembang</span>
                            <span class="text-blue-600">+0.6</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <span class="font-medium text-red-800">masalah</span>
                            <span class="text-red-600">-0.3</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="text-center p-6 bg-green-50 rounded-xl">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-smile text-2xl text-green-600"></i>
                    </div>
                    <h4 class="text-xl font-bold text-green-800 mb-2">Positif</h4>
                    <p class="text-green-600">92.5% Confidence</p>
                </div>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Model</span>
                        <span class="font-medium">BERT</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Waktu Proses</span>
                        <span class="font-medium">0.3s</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Bahasa</span>
                        <span class="font-medium">Indonesia</span>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <button class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        <i class="fas fa-redo mr-2"></i>Analisis Ulang
                    </button>
                    <button class="w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        <i class="fas fa-download mr-2"></i>Export Detail
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#analysisDetailContent').html(detailHtml);
    $('#analysisDetailModal').removeClass('hidden').addClass('flex');
}

// Re-analyze Function
function reAnalyze(id) {
    if (confirm('Analisis ulang item ini dengan model terbaru?')) {
        alert('Memulai analisis ulang...');
    }
}

// Delete Analysis Function
function deleteAnalysis(id) {
    if (confirm('Hapus hasil analisis ini?')) {
        alert('Hasil analisis berhasil dihapus!');
    }
}

// Close Analysis Modal
function closeAnalysisModal() {
    $('#analysisDetailModal').addClass('hidden').removeClass('flex');
}
</script>
@endpush
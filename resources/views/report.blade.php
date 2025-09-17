@extends('layouts.app')

@section('title', 'Cetak Laporan - Sistem Analisis Sentimen')
@section('page-title', 'Cetak Laporan')
@section('page-description', 'Generate dan export laporan analisis sentimen')

@section('content')
<div class="space-y-8">
    <!-- Report Generation -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Report Configuration -->
        <div class="bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-cog text-indigo-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Konfigurasi Laporan</h3>
                    <p class="text-gray-600 text-sm">Atur parameter laporan yang akan dibuat</p>
                </div>
            </div>
            
            <form id="reportForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Laporan</label>
                    <select name="report_type" id="reportType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="summary">Ringkasan Analisis</option>
                        <option value="detailed">Laporan Detail</option>
                        <option value="trend">Analisis Trend</option>
                        <option value="comparison">Perbandingan Periode</option>
                        <option value="custom">Custom Report</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="startDate" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="endDate" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sumber Data</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="sources[]" value="news" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Berita Online</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="sources[]" value="social" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Media Sosial</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="sources[]" value="manual" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Input Manual</span>
                        </label>
                    </div>
                </div>
                
                <div id="customOptions" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Opsi Custom</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="custom_options[]" value="keywords" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Analisis Kata Kunci</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="custom_options[]" value="charts" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Grafik dan Visualisasi</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="custom_options[]" value="raw_data" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Raw Data</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Format Export</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center justify-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="format" value="pdf" checked class="sr-only">
                            <div class="text-center">
                                <i class="fas fa-file-pdf text-red-500 text-xl mb-1"></i>
                                <div class="text-sm font-medium">PDF</div>
                            </div>
                        </label>
                        <label class="flex items-center justify-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="format" value="excel" class="sr-only">
                            <div class="text-center">
                                <i class="fas fa-file-excel text-green-500 text-xl mb-1"></i>
                                <div class="text-sm font-medium">Excel</div>
                            </div>
                        </label>
                        <label class="flex items-center justify-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="format" value="csv" class="sr-only">
                            <div class="text-center">
                                <i class="fas fa-file-csv text-blue-500 text-xl mb-1"></i>
                                <div class="text-sm font-medium">CSV</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <button type="submit" id="generateReport" class="w-full px-6 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-xl hover:from-indigo-600 hover:to-indigo-700 transition-all duration-200 font-medium">
                    <i class="fas fa-file-alt mr-2"></i>Generate Laporan
                </button>
            </form>
        </div>
        
        <!-- Quick Templates -->
        <div class="bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-templates text-green-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Template Cepat</h3>
                    <p class="text-gray-600 text-sm">Pilih template laporan yang sudah tersedia</p>
                </div>
            </div>
            
            <div class="space-y-3">
                @php
                $templates = [
                    ['name' => 'Laporan Harian', 'desc' => 'Analisis sentimen hari ini', 'icon' => 'calendar-day', 'color' => 'blue'],
                    ['name' => 'Laporan Mingguan', 'desc' => '7 hari terakhir dengan trend', 'icon' => 'calendar-week', 'color' => 'green'],
                    ['name' => 'Laporan Bulanan', 'desc' => 'Ringkasan lengkap 30 hari', 'icon' => 'calendar-alt', 'color' => 'purple'],
                    ['name' => 'Laporan Eksekutif', 'desc' => 'Summary untuk management', 'icon' => 'chart-pie', 'color' => 'indigo']
                ];
                @endphp
                
                @foreach($templates as $template)
                <div class="flex items-center p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer" onclick="useTemplate('{{ strtolower(str_replace(' ', '_', $template['name'])) }}')">
                    <div class="w-10 h-10 bg-{{ $template['color'] }}-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-{{ $template['icon'] }} text-{{ $template['color'] }}-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">{{ $template['name'] }}</h4>
                        <p class="text-sm text-gray-600">{{ $template['desc'] }}</p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </div>
                @endforeach
            </div>
            
            <div class="mt-6 p-4 bg-yellow-50 rounded-xl">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-lightbulb text-yellow-600 mt-1"></i>
                    <div>
                        <h4 class="font-medium text-yellow-800 mb-1">Tips</h4>
                        <p class="text-sm text-yellow-700">Gunakan template untuk membuat laporan dengan cepat. Anda bisa menyesuaikan parameter setelah memilih template.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Report Progress -->
    <div id="reportProgress" class="bg-white rounded-2xl p-6 card-shadow hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Membuat Laporan</h3>
            <button id="cancelReport" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                <i class="fas fa-times mr-2"></i>Batalkan
            </button>
        </div>
        <div class="space-y-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Progress</span>
                <span id="reportProgressText" class="font-medium">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div id="reportProgressBar" class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div id="currentStep" class="text-sm text-gray-600">Mempersiapkan...</div>
        </div>
    </div>
    
    <!-- Recent Reports -->
    <div class="bg-white rounded-2xl card-shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Laporan Terbaru</h3>
                    <p class="text-gray-600 text-sm">Riwayat laporan yang telah dibuat</p>
                </div>
                <div class="flex space-x-3">
                    <button class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    <button class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i>Hapus Semua
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Filter Controls -->
            <div class="mb-6 flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Jenis:</label>
                    <select id="typeFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Jenis</option>
                        <option value="summary">Ringkasan</option>
                        <option value="detailed">Detail</option>
                        <option value="trend">Trend</option>
                        <option value="comparison">Perbandingan</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Format:</label>
                    <select id="formatFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Format</option>
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>
                <div class="ml-auto text-sm text-gray-600">
                    Total: <span id="totalReports">15</span> laporan
                </div>
            </div>
            
            <!-- Reports Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Nama Laporan</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Jenis</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Periode</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Format</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Ukuran</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Dibuat</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $sampleReports = [
                            ['name' => 'Laporan Sentimen September 2025', 'type' => 'summary', 'period' => '01-30 Sep 2025', 'format' => 'pdf', 'size' => '2.4 MB', 'created' => '16 Sep 2025, 14:30'],
                            ['name' => 'Analisis Detail Politik', 'type' => 'detailed', 'period' => '10-16 Sep 2025', 'format' => 'excel', 'size' => '1.8 MB', 'created' => '16 Sep 2025, 10:15'],
                            ['name' => 'Trend Sentimen Mingguan', 'type' => 'trend', 'period' => '09-15 Sep 2025', 'format' => 'pdf', 'size' => '3.2 MB', 'created' => '15 Sep 2025, 16:45'],
                            ['name' => 'Data Raw Ekonomi', 'type' => 'custom', 'period' => '01-15 Sep 2025', 'format' => 'csv', 'size' => '874 KB', 'created' => '15 Sep 2025, 09:20'],
                            ['name' => 'Perbandingan Q3 2025', 'type' => 'comparison', 'period' => 'Jul-Sep 2025', 'format' => 'pdf', 'size' => '4.1 MB', 'created' => '14 Sep 2025, 13:10']
                        ];
                        @endphp
                        
                        @foreach($sampleReports as $index => $report)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-3">
                                    @if($report['format'] === 'pdf')
                                        <i class="fas fa-file-pdf text-red-500"></i>
                                    @elseif($report['format'] === 'excel')
                                        <i class="fas fa-file-excel text-green-500"></i>
                                    @else
                                        <i class="fas fa-file-csv text-blue-500"></i>
                                    @endif
                                    <span class="font-medium text-gray-800">{{ $report['name'] }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                @php
                                $typeColors = [
                                    'summary' => 'blue',
                                    'detailed' => 'green',
                                    'trend' => 'purple',
                                    'comparison' => 'indigo',
                                    'custom' => 'gray'
                                ];
                                $typeLabels = [
                                    'summary' => 'Ringkasan',
                                    'detailed' => 'Detail',
                                    'trend' => 'Trend',
                                    'comparison' => 'Perbandingan',
                                    'custom' => 'Custom'
                                ];
                                $color = $typeColors[$report['type']] ?? 'gray';
                                $label = $typeLabels[$report['type']] ?? 'Unknown';
                                @endphp
                                <span class="px-2 py-1 bg-{{ $color }}-100 text-{{ $color }}-800 text-xs rounded-full">{{ $label }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-gray-700 text-sm">{{ $report['period'] }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded uppercase">{{ $report['format'] }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-gray-600 text-sm">{{ $report['size'] }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-gray-600 text-sm">{{ $report['created'] }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-800 text-sm" onclick="downloadReport({{ $index + 1 }})">
                                        <i class="fas fa-download" title="Download"></i>
                                    </button>
                                    <button class="text-green-600 hover:text-green-800 text-sm" onclick="previewReport({{ $index + 1 }})">
                                        <i class="fas fa-eye" title="Preview"></i>
                                    </button>
                                    <button class="text-purple-600 hover:text-purple-800 text-sm" onclick="shareReport({{ $index + 1 }})">
                                        <i class="fas fa-share" title="Share"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-800 text-sm" onclick="deleteReport({{ $index + 1 }})">
                                        <i class="fas fa-trash" title="Delete"></i>
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
                    Menampilkan 1-5 dari 15 laporan
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors disabled:opacity-50" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="px-3 py-2 bg-indigo-500 text-white rounded-lg text-sm">1</button>
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

<!-- Report Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 m-4 max-w-4xl w-full max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">Preview Laporan</h3>
            <button onclick="closePreviewModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="previewContent">
            <!-- Preview content will be loaded here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let isGenerating = false;
    
    // Report type change handler
    $('#reportType').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#customOptions').removeClass('hidden');
        } else {
            $('#customOptions').addClass('hidden');
        }
    });
    
    // Format radio button styling
    $('input[name="format"]').on('change', function() {
        $('input[name="format"]').parent().removeClass('border-indigo-500 bg-indigo-50');
        $(this).parent().addClass('border-indigo-500 bg-indigo-50');
    });
    
    // Report form submission
    $('#reportForm').on('submit', function(e) {
        e.preventDefault();
        
        if (isGenerating) return;
        
        generateReport();
    });
    
    // Generate Report Function
    function generateReport() {
        if (isGenerating) return;
        
        isGenerating = true;
        $('#reportProgress').removeClass('hidden');
        $('#generateReport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Membuat...');
        
        const steps = [
            'Mengumpulkan data...',
            'Menganalisis sentimen...',
            'Membuat visualisasi...',
            'Menyusun laporan...',
            'Mengeksport file...'
        ];
        
        let currentStep = 0;
        const interval = setInterval(() => {
            const progress = ((currentStep + 1) / steps.length) * 100;
            $('#reportProgressBar').css('width', progress + '%');
            $('#reportProgressText').text(Math.round(progress) + '%');
            $('#currentStep').text(steps[currentStep]);
            
            currentStep++;
            
            if (currentStep >= steps.length) {
                clearInterval(interval);
                completeReportGeneration();
            }
        }, 800);
    }
    
    // Complete Report Generation
    function completeReportGeneration() {
        isGenerating = false;
        $('#currentStep').text('Laporan selesai dibuat!');
        $('#generateReport').prop('disabled', false).html('<i class="fas fa-file-alt mr-2"></i>Generate Laporan');
        
        setTimeout(() => {
            $('#reportProgress').addClass('hidden');
            $('#reportProgressBar').css('width', '0%');
            $('#reportProgressText').text('0%');
            
            // Show download prompt
            const reportName = 'Laporan_Sentimen_' + new Date().toISOString().slice(0,10) + '.pdf';
            if (confirm('Laporan berhasil dibuat! Download sekarang?')) {
                // Simulate download
                const link = document.createElement('a');
                link.href = '#';
                link.download = reportName;
                link.click();
            }
        }, 2000);
    }
    
    // Cancel Report Generation
    $('#cancelReport').on('click', function() {
        isGenerating = false;
        $('#reportProgress').addClass('hidden');
        $('#generateReport').prop('disabled', false).html('<i class="fas fa-file-alt mr-2"></i>Generate Laporan');
    });
    
    // Filter handlers
    $('#typeFilter, #formatFilter').on('change', function() {
        // Implement filtering logic here
        console.log('Filter applied');
    });
});

// Use Template Function
function useTemplate(templateType) {
    const templates = {
        'laporan_harian': {
            type: 'summary',
            startDate: new Date().toISOString().slice(0,10),
            endDate: new Date().toISOString().slice(0,10),
            sources: ['news']
        },
        'laporan_mingguan': {
            type: 'trend',
            startDate: new Date(Date.now() - 7*24*60*60*1000).toISOString().slice(0,10),
            endDate: new Date().toISOString().slice(0,10),
            sources: ['news', 'social']
        },
        'laporan_bulanan': {
            type: 'detailed',
            startDate: new Date(Date.now() - 30*24*60*60*1000).toISOString().slice(0,10),
            endDate: new Date().toISOString().slice(0,10),
            sources: ['news', 'social', 'manual']
        },
        'laporan_eksekutif': {
            type: 'summary',
            startDate: new Date(Date.now() - 30*24*60*60*1000).toISOString().slice(0,10),
            endDate: new Date().toISOString().slice(0,10),
            sources: ['news']
        }
    };
    
    const template = templates[templateType];
    if (template) {
        $('#reportType').val(template.type);
        $('#startDate').val(template.startDate);
        $('#endDate').val(template.endDate);
        
        $('input[name="sources[]"]').prop('checked', false);
        template.sources.forEach(source => {
            $('input[name="sources[]"][value="' + source + '"]').prop('checked', true);
        });
        
        alert('Template ' + templateType.replace('_', ' ') + ' telah diterapkan!');
    }
}

// Download Report Function
function downloadReport(id) {
    const filename = 'laporan_' + id + '.pdf';
    alert('Mengunduh ' + filename + '...');
    
    // Simulate download
    const link = document.createElement('a');
    link.href = '#';
    link.download = filename;
    link.click();
}

// Preview Report Function
function previewReport(id) {
    const previewHtml = `
        <div class="space-y-6">
            <!-- Report Header -->
            <div class="text-center border-b border-gray-200 pb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Laporan Analisis Sentimen</h2>
                <p class="text-gray-600">Periode: 01 - 30 September 2025</p>
                <p class="text-sm text-gray-500">Dibuat pada: 16 September 2025, 14:30 WIB</p>
            </div>
            
            <!-- Executive Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-green-50 rounded-xl">
                    <div class="text-3xl font-bold text-green-600">68%</div>
                    <div class="text-sm text-gray-600">Sentimen Positif</div>
                </div>
                <div class="text-center p-4 bg-yellow-50 rounded-xl">
                    <div class="text-3xl font-bold text-yellow-600">10%</div>
                    <div class="text-sm text-gray-600">Sentimen Netral</div>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-xl">
                    <div class="text-3xl font-bold text-red-600">22%</div>
                    <div class="text-sm text-gray-600">Sentimen Negatif</div>
                </div>
            </div>
            
            <!-- Key Insights -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4">Key Insights</h3>
                <div class="space-y-3">
                    <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg">
                        <i class="fas fa-lightbulb text-blue-600 mt-1"></i>
                        <div>
                            <p class="font-medium text-blue-800">Trend Positif</p>
                            <p class="text-sm text-blue-700">Sentimen positif meningkat 12% dibanding bulan lalu</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                        <i class="fas fa-chart-up text-green-600 mt-1"></i>
                        <div>
                            <p class="font-medium text-green-800">Topik Populer</p>
                            <p class="text-sm text-green-700">Teknologi dan ekonomi mendominasi diskusi positif</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-3 pt-4 border-t">
                <button class="flex-1 px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition-colors" onclick="downloadReport(${id})">
                    <i class="fas fa-download mr-2"></i>Download Full Report
                </button>
                <button class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-share mr-2"></i>Share Report
                </button>
            </div>
        </div>
    `;
    
    $('#previewContent').html(previewHtml);
    $('#previewModal').removeClass('hidden').addClass('flex');
}

// Share Report Function
function shareReport(id) {
    const reportUrl = window.location.origin + '/reports/share/' + id;
    
    if (navigator.share) {
        navigator.share({
            title: 'Laporan Analisis Sentimen',
            text: 'Lihat laporan analisis sentimen terbaru',
            url: reportUrl
        });
    } else {
        // Fallback - copy to clipboard
        navigator.clipboard.writeText(reportUrl).then(() => {
            alert('Link laporan berhasil disalin ke clipboard!');
        });
    }
}

// Delete Report Function
function deleteReport(id) {
    if (confirm('Hapus laporan ini? Tindakan ini tidak dapat dibatalkan.')) {
        alert('Laporan berhasil dihapus!');
        // Implement delete logic here
    }
}

// Close Preview Modal
function closePreviewModal() {
    $('#previewModal').addClass('hidden').removeClass('flex');
}
</script>
@endpush
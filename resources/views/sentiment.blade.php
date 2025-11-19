@extends('layouts.app')

@section('title', 'Analisis Sentimen - Sistem Analisis Sentimen')
@section('page-title', 'Analisis Sentimen')
@section('page-description', 'Analisis sentimen berita dan teks menggunakan AI')

@section('content')
<div class="space-y-8">

    <!-- PILIH KATEGORI ANALISIS -->
    <div class="bg-white rounded-2xl p-6 card-shadow">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-layer-group text-blue-600"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-800">Pilih Kategori Analisis</h3>
                <p class="text-gray-600 text-sm">Pilih jenis data yang ingin dianalisis sentimennya</p>
            </div>
        </div>

        <input type="hidden" id="analysisType" value="news">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Card: Analisis Berita -->
            <button type="button" id="cardNews"
                class="w-full text-left border rounded-2xl p-5 transition-all bg-white border-gray-200 ring-0 hover:bg-blue-50">
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-newspaper text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-gray-800">Analisis Berita</h4>
                        <p class="text-sm text-gray-600 mt-1">Analisis sentimen teks/berita dari portal berita</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="px-2 py-1 text-xs rounded bg-blue-50 text-blue-700">Detik</span>
                            <span class="px-2 py-1 text-xs rounded bg-blue-50 text-blue-700">Kompas</span>
                            <span class="px-2 py-1 text-xs rounded bg-blue-50 text-blue-700">CNN</span>
                        </div>
                    </div>
                </div>
            </button>

            <!-- Card: Analisis YouTube -->
            <button type="button" id="cardYoutube"
                class="w-full text-left border rounded-2xl p-5 transition-all hover:bg-red-50 hover:border-red-200">
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                        <i class="fab fa-youtube text-red-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-gray-800">Analisis YouTube</h4>
                        <p class="text-sm text-gray-600 mt-1">Analisis sentimen komentar dari video YouTube</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="px-2 py-1 text-xs rounded bg-rose-50 text-rose-700">Comments</span>
                            <span class="px-2 py-1 text-xs rounded bg-rose-50 text-rose-700">Replies</span>
                            <span class="px-2 py-1 text-xs rounded bg-rose-50 text-rose-700">Metadata</span>
                        </div>
                    </div>
                </div>
            </button>
        </div>
    </div>
    <!-- END PILIH KATEGORI -->

    <!-- Analysis Controls -->
    <div id="cardsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Text Input Analysis -->
        <div id="manualCard" class="bg-white rounded-2xl p-6 card-shadow h-full flex flex-col">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-keyboard text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Analisis Teks Manual</h3>
                    <p class="text-gray-600 text-sm">Masukkan teks untuk dianalisis</p>
                </div>
            </div>

            <form id="textAnalysisForm" method="POST" action="{{ route('analyze.sentiment') }}" class="flex flex-col h-full">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Teks untuk Dianalisis</label>
                    <textarea id="analysisText" name="text" rows="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                        placeholder="Masukkan teks berita atau kalimat yang ingin dianalisis sentimennya..."></textarea>
                    <div class="mt-2 text-sm text-gray-500">
                        <span id="charCount">0</span> karakter | Min. 10 karakter
                    </div>
                </div>

                <div class="mt-auto">
                    <button type="submit" id="analyzeTextBtn"
                        class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-medium">
                        <i class="fas fa-brain mr-2"></i>Analisis Sekarang
                    </button>
                </div>
            </form>
        </div>

        <!-- Batch Analysis -->
        <div id="batchCard" class="bg-white rounded-2xl p-6 card-shadow h-full flex flex-col">
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
                    <div class="text-2xl font-bold text-gray-800">{{ $batchStats['total'] ?? '0' }}</div>
                    <div class="text-sm text-gray-600">Total Berita</div>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-xl">
                    <div class="text-2xl font-bold text-green-600">{{ $batchStats['analyzed'] ?? '0' }}</div>
                    <div class="text-sm text-gray-600">Sudah Dianalisis</div>
                </div>
                <div class="text-center p-3 bg-yellow-50 rounded-xl">
                    <div class="text-2xl font-bold text-yellow-600">{{ $batchStats['pending'] ?? '0' }}</div>
                    <div class="text-sm text-gray-600">Menunggu</div>
                </div>
            </div>

            <form id="batchAnalysisForm" method="POST" action="{{ route('analyze.sentiment.batch') }}"
                  enctype="multipart/form-data" class="space-y-4 flex flex-col h-full">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Sumber Data</label>
                    <select name="data_source" id="dataSource"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="uploaded">File Upload (CSV/Excel)</option>
                        <option value="scraped">Berita dari Scraping</option>
                        <option value="database">Data dari Database</option>
                    </select>
                </div>

                <div id="fileUploadSection">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload File</label>

                    <!-- Drop Area yang bisa DIKLIK -->
                    <div id="fileDropArea"
                         class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors cursor-pointer"
                         title="Klik untuk memilih file">
                        <input type="file" id="fileUpload" name="file" accept=".csv,.xlsx,.xls" class="hidden">
                        <div class="select-none pointer-events-none">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Klik untuk upload atau drag & drop file</p>
                            <p class="text-sm text-gray-500">CSV, Excel (.xlsx, .xls)</p>
                        </div>
                        <p id="fileName" class="mt-3 text-sm text-gray-600 hidden"></p>
                    </div>
                </div>

                <div class="mt-auto">
                    <button type="submit" id="startBatchAnalysis"
                            class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-medium">
                        <i class="fas fa-play mr-2"></i>Mulai Analisis
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hasil Sentimen dari Database -->
    <div class="bg-white rounded-2xl p-6 card-shadow mt-6">
        <h3 class="text-xl font-bold text-gray-800 mb-6">Hasil Analisis Sentimen dari Berita dan</h3>

        {{-- Anchor untuk auto-scroll saat pindah page --}}
        <div id="sentiment-table-anchor"></div>

        @if($newsSentiments->count())
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="py-2 px-4 text-left text-gray-700">Article ID</th>
                            <th class="py-2 px-4 text-left text-gray-700">Quote Text</th>
                            <th class="py-2 px-4 text-left text-gray-700">Sentiment</th>
                            <th class="py-2 px-4 text-left text-gray-700">Positive Score</th>
                            <th class="py-2 px-4 text-left text-gray-700">Negative Score</th>
                            <th class="py-2 px-4 text-left text-gray-700">Neutral Score</th>
                            <th class="py-2 px-4 text-left text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($newsSentiments as $sentiment)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-4">{{ $sentiment->article_idx ?? '-' }}</td>
                                <td class="py-4 px-4">{{ \Illuminate\Support\Str::limit($sentiment->quote_text, 70) }}</td>
                                <td class="py-4 px-4">
                                    <span class="px-3 py-1 rounded-full capitalize
                                        @if($sentiment->label === 'positive') bg-green-100 text-green-600
                                        @elseif($sentiment->label === 'negative') bg-red-100 text-red-600
                                        @elseif($sentiment->label === 'positive-neutral') bg-blue-100 text-blue-600
                                        @elseif($sentiment->label === 'negative-neutral') bg-orange-100 text-orange-600
                                        @else bg-yellow-100 text-yellow-600 @endif">
                                        {{ $sentiment->label }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">{{ number_format((float)$sentiment->score_positive, 5, '.', '') }}</td>
                                <td class="py-4 px-4">{{ number_format((float)$sentiment->score_negative, 5, '.', '') }}</td>
                                <td class="py-4 px-4">{{ number_format((float)$sentiment->score_neutral, 5, '.', '') }}</td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        {{-- tombol lihat detail --}}
                                        <button type="button"
                                                class="btn-view text-blue-600 hover:text-blue-800"
                                                title="Lihat detail"
                                                data-id="{{ $sentiment->id }}"
                                                data-url="{{ route('sentiment.show', $sentiment->id) }}">
                                            <i class="fas fa-eye text-lg"></i>
                                        </button>
                                        <button type="button"
                                                class="btn-delete text-red-600 hover:text-red-800"
                                                title="Hapus data"
                                                data-id="{{ $sentiment->id }}">
                                            <i class="fas fa-trash text-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4 text-sm text-gray-600">
                <div>
                    Menampilkan {{ $newsSentiments->firstItem() }}–{{ $newsSentiments->lastItem() }}
                    dari {{ $newsSentiments->total() }} hasil
                </div>
                <div class="self-start md:self-auto">
                    {{ $newsSentiments->withQueryString()->onEachSide(1)->links() }}
                </div>
            </div>
        @else
            <p class="text-gray-600">Belum ada hasil analisis dari database.</p>
        @endif
    </div>

    <!-- Analysis Result (manual) -->
    <div id="analysisResult" class="bg-white rounded-2xl p-6 card-shadow hidden">
        <h3 class="text-xl font-bold text-gray-800 mb-6">Hasil Analisis</h3>
        <div id="resultContent"></div>
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
                <div id="batchProgressBar"
                     class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-300"
                     style="width: 0%"></div>
            </div>
            <div id="currentAnalysis" class="text-sm text-gray-600">Menunggu...</div>
        </div>
    </div>
</div>

<!-- Loading/Progress Modal (tidak bisa ditutup sampai selesai) -->
<div id="batchProcessModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 m-4 max-w-md w-full shadow-2xl">
        <div class="text-center">
            <!-- Spinner -->
            <div class="mb-4">
                <i class="fas fa-spinner fa-spin text-6xl text-blue-600"></i>
            </div>
            
            <h3 class="text-xl font-bold text-gray-800 mb-2">Sedang Memproses File</h3>
            <p class="text-gray-600 mb-4">Mohon tunggu, jangan tutup halaman ini...</p>
            
            <!-- Progress Info -->
            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                <div class="text-sm text-gray-600 mb-2">File: <span id="modalFileName" class="font-semibold text-gray-800">-</span></div>
                <div class="text-sm text-gray-600">Baris: <span id="modalFileRows" class="font-semibold text-gray-800">-</span></div>
            </div>
            
            <div class="text-xs text-gray-500">Proses ini mungkin memakan waktu beberapa saat</div>
        </div>
    </div>
</div>

<!-- Result Modal (bisa ditutup setelah selesai) -->
<div id="batchResultModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 m-4 max-w-md w-full shadow-2xl">
        <div class="text-center">
            <!-- Success Icon -->
            <div class="mb-4">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                    <i class="fas fa-check text-3xl text-green-600"></i>
                </div>
            </div>
            
            <h3 class="text-xl font-bold text-gray-800 mb-2">Analisis Selesai!</h3>
            <p class="text-gray-600 mb-4">File berhasil diproses</p>
            
            <!-- Result Details -->
            <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="text-gray-600">File:</div>
                    <div id="resultFileName" class="font-semibold text-gray-800 text-right truncate">-</div>
                    
                    <div class="text-gray-600">Total Baris:</div>
                    <div id="resultRows" class="font-semibold text-gray-800 text-right">-</div>
                    
                    <div class="text-gray-600">Berhasil:</div>
                    <div id="resultInserted" class="font-semibold text-green-600 text-right">-</div>
                    
                    <div class="text-gray-600">Dilewati:</div>
                    <div id="resultSkipped" class="font-semibold text-yellow-600 text-right">-</div>
                </div>
            </div>
            
            <button onclick="closeResultModal()" class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-medium">
                <i class="fas fa-check mr-2"></i>OK
            </button>
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
        <div id="analysisDetailContent"><!-- filled by JS --></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function safeJSON(x){ if(typeof x==='object') return x; try{ return JSON.parse(x);}catch{ return null; } }
$.ajaxSetup({
  headers:{
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    'X-Requested-With':'XMLHttpRequest',
    'Accept':'application/json'
  }
});

/* Upload area → buka file manager saat diklik */
$(document).on('click', '#fileDropArea', function (e) {
    if (e.target.id === 'fileUpload') return;
    $('#fileUpload').trigger('click');
});
$('#fileUpload').on('change', function () {
    const f = this.files && this.files[0] ? this.files[0] : null;
    $('#fileName').toggleClass('hidden', !f).text(f ? `Dipilih: ${f.name} (${Math.round(f.size/1024)} KB)` : '');
});
$('#fileDropArea').on('dragover', function(e){ e.preventDefault(); $(this).addClass('border-blue-400 bg-blue-50'); })
                  .on('dragleave', function(e){ e.preventDefault(); $(this).removeClass('border-blue-400 bg-blue-50'); })
                  .on('drop', function(e){ e.preventDefault(); $(this).removeClass('border-blue-400 bg-blue-50');
                      const files=e.originalEvent.dataTransfer.files;
                      if(files && files[0]){ const dt=new DataTransfer(); dt.items.add(files[0]); document.getElementById('fileUpload').files=dt.files;
                          $('#fileName').removeClass('hidden').text(`Dipilih: ${files[0].name} (${Math.round(files[0].size/1024)} KB)`); }});

/* Auto-scroll saat pindah page */
function patchPaginationLinks(){
  const anchor='#sentiment-table-anchor';
  document.querySelectorAll('a[href*="?page="]').forEach(a=>{
    const href=a.getAttribute('href')||'';
    if(!href.includes(anchor)) a.setAttribute('href', href+anchor);
  });
}
document.addEventListener('DOMContentLoaded', function(){
  patchPaginationLinks();
  if(location.hash==='#sentiment-table-anchor'){
    document.getElementById('sentiment-table-anchor')?.scrollIntoView({behavior:'smooth', block:'start'});
  }
});

/* Modal helpers */
function openAnalysisModal(){ $('#analysisDetailModal').removeClass('hidden').addClass('flex'); }
function closeAnalysisModal(){ $('#analysisDetailModal').addClass('hidden').removeClass('flex'); }
function closeResultModal() {
    $('#batchResultModal').removeClass('flex').addClass('hidden');
    location.href = location.href.split('#')[0] + '#sentiment-table-anchor';
    location.reload();
}

/* Handler tombol LIHAT */
$(document).on('click', '.btn-view', function(){
    const id  = $(this).data('id');
    const url = $(this).data('url') || "{{ route('sentiment.show', ['id'=>'__ID__']) }}".replace('__ID__', id);
    if(!id || !url) return;

    $('#analysisDetailContent').html('<div class="text-gray-600">Memuat detail...</div>');
    openAnalysisModal();

    $.get(url, function(resp){
        if(resp && resp.success && resp.data){
            const d = resp.data;
            const badgeMap = {
                'positive':'bg-green-100 text-green-700 border border-green-300', 
                'negative':'bg-red-100 text-red-700 border border-red-300', 
                'neutral':'bg-yellow-100 text-yellow-700 border border-yellow-300',
                'positive-neutral':'bg-blue-100 text-blue-700 border border-blue-300',
                'negative-neutral':'bg-orange-100 text-orange-700 border border-orange-300'
            };
            const badge = badgeMap[d.label] || badgeMap.neutral;
            
            let keywordsHtml = '';
            if (d.detected_keywords) {
                if (d.detected_keywords.positive && d.detected_keywords.positive.length > 0) {
                    keywordsHtml += '<div class="mb-3"><div class="text-sm font-semibold text-green-700 mb-2">✓ Kata Positif:</div>';
                    d.detected_keywords.positive.forEach(kw => {
                        const scorePercent = Math.round(kw.score * 100);
                        keywordsHtml += `
                            <div class="flex items-center justify-between bg-green-50 px-3 py-2 rounded-lg mb-2 border border-green-200">
                                <span class="text-sm text-gray-800">"${kw.keyword}"</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width:${scorePercent}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-green-700">${kw.score.toFixed(2)}</span>
                                </div>
                            </div>`;
                    });
                    keywordsHtml += '</div>';
                }
                
                if (d.detected_keywords.negative && d.detected_keywords.negative.length > 0) {
                    keywordsHtml += '<div class="mb-3"><div class="text-sm font-semibold text-red-700 mb-2">✗ Kata Negatif:</div>';
                    d.detected_keywords.negative.forEach(kw => {
                        const scorePercent = Math.round(kw.score * 100);
                        keywordsHtml += `
                            <div class="flex items-center justify-between bg-red-50 px-3 py-2 rounded-lg mb-2 border border-red-200">
                                <span class="text-sm text-gray-800">"${kw.keyword}"</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-gray-200 rounded-full h-2">
                                        <div class="bg-red-600 h-2 rounded-full" style="width:${scorePercent}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-red-700">${kw.score.toFixed(2)}</span>
                                </div>
                            </div>`;
                    });
                    keywordsHtml += '</div>';
                }
            }
            
            if (!keywordsHtml) {
                keywordsHtml = '<div class="text-sm text-gray-500 italic">Tidak ada kata kunci sentimen yang terdeteksi</div>';
            }

            const html = `
                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <div class="text-sm text-gray-600 mb-2 font-semibold">Quote Text</div>
                        <div class="text-gray-800 leading-relaxed">${(d.quote_text||'').replace(/\\n/g,'<br>')}</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl border-2 ${badge}">
                            <div class="text-sm text-gray-600 mb-1">Sentimen</div>
                            <div class="text-xl font-bold capitalize">${d.label}</div>
                        </div>
                        <div class="p-4 rounded-xl border border-gray-200">
                            <div class="text-sm text-gray-600 mb-2">Confidence</div>
                            <div class="flex items-center space-x-3">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width:${d.confidence}%"></div>
                                </div>
                                <div class="text-sm font-semibold text-gray-800">${d.confidence}%</div>
                            </div>
                        </div>
                        <div class="p-4 rounded-xl border border-gray-200">
                            <div class="text-sm text-gray-600 mb-1">Skor</div>
                            <div class="text-xs text-gray-800 space-y-1">
                                <div>Pos: ${Number(d.score_positive).toFixed(3)}</div>
                                <div>Neg: ${Number(d.score_negative).toFixed(3)}</div>
                                <div>Neu: ${Number(d.score_neutral).toFixed(3)}</div>
                            </div>
                        </div>
                    </div>

                    ${d.reasoning ? `
                    <div class="p-4 rounded-xl border border-blue-200 bg-blue-50">
                        <div class="flex items-start gap-3">
                            <div class="text-xl">💡</div>
                            <div>
                                <div class="text-sm font-semibold text-blue-900 mb-1">Alasan</div>
                                <div class="text-sm text-blue-800">${d.reasoning}</div>
                            </div>
                        </div>
                    </div>` : ''}

                    <div class="p-4 rounded-xl border border-gray-200">
                        <div class="text-sm font-semibold text-gray-700 mb-3">🔍 Kata Kunci Terdeteksi</div>
                        ${keywordsHtml}
                    </div>

                    <div class="text-xs text-gray-500">
                        Article FK: ${d.article_fk ?? '-'} • Article IDX: ${d.article_idx ?? '-'}<br>
                        Dibuat: ${d.created_at ?? '-'} • Diperbarui: ${d.updated_at ?? '-'}
                    </div>
                </div>`;
            $('#analysisDetailContent').html(html);
        } else {
            $('#analysisDetailContent').html('<div class="text-red-600">Gagal memuat detail.</div>');
        }
    }).fail(function(){
        $('#analysisDetailContent').html('<div class="text-red-600">Gagal memuat detail.</div>');
    });
});

/* Hapus row */
$(document).on('click', '.btn-delete', function(){
    const id=$(this).data('id'); if(!id) return;
    if(!confirm('Yakin ingin menghapus data ini?')) return;

    $.ajax({
        url: "{{ url('/sentiment') }}/"+id,
        method:'DELETE',
        success:function(res){
            if(res && res.success){
                location.href = location.href.split('#')[0] + '#sentiment-table-anchor';
                location.reload();
            }else{
                alert(res?.message || 'Gagal menghapus data.');
            }
        },
        error:function(xhr){
            const j=safeJSON(xhr.responseText) || xhr.responseJSON;
            alert(j?.message || 'Terjadi kesalahan saat menghapus.');
        }
    });
});

/* UX kecil-kecilan */
$('#analysisText').on('input', function(){
  const n=$(this).val().length;
  $('#charCount').text(n);
  $('#analyzeTextBtn').prop('disabled', n<10);
});

/* Switch card news/youtube (UI) */
function setType(type){
    $('#analysisType').val(type);
    if(type==='youtube'){
        $('#analysisText').attr('placeholder','Tempel teks/komentar YouTube yang ingin dianalisis sentimennya...');
        $('#dataSource').html(`
            <option value="uploaded">File Upload (CSV/Excel)</option>
            <option value="scraped">Komentar dari Scraping YouTube</option>
            <option value="database">Data dari Database</option>
        `);
        $('#manualCard').addClass('hidden');
        $('#cardsGrid').removeClass('lg:grid-cols-2').addClass('lg:grid-cols-1');
        $('#batchCard').addClass('w-full');
    }else{
        $('#analysisText').attr('placeholder','Masukkan teks berita atau kalimat yang ingin dianalisis sentimennya...');
        $('#dataSource').html(`
            <option value="uploaded">File Upload (CSV/Excel)</option>
            <option value="scraped">Berita dari Scraping</option>
            <option value="database">Data dari Database</option>
        `);
        $('#manualCard').removeClass('hidden');
        $('#cardsGrid').removeClass('lg:grid-cols-1').addClass('lg:grid-cols-2');
        $('#batchCard').removeClass('w-full');
    }
}
$('#cardNews').on('click', ()=>setType('news'));
$('#cardYoutube').on('click', ()=>setType('youtube'));
setType('news');

/* AJAX manual text analysis */
$(document).on('submit', '#textAnalysisForm', function (e) {
    e.preventDefault();

    const $btn = $('#analyzeTextBtn');
    const text = $('#analysisText').val().trim();
    if (text.length < 10) { alert('Minimal 10 karakter.'); return; }

    $btn.prop('disabled', true)
        .addClass('opacity-50 cursor-not-allowed')
        .html('<i class="fas fa-spinner fa-spin mr-2"></i>Menganalisis...');

    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: { text },
        success: function (res) {
            if (res && res.success && res.data) {
                const d = res.data;
                const badgeMap = {
                    'positive':'bg-green-100 text-green-700 border border-green-300', 
                    'negative':'bg-red-100 text-red-700 border border-red-300', 
                    'neutral':'bg-yellow-100 text-yellow-700 border border-yellow-300',
                    'positive-neutral':'bg-blue-100 text-blue-700 border border-blue-300',
                    'negative-neutral':'bg-orange-100 text-orange-700 border border-orange-300'
                };
                const badge = badgeMap[d.sentiment] || badgeMap.neutral;
                
                let keywordsHtml = '';
                if (d.detected_keywords) {
                    if (d.detected_keywords.positive && d.detected_keywords.positive.length > 0) {
                        keywordsHtml += '<div class="mb-3"><div class="text-sm font-semibold text-green-700 mb-2">✓ Kata Positif:</div>';
                        d.detected_keywords.positive.forEach(kw => {
                            const scorePercent = Math.round(kw.score * 100);
                            keywordsHtml += `
                                <div class="flex items-center justify-between bg-green-50 px-3 py-2 rounded-lg mb-2 border border-green-200">
                                    <span class="text-sm text-gray-800">"${kw.keyword}"</span>
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-600 h-2 rounded-full" style="width:${scorePercent}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-green-700">${kw.score.toFixed(2)}</span>
                                    </div>
                                </div>`;
                        });
                        keywordsHtml += '</div>';
                    }
                    
                    if (d.detected_keywords.negative && d.detected_keywords.negative.length > 0) {
                        keywordsHtml += '<div class="mb-3"><div class="text-sm font-semibold text-red-700 mb-2">✗ Kata Negatif:</div>';
                        d.detected_keywords.negative.forEach(kw => {
                            const scorePercent = Math.round(kw.score * 100);
                            keywordsHtml += `
                                <div class="flex items-center justify-between bg-red-50 px-3 py-2 rounded-lg mb-2 border border-red-200">
                                    <span class="text-sm text-gray-800">"${kw.keyword}"</span>
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-red-600 h-2 rounded-full" style="width:${scorePercent}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-red-700">${kw.score.toFixed(2)}</span>
                                    </div>
                                </div>`;
                        });
                        keywordsHtml += '</div>';
                    }
                }
                
                if (!keywordsHtml) {
                    keywordsHtml = '<div class="text-sm text-gray-500 italic">Tidak ada kata kunci sentimen yang terdeteksi</div>';
                }

                const html = `
                    <div class="space-y-6">
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="text-sm text-gray-600 mb-2 font-semibold">📝 Teks yang Dianalisis</div>
                            <div class="text-gray-800 leading-relaxed">${(d.text||'').replace(/\\n/g,'<br>')}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl border-2 ${badge}">
                                <div class="text-sm text-gray-600 mb-2">Sentimen</div>
                                <div class="text-2xl font-bold capitalize">${d.sentiment}</div>
                            </div>
                            <div class="p-4 rounded-xl border border-gray-200 bg-white">
                                <div class="text-sm text-gray-600 mb-2">Confidence</div>
                                <div class="mt-2 flex items-center space-x-3">
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all" style="width:${d.confidence}%"></div>
                                    </div>
                                    <div class="text-lg font-bold text-gray-800">${d.confidence}%</div>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl border border-gray-200 bg-white">
                                <div class="text-sm text-gray-600 mb-2">Skor Detail</div>
                                <div class="space-y-1 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-green-600">● Positif:</span>
                                        <span class="font-semibold">${(d.scores?.positive || 0).toFixed(4)}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-red-600">● Negatif:</span>
                                        <span class="font-semibold">${(d.scores?.negative || 0).toFixed(4)}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-yellow-600">● Netral:</span>
                                        <span class="font-semibold">${(d.scores?.neutral || 0).toFixed(4)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 rounded-xl border border-blue-200 bg-blue-50">
                            <div class="flex items-start gap-3">
                                <div class="text-2xl">💡</div>
                                <div>
                                    <div class="text-sm font-semibold text-blue-900 mb-2">Alasan Deteksi Sentimen</div>
                                    <div class="text-sm text-blue-800 leading-relaxed">${d.reasoning || 'Tidak ada penjelasan tersedia.'}</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl border border-gray-200 bg-white">
                            <div class="text-sm font-semibold text-gray-700 mb-3">🔍 Kata Kunci Terdeteksi</div>
                            ${keywordsHtml}
                        </div>

                        <div class="text-xs text-gray-500 text-center">
                            Diproses: ${d.processed_at || '-'} | Model: Advanced Rule-Based
                        </div>
                    </div>`;
                $('#resultContent').html(html);
                $('#analysisResult').removeClass('hidden');
                document.getElementById('analysisResult').scrollIntoView({behavior:'smooth', block:'start'});
            } else {
                alert(res?.message || 'Gagal menganalisis.');
            }
        },
        error: function (xhr) {
            const j = safeJSON(xhr.responseText) || xhr.responseJSON;
            alert(j?.message || 'Terjadi kesalahan saat analisis.');
        },
        complete: function () {
            $btn.prop('disabled', false)
                .removeClass('opacity-50 cursor-not-allowed')
                .html('<i class="fas fa-brain mr-2"></i>Analisis Sekarang');
        }
    });
});

/* AJAX submit form batch dengan modal progress */
$(document).on('submit', '#batchAnalysisForm', function (e) {
    e.preventDefault();

    const $btn = $('#startBatchAnalysis');
    const fd   = new FormData(this);
    
    // Ambil info file
    const fileInput = document.getElementById('fileUpload');
    const fileName = fileInput.files[0]?.name || 'Unknown';
    
    // Tampilkan modal progress
    $('#modalFileName').text(fileName);
    $('#modalFileRows').text('Menghitung...');
    $('#batchProcessModal').removeClass('hidden').addClass('flex');

    $btn.prop('disabled', true)
        .addClass('opacity-50 cursor-not-allowed')
        .html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');

    $.ajax({
        url: "{{ route('analyze.sentiment.batch') }}",
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
        timeout: 600000,
        success: function (res) {
            // Tutup modal progress
            $('#batchProcessModal').removeClass('flex').addClass('hidden');
            
            if (res && res.success) {
                const d = res.data || {};
                
                // Tampilkan modal hasil
                $('#resultFileName').text(d.filename || '-');
                $('#resultRows').text(d.rows || 0);
                $('#resultInserted').text(d.inserted || 0);
                $('#resultSkipped').text(d.skipped || 0);
                $('#batchResultModal').removeClass('hidden').addClass('flex');
                
            } else {
                alert(res?.message || 'Gagal memproses file.');
            }
        },
        error: function (xhr, status) {
            // Tutup modal progress
            $('#batchProcessModal').removeClass('flex').addClass('hidden');
            
            if (status === 'timeout') { 
                alert('Timeout memproses file.'); 
                return; 
            }
            const j = (function(){ try { return xhr.responseJSON || JSON.parse(xhr.responseText); } catch(_) { return null; }})();
            alert(j?.message || 'Terjadi kesalahan saat upload/analisis.');
        },
        complete: function () {
            $btn.prop('disabled', false)
                .removeClass('opacity-50 cursor-not-allowed')
                .html('<i class="fas fa-play mr-2"></i>Mulai Analisis');
        }
    });
});
</script>
@endpush

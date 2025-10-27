@extends('layouts.app')

@section('title', 'Cetak Laporan - Sistem Analisis Sentimen')
@section('page-title', 'Cetak Laporan')
@section('page-description', 'Generate dan preview laporan analisis sentimen (PDF)')

@section('content')
<div class="space-y-8">
    {{-- ===== Toolbar: pisah Tabel & Grafik ===== --}}
    <div class="flex items-center justify-between gap-3 bg-white rounded-2xl p-4 card-shadow">
        <div class="text-sm text-gray-600">
            Pilih jenis laporan yang ingin kamu buat.
        </div>
        <div class="flex items-center gap-2">
            {{-- Tombol ke halaman Laporan Grafik Bulanan (3 filter: sumber, bulan, tahun) --}}
            <a href="{{ route('reports.charts') }}" class="px-4 py-2 rounded-xl border border-indigo-200 text-indigo-700 hover:bg-indigo-50">
                <i class="fas fa-chart-bar mr-2"></i>Laporan Grafik Bulanan
            </a>

            {{-- Tombol Generate Tabel tetap pakai form kiri (id="reportForm") --}}
            <button type="submit" form="reportForm" class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
                <i class="fas fa-file-alt mr-2"></i>Generate Laporan Tabel (PDF)
            </button>
        </div>
    </div>

    {{-- ================== Panel Kiri: Form ================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
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

                {{-- Nama File --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama File</label>
                    <input type="text" id="reportName" name="report_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Laporan Bulanan 10/2025" value="Laporan Analisis Sentimen - {{ date('Y-m') }}">
                    <p class="mt-1 text-xs text-gray-500">Tanpa ekstensi. Sistem akan menyimpan sebagai .pdf.</p>
                </div>

                {{-- Output (tetap PDF) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Output</label>
                    <div class="px-3 py-2 border border-gray-200 rounded-lg inline-flex items-center gap-2 text-sm text-gray-700">
                        <i class="fas fa-file-pdf text-red-500"></i> PDF
                    </div>
                </div>

                {{-- Pilih Bulan Awal & Akhir --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bulan Mulai</label>
                        <input type="month" name="start_month" id="startMonth" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ now()->startOfMonth()->format('Y-m') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bulan Selesai</label>
                        <input type="month" name="end_month" id="endMonth" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ now()->endOfMonth()->format('Y-m') }}">
                    </div>
                </div>

                {{-- Hidden inputs untuk tanggal sebenarnya (akan di-set oleh JS) --}}
                <input type="hidden" name="start_date" id="startDate">
                <input type="hidden" name="end_date" id="endDate">

                {{-- Sumber Data --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sumber Data</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="source" value="news" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Artikel</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="source" value="youtube" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">YouTube</span>
                        </label>
                    </div>
                </div>

                {{-- report_type (monthly|yearly) --}}
                <input type="hidden" name="report_type" id="reportTypeHidden" value="monthly">

                {{-- Info Helper --}}
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                        <div>
                            <h4 class="font-medium text-blue-800 mb-1">ℹ️ Cara Kerja Laporan</h4>
                            <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                                <li><strong>Halaman 1:</strong> Tabel detail data (top 50 item)</li>
                                <li><strong>Halaman 2:</strong> Grafik sentimen per bulan + tabel ringkasan</li>
                                <li><strong>Format Bulan:</strong> Pilih bulan awal & akhir (maks 12 bulan)</li>
                                <li><strong>Sumber Data:</strong> Artikel Berita atau YouTube (pilih salah satu)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <button type="submit" id="generateReport" class="w-full px-6 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-xl hover:from-indigo-600 hover:to-indigo-700 transition-all duration-200 font-medium">
                    <i class="fas fa-file-alt mr-2"></i>Generate Laporan (PDF)
                </button>
            </form>
        </div>

        {{-- ================== Panel Kanan: Template Cepat ================== --}}
        <div class="bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-layer-group text-green-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Template Cepat</h3>
                    <p class="text-gray-600 text-sm">Pilih template laporan yang sudah tersedia</p>
                </div>
            </div>

            @php
            $templates = [
            ['key' => 'monthly', 'name' => 'Laporan Bulanan', 'desc' => 'Pilih bulan & tahun', 'icon' => 'calendar-alt', 'color' => 'purple'],
            ['key' => 'yearly', 'name' => 'Laporan Tahunan', 'desc' => 'Pilih tahun', 'icon' => 'chart-pie', 'color' => 'indigo'],
            ];
            @endphp

            <div class="space-y-3">
                @foreach($templates as $t)
                <div class="template-card flex items-center p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer" data-key="{{ $t['key'] }}">
                    <div class="w-10 h-10 bg-{{ $t['color'] }}-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-{{ $t['icon'] }} text-{{ $t['color'] }}-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">{{ $t['name'] }}</h4>
                        <p class="text-sm text-gray-600">{{ $t['desc'] }}</p>
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
                        <p class="text-sm text-yellow-700">Gunakan template untuk mengisi periode dan nama file lebih cepat.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================== Progress ================== --}}
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

    {{-- ================== Riwayat Laporan ================== --}}
    <div class="bg-white rounded-2xl card-shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Laporan Terbaru</h3>
                    <p class="text-gray-600 text-sm">Riwayat laporan yang telah dibuat</p>
                </div>
                {{-- Header filter --}}
                <div class="bg-white rounded-2xl p-4 card-shadow flex items-center justify-between gap-3">
                    <form method="GET" action="{{ route('reports.charts') }}" class="flex items-center gap-3">
                        @if(request()->filled('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        @endif

                        {{-- Input + Button jadi satu grup, tinggi & radius seragam --}}
                        <div class="flex items-stretch w-full max-w-md">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / sumber / file…" class="flex-1 h-11 px-3 border border-gray-300 rounded-l-xl rounded-r-none focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                            <button class="h-11 px-4 border border-l-0 border-indigo-600 bg-indigo-600 text-white rounded-r-xl rounded-l-none hover:bg-indigo-700">
                                <i class="fas fa-search mr-2"></i>
                                <span class="hidden sm:inline">Cari</span>
                            </button>
                        </div>

                        @if(request()->filled('q'))
                        <a href="{{ route('reports.charts') }}" class="h-11 inline-flex items-center px-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50">
                            Reset
                        </a>
                        @endif

                        <div class="text-sm text-gray-600 whitespace-nowrap">
                            Total: {{ method_exists($reports,'total') ? $reports->total() : (is_countable($reports) ? count($reports) : '-') }} laporan
                        </div>
                    </form>
                </div>

            </div>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Nama Laporan</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Sumber</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Periode</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Ukuran</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Dibuat</th>
                            <th class="text-left py-4 px-4 font-medium text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $r)
                        @php
                        $fname = $r->file_name ?: ($r->file_path ? basename($r->file_path) : null);
                        @endphp
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-type="{{ $r->report_type }}" data-source="{{ $r->source }}" data-id="{{ $r->id }}">
                            {{-- Nama --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-3">
                                    @if(($r->source ?? '') === 'youtube')
                                    <i class="fab fa-youtube text-red-600"></i>
                                    @else
                                    <i class="far fa-newspaper text-indigo-600"></i>
                                    @endif
                                    <span class="font-medium text-gray-800">{{ $r->report_name }}</span>
                                </div>
                            </td>

                            {{-- Sumber --}}
                            <td class="py-4 px-4">
                                @if(($r->source ?? '') === 'youtube')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                    <i class="fab fa-youtube mr-1"></i>YouTube
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i class="far fa-newspaper mr-1"></i>Berita
                                </span>
                                @endif
                            </td>

                            {{-- Periode --}}
                            <td class="py-4 px-4">
                                <span class="text-gray-700 text-sm">
                                    {{ \Illuminate\Support\Carbon::parse($r->start_date)->format('d M Y') }} –
                                    {{ \Illuminate\Support\Carbon::parse($r->end_date)->format('d M Y') }}
                                </span>
                            </td>

                            {{-- Ukuran --}}
                            <td class="py-4 px-4">
                                <span class="text-gray-600 text-sm">{{ $r->size_human ?? '—' }}</span>
                            </td>

                            {{-- Dibuat --}}
                            <td class="py-4 px-4">
                                <span class="text-gray-600 text-sm">
                                    {{ optional($r->created_at)->timezone(config('app.timezone'))->format('d M Y, H:i') }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2">
                                    @if($fname)
                                    <a href="{{ route('reports.preview', ['filename' => $fname]) }}" target="_blank" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 hover:bg-gray-50" title="Preview">
                                        <i class="far fa-eye"></i>
                                    </a>
                                    <a href="{{ route('reports.download', ['filename' => $fname]) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 hover:bg-gray-50" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @else
                                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-400" title="File belum tersedia">
                                        <i class="fas fa-hourglass-half"></i>
                                    </span>
                                    @endif
                                    <button type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 text-red-600 hover:bg-red-50 action-delete" data-id="{{ $r->id }}" data-url="{{ route('reports.destroy', $r) }}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 px-4 text-center text-sm text-gray-500">
                                Belum ada laporan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ================== Modal (Template Cepat) ================== --}}
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 m-4 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 id="modalTitle" class="text-xl font-bold text-gray-800">Cetak Laporan</h3>
            <button type="button" onclick="closePrintModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="previewContent"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    /* ================== State ================== */
    let isGenerating = false;
    let progressTimer = null;

    /* ================== Helpers ================== */

    function monthToDateRange() {
        const startMonth = $('#startMonth').val(); // YYYY-MM
        const endMonth = $('#endMonth').val(); // YYYY-MM

        if (!startMonth || !endMonth) return false;

        // Start: awal bulan
        const [startYear, startMon] = startMonth.split('-');
        const startDate = `${startYear}-${startMon}-01`;

        // End: akhir bulan
        const [endYear, endMon] = endMonth.split('-');
        const lastDay = new Date(endYear, endMon, 0).getDate();
        const endDate = `${endYear}-${endMon}-${String(lastDay).padStart(2,'0')}`;

        $('#startDate').val(startDate);
        $('#endDate').val(endDate);

        return true;
    }

    function guessReportTypeAndFixRange() {
        const s = new Date($('#startDate').val());
        const e = new Date($('#endDate').val());
        const sameMonth = s.getFullYear() === e.getFullYear() && s.getMonth() === e.getMonth();
        $('#reportTypeHidden').val(sameMonth ? 'monthly' : 'yearly');
    }

    function resetProgressUI() {
        if (progressTimer) {
            clearInterval(progressTimer);
            progressTimer = null;
        }
        $('#reportProgress').addClass('hidden');
        $('#reportProgressBar').css('width', '0%');
        $('#reportProgressText').text('0%');
        $('#currentStep').text('Mempersiapkan...');
        $('#generateReport').prop('disabled', false).html('<i class="fas fa-file-alt mr-2"></i>Generate Laporan (PDF)');
        isGenerating = false;
    }

    function showSuccessBanner(filename, url) {
        const $banner = $(`
      <div id="reportSuccess" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700">
        <i class="fas fa-check-circle mr-2"></i>
        Laporan berhasil dibuat:
        <a class="underline" target="_blank" href="${url}">${filename}</a>
      </div>`);
        $('#reportProgress').after($banner);
        setTimeout(() => $banner.fadeOut(300, () => $banner.remove()), 4000);
    }

    function finalizeReport(success, res) {
        if (progressTimer) {
            clearInterval(progressTimer);
            progressTimer = null;
        }
        $('#reportProgressBar').css('width', '100%');
        $('#reportProgressText').text('100%');
        $('#currentStep').text(success ? 'Selesai' : 'Gagal');

        if (success) {
            $('#reportName').val('');
        }

        const preview = res?.preview_url || (res?.filename ? '/reports/preview/' + encodeURIComponent(res.filename) : null);
        if (success && preview) showSuccessBanner(res.filename || 'report.pdf', preview);
        setTimeout(resetProgressUI, 800);
    }

    /* ================== Count helper ================== */
    function countItems(start, end, source) {
        return $.ajax({
            url: "{{ route('reports.count') }}",
            method: "POST",
            dataType: "json",
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                start_date: start,
                end_date: end,
                source
            }
        });
    }

    /* ================== Generate (Form Kiri) ================== */
    async function generateReport() {
        if (isGenerating) return;

        // Konversi bulan ke tanggal
        if (!monthToDateRange()) {
            alert('Pilih bulan mulai dan selesai!');
            return;
        }

        const source = $('input[name="source"]:checked').val();
        let startDate = $('#startDate').val();
        let endDate = $('#endDate').val();
        const reportName = $('#reportName').val().trim();

        if (!source) return alert('Pilih satu sumber data!');
        if (!startDate || !endDate) return alert('Lengkapi bulan!');
        if (new Date(startDate) > new Date(endDate)) return alert('Range bulan salah');
        if (!reportName) return alert('Isi nama file laporan!');

        guessReportTypeAndFixRange();
        startDate = $('#startDate').val();
        endDate = $('#endDate').val();

        try {
            const res = await countItems(startDate, endDate, source);
            const n = parseInt(res?.items_count || 0, 10);
            if (n <= 0) {
                alert('Tidak ada data pada periode tersebut.');
                return;
            }
            const ok = confirm(`Data ditemukan: ${n} ${source==='news'?'artikel':'video'}.\nLanjutkan generate?`);
            if (!ok) return;
        } catch {
            const ok = confirm('Gagal memeriksa jumlah data. Lanjutkan generate?');
            if (!ok) return;
        }

        isGenerating = true;
        $('#reportProgress').removeClass('hidden');
        $('#generateReport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Membuat...');

        const payload = {
            _token: '{{ csrf_token() }}',
            report_type: $('#reportTypeHidden').val(),
            start_date: startDate,
            end_date: endDate,
            source: source,
            report_name: reportName
        };

        const steps = [
            'Memeriksa periode & sumber data...',
            'Mengumpulkan data artikel/video...',
            'Menganalisis sentimen per bulan...',
            'Membuat grafik Bar Chart...',
            'Membuat grafik Pie Chart...',
            'Menyusun tabel data...',
            'Mengekspor ke PDF...'
        ];

        let idx = 0;
        progressTimer = setInterval(() => {
            const pct = Math.min(95, Math.round(((idx + 1) / steps.length) * 100));
            $('#reportProgressBar').css('width', pct + '%');
            $('#reportProgressText').text(pct + '%');
            $('#currentStep').text(steps[idx] || steps[steps.length - 1]);
            idx++;
            if (idx >= steps.length) idx = steps.length - 1;
        }, 800);

        $.ajax({
                url: "{{ route('reports.generate') }}",
                method: "POST",
                dataType: "json",
                data: payload,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .done(function(res) {
                if (res?.report) appendReportRow(res.report);
                finalizeReport(true, res);
            })
            .fail(function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.statusText || 'Unknown error');
                alert('Gagal membuat laporan: ' + msg);
                finalizeReport(false);
            })
            .always(function() {
                setTimeout(() => {
                    if (isGenerating) resetProgressUI();
                }, 2000);
            });
    }

    /* ================== Template Cepat (Monthly/Yearly) ================== */
    let tplState = {
        type: null,
        start: null,
        end: null,
        count: 0
    };

    function openTemplateModal(type) {
        tplState.type = type;
        const sourceLabel = $('input[name="source"]:checked').parent().find('span').text().trim() || '-';
        const now = new Date();
        const yyyy = now.getFullYear();

        let innerInputs = '';
        if (type === 'monthly') {
            const monthOpts = Array.from({
                length: 12
            }, (_, i) => {
                const m = i + 1;
                const names = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                return `<option value="${m}" ${m===(now.getMonth()+1)?'selected':''}>${names[i]}</option>`;
            }).join('');

            const years = Array.from({
                length: 8
            }, (_, i) => yyyy - 5 + i);
            const yearOpts = years.map(y => `<option value="${y}" ${y===yyyy?'selected':''}>${y}</option>`).join('');

            innerInputs = `
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
            <select id="tplMonth" class="w-full px-3 py-2 border rounded-lg">${monthOpts}</select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
            <select id="tplYear" class="w-full px-3 py-2 border rounded-lg">${yearOpts}</select>
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">
            <i class="fas fa-info-circle"></i> Template ini akan generate laporan untuk 1 bulan yang dipilih
        </p>`;
        } else {
            const years = Array.from({
                length: 8
            }, (_, i) => yyyy - 5 + i);
            const yearOpts = years.map(y => `<option value="${y}" ${y===yyyy?'selected':''}>${y}</option>`).join('');

            innerInputs = `
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
          <select id="tplYearOnly" class="w-full px-3 py-2 border rounded-lg">${yearOpts}</select>
        </div>
        <p class="text-xs text-gray-500 mt-2">
            <i class="fas fa-info-circle"></i> Template ini akan generate laporan untuk seluruh tahun (Jan - Des)
        </p>`;
        }

        const html = `
      <div class="space-y-4">
        <div class="text-center border-b pb-4">
          <h3 class="text-xl font-bold text-gray-800">Cetak Laporan ${type==='monthly'?'Bulanan':'Tahunan'}</h3>
          <p class="text-sm text-gray-500">Sumber: ${sourceLabel}</p>
        </div>
        ${innerInputs}
        <div id="tplInfo" class="p-3 rounded-lg bg-gray-50 text-sm text-gray-700">
            <i class="fas fa-spinner fa-spin mr-2"></i>Memeriksa data...
        </div>
        <div class="flex gap-3 pt-2">
          <button id="tplGenerateBtn"
                  class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
            <i class="fas fa-file-pdf mr-2"></i>Generate PDF
          </button>
          <button type="button" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                  onclick="closePrintModal()">
            <i class="fas fa-times mr-2"></i>Batal
          </button>
        </div>
      </div>`;

        $('#modalTitle').text('Cetak Laporan');
        $('#previewContent').html(html);
        $('#previewModal').removeClass('hidden').addClass('flex');

        if (type === 'monthly') $('#tplMonth,#tplYear').on('change', updateTplPeriodAndCount);
        else $('#tplYearOnly').on('change', updateTplPeriodAndCount);

        $('#tplGenerateBtn').on('click', generateFromTemplate);
        updateTplPeriodAndCount();
    }

    function toISO(d) {
        const y = d.getFullYear(),
            m = String(d.getMonth() + 1).padStart(2, '0'),
            day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function computeTplPeriod(type) {
        if (type === 'monthly') {
            const m = parseInt($('#tplMonth').val(), 10);
            const y = parseInt($('#tplYear').val(), 10);
            const start = new Date(y, m - 1, 1);
            const lastDay = new Date(y, m, 0).getDate();
            const end = new Date(y, m - 1, lastDay);
            return {
                start: toISO(start),
                end: toISO(end)
            };
        } else {
            const y = parseInt($('#tplYearOnly').val(), 10);
            return {
                start: `${y}-01-01`,
                end: `${y}-12-31`
            };
        }
    }

    function buildTplReportName() {
        const t = tplState.type;
        const e = tplState.end;

        if (t === 'monthly') {
            const [y, m] = e.split('-');
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const monthName = monthNames[parseInt(m) - 1];
            return `Laporan ${monthName} ${y}`;
        }

        const [y] = e.split('-');
        return `Laporan Tahunan ${y}`;
    }

    function updateTplPeriodAndCount() {
        const {
            start,
            end
        } = computeTplPeriod(tplState.type);
        tplState.start = start;
        tplState.end = end;

        const source = $('input[name="source"]:checked').val();
        $('#tplInfo').html('<i class="fas fa-spinner fa-spin mr-2"></i>Memeriksa data...');
        $('#tplGenerateBtn').prop('disabled', true);

        $.ajax({
                url: "{{ route('reports.count') }}",
                method: "POST",
                dataType: "json",
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    start_date: start,
                    end_date: end,
                    source
                }
            })
            .done(res => {
                const n = parseInt(res?.items_count || 0, 10);
                tplState.count = n;
                if (n > 0) {
                    $('#tplInfo').html(`Data ditemukan: <b>${n}</b> ${source==='news'?'artikel':'video'} pada periode <b>${res.start_date}</b> – <b>${res.end_date}</b>.`);
                    $('#tplGenerateBtn').prop('disabled', false);
                } else {
                    $('#tplInfo').html(`<span class="text-red-600">Tidak ada data pada periode tersebut.</span>`);
                    $('#tplGenerateBtn').prop('disabled', true);
                }
            })
            .fail(() => $('#tplInfo').html(`<span class="text-red-600">Gagal memeriksa data.</span>`));
    }

    function generateFromTemplate() {
        if ($('#tplGenerateBtn').prop('disabled')) return;
        const n = Number(tplState.count || 0);
        if (n <= 0) {
            alert('Tidak ada data pada periode tersebut.');
            return;
        }
        const ok = confirm(`Data ditemukan: ${n} ${$('input[name="source"]:checked').val()==='news'?'artikel':'video'}.\nLanjutkan generate?`);
        if (!ok) return;

        if (isGenerating) return;
        isGenerating = true;
        $('#reportProgress').removeClass('hidden');
        $('#reportProgressBar').css('width', '0%');
        $('#reportProgressText').text('0%');
        $('#currentStep').text('Mempersiapkan...');

        const steps = [
            'Memeriksa periode & sumber data...',
            'Mengumpulkan data artikel/video...',
            'Menganalisis sentimen per bulan...',
            'Membuat grafik Bar Chart...',
            'Membuat grafik Pie Chart...',
            'Menyusun tabel data...',
            'Mengekspor ke PDF...'
        ];

        let idx = 0;
        progressTimer = setInterval(() => {
            const pct = Math.min(95, Math.round(((idx + 1) / steps.length) * 100));
            $('#reportProgressBar').css('width', pct + '%');
            $('#reportProgressText').text(pct + '%');
            $('#currentStep').text(steps[idx] || steps[steps.length - 1]);
            idx++;
            if (idx >= steps.length) idx = steps.length - 1;
        }, 800);

        const payload = {
            _token: '{{ csrf_token() }}',
            report_type: tplState.type,
            start_date: tplState.start,
            end_date: tplState.end,
            source: $('input[name="source"]:checked').val(),
            report_name: buildTplReportName()
        };

        closePrintModal();

        $.ajax({
                url: "{{ route('reports.generate') }}",
                method: "POST",
                dataType: "json",
                data: payload,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .done(res => {
                if (res?.report) appendReportRow(res.report);
                finalizeReport(true, res);
            })
            .fail(xhr => {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.statusText || 'Unknown error');
                alert('Gagal membuat laporan: ' + msg);
                finalizeReport(false);
            })
            .always(() => {
                setTimeout(() => {
                    if (isGenerating) resetProgressUI();
                }, 2000);
            });
    }

    function closePrintModal() {
        $('#previewModal').addClass('hidden').removeClass('flex');
    }

    /* ================== Tabel: tambah baris baru ================== */
    function appendReportRow(r) {
        if (!r) return;
        $('#empty-filter-row').remove();

        const fmtDate = (s) => {
            const d = new Date((s) || '');
            return isNaN(d) ? s : d.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        };
        const fmtDateTime = (s) => {
            const d = new Date((s) || '');
            return isNaN(d) ? (s || '') : d.toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        const src = (r.source || '').toLowerCase();
        const iconHTML = src === 'youtube' ? `<i class="fab fa-youtube text-red-600"></i>` : `<i class="far fa-newspaper text-indigo-600"></i>`;
        const badgeHTML = src === 'youtube' ?
            `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-50 text-red-700 border border-red-200"><i class="fab fa-youtube mr-1"></i>YouTube</span>` :
            `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200"><i class="far fa-newspaper mr-1"></i>Berita</span>`;

        const previewUrl = '/reports/preview/' + encodeURIComponent(r.file_name || '');
        const downloadUrl = '/reports/download/' + encodeURIComponent(r.file_name || '');
        const deleteUrl = '/reports/' + r.id;

        const tr = `
      <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-id="${r.id}" data-type="${(r.report_type||'').toLowerCase()}" data-source="${src}">
        <td class="py-4 px-4">
          <div class="flex items-center space-x-3">
            ${iconHTML}
            <span class="font-medium text-gray-800">${r.report_name||'-'}</span>
          </div>
        </td>
        <td class="py-4 px-4">${badgeHTML}</td>
        <td class="py-4 px-4"><span class="text-gray-700 text-sm">${fmtDate(r.start_date)} – ${fmtDate(r.end_date)}</span></td>
        <td class="py-4 px-4"><span class="text-gray-600 text-sm">${r.size_human||'—'}</span></td>
        <td class="py-4 px-4"><span class="text-gray-600 text-sm">${fmtDateTime(r.created_at)}</span></td>
        <td class="py-4 px-4">
          <div class="flex items-center gap-2">
          <a href="${previewUrl}" target="_blank" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 hover:bg-gray-50" title="Preview"><i class="far fa-eye"></i></a>
            <a href="${downloadUrl}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 hover:bg-gray-50" title="Download"><i class="fas fa-download"></i></a>
            <button type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 text-red-600 hover:bg-red-50 action-delete" data-id="${r.id}" data-url="${deleteUrl}" title="Hapus"><i class="fas fa-trash"></i></button>
          </div>
        </td>
      </tr>`;
        $('table tbody').prepend(tr);

        const allNow = Number($('#totalReports').attr('data-all')) || 0;
        $('#totalReports').attr('data-all', allNow + 1);
        bumpTotal(+1);
        applyTableFilter();
    }

    /* ================== Tabel & Filter ================== */
    function bumpTotal(delta) {
        const $t = $('#totalReports');
        const oldAll = Number($t.attr('data-all')) || 0;
        const newAll = Math.max(0, oldAll + delta);
        $t.attr('data-all', newAll);
        if (($('#typeFilter').val() || '') === '') $t.text(newAll);
    }

    function applyTableFilter() {
        const val = $('#typeFilter').val();
        const $rows = $('tbody tr[data-type]');
        const $emptyRowId = 'empty-filter-row';

        if (!val) {
            $rows.show();
        } else {
            $rows.hide().filter(`[data-type="${val}"]`).show();
        }

        const visible = $rows.filter(':visible').length;

        let $empty = $('#' + $emptyRowId);
        if (visible === 0) {
            if ($empty.length === 0) {
                const colSpan = $('table thead th').length || 6;
                $empty = $(`
          <tr id="${$emptyRowId}">
            <td colspan="${colSpan}" class="py-6 px-4 text-center text-sm text-gray-500">
              Tidak ada laporan untuk jenis ini di halaman ini.
            </td>
          </tr>`);
                $('tbody').append($empty);
            }
            $empty.show();
        } else {
            $empty.hide();
        }

        const totalAll = Number($('#totalReports').attr('data-all')) || 0;
        if (!val) $('#totalReports').text(totalAll);
        else $('#totalReports').text(visible);
    }

    /* ================== Bind Once ================== */
    $(document).ready(function() {
        // Filter
        $(document).on('change', '#typeFilter', applyTableFilter);
        const totalInit = Number($('#totalReports').text()) || 0;
        $('#totalReports').attr('data-all', totalInit);
        $('#typeFilter').val('');
        applyTableFilter();

        // Submit form kiri
        $('#reportForm').on('submit', function(e) {
            e.preventDefault();
            if (!isGenerating) generateReport();
        });

        // Cancel progress
        $('#cancelReport').on('click', function() {
            resetProgressUI();
        });

        // Shortcuts
        $(document).keydown(function(e) {
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 13) {
                e.preventDefault();
                if (!isGenerating) $('#reportForm').trigger('submit');
            }
            if (e.keyCode === 27) {
                if (!$('#previewModal').hasClass('hidden')) closePrintModal();
            }
        });

        // HAPUS (delegasi)
        $(document).on('click', '.action-delete', function() {
            const $btn = $(this);
            if ($btn.prop('disabled')) return;

            const id = $btn.data('id');
            const url = $btn.data('url') || ('/reports/' + id);
            if (!id) return;
            if (!confirm('Hapus laporan ini? Tindakan ini tidak dapat dibatalkan.')) return;

            $btn.prop('disabled', true).addClass('opacity-60 cursor-not-allowed');

            $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .done(function(res) {
                    if (res?.success) {
                        $(`tr[data-id="${id}"]`).remove();
                        bumpTotal(-1); // hanya saat sukses 👍
                        applyTableFilter();
                    } else {
                        alert('Gagal menghapus laporan.');
                    }
                })
                .fail(function() {
                    alert('Gagal menghapus laporan (server error).');
                })
                .always(function() {
                    $btn.prop('disabled', false).removeClass('opacity-60 cursor-not-allowed');
                });
        });

        // Template cards
        $(document).on('click', '.template-card', function() {
            const key = String($(this).data('key'));
            openTemplateModal(key);
        });
    });
</script>
@endpush
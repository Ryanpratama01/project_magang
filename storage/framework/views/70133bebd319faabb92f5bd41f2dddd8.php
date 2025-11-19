

<?php $__env->startSection('title', 'Laporan Grafik Bulanan'); ?>
<?php $__env->startSection('page-title', 'Cetak Laporan'); ?>
<?php $__env->startSection('page-description', 'Generate dan preview laporan analisis sentimen (PDF)'); ?>

<?php $__env->startSection('content'); ?>

<div id="flashAreaTop" class="mb-4 space-y-2"></div>

<div class="space-y-8">

    
    <div class="bg-white rounded-2xl p-4 card-shadow flex items-center justify-between">
        <div class="text-gray-600">Pilih jenis laporan yang ingin kamu buat.</div>
        <div class="flex gap-3">
            <a href="<?php echo e(route('reports.charts')); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                <i class="fas fa-chart-bar"></i> Laporan Grafik Bulanan
            </a>
            <a href="<?php echo e(url('/cetak-laporan')); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-indigo-600 bg-indigo-600 text-white hover:bg-indigo-700">
                <i class="fas fa-file-alt"></i> Generate Laporan Tabel (PDF)
            </a>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:auto-rows-[330px]">

        
        <div class="lg:row-span-2 bg-white rounded-2xl p-6 card-shadow flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-sliders-h text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Laporan Grafik Bulanan</h3>
                        <p class="text-gray-600 text-sm">Pilih sumber, tahun, lalu (opsional) bulan</p>
                    </div>
                </div>

                <form id="chartForm" class="space-y-4">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Laporan</label>
                        <input id="chartReportName" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" value="Laporan Grafik Bulanan">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Sumber</label>
                        <select id="chartSource" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="news" selected>Berita</option>
                            <option value="youtube">YouTube</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                            <select id="chartYear" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($y); ?>" <?php echo e($y==$defaultYear?'selected':''); ?>><?php echo e($y); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                            <select id="chartMonth" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">Semua Bulan</option>
                                <?php $mnames=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']; ?>
                                <?php $__currentLoopData = $mnames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx=>$name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($idx+1); ?>"><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Kosongkan untuk menampilkan 1 tahun penuh.</p>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mt-4">
                <button type="button" id="btnGeneratePdf" class="w-full px-4 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-xl hover:from-indigo-600 hover:to-indigo-700">
                    <i class="fas fa-file-pdf mr-2"></i>Generate Laporan Grafik (PDF)
                </button>

                
                <div id="panelInfo" class="hidden mt-3 text-sm text-gray-600"></div>

                
                <div id="flashArea" class="mt-3 space-y-2"></div>
            </div>
        </div>

        
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Trend Sentimen per Bulan</h3>
            <div id="trendEmpty" class="text-sm text-gray-500">Grafik akan dimuat otomatis sesuai pilihan.</div>
            <div class="h-full"><canvas id="barChart"></canvas></div>
        </div>

        
        <div class="lg:col-span-2 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Sentimen (Total)</h3>
                <div id="pieEmpty" class="text-sm text-gray-500">Belum ada data.</div>
                <div class="h-full"><canvas id="pieChart"></canvas></div>
            </div>

            <div class="bg-white rounded-2xl p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Ringkasan</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-xs text-gray-500">POSITIF</div>
                        <div id="sumPos" class="text-2xl font-bold text-emerald-600">0</div>
                        <div id="pctPos" class="text-xs text-gray-500">0%</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-gray-500">NETRAL</div>
                        <div id="sumNeu" class="text-2xl font-bold text-amber-600">0</div>
                        <div id="pctNeu" class="text-xs text-gray-500">0%</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs text-gray-500">NEGATIF</div>
                        <div id="sumNeg" class="text-2xl font-bold text-rose-600">0</div>
                        <div id="pctNeg" class="text-xs text-gray-500">0%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl p-6 card-shadow">
        <div class="flex items-center justify-between mb-4 gap-3">
            <h3 class="text-lg font-semibold text-gray-800">Laporan Terbaru</h3>

            
            <form id="reportsSearchForm" class="flex items-center gap-2">
                <div class="relative">
                    <input id="reportsSearch" type="text" name="q" value="<?php echo e($q ?? ''); ?>" class="pl-10 pr-3 py-2 border rounded-lg w-72" placeholder="Cari nama / sumber / file..." />
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>

                <button type="submit" id="reportsSearchBtn" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                    Cari
                </button>

                <div class="ml-3 text-sm text-gray-500">
                    Total: <span id="reportsTotal"><?php echo e($total ?? ($reports->total() ?? 0)); ?></span> laporan
                </div>
            </form>
        </div>

        <div id="reportsTableWrap" data-reports-table-wrapper>
            <?php echo $__env->make('reports._table', ['reports' => $reports], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    Chart.register(ChartDataLabels);

    function getReportsTotal() {
        const el = document.getElementById('reportsTotal');
        const n = parseInt((el?.textContent || '0').replace(/\D+/g, ''), 10);
        return Number.isFinite(n) ? n : 0;
    }

    function setReportsTotal(n) {
        const el = document.getElementById('reportsTotal');
        if (el) el.textContent = Math.max(0, n);
    }

    function incReportsTotal(delta) {
        setReportsTotal(getReportsTotal() + (delta || 0));
    }


    /* ========== FLASH CARD ========== */
    function showFlash(type, html, opts = {}) {
        if (typeof opts === 'number') opts = {
            ms: opts
        };

        const {
            target = 'flashAreaTop',
                ms = (type === 'error' ? 6000 : 3000),
                dismissible = (type === 'error'),
                replace = false, // <- baru: hapus flash lama di area target
                maxKeep = 2 // <- baru: batasi maksimal kartu
        } = opts;
        const host = document.getElementById(target);
        if (!host) return;

        const el = document.createElement('div');
        el.className = 'rounded-xl px-4 py-3 border shadow-sm flex items-start gap-2 text-sm transition-opacity ' +
            (type === 'success' ?
                'bg-emerald-50 border-emerald-200 text-emerald-700' :
                type === 'error' ?
                'bg-rose-50 border-rose-200 text-rose-700' :
                'bg-indigo-50 border-indigo-200 text-indigo-700');

        const icon = type === 'success' ?
            '<i class="fas fa-check-circle mt-0.5"></i>' :
            type === 'error' ?
            '<i class="fas fa-exclamation-triangle mt-0.5"></i>' :
            '<i class="fas fa-info-circle mt-0.5"></i>';

        const closeBtn = dismissible ? '<button type="button" class="ml-auto text-inherit/70 hover:text-inherit" title="Tutup" aria-label="Tutup">&times;</button>' : '';

        el.innerHTML = `${icon}<div>${html}</div>${closeBtn}`;
        host.appendChild(el);
        if (window.FontAwesome?.dom?.i2svg) FontAwesome.dom.i2svg({
            node: el
        });

        if (dismissible) el.querySelector('button')?.addEventListener('click', () => el.remove());
        if (ms > 0) {
            setTimeout(() => {
                el.style.opacity = '0';
            }, ms - 250);
            setTimeout(() => el.remove(), ms);
        }
    }

    /* ========== CHART HELPERS ========== */
    let bar, pie;

    function toISO(d) {
        const y = d.getFullYear(),
            m = String(d.getMonth() + 1).padStart(2, '0'),
            day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function destroyCharts() {
        if (bar) {
            bar.destroy();
            bar = null;
        }
        if (pie) {
            pie.destroy();
            pie = null;
        }
    }

    function niceStep(maxVal) {
        if (maxVal <= 10) return 1;
        if (maxVal <= 20) return 2;
        if (maxVal <= 50) return 5;
        if (maxVal <= 100) return 10;
        if (maxVal <= 200) return 20;
        if (maxVal <= 500) return 50;
        return 100;
    }

    function setSummary(pos, neu, neg) {
        const t = (pos || 0) + (neu || 0) + (neg || 0);
        $('#sumPos').text(pos || 0);
        $('#sumNeu').text(neu || 0);
        $('#sumNeg').text(neg || 0);
        $('#pctPos').text(t ? Math.round((pos * 100) / t) + '%' : '0%');
        $('#pctNeu').text(t ? Math.round((neu * 100) / t) + '%' : '0%');
        $('#pctNeg').text(t ? Math.round((neg * 100) / t) + '%' : '0%');
    }

    function renderCharts(labels, pos, neu, neg, titleText) {
        destroyCharts();
        $('#trendEmpty,#pieEmpty').addClass('hidden');

        const maxVal = Math.max(...(pos || [0]), ...(neu || [0]), ...(neg || [0])) || 0;
        const isSingle = (labels || []).length <= 1;
        const CATEGORY_PCT = isSingle ? 0.30 : 0.55;
        const BAR_PCT = isSingle ? 0.50 : 0.65;
        const MAX_THICK = 22;

        // BAR
        const ctx1 = document.getElementById('barChart').getContext('2d');
        bar = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                        label: 'Positif',
                        data: pos,
                        backgroundColor: '#10b981',
                        categoryPercentage: CATEGORY_PCT,
                        barPercentage: BAR_PCT,
                        maxBarThickness: MAX_THICK,
                        borderRadius: 6,
                        borderSkipped: false
                    },
                    {
                        label: 'Netral',
                        data: neu,
                        backgroundColor: '#f59e0b',
                        categoryPercentage: CATEGORY_PCT,
                        barPercentage: BAR_PCT,
                        maxBarThickness: MAX_THICK,
                        borderRadius: 6,
                        borderSkipped: false
                    },
                    {
                        label: 'Negatif',
                        data: neg,
                        backgroundColor: '#ef4444',
                        categoryPercentage: CATEGORY_PCT,
                        barPercentage: BAR_PCT,
                        maxBarThickness: MAX_THICK,
                        borderRadius: 6,
                        borderSkipped: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: titleText,
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        position: 'top'
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        offset: 4,
                        formatter: v => (v > 0 ? Math.round(v) : ''),
                        clip: false,
                        clamp: true
                    }
                },
                layout: {
                    padding: {
                        top: 8,
                        right: 6,
                        left: 6
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: Math.ceil(maxVal * 1.15),
                        grace: '10%',
                        ticks: {
                            precision: 0,
                            stepSize: niceStep(maxVal)
                        }
                    }
                }
            }
        });

        // PIE
        const sum = arr => (arr || []).reduce((a, b) => a + (b || 0), 0);
        const ps = sum(pos),
            ns = sum(neu),
            gs = sum(neg);

        const ctx2 = document.getElementById('pieChart').getContext('2d');
        pie = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Positif', 'Netral', 'Negatif'],
                datasets: [{
                    data: [ps, ns, gs],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    datalabels: {
                        color: '#fff',
                        font: {
                            weight: 'bold'
                        },
                        formatter: (v, ctx) => {
                            const t = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            return t ? Math.round((v * 100) / t) + '%' : '';
                        }
                    }
                }
            }
        });

        setSummary(ps, ns, gs);
    }

    async function fetchData() {
        const source = $('#chartSource').val();
        const year = parseInt($('#chartYear').val(), 10);
        const month = parseInt($('#chartMonth').val() || '0', 10);
        const start = new Date(year, 0, 1),
            end = new Date(year, 11, 31);

        try {
            const res = await $.ajax({
                url: "<?php echo e(route('reports.charts.data')); ?>",
                method: "POST",
                dataType: "json",
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
                    start_date: toISO(start),
                    end_date: toISO(end),
                    source: source
                }
            });

            const tr = res.trend || {
                labels: [],
                positive: [],
                neutral: [],
                negative: []
            };
            let labels = tr.labels.slice(),
                p = tr.positive.slice(),
                n = tr.neutral.slice(),
                g = tr.negative.slice();
            if (month) {
                const idx = month - 1;
                labels = [tr.labels[idx]];
                p = [tr.positive[idx] || 0];
                n = [tr.neutral[idx] || 0];
                g = [tr.negative[idx] || 0];
            }
            const title = month ? `Trend Sentimen ${labels[0] || ('Bulan ' + month)} ${year}` : `Trend Sentimen Tahun ${year}`;
            renderCharts(labels, p, n, g, title);
        } catch (e) {
            $('#trendEmpty').removeClass('hidden').text('Tidak ada data / gagal memuat.');
            $('#pieEmpty').removeClass('hidden').text('Tidak ada data / gagal memuat.');
            destroyCharts();
            console.error(e);
        }
    }

    function humanSize(bytes) {
        const n = Number(bytes);
        if (!isFinite(n) || n <= 0) return '0 B';
        const u = ['B', 'KB', 'MB', 'GB', 'TB'];
        const e = Math.floor(Math.log(n) / Math.log(1024));
        return (n / Math.pow(1024, e)).toFixed(e === 0 ? 0 : 1) + ' ' + u[e];
    }

    /* ========== Tambah baris baru ke tabel setelah generate sukses (tanpa refresh) ========== */
    function prependReportRow(reportLike) {
        const $tbody = $('table[data-reports-table] tbody');
        if ($tbody.length === 0) return;

        const r = reportLike || {};
        const start = r.start_date || r.start || '';
        const end = r.end_date || r.end || '';
        const src = (r.source || '').toLowerCase();
        const id = r.id || r.report_id || null;

        let sizeText = r.size_human || r.file_size_human || r.file_size || '';
        if (/^\d+$/.test(String(sizeText))) sizeText = humanSize(sizeText);
        if (!sizeText) sizeText = '-';

        const fmtDate = s => {
            try {
                return new Date(String(s).replace(' ', 'T')).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            } catch {
                return s || '-';
            }
        };
        const fmtDateTime = s => {
            try {
                return new Date(String(s).replace(' ', 'T')).toLocaleString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch {
                return s || '-';
            }
        };

        const srcBadge = src === 'youtube' ?
            '<span class="inline-flex items-center gap-1 text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-md text-xs"><i class="fab fa-youtube"></i> YouTube</span>' :
            '<span class="inline-flex items-center gap-1 text-indigo-600 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-md text-xs"><i class="far fa-newspaper"></i> Berita</span>';

        const previewUrl = r.preview_url || (r.file_name ? "<?php echo e(url('reports/preview')); ?>/" + encodeURIComponent(r.file_name) : '#');
        const downloadUrl = r.download_url || (r.file_name ? "<?php echo e(url('reports/download')); ?>/" + encodeURIComponent(r.file_name) : '#');
        const deleteUrl = id ? ("<?php echo e(url('reports')); ?>/" + id) : '';
        const periodText = `${fmtDate(start)} – ${fmtDate(end)}`;
        const createdAtText = fmtDateTime(r.created_at || r.generated_at || new Date());
        const rowId = 'row-new-' + (id ?? Date.now());

        const rowHtml = `
      <tr id="${rowId}" class="border-b border-gray-100 hover:bg-gray-50">
        <td class="py-3 px-3">
          <div class="flex items-center gap-2">
            <i class="far fa-file-pdf text-indigo-500"></i>
            <a href="${previewUrl}" target="_blank" rel="noopener" class="text-indigo-700 hover:underline">
              ${$('<div>').text(r.report_name || '(tanpa nama)').html()}
            </a>
          </div>
        </td>
        <td class="py-3 px-3">${srcBadge}</td>
        <td class="py-3 px-3">${periodText}</td>
        <td class="py-3 px-3">${sizeText}</td>
        <td class="py-3 px-3">${createdAtText}</td>
        <td class="py-3 px-3">
          <div class="flex items-center gap-2">
            <a class="p-2 rounded-md border hover:bg-gray-50" title="Preview" href="${previewUrl}" target="_blank"><i class="far fa-eye"></i></a>
            <a class="p-2 rounded-md border hover:bg-gray-50" title="Download" href="${downloadUrl}"><i class="fas fa-download"></i></a>
            <button class="p-2 rounded-md border hover:bg-rose-50 text-rose-600 js-del-report"
                    title="Hapus" data-url="${deleteUrl}" data-row="#${rowId}">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>`;
        $tbody.prepend(rowHtml);
        if (window.FontAwesome?.dom?.i2svg) FontAwesome.dom.i2svg({
            node: $tbody[0]
        });

        incReportsTotal(1);
    }

    /* ========== Generate PDF (status + flash) ========== */
    async function generatePdf() {
        const src = $('#chartSource').val();
        const year = parseInt($('#chartYear').val(), 10);
        const month = parseInt($('#chartMonth').val() || '0', 10);
        const name = $('#chartReportName').val().trim() || 'Laporan Grafik';

        let start, end, rtype, rname;
        if (month) {
            start = new Date(year, month - 1, 1);
            end = new Date(year, month, 0);
            rtype = 'monthly';
            rname = `${name} - ${String(month).padStart(2,'0')}/${year}`;
        } else {
            start = new Date(year, 0, 1);
            end = new Date(year, 11, 31);
            rtype = 'yearly';
            rname = `${name} - ${year}`;
        }

        const $btn = $('#btnGeneratePdf'),
            $info = $('#panelInfo');

        // tampilkan status loading
        $btn.prop('disabled', true).addClass('opacity-80 cursor-not-allowed');
        $info.removeClass('hidden').html(`
      <span class="inline-flex items-center gap-2 text-gray-600">
        <span class="inline-block w-4 h-4 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></span>
        Sedang membuat laporan...
      </span>`);

        try {
            const payload = {
                _token: '<?php echo e(csrf_token()); ?>',
                report_name: rname,
                source: src,
                report_type: rtype, // 'monthly' atau 'yearly'
                year: year
            };
            if (rtype === 'monthly') {
                payload.month = month; // wajib saat monthly
            }

            const res = await $.ajax({
                url: "<?php echo e(route('reports.generate.charts')); ?>",
                method: "POST",
                dataType: "json",
                headers: {
                    'Accept': 'application/json'
                },
                data: payload
            });

            $info.addClass('hidden').empty();

            // sukses → flash di bawah tombol (auto 3 detik)
            showFlash('success', `Berhasil: <a class="underline" target="_blank" rel="noopener" href="${res.preview_url}">${res.filename}</a>`, {
                target: 'flashArea',
                ms: 3000
            });

            // tambah baris baru di tabel (pakai id dari server agar delete berfungsi)
            prependReportRow(Object.assign({
                    report_name: rname,
                    source: src,
                    start_date: toISO(start),
                    end_date: toISO(end)
                },
                res.report || {}, {
                    preview_url: res.preview_url,
                    download_url: res.download_url,
                    file_size: res.file_size,
                    created_at: res.generated_at
                }
            ));

        } catch (e) {
            $info.addClass('hidden').empty();
            const msg = e?.responseJSON?.message || 'Server error';
            // error → flash sticky di bawah tombol
            showFlash('error', 'Gagal membuat PDF: ' + msg, {
                target: 'flashArea',
                ms: 3000,
                dismissible: true,
                replace: true
            });
        } finally {
            $btn.prop('disabled', false).removeClass('opacity-80 cursor-not-allowed');
        }
    }

    /* ========== INIT ========== */
    $(function() {
        $('#barChart').parent().css('height', '260px');
        fetchData();
        $('#chartSource,#chartYear,#chartMonth').on('change', fetchData);
        $('#btnGeneratePdf').on('click', generatePdf);
    });

    /* ========== TABEL: Search + Pagination AJAX + Delete (stabil) ========== */
    (function() {
        const SEARCH_URL = "<?php echo e(route('reports.charts')); ?>";

        function renderReports(res) {
            if (res && res.html !== undefined) {
                $('#reportsTableWrap').html(res.html);
                if (window.FontAwesome?.dom?.i2svg) FontAwesome.dom.i2svg({
                    node: document.getElementById('reportsTableWrap')
                });
            }
            if (res && res.total !== undefined) setReportsTotal(res.total);
        }

        // cari
        $('#reportsSearchForm').on('submit', function(e) {
            e.preventDefault();
            const q = $('#reportsSearch').val().trim();
            const $btn = $('#reportsSearchBtn');
            $btn.prop('disabled', true).text('Memuat...');
            $.get(SEARCH_URL, {
                q
            }, function(res) {
                renderReports(res);
            }).always(function() {
                $btn.prop('disabled', false).text('Cari');
            });
        });

        // pagination
        $('#reportsTableWrap').on('click', 'a.page-link', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            if (!href) return;
            const q = $('#reportsSearch').val().trim();
            $.get(href, {
                q
            }, function(res) {
                renderReports(res);
                document.querySelector('#reportsTableWrap')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });

        // DELETE stabil (delegasi)
        let deleting = false;
        $('#reportsTableWrap').on('click', '.js-del-report', async function() {
            if (deleting) return;
            const btn = this;
            const url = btn.dataset.url;
            const rowSel = btn.dataset.row;
            if (!url || !rowSel) {
                showFlash('error', 'URL hapus tidak ditemukan.', {
                    target: 'flashAreaTop',
                    ms: 3000
                });
                return;
            }
            if (!confirm('Hapus laporan ini?')) return;

            deleting = true;
            btn.disabled = true;
            btn.classList.add('opacity-60', 'cursor-not-allowed');

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify({
                        _method: 'DELETE'
                    })
                });

                let data = null;
                try {
                    data = await res.json();
                } catch {
                    /* ignore non-JSON */
                }

                if (data?.success || res.ok) {
                    const row = document.querySelector(rowSel);
                    if (row) {
                        row.style.transition = 'opacity .2s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 200);
                    }
                    showFlash('success', 'Laporan berhasil dihapus.', {
                        target: 'flashAreaTop',
                        ms: 3000
                    });

                    // ⬇️ pindahkan pengurangan total ke dalam cabang sukses
                    incReportsTotal(-1);
                } else {
                    showFlash('error', (data?.message || 'Gagal menghapus laporan.'), {
                        target: 'flashAreaTop',
                        ms: 3000
                    });
                }
            } catch (err) {
                showFlash('error', 'Gagal menghapus laporan (network error).', {
                    target: 'flashAreaTop',
                    ms: 3000
                });
            } finally {
                deleting = false;
                btn.disabled = false;
                btn.classList.remove('opacity-60', 'cursor-not-allowed');
            }
        });

    })();
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\project_magang\resources\views/reports/charts.blade.php ENDPATH**/ ?>
@extends('layouts.app')

@section('title', 'Dashboard - Sistem Analisis Sentimen')
@section('page-title', 'Dashboard')
@section('page-description', 'Ringkasan analisis sentimen dan statistik sistem')

@section('content')
<div class="space-y-8">
    {{-- Bridge untuk JS (ambil base url & query) --}}
    <div id="dashRoot" data-base-url="{{ route('dashboard') }}" data-data-url="{{ route('dashboard.data') }}" data-q="{{ request('q') }}" data-source="{{ $source ?? (request('source') === 'youtube' ? 'youtube' : 'news') }}">
    </div>

    <!-- Header + Filter Sumber -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Dashboard Analytics</h2>
            <p class="text-gray-600 text-sm">Analisis sentimen berdasarkan sumber data</p>
        </div>
        <div class="flex items-center space-x-3">
            <label for="sourceFilter" class="text-sm font-medium text-gray-700">Filter Sumber:</label>
            <select id="sourceFilter" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="news">Berita</option>
                <option value="youtube">YouTube</option>
            </select>
        </div>
    </div>

    {{-- NEWS CARDS --}}
    <div id="cards-news" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Kutipan (Berita)</p>
                    <p class="text-3xl font-bold text-gray-800" data-stat="total-news">
                        {{ $stats['news']['total'] ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-database text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Sentimen Positif</p>
                    <p class="text-3xl font-bold text-gray-800" data-stat="pos-news">
                        {{ $stats['news']['positive'] ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-smile text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Sentimen Negatif</p>
                    <p class="text-3xl font-bold text-gray-800" data-stat="neg-news">
                        {{ $stats['news']['negative'] ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-frown text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Sentimen Netral</p>
                    <p class="text-3xl font-bold text-gray-800" data-stat="neu-news">
                        {{ $stats['news']['neutral'] ?? 0 }}

                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-meh text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- YOUTUBE CARDS --}}
    <div id="cards-youtube" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 hidden">
        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Komentar (YouTube)</p>
                    <p class="text-3xl font-bold text-gray-800" data-stat="total-yt">
                        {{ $stats['youtube']['total'] ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-database text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Sentimen Positif</p>
                    <p class="text-3xl font-bold text-gray-800" data-stat="pos-yt">
                        {{ $stats['youtube']['positive'] ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-smile text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Sentimen Negatif</p>
                    <p class="text-3xl font-bold text-gray-800" data-stat="neg-yt">
                        {{ $stats['youtube']['negative'] ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-frown text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Sentimen Netral</p>
                    <p class="text-3xl font-bold text-gray-800" data-stat="neu-yt">
                        {{ $stats['youtube']['neutral'] ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-meh text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Sentiment Trend -->
        <div class="bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">Trend Sentimen per Bulan</h3>
                <!-- Dropdown Tahun -->
                <select id="yearFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(($monthlyTrend['years'] ?? []) as $y)
                    <option value="{{ $y }}" {{ ($monthlyTrend['year'] ?? now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="relative h-80">
                <canvas id="sentimentChart"></canvas>
            </div>
        </div>

        <!-- Sentiment Distribution -->
        <div class="bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">Distribusi Sentimen</h3>
                <div class="flex space-x-2">
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full">Positif</span>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Netral</span>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-xs rounded-full">Negatif</span>
                </div>
            </div>
            <div class="relative h-80">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- YouTube Videos -->
    <div id="youtubeSection" class="hidden bg-white rounded-2xl p-6 card-shadow mt-8">
        <h3 class="text-xl font-bold text-gray-800 mb-6">Video YouTube Terbaru</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2">Judul</th>
                        <th class="p-2">Channel</th>
                        <th class="p-2">Komentar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($youtubeVideos as $video)
                    <tr class="border-t">
                        <td class="p-2 font-medium">{{ $video['title'] }}</td>
                        <td class="p-2">{{ $video['channel'] }}</td>
                        <td class="p-2">
                            <ul class="space-y-1">
                                @foreach($video['comments'] as $comment)
                                <li>
                                    <span class="text-gray-800">• {{ $comment['text'] }}</span>
                                    <span class="ml-2 text-xs {{ $comment['sentiment'] === 'positive' ? 'text-green-600' : ($comment['sentiment'] === 'negative' ? 'text-red-600' : 'text-yellow-600') }}">
                                        ({{ ucfirst($comment['sentiment']) }})
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-6 text-center text-gray-500">Tidak ada data.</td> {{-- was 4 -> now 3 --}}
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    @php
    $q = trim((string) request('q', ''));
    $hl = function (?string $text) use ($q) {
    $text = (string) ($text ?? '');
    if ($q === '') return e($text);
    $safe = e($text);
    $pattern = '/' . preg_quote($q, '/') . '/i';
    return preg_replace($pattern, '<mark class="bg-yellow-200">$0</mark>', $safe);
    };
    @endphp

    <!-- News Table -->
    <div class="bg-white rounded-2xl p-6 card-shadow news-section">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-800">Berita Terbaru</h3>
            <a href="{{ route('scraping') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Lihat Semua</a>
        </div>

        {{-- Toolbar pencarian --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center w-full md:max-w-xl">
                <div class="relative flex-1">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari judul atau ringkasan…" class="w-full pl-10 pr-24 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 flex gap-2">
                        <button type="button" id="btnClearQ" class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded hidden" aria-label="Bersihkan pencarian">Clear</button>
                        <button class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700" type="submit">Cari</button>
                    </div>


                </div>
                {{-- Pertahankan pilihan tahun saat search --}}
                @if(isset($monthlyTrend['year']))
                <input type="hidden" name="year" value="{{ $monthlyTrend['year'] }}">
                @endif
            </form>

            <div class="text-sm text-gray-600">
                Menampilkan <span class="font-medium">{{ $newsData->count() }}</span> item
                @if($q) untuk kata kunci <span class="font-medium">"{{ $q }}"</span>@endif
            </div>
        </div>

        <div id="newsTableContainer" class="overflow-x-auto">
            @include('partials._news_table', [
            'newsData' => $newsData,
            'q' => $q ?? request('q'),
            ])
        </div>


        <!-- Top Keywords (dummy) -->
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        // === A. Seed data dari server ===
        const STATS_FROM_SERVER = JSON.parse('{!! json_encode($stats, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}');
        const TREND_FROM_SERVER = JSON.parse('{!! json_encode($monthlyTrend, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}');

        window._statsData = STATS_FROM_SERVER;
        window._monthlyPack = TREND_FROM_SERVER;
        window._piePack = STATS_FROM_SERVER?.distribution ?? {
            news: {
                pos: 0,
                neu: 0,
                neg: 0
            },
            youtube: {
                pos: 0,
                neu: 0,
                neg: 0
            },
        };

        // === B. State & URLs ===
        const root = document.getElementById('dashRoot');
        const baseUrl = root?.dataset.baseUrl || '';
        const dataUrl = root?.dataset.dataUrl || (baseUrl.replace(/\/$/, '') + '/data');
        let currentQ = root?.dataset.q || '';
        let currentYear = $('#yearFilter').val();
        let currentSource = (new URLSearchParams(location.search).get('source') === 'youtube' || root?.dataset.source === 'youtube') ? 'youtube' : 'news';

        // === C. Helpers ===
        function getYearsForSource(src) {
            const pack = window._monthlyPack || {};
            if (src === 'youtube') return Array.isArray(pack.years_youtube) ? pack.years_youtube.slice() : (pack.years || []);
            return Array.isArray(pack.years_news) ? pack.years_news.slice() : (pack.years || []);
        }

        async function fetchData({
            year,
            q,
            source
        }) {
            const params = new URLSearchParams();
            if (year) params.set('year', year);
            if (q !== undefined) params.set('q', q);
            if (source) params.set('source', source);

            const res = await fetch(`${dataUrl}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error('Gagal load data');
            return await res.json();
        }

        function rebuildYearOptions(years, selected) {
            const $sel = $('#yearFilter');
            $sel.empty();
            if (!years || years.length === 0) {
                $sel.append('<option value="" disabled selected>—</option>');
                $sel.prop('disabled', true);
                return;
            }
            $sel.prop('disabled', false);
            years.forEach(y => {
                const sel = String(y) === String(selected) ? 'selected' : '';
                $sel.append(`<option value="${y}" ${sel}>${y}</option>`);
            });
        }

        async function syncYearOptionsWithSource({
            fetchIfChanged = false
        } = {}) {
            const years = getYearsForSource(currentSource);
            const before = String(currentYear);

            if (!years.includes(Number(currentYear)) && !years.includes(String(currentYear))) {
                currentYear = years[0] || before;
            }
            rebuildYearOptions(years, currentYear);

            if (fetchIfChanged && String(currentYear) !== before && currentYear) {
                try {
                    const payload = await fetchData({
                        year: currentYear,
                        q: currentQ,
                        source: currentSource
                    });
                    applyPayload(payload);
                    rebuildYearOptions(getYearsForSource(currentSource), currentYear);
                    history.replaceState(null, '',
                        `${baseUrl}?year=${encodeURIComponent(currentYear)}${currentQ?`&q=${encodeURIComponent(currentQ)}`:''}&source=${encodeURIComponent(currentSource)}`
                    );
                } catch (e) {
                    console.error(e);
                }
            }
        }

        function toggleSections(src) {
            const isNews = (src === 'news');
            $('#cards-news').toggleClass('hidden', !isNews);
            $('.news-section').toggleClass('hidden', !isNews);
            $('#cards-youtube').toggleClass('hidden', isNews);
            $('#youtubeSection').toggleClass('hidden', isNews);
        }

        function updateCards(src) {
            const pack = window._statsData || {};
            const s = pack[src] || {
                total: 0,
                positive: 0,
                negative: 0,
                neutral: 0
            };

            if (src === 'news') {
                $('#cards-news [data-stat="total-news"]').text((s.total ?? 0).toLocaleString());
                $('#cards-news [data-stat="pos-news"]').text((s.positive ?? 0).toLocaleString());
                $('#cards-news [data-stat="neg-news"]').text((s.negative ?? 0).toLocaleString());
                $('#cards-news [data-stat="neu-news"]').text((s.neutral ?? 0).toLocaleString());
            } else {
                $('#cards-youtube [data-stat="total-yt"]').text((s.total ?? 0).toLocaleString());
                $('#cards-youtube [data-stat="pos-yt"]').text((s.positive ?? 0).toLocaleString());
                $('#cards-youtube [data-stat="neg-yt"]').text((s.negative ?? 0).toLocaleString());
                $('#cards-youtube [data-stat="neu-yt"]').text((s.neutral ?? 0).toLocaleString());
            }
        }


        function applyPayload(p) {
            window._statsData = p.summary || window._statsData || {};
            window._monthlyPack = p.trend || window._monthlyPack || {};
            window._piePack = p.pie || window._piePack || {};

            currentQ = p.q ?? currentQ;
            currentYear = p.year ?? currentYear;
            currentSource = p.source ?? currentSource;

            $('#yearFilter').val(String(currentYear));
            $('#sourceFilter').val(currentSource);
            toggleSections(currentSource);
            updateCards(currentSource);
            updateCharts(currentSource);

            if (p.newsTableHtml) $('#newsTableContainer').html(p.newsTableHtml);

            // sinkron dropdown tahun setelah apply
            rebuildYearOptions(getYearsForSource(currentSource), currentYear);
        }

        // === D. Charts ===
        let sentimentChart, pieChart;

        function makeMonthlyBarData(src) {
            const pack = window._monthlyPack || {};
            const labelsYM = pack.labels ?? [];
            const ds = pack[src] ?? {
                positive: [],
                neutral: [],
                negative: []
            };

            const labels = labelsYM.map(ym => {
                const [y, m] = (ym || '').split('-');
                const names = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const i = Math.max(1, Math.min(12, parseInt(m || '1', 10)));
                return `${names[i-1]} ${y}`;
            });

            const pad = arr => {
                const a = Array.isArray(arr) ? arr.slice() : [];
                while (a.length < labels.length) a.push(0);
                return a.slice(0, labels.length);
            };

            return {
                labels,
                datasets: [{
                        label: 'Positif',
                        data: pad(ds.positive),
                        backgroundColor: '#10b981'
                    },
                    {
                        label: 'Netral',
                        data: pad(ds.neutral),
                        backgroundColor: '#f59e0b'
                    },
                    {
                        label: 'Negatif',
                        data: pad(ds.negative),
                        backgroundColor: '#ef4444'
                    },
                ],
            };
        }

        function makePieData(src) {
            const dpack = window._piePack || {};
            const d = dpack[src] || {
                pos: 0,
                neu: 0,
                neg: 0
            };
            const total = (d.pos || 0) + (d.neu || 0) + (d.neg || 0);

            const toPct = (n) => total > 0 ? +((n / total) * 100).toFixed(1) : 0;

            return {
                labels: ['Positif', 'Netral', 'Negatif'],
                datasets: [{
                    // sekarang data = persen
                    data: [toPct(d.pos), toPct(d.neu), toPct(d.neg)],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            };
        }


        function initCharts(src) {
            const ctx1 = document.getElementById('sentimentChart').getContext('2d');
            const ctx2 = document.getElementById('pieChart').getContext('2d');

            sentimentChart = new Chart(ctx1, {
                type: 'bar',
                data: makeMonthlyBarData(src),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            pieChart = new Chart(ctx2, {
                type: 'doughnut',
                data: makePieData(src),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
            // tampilkan % di tooltip pie
            pieChart.options.plugins.tooltip = {
                callbacks: {
                    label: (ctx) => {
                        const lbl = ctx.label || '';
                        const val = typeof ctx.parsed === 'number' ? ctx.parsed : 0; // ini sudah persen
                        return `${lbl}: ${val}%`;
                    }
                }
            };
            pieChart.update();

        }

        function updateCharts(src) {
            const bar = makeMonthlyBarData(src);
            sentimentChart.data.labels = bar.labels;
            for (let i = 0; i < 3; i++) {
                if (!sentimentChart.data.datasets[i]) sentimentChart.data.datasets[i] = {
                    data: []
                };
                sentimentChart.data.datasets[i].label = bar.datasets[i].label;
                sentimentChart.data.datasets[i].data = bar.datasets[i].data;
                sentimentChart.data.datasets[i].backgroundColor = bar.datasets[i].backgroundColor;
            }
            sentimentChart.update();

            const pie = makePieData(src);
            pieChart.data.labels = pie.labels;
            pieChart.data.datasets[0].data = pie.datasets[0].data;
            pieChart.update();
        }

        // === E. Init pertama ===
        $('#sourceFilter').val(currentSource);
        toggleSections(currentSource);
        rebuildYearOptions(getYearsForSource(currentSource), currentYear); // sinkron dropdown awal
        initCharts(currentSource);
        updateCards(currentSource);
        updateCharts(currentSource);

        // === F. Events (1x saja, tanpa duplikasi!) ===
        // ganti tahun (AJAX)
        $('#yearFilter').on('change', async function() {
            const y = this.value;
            try {
                const payload = await fetchData({
                    year: y,
                    q: currentQ,
                    source: currentSource
                });
                applyPayload(payload);
                history.replaceState(null, '',
                    `${baseUrl}?year=${encodeURIComponent(y)}${currentQ?`&q=${encodeURIComponent(currentQ)}`:''}&source=${encodeURIComponent(currentSource)}`
                );
            } catch (err) {
                console.error(err);
            }
        });

        // ganti sumber (sinkron dropdown & grafik)
        $('#sourceFilter').on('change', async function() {
            currentSource = this.value;
            toggleSections(currentSource);
            updateCards(currentSource);
            await syncYearOptionsWithSource({
                fetchIfChanged: true
            });
            updateCharts(currentSource);
            history.replaceState(null, '',
                `${baseUrl}?year=${encodeURIComponent(currentYear)}${currentQ?`&q=${encodeURIComponent(currentQ)}`:''}&source=${encodeURIComponent(currentSource)}`
            );
        });

        // form pencarian (AJAX) + tombol Clear
        const $form = $(`form[action="{{ route('dashboard') }}"]`);
        const $qInput = $form.find('input[name="q"]');
        const $btnClr = $('#btnClearQ');

        function toggleClearBtn() {
            $btnClr.toggleClass('hidden', $qInput.val().trim() === '');
        }
        toggleClearBtn();
        $qInput.on('input', toggleClearBtn);

        $form.on('submit', async function(e) {
            e.preventDefault();
            const q = $qInput.val().trim();
            try {
                const payload = await fetchData({
                    year: currentYear,
                    q,
                    source: currentSource
                });
                applyPayload(payload);
                history.replaceState(null, '',
                    `${baseUrl}?year=${encodeURIComponent(currentYear)}${q?`&q=${encodeURIComponent(q)}`:''}&source=${encodeURIComponent(currentSource)}`
                );
            } catch (err) {
                console.error(err);
            }
        });

        $btnClr.on('click', async function() {
            $qInput.val('');
            toggleClearBtn();
            try {
                const payload = await fetchData({
                    year: currentYear,
                    q: '',
                    source: currentSource
                });
                applyPayload(payload);
                const url = new URL(baseUrl, location.origin);
                url.searchParams.set('year', currentYear);
                url.searchParams.set('source', currentSource);
                history.replaceState(null, '', url.toString());
            } catch (err) {
                console.error(err);
            }
        });

        $qInput.on('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                $btnClr.click();
                this.blur();
            }
        });
    });
</script>
@endpush

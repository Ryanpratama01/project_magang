<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Sentimen - {{ $source === 'news' ? 'Berita' : 'YouTube' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #4F46E5;
        }

        .header h1 {
            font-size: 22px;
            color: #4F46E5;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .header p {
            font-size: 11px;
            color: #666;
        }

        .meta {
            background: #F3F4F6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 6px;
            vertical-align: top;
        }

        .meta .label {
            font-weight: bold;
            width: 170px;
            color: #374151;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1F2937;
            margin: 30px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #E5E7EB;
        }

        /* Summary Cards */
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            gap: 10px;
        }

        .card {
            flex: 1;
            background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
            padding: 18px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #E5E7EB;
        }

        .card .label {
            font-size: 11px;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .card .number {
            font-size: 32px;
            font-weight: bold;
            margin: 8px 0;
        }

        .card.positive .number {
            color: #10B981;
        }

        .card.neutral .number {
            color: #F59E0B;
        }

        .card.negative .number {
            color: #EF4444;
        }

        .card .pct {
            font-size: 12px;
            color: #6B7280;
            font-weight: 600;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10px;
        }

        .data-table th {
            background: #4F46E5;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #E5E7EB;
            vertical-align: top;
        }

        .data-table tr:nth-child(even) {
            background: #F9FAFB;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge.positive {
            background: #D1FAE5;
            color: #065F46;
        }

        .badge.neutral {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge.negative {
            background: #FEE2E2;
            color: #991B1B;
        }

        .page-break {
            page-break-after: always;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #E5E7EB;
            text-align: center;
            font-size: 9px;
            color: #9CA3AF;
        }

        /* Chart Section */
        .chart-page {
            page-break-before: always;
        }

        .chart-container {
            margin: 25px 0;
            text-align: center;
            page-break-inside: avoid;
        }

        .chart-container h3 {
            font-size: 15px;
            margin-bottom: 20px;
            color: #374151;
            font-weight: bold;
        }

        .chart-container img {
            max-width: 100%;
            height: auto;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Tabel Data Bulanan */
        .monthly-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11px;
        }

        .monthly-table th {
            background: #6366F1;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
        }

        .monthly-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #E5E7EB;
        }

        .monthly-table tr:nth-child(even) {
            background: #F9FAFB;
        }
    </style>
</head>

<body>
    {{-- ============================================
         BAGIAN 1: LAPORAN DETAIL DATA
         ============================================ --}}
    <div class="header">
        <h1>📊 LAPORAN ANALISIS SENTIMEN</h1>
        <p><strong>{{ $source === 'news' ? 'Sumber: Artikel Berita' : 'Sumber: Komentar YouTube' }}</strong></p>
    </div>

    {{-- Meta Info --}}
    <div class="meta">
        <table>
            <tr>
                <td class="label">Periode Laporan</td>
                <td>: <strong>{{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}</strong></td>
            </tr>
            <tr>
                <td class="label">Rentang Bulan</td>
                <td>: <strong>{{ $start->format('M Y') }} - {{ $end->format('M Y') }}</strong></td>
            </tr>
            <tr>
                <td class="label">Jenis Laporan</td>
                <td>: {{ $rtype === 'monthly' ? 'Bulanan' : 'Tahunan' }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Dibuat</td>
                <td>: {{ $generated_at->format('d M Y, H:i') }} WIB</td>
            </tr>
            <tr>
                <td class="label">Total Data Dianalisis</td>
                <td>: <strong>{{ number_format($chartUrls['summary']['total']) }}</strong> {{ $source === 'news' ? 'artikel' : 'komentar' }}</td>
            </tr>
        </table>
    </div>

    {{-- Summary Cards --}}
    <h2 class="section-title">📈 Ringkasan Sentimen</h2>
    <div class="summary-cards">
        <div class="card positive">
            <div class="label">✅ Positif</div>
            <div class="number">{{ number_format($chartUrls['summary']['positive']) }}</div>
            <div class="pct">{{ $chartUrls['summary']['pos_pct'] }}%</div>
        </div>
        <div class="card neutral">
            <div class="label">⚪ Netral</div>
            <div class="number">{{ number_format($chartUrls['summary']['neutral']) }}</div>
            <div class="pct">{{ $chartUrls['summary']['neu_pct'] }}%</div>
        </div>
        <div class="card negative">
            <div class="label">❌ Negatif</div>
            <div class="number">{{ number_format($chartUrls['summary']['negative']) }}</div>
            <div class="pct">{{ $chartUrls['summary']['neg_pct'] }}%</div>
        </div>
    </div>

    {{-- Detail Data Table --}}
    <h2 class="section-title">📋 Detail Data (Top 50)</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:12%;">Tanggal</th>
                <th style="width:50%;">{{ $source === 'news' ? 'Judul Artikel' : 'Judul Video' }}</th>
                <th style="width:15%;">Sentimen</th>
                <th style="width:23%;">Rincian (Pos/Neu/Neg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach((is_array($items) ? array_slice($items, 0, 50) : $items->take(50)) as $item)
            <tr>
                <td>{{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y') }}</td>
                <td>
                    <strong>{{ \Illuminate\Support\Str::limit($item['title'] ?? '', 90) }}</strong>
                    @if(!empty($item['snippet']))
                    <br><span style="color:#6B7280; font-size:9px;">{{ Str::limit($item['snippet'], 120) }}</span>
                    @endif
                </td>
                @php
                $pos = (int) ($item['pos'] ?? 0);
                $neu = (int) ($item['neu'] ?? 0);
                $neg = (int) ($item['neg'] ?? 0);

                // fallback jika tidak ada 'label'
                $safeLabel = $item['label'] ?? (
                ($pos > $neu && $pos > $neg) ? 'positive'
                : (($neg > $pos && $neg > $neu) ? 'negative' : 'neutral')
                );
                @endphp
                <td style="text-align:center;">
                    <span class="badge {{ $safeLabel }}">
                        {{ ucfirst($safeLabel) }}
                    </span>
                </td>

                <td style="text-align:center; font-size:10px;">
                    <strong style="color:#10B981;">{{ $item['pos'] ?? 0 }}</strong> /
                    <strong style="color:#F59E0B;">{{ $item['neu'] ?? 0 }}</strong> /
                    <strong style="color:#EF4444;">{{ $item['neg'] ?? 0 }}</strong>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Halaman 1 dari 2</strong> - Laporan Detail Data</p>
    </div>

    {{-- ============================================
         BAGIAN 2: LAPORAN GRAFIK (HALAMAN BARU)
         ============================================ --}}
    <div class="chart-page">
        <div class="header">
            <h1>📊 LAPORAN GRAFIK SENTIMEN</h1>
            <p><strong>{{ $source === 'news' ? 'Sumber: Artikel Berita' : 'Sumber: Komentar YouTube' }}</strong></p>
        </div>

        <div class="meta">
            <table>
                <tr>
                    <td class="label">Periode Analisis</td>
                    <td>: <strong>{{ $start->format('M Y') }} - {{ $end->format('M Y') }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Total Data</td>
                    <td>: <strong>{{ number_format($chartUrls['summary']['total']) }}</strong> {{ $source === 'news' ? 'artikel' : 'komentar' }}</td>
                </tr>
            </table>
        </div>

        {{-- Chart 1: Bar Chart --}}
        <div class="chart-container">
            <h3>📊 Trend Sentimen per Bulan</h3>
            @if(!empty($chartImg['bar']))
            <img src="{{ $chartImg['bar'] }}" alt="Bar Chart">
            @else
            <div style="font-size:10px;color:#6B7280;">(Grafik tidak tersedia)</div>
            @endif
        </div>

        {{-- Chart 2: Pie Chart --}}
        <div class="chart-container">
            <h3>🥧 Distribusi Sentimen Keseluruhan</h3>
            @if(!empty($chartImg['pie']))
            <img src="{{ $chartImg['pie'] }}" alt="Pie Chart">
            @else
            <div style="font-size:10px;color:#6B7280;">(Grafik tidak tersedia)</div>
            @endif
        </div>


        {{-- Tabel Data Bulanan --}}
        <h2 class="section-title">📅 Data Sentimen per Bulan</h2>
        <table class="monthly-table">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th style="background:#10B981;">Positif</th>
                    <th style="background:#F59E0B;">Netral</th>
                    <th style="background:#EF4444;">Negatif</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chartUrls['monthly']['labels'] as $index => $label)
                @php
                $pos = $chartUrls['monthly']['positive'][$index] ?? 0;
                $neu = $chartUrls['monthly']['neutral'][$index] ?? 0;
                $neg = $chartUrls['monthly']['negative'][$index] ?? 0;
                $tot = $pos + $neu + $neg;
                @endphp
                <tr>
                    <td><strong>{{ $label }}</strong></td>
                    <td style="color:#065F46; font-weight:bold;">{{ number_format($pos) }}</td>
                    <td style="color:#92400E; font-weight:bold;">{{ number_format($neu) }}</td>
                    <td style="color:#991B1B; font-weight:bold;">{{ number_format($neg) }}</td>
                    <td><strong>{{ number_format($tot) }}</strong></td>
                </tr>
                @endforeach
                <tr style="background:#E5E7EB; font-weight:bold;">
                    <td>TOTAL</td>
                    <td>{{ number_format(array_sum($chartUrls['monthly']['positive'] ?? [])) }}</td>
                    <td>{{ number_format(array_sum($chartUrls['monthly']['neutral']  ?? [])) }}</td>
                    <td>{{ number_format(array_sum($chartUrls['monthly']['negative'] ?? [])) }}</td>
                    <td>{{ number_format($chartUrls['summary']['total'] ?? 0) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p><strong>Halaman 2 dari 2</strong> - Laporan Grafik</p>
            <p style="margin-top:10px;">Dokumen ini digenerate otomatis oleh Sistem Analisis Sentimen</p>
            <p>© {{ now()->year }} - Laporan ini bersifat rahasia dan hanya untuk penggunaan internal</p>
        </div>
    </div>
</body>

</html>
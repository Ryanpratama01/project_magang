<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Grafik Sentimen - {{ $source === 'news' ? 'Berita' : 'YouTube' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #1F2937;
            line-height: 1.5;
            background: #ffffff;
            padding: 20px;
        }

        /* Header - Sama seperti Laporan Tabel */
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #4F46E5;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .report-meta {
            font-size: 10px;
            color: #6B7280;
            line-height: 1.6;
        }

        .report-meta strong {
            color: #374151;
        }

        /* Chart Section */
        .chart-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .chart-title {
            background: #F3F4F6;
            padding: 8px 12px;
            border-left: 4px solid #6366F1;
            font-size: 12px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 12px;
        }

        .chart-wrapper {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }

        .chart-wrapper img {
            max-width: 100%;
            width: 550px;
            height: auto;
        }

        /* Summary Table */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 9px;
        }

        .summary-table th {
            background: #4F46E5;
            color: white;
            padding: 7px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #4338CA;
            font-size: 9px;
        }

        .summary-table td {
            padding: 6px;
            text-align: center;
            border: 1px solid #E5E7EB;
        }

        .summary-table tr:nth-child(even) {
            background: #F9FAFB;
        }

        .summary-table .total-row {
            background: #EEF2FF;
            font-weight: bold;
        }

        .stat-positive {
            color: #059669;
            font-weight: bold;
        }

        .stat-neutral {
            color: #D97706;
            font-weight: bold;
        }

        .stat-negative {
            color: #DC2626;
            font-weight: bold;
        }

        /* Sample Data Section */
        .sample-section {
            margin-top: 25px;
            page-break-before: always;
        }

        .sample-title {
            background: #F3F4F6;
            padding: 8px 12px;
            border-left: 4px solid #10B981;
            font-size: 12px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 12px;
        }

        .sample-item {
            background: #FAFAFA;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .sample-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid #E5E7EB;
        }

        .sample-date {
            font-size: 8px;
            color: #6B7280;
            font-weight: 600;
        }

        .sample-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-positive {
            background: #D1FAE5;
            color: #065F46;
        }

        .badge-neutral {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-negative {
            background: #FEE2E2;
            color: #991B1B;
        }

        .sample-title-text {
            font-size: 9px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .sample-content {
            font-size: 8px;
            color: #4B5563;
            line-height: 1.4;
            font-style: italic;
            margin-top: 4px;
            padding: 6px;
            background: white;
            border-left: 2px solid #E5E7EB;
        }

        .sample-stats {
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #E5E7EB;
            font-size: 7px;
            color: #6B7280;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 8px;
            color: #9CA3AF;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            background: #FEF3C7;
            border-radius: 6px;
            color: #92400E;
            font-size: 9px;
        }
    </style>
</head>

<body>
    {{-- HEADER SAMA DENGAN LAPORAN TABEL --}}
    <div class="report-header">
        <div class="report-title">{{ $report_name ?? 'Laporan Analisis Sentimen' }}</div>
        <div class="report-meta">
            <strong>Sumber:</strong> {{ $source === 'youtube' ? 'YouTube' : 'Artikel Berita' }}
            @if(isset($start) && isset($end))
            • <strong>Periode:</strong> {{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}
            @endif
            • <strong>Jenis:</strong> {{ $rtype === 'monthly' ? 'Bulanan' : 'Tahunan' }}
            • <strong>Dibuat:</strong> {{ $generated_at->format('d M Y, H:i') }} WIB
        </div>
    </div>

    {{-- DATA SUMMARY --}}
    @php
    $summaryData = $source === 'news'
    ? ($summary['news'] ?? [])
    : ($summary['youtube'] ?? []);
    $totalData = $summaryData['total'] ?? 0;
    @endphp

    {{-- CHART 1: BAR CHART --}}
    <div class="chart-section">
        <div class="chart-title">
            Grafik Trend Sentimen {{ $rtype === 'monthly' ? 'Harian' : 'Bulanan' }}
        </div>
        <div class="chart-wrapper">
            @if(!empty($chartImg['bar']))
            <img src="{{ $chartImg['bar'] }}" alt="Bar Chart">
            @else
            <div class="no-data">Grafik tidak tersedia</div>
            @endif
        </div>

        {{-- Tabel Ringkasan di Bawah Bar Chart --}}
        @if(isset($chartUrls['monthly']) && !empty($chartUrls['monthly']['labels']))
        <table class="summary-table">
            <thead>
                <tr>
                    <th>{{ $rtype === 'monthly' ? 'Tanggal' : 'Bulan' }}</th>
                    <th>Positif</th>
                    <th>Netral</th>
                    <th>Negatif</th>
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
                    <td class="stat-positive">{{ number_format($pos) }}</td>
                    <td class="stat-neutral">{{ number_format($neu) }}</td>
                    <td class="stat-negative">{{ number_format($neg) }}</td>
                    <td>{{ number_format($tot) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="stat-positive">{{ number_format(array_sum($chartUrls['monthly']['positive'] ?? [])) }}</td>
                    <td class="stat-neutral">{{ number_format(array_sum($chartUrls['monthly']['neutral'] ?? [])) }}</td>
                    <td class="stat-negative">{{ number_format(array_sum($chartUrls['monthly']['negative'] ?? [])) }}</td>
                    <td>{{ number_format($totalData) }}</td>
                </tr>
            </tbody>
        </table>
        @endif
    </div>

    {{-- PAGE BREAK --}}
    <div class="page-break"></div>

    {{-- CHART 2: PIE CHART --}}
    <div class="chart-section">
        <div class="chart-title">
            Grafik Distribusi Sentimen Keseluruhan
        </div>
        <div class="chart-wrapper">
            @if(!empty($chartImg['pie']))
            <img src="{{ $chartImg['pie'] }}" alt="Pie Chart">
            @else
            <div class="no-data">Grafik tidak tersedia</div>
            @endif
        </div>

        {{-- Tabel Persentase --}}
        <table class="summary-table" style="margin-top:12px;">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Jumlah</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                @php
                $posPct = $totalData > 0 ? round((($summaryData['positive'] ?? 0) / $totalData) * 100, 1) : 0;
                $neuPct = $totalData > 0 ? round((($summaryData['neutral'] ?? 0) / $totalData) * 100, 1) : 0;
                $negPct = $totalData > 0 ? round((($summaryData['negative'] ?? 0) / $totalData) * 100, 1) : 0;
                @endphp
                <tr>
                    <td><strong>Positif</strong></td>
                    <td class="stat-positive">{{ number_format($summaryData['positive'] ?? 0) }}</td>
                    <td class="stat-positive">{{ $posPct }}%</td>
                </tr>
                <tr>
                    <td><strong>Netral</strong></td>
                    <td class="stat-neutral">{{ number_format($summaryData['neutral'] ?? 0) }}</td>
                    <td class="stat-neutral">{{ $neuPct }}%</td>
                </tr>
                <tr>
                    <td><strong>Negatif</strong></td>
                    <td class="stat-negative">{{ number_format($summaryData['negative'] ?? 0) }}</td>
                    <td class="stat-negative">{{ $negPct }}%</td>
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL</strong></td>
                    <td>{{ number_format($totalData) }}</td>
                    <td>100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- SAMPLE DATA --}}
    @if(!empty($items) && count($items) > 0)
    <div class="sample-section">
        <div class="sample-title">
            {{ $source === 'news' ? 'Sample Artikel dengan Kutipan (Top 10)' : 'Sample Video dengan Komentar (Top 10)' }}
        </div>

        @foreach(array_slice($items, 0, 10) as $item)
        <div class="sample-item">
            <div class="sample-header">
                <span class="sample-date">
                    {{ \Carbon\Carbon::parse($item['date'])->format('d M Y') }}
                </span>
                <span class="sample-badge badge-{{ $item['label'] ?? 'neutral' }}">
                    {{ ucfirst($item['label'] ?? 'neutral') }}
                </span>
            </div>

            <div class="sample-title-text">
                {{ Str::limit($item['title'] ?? 'Tanpa Judul', 120) }}
            </div>

            {{-- UNTUK BERITA: Tampilkan Kutipan --}}
            @if($source === 'news' && !empty($item['snippet']))
            <div class="sample-content">
                "{{ Str::limit($item['snippet'], 200) }}"
            </div>
            @endif

            {{-- UNTUK YOUTUBE: Tampilkan Komentar --}}
            @if($source === 'youtube' && !empty($item['comment_text']))
            <div class="sample-content">
                "{{ Str::limit($item['comment_text'], 180) }}"
            </div>
            @endif

            <div class="sample-stats">
                <strong>Sentimen:</strong>
                <span class="stat-positive">Positif: {{ $item['pos'] ?? 0 }}</span> •
                <span class="stat-neutral">Netral: {{ $item['neu'] ?? 0 }}</span> •
                <span class="stat-negative">Negatif: {{ $item['neg'] ?? 0 }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Analisis Sentimen</p>
        <p>Menampilkan {{ $totalData }} {{ $source === 'youtube' ? 'video' : 'artikel' }}</p>
    </div>
</body>

</html>
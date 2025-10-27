<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $report_name ?? 'Laporan Sentimen' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1F2937;
            background: white;
        }

        .container {
            padding: 20px;
            max-width: 100%;
        }

        /* Header */
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
        }

        .report-meta strong {
            color: #374151;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }

        thead {
            background: #F3F4F6;
        }

        th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            color: #111827;
            border: 1px solid #E5E7EB;
            font-size: 9px;
        }

        td {
            padding: 7px 6px;
            border: 1px solid #E5E7EB;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #F9FAFB;
        }

        .col-date {
            width: 12%;
        }

        .col-source {
            width: 16%;
        }

        .col-link {
            width: 24%;
        }

        .col-title {
            width: 36%;
        }

        .col-sentiment {
            width: 12%;
            text-align: center;
        }

        .source-host {
            font-weight: 600;
            color: #1F2937;
        }

        .title-text {
            color: #374151;
            line-height: 1.3;
        }

        .url-link {
            font-size: 8px;
            color: #6B7280;
            margin-top: 2px;
            word-break: break-all;
        }

        .sentiment-pos {
            color: #065F46;
            font-weight: bold;
        }

        .sentiment-neu {
            color: #92400E;
            font-weight: bold;
        }

        .sentiment-neg {
            color: #991B1B;
            font-weight: bold;
        }

        /* Footer / Totals */
        .totals {
            margin-top: 12px;
            font-size: 9px;
            color: #374151;
        }

        .totals table {
            width: auto;
            margin-top: 6px;
        }

        .totals td,
        .totals th {
            border: 1px solid #E5E7EB;
            padding: 4px 6px;
        }

        .totals.totals-right {
            display: flex;
            justify-content: flex-end;
        }

        .totals.totals-right table {
            margin-left: auto;
        }

        .report-footer {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 9px;
            color: #6B7280;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6B7280;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="report-header">
            <div class="report-title">{{ $report_name ?? 'Laporan Analisis Sentimen' }}</div>
            <div class="report-meta">
                <strong>Sumber:</strong> {{ $source === 'youtube' ? 'YouTube' : 'Artikel Berita' }}
                @if(isset($start) && isset($end))
                • <strong>Periode:</strong> {{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}
                @endif
                • <strong>Dibuat:</strong> {{ $generated_at->format('d M Y, H:i') }} WIB
            </div>
        </div>

        <!-- Data Table -->
        <table>
            <thead>
                <tr>
                    <th class="col-date">Tanggal</th>
                    <th class="col-source">Sumber</th>
                    <th class="col-link">Link Berita</th>
                    <th class="col-title">Judul</th>
                    <th class="col-sentiment">Sentimen</th>
                </tr>
            </thead>
            <tbody>
                @php
                $rows = $items ?? $details ?? [];
                if ($rows instanceof \Illuminate\Support\Collection) { $rows = $rows->all(); }

                // aggregator total
                $sumPos = 0; $sumNeu = 0; $sumNeg = 0;

                // helper label dari hitungan (1 kolom)
                $deriveLabel = function(int $pos, int $neu, int $neg) {
                $max = max($pos, $neu, $neg);
                if ($max === 0) return '-';
                // jika ada nilai unik paling besar → ambil itu
                $winners = [];
                if ($pos === $max) $winners[] = 'positive';
                if ($neu === $max) $winners[] = 'neutral';
                if ($neg === $max) $winners[] = 'negative';
                // jika tie, prefer 'neutral' jika termasuk; kalau tidak, ambil salah satu (pos/neg)
                if (count($winners) === 1) return $winners[0];
                if (in_array('neutral', $winners)) return 'neutral';
                // tie pos vs neg → anggap neutral agar aman
                return 'neutral';
                };
                @endphp

                @forelse($rows as $row)
                @php
                // Normalisasi data
                $r = is_array($row) ? $row : (array)$row;

                // Tanggal
                $dateRaw = $r['date'] ?? $r['published_at'] ?? $r['created_at'] ?? null;
                try { $dateText = $dateRaw ? \Carbon\Carbon::parse($dateRaw)->format('d M Y') : '-'; }
                catch (\Throwable $e) { $dateText = '-'; }

                // URL & host
                $url = $r['url'] ?? null;
                $host = null;
                if ($url) {
                $parsedUrl = parse_url(preg_match('~^https?://~i', $url) ? $url : ('http://' . $url));
                $host = $parsedUrl['host'] ?? null;
                }

                // Judul
                $title = $r['title'] ?? 'Tanpa Judul';

                // Hitungan sentimen (opsional ada nama kolom berbeda)
                $pos = (int)($r['pos'] ?? $r['positive'] ?? $r['pos_count'] ?? 0);
                $neu = (int)($r['neu'] ?? $r['neutral'] ?? $r['neu_count'] ?? 0);
                $neg = (int)($r['neg'] ?? $r['negative'] ?? $r['neg_count'] ?? 0);

                // akumulasi untuk total bawah
                $sumPos += $pos; $sumNeu += $neu; $sumNeg += $neg;

                $label = $deriveLabel($pos, $neu, $neg);
                $labelClass = $label === 'positive' ? 'sentiment-pos' : ($label === 'negative' ? 'sentiment-neg' : 'sentiment-neu');
                $labelText = $label === '-' ? '-' : ucfirst($label);
                @endphp

                <tr>
                    <td class="col-date">{{ $dateText }}</td>
                    <td class="col-source">
                        @if($host)
                        <span class="source-host">{{ $host }}</span>
                        @else
                        <span style="color:#9CA3AF;">—</span>
                        @endif
                    </td>
                    <td class="col-link">
                        @if($url)
                        <div class="url-link">{{ Str::limit($url, 90) }}</div>
                        @else
                        <span style="color:#9CA3AF;">—</span>
                        @endif
                    </td>
                    <td class="col-title">
                        <div class="title-text">{{ Str::limit($title, 150) }}</div>
                    </td>
                    <td class="col-sentiment {{ $labelClass }}">{{ $labelText }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">Tidak ada data untuk ditampilkan</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totals di bawah tabel -->
        @php $totalItems = is_countable($rows) ? count($rows) : 0; @endphp
        <div class="totals totals-right">
            <div style="text-align:right;">
                <strong>Total</strong>
                <table>
                    <tr>
                        <th>Artikel</th>
                        <th>Positif</th>
                        <th>Netral</th>
                        <th>Negatif</th>
                    </tr>
                    <tr>
                        <td>{{ number_format($totalItems) }}</td>
                        <td>{{ number_format($sumPos) }}</td>
                        <td>{{ number_format($sumNeu) }}</td>
                        <td>{{ number_format($sumNeg) }}</td>
                    </tr>
                </table>
            </div>
        </div>


        <!-- Footer -->
        <div class="report-footer">
            Laporan ini dibuat secara otomatis oleh Sistem Analisis Sentimen
            <br>Menampilkan {{ $totalItems }} {{ $source === 'youtube' ? 'video' : 'artikel' }}
        </div>
    </div>
</body>

</html>
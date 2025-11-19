{{-- resources/views/reports/_pdf_detailed.blade.php --}}
@php
// Asumsi dipakai untuk NEWS
$rows = $newsItems ?? $items ?? [];
$agg = $summary['news'] ?? ['positive'=>0,'neutral'=>0,'negative'=>0,'total'=>0];
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Berita (News)</title>
    <style>
        @page {
            margin: 24px 24px 32px 24px;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #111;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }

        .meta {
            color: #666;
            margin-bottom: 14px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* penting agar wrap rapi */
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            vertical-align: top;
            white-space: normal;
            /* bungkus teks */
            word-wrap: break-word;
            /* dukungan dompdf */
            overflow-wrap: anywhere;
            /* modern */
            word-break: break-word;
            /* bantuan ekstra */
        }

        th {
            background: #f3f4f6;
            text-align: left;
            font-weight: 600;
        }

        td.url a {
            word-break: break-all;
            /* URL sangat panjang */
            text-decoration: underline;
            font-size: 10px;
            /* kecilkan sedikit */
        }

        tr {
            page-break-inside: avoid;
        }

        /* hindari terbelah di akhir halaman */

        .muted {
            color: #6b7280;
            font-style: italic;
            font-size: 11px;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            line-height: 1.2;
        }

        .pos {
            background: #E6F7ED;
            color: #13745D;
        }

        .neu {
            background: #F2F3F5;
            color: #555;
        }

        .neg {
            background: #FDECEC;
            color: #A61E1E;
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <h1>Laporan Berita (News)</h1>
    <div class="meta">
        Periode: {{ optional($start)->toDateString() }} – {{ optional($end)->toDateString() }}
        | Dibuat: {{ optional($generated_at)->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
        | Tipe: {{ strtoupper($rtype ?? '-') }}
    </div>

    {{-- Ringkasan --}}
    <p class="meta">
        Ringkasan: Positif {{ $agg['positive'] ?? 0 }},
        Netral {{ $agg['neutral'] ?? 0 }},
        Negatif {{ $agg['negative'] ?? 0 }},
        Total {{ $agg['total'] ?? 0 }} artikel (label terakhir per artikel).
    </p>

    {{-- Tabel Utama --}}
    <table>
        <thead>
            <tr>
                <th style="width:16%;">Tanggal</th>
                <th style="width:42%;">Judul</th>
                <th style="width:28%;">URL</th>
                <th style="width:14%;">Label</th>
                <th style="width: 85px;">Rekap</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
            @php
            $dateStr = !empty($r['date']) ? \Carbon\Carbon::parse($r['date'])->format('Y-m-d H:i:s') : '-';
            $titleText = \Illuminate\Support\Str::limit($r['title'] ?? '(tanpa judul)', 120);
            $urlFull = $r['url'] ?? '';
            $urlText = \Illuminate\Support\Str::limit($urlFull, 80);

            $lblRaw = strtolower($r['label'] ?? '');
            // normalisasi id/en
            $lblKey = in_array($lblRaw, ['positive','positif']) ? 'positive'
            : (in_array($lblRaw, ['negative','negatif']) ? 'negative' : 'neutral');
            $lblCls = $lblKey === 'positive' ? 'pos' : ($lblKey === 'negative' ? 'neg' : 'neu');
            $lblShow= $r['label'] ?? '-';
            @endphp
            <tr>
                <td>{{ $dateStr }}</td>
                <td class="title">{{ $titleText }}</td>
                <td class="url">
                    @if($urlFull)
                    <a href="{{ $urlFull }}">{{ $urlText }}</a>
                    @else
                    <span class="muted">—</span>
                    @endif
                </td>
                <td><span class="badge {{ $lblCls }}">{{ $lblShow }}</span></td>
                <td class="small">P: {{ $row['pos'] }} | N: {{ $row['neu'] }} | Neg: {{ $row['neg'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="muted" style="text-align:center;padding:14px;">
                    Tidak ada artikel pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
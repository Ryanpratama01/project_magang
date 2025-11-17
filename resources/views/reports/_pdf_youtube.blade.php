<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan YouTube</title>
    <style>
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
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* penting */
        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            font-weight: 600;
        }

        /* Bungkus teks panjang di semua sel */
        th,
        td {
            white-space: normal;
            word-wrap: break-word;
            /* alias lama, didukung dompdf */
            overflow-wrap: anywhere;
            /* modern */
            word-break: break-word;
            /* bantu tambahan */
        }

        /* URL sering sangat panjang — paksa pecah per karakter */
        td.url a {
            word-break: break-all;
            text-decoration: underline;
            font-size: 10px;
            /* kecilkan sedikit agar rapi */
        }

        /* Kurangi risiko baris kepotong di tengah halaman */
        tr {
            page-break-inside: avoid;
        }

        .muted {
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>

<body>
    <h1>Laporan Video YouTube</h1>
    <div class="meta">
        Periode: {{ $start->toDateString() }} – {{ $end->toDateString() }}
        | Dibuat: {{ $generated_at->format('Y-m-d H:i') }}
        | Tipe: {{ strtoupper($rtype) }}
    </div>

    {{-- Ringkasan --}}
    @if (!empty($summary['youtube']))
    @php $agg = $summary['youtube']; @endphp
    <p class="meta">
        Ringkasan: Positif {{ $agg['positive'] ?? 0 }},
        Netral {{ $agg['neutral'] ?? 0 }},
        Negatif {{ $agg['negative'] ?? 0 }},
        Total {{ $agg['total'] ?? 0 }} komentar (label terakhir).
    </p>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:16%;">Tanggal</th>
                <th style="width:42%;">Judul</th>
                <th style="width:28%;">URL</th>
                <th style="width:14%;">Tendensi Komentar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $v)
            @php
            $titleText = \Illuminate\Support\Str::limit($v['title'] ?? '(tanpa judul)', 120);
            $urlFull = $v['url'] ?? '';
            $urlText = \Illuminate\Support\Str::limit($urlFull, 80);
            @endphp
            <tr>
                <td class="date">{{ \Carbon\Carbon::parse($row['date'] ?? $v['date'] ?? null)
       ?->timezone(config('app.timezone'))
       ->format('Y-m-d H:i:s') ?? '-' }}
                </td>
                <td class="title">{{ $titleText }}</td>
                <td class="url">
                    @if($urlFull)
                    <a href="{{ $urlFull }}">{{ $urlText }}</a>
                    @else
                    <span class="muted">—</span>
                    @endif
                </td>
                <td class="sent">
                    P: {{ $v['pos'] ?? 0 }} |
                    N: {{ $v['neu'] ?? 0 }} |
                    Neg: {{ $v['neg'] ?? 0 }} |
                    Σ: {{ $v['total'] ?? 0 }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="muted">Tidak ada video pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>

    </table>
</body>

</html>
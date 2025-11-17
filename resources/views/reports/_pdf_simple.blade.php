@php
use Illuminate\Support\Str;

$rows = $items ?? $details ?? [];
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Laporan Analisis Sentimen</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #222;
    }

    h1 {
      font-size: 18px;
      margin: 0 0 6px;
    }

    h2 {
      font-size: 14px;
      margin: 18px 0 8px;
    }

    .meta {
      font-size: 12px;
      margin-bottom: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 6px;
      vertical-align: top;
    }

    th {
      background: #f5f5f5;
    }

    .right {
      text-align: right;
    }

    .label {
      font-weight: bold;
      text-transform: capitalize;
    }

    .muted {
      color: #666;
    }

    .wrap {
      word-wrap: break-word;
      word-break: break-word;
    }

    .mb16 {
      margin-bottom: 16px;
    }
  </style>
</head>

<body>

  <h1>Laporan Analisis Sentimen ({{ strtoupper($source) }})</h1>
  <div class="meta">
    Periode: {{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}<br>
    Jenis Laporan: {{ ucfirst($rtype) }}<br>
    Dibuat: {{ $generated_at->format('d M Y H:i') }}
  </div>

  {{-- BAGIAN A: DATA DETAIL --}}
  <h2>Daftar Data</h2>
  <table>
    <thead>
      <tr>
        <th style="width:30px;">#</th>
        <th style="width:110px;">Tanggal</th>
        <th style="width:80px;">Label</th>
        <th>Judul / Teks</th>
        <th style="width:180px;">URL</th>
      </tr>
    </thead>
    <tbody>
      @forelse($details as $i => $r)
      <tr>
        <td class="right">{{ $i+1 }}</td>
        <td>{{ \Carbon\Carbon::parse($r['date'] ?? '')->format('d M Y H:i') }}</td>
        <td class="label">{{ $r['label'] ?? '-' }}</td>
        <td class="wrap">
          @if(!empty($r['title']))
          <div><strong>{{ $r['title'] }}</strong></div>
          @endif
          @if(!empty($r['snippet']))
          <div class="muted">{{ Str::limit($r['snippet'], 200) }}</div>
          @endif
        </td>
        <td class="wrap">
          @if(!empty($r['url']))
          <a href="{{ $r['url'] }}">{{ Str::limit($r['url'], 70) }}</a>
          @else
          —
          @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="muted">Tidak ada data pada rentang tanggal ini.</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  {{-- BAGIAN B: RINGKASAN --}}
  <h2>Ringkasan Sentimen</h2>
  @php
  $agg = $summary[$source] ?? ['positive'=>0,'neutral'=>0,'negative'=>0,'total'=>0];
  @endphp
  <table class="mb16" style="width:380px;">
    <thead>
      <tr>
        <th>Positif</th>
        <th>Netral</th>
        <th>Negatif</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="right">{{ $agg['positive'] ?? 0 }}</td>
        <td class="right">{{ $agg['neutral']  ?? 0 }}</td>
        <td class="right">{{ $agg['negative'] ?? 0 }}</td>
        <td class="right">{{ $agg['total']    ?? 0 }}</td>
      </tr>
    </tbody>
  </table>

  <div class="muted">Catatan: daftar data dibatasi agar PDF tetap ringan. Silakan sesuaikan batas di controller jika diperlukan.</div>

</body>

</html>
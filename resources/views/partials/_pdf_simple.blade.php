<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Sentimen</title>
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
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th {
            background: #4F46E5;
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Analisis Sentimen</h1>
        <p>{{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}</p>
    </div>

    <p><strong>Sumber:</strong> {{ $source === 'news' ? 'Artikel Berita' : 'YouTube' }}</p>
    <p><strong>Total Data:</strong> {{ count($items) }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Judul</th>
                <th>Sentimen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y') }}</td>
                <td>{{ Str::limit($item['title'], 80) }}</td>
                <td>{{ ucfirst($item['label']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: center; font-size: 9px; color: #999;">
        <p>Generated: {{ now()->format('d M Y H:i') }} WIB</p>
    </div>
</body>

</html>
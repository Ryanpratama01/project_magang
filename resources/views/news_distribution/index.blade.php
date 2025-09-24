<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribusi Berita</title>
</head>
<body>
    <h1>Distribusi Berita</h1>

    <!-- Tabel untuk menampilkan data -->
    <table border="1">
        <thead>
            <tr>
                <th>Label</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($distributionData as $data)
                <tr>
                    <td>{{ $data->label }}</td>
                    <td>{{ $data->n }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
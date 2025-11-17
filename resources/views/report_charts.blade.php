@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Laporan Grafik Bulanan</h3>
    <form id="charts-form" action="{{ route('reports.generate.charts') }}" method="POST">
        @csrf
        <div style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
            <div>
                <label>Nama Laporan</label>
                <input type="text" name="report_name" class="form-control" value="Laporan Grafik Bulanan" required>
            </div>
            <div>
                <label>Jenis Sumber</label>
                <select name="source" class="form-control" required>
                    <option value="news">Berita</option>
                    <option value="youtube">YouTube</option>
                </select>
            </div>
            <div>
                <label>Bulan</label>
                <select name="month" class="form-control" required>
                    @foreach($months as $mVal=>$mName)
                    <option value="{{ $mVal }}" {{ $mVal==$defaultMonth?'selected':'' }}>{{ $mName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Tahun</label>
                <select name="year" class="form-control" required>
                    @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y==$defaultYear?'selected':'' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Generate Laporan Grafik (PDF)</button>
            </div>
        </div>
    </form>

    <div id="charts-result" style="margin-top:16px; display:none;">
        <a id="preview-link" class="btn btn-outline-secondary" target="_blank">Preview PDF</a>
        <a id="download-link" class="btn btn-success" target="_blank">Download PDF</a>
    </div>
</div>

<script>
    document.getElementById('charts-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.currentTarget;
        const fd = new FormData(form);
        const res = await fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('charts-result').style.display = 'block';
            document.getElementById('preview-link').href = data.preview_url;
            document.getElementById('download-link').href = data.download_url;
        } else {
            alert(data.message || 'Gagal membuat PDF');
        }
    });
</script>
@endsection
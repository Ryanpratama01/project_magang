@php use Carbon\Carbon; @endphp

<table class="w-full text-sm" data-reports-table>
    <thead>
        <tr class="text-left text-gray-500">
            <th class="py-2 px-3">Nama Laporan</th>
            <th class="py-2 px-3">Sumber</th>
            <th class="py-2 px-3">Periode</th>
            <th class="py-2 px-3">Ukuran</th>
            <th class="py-2 px-3">Dibuat</th>
            <th class="py-2 px-3">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($reports as $r)
        @php
        $rowId = 'row-' . $r->id;
        $fname = $r->file_name ?: ($r->file_path ? basename($r->file_path) : null);
        @endphp
        <tr id="{{ $rowId }}" class="border-b border-gray-100 hover:bg-gray-50">
            <td class="py-3 px-3">
                <div class="flex items-center gap-2">
                    <i class="far fa-file-pdf text-indigo-500"></i>
                    @if($fname)
                    <a class="text-indigo-700 hover:underline" href="{{ route('reports.preview', ['filename' => $fname]) }}" target="_blank" rel="noopener">
                        {{ $r->report_name }}
                    </a>
                    @else
                    <span class="text-gray-500">
                        {{ $r->report_name }} <em class="text-xs">(belum siap)</em>
                    </span>
                    @endif
                </div>
            </td>
            <td class="py-3 px-3">
                @if($r->source === 'youtube')
                <span class="inline-flex items-center gap-1 text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-md text-xs">
                    <i class="fab fa-youtube"></i> YouTube
                </span>
                @else
                <span class="inline-flex items-center gap-1 text-indigo-600 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-md text-xs">
                    <i class="far fa-newspaper"></i> Berita
                </span>
                @endif
            </td>
            <td class="py-3 px-3">
                {{ \Carbon\Carbon::parse($r->start_date)->format('d M Y') }} – {{ \Carbon\Carbon::parse($r->end_date)->format('d M Y') }}
            </td>
            <td class="py-3 px-3">{{ $r->size_human ?? '-' }}</td>
            <td class="py-3 px-3">{{ optional($r->created_at)->timezone(config('app.timezone'))->format('d M Y, H:i') }}</td>
            <td class="py-3 px-3">
                <div class="flex items-center gap-2">
                    @if($fname)
                    <a class="p-2 rounded-md border hover:bg-gray-50" title="Preview" href="{{ route('reports.preview', ['filename' => $fname]) }}" target="_blank" rel="noopener">
                        <i class="far fa-eye"></i>
                    </a>
                    <a class="p-2 rounded-md border hover:bg-gray-50" title="Download" href="{{ route('reports.download', ['filename' => $fname]) }}">
                        <i class="fas fa-download"></i>
                    </a>
                    @else
                    <span class="p-2 rounded-md border text-gray-400 cursor-not-allowed" title="File belum tersedia">
                        <i class="far fa-eye"></i>
                    </span>
                    <span class="p-2 rounded-md border text-gray-400 cursor-not-allowed" title="File belum tersedia">
                        <i class="fas fa-download"></i>
                    </span>
                    @endif

                    {{-- tombol delete ajax --}}
                    <button class="p-2 rounded-md border hover:bg-rose-50 text-rose-600 js-del-report" data-action="delete" title="Hapus" data-url="{{ route('reports.destroy', $r) }}" data-row="#{{ $rowId }}">
                        <i class="fas fa-trash"></i>
                    </button>

                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="py-6 text-center text-gray-500">Belum ada laporan.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if(method_exists($reports, 'links'))
<div class="mt-3">
    {{-- pastikan link punya class page-link agar ditangkap JS --}}
    {{ $reports->withQueryString()->links('pagination::tailwind') }}

</div>
@endif
@php
$kw = trim((string) ($q ?? ''));
$hl = function (?string $text) use ($kw) {
$text = (string) ($text ?? '');
if ($kw === '') return e($text);
$safe = e($text);
return preg_replace('/'.preg_quote($kw, '/').'/i', '<mark class="bg-yellow-200">$0</mark>', $safe);
};
@endphp

<table class="w-full text-sm border">
    <thead>
        <tr class="bg-gray-100">
            <th class="p-2 text-left">Judul</th>
            <th class="p-2 text-left">Ringkasan</th>
            <th class="p-2 text-left">Waktu</th>
            <th class="p-2 text-left">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($newsData as $news)
        <tr class="border-t hover:bg-gray-50">
            <td class="p-3 font-medium">
                <a href="{{ $news->url ?? '#' }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-800">
                    {!! $hl($news->title ?? 'Tanpa Judul') !!}
                </a>
            </td>
            <td class="p-3 text-gray-700">
                {!! $hl(\Illuminate\Support\Str::limit($news->summary_clean ?? '', 140)) !!}
            </td>
            <td class="p-3 text-gray-600">
                {{ isset($news->published_at) ? \Carbon\Carbon::parse($news->published_at)->diffForHumans() : '-' }}
            </td>
            <td class="p-3">
                <a href="{{ $news->url ?? '#' }}" target="_blank" rel="noopener" class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded">Lihat</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="p-6 text-center text-gray-500">Tidak ada data.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@php
$kw = trim((string) ($q ?? ''));
$hl = function (?string $text) use ($kw) {
$text = (string) ($text ?? '');
if ($kw === '') return e($text);
$safe = e($text);
return preg_replace('/'.preg_quote($kw, '/').'/iu', '<mark class="bg-yellow-200">$0</mark>', $safe);
};

@endphp

<table class="w-full text-sm border">
    <thead>
        <tr class="bg-gray-100">
            <th class="p-2 text-left">Judul</th>
            <th class="p-2 text-left">Sumber</th>
            <th class="p-2 text-left">Waktu</th>
            <th class="p-2 text-left">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($newsData as $news)
        @php
        $url = $news->url ?? null;
        $host = null;
        if ($url) {
        $tmp = preg_match('~^https?://~i', $url) ? $url : ('http://' . $url);
        $parsed = parse_url($tmp, PHP_URL_HOST);
        $host = $parsed ?: null;
        }
        @endphp

        <tr class="border-t hover:bg-gray-50">
            {{-- Judul (tanpa link internal, hanya teks dengan highlight) --}}
            <td class="p-3 font-medium text-gray-800">
                {!! $hl($news->title ?? 'Tanpa Judul') !!}
            </td>

            {{-- Sumber (domain) --}}
            <td class="p-3 text-gray-700">
                @if($host)
                <span class="font-medium text-gray-800">{{ $host }}</span>
                @else
                <span class="text-gray-400">—</span>
                @endif
            </td>

            {{-- Waktu (relative) --}}
            <td class="p-3 text-gray-600">
                @php
                $whenRaw = $news->published_at ?? $news->created_at ?? null;
                try {
                echo $whenRaw ? \Carbon\Carbon::parse($whenRaw)->diffForHumans() : '-';
                } catch (\Throwable $e) {
                echo '-';
                }
                @endphp
            </td>


            {{-- Aksi (ke URL asli berita) --}}
            <td class="p-3">
                @if($url)
                <a href="{{ $url }}" target="_blank" rel="noopener" class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded">
                    Lihat
                </a>
                @else
                <span class="text-gray-400">—</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="p-6 text-center text-gray-500">Tidak ada data.</td>
        </tr>
        @endforelse
    </tbody>
</table>
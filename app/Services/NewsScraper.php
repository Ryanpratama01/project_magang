<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NewsScraper
{
    public function fetch(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST);

        
        // 1) Ambil HTML dengan beberapa strategi agar tidak time-out TLS
        $html = Http::retry(3, 1000, throw: false)
        ->timeout(30)
        ->withHeaders([
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36',
            'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'Referer'         => $url,
        ])
        ->get($url)
        ->throw()
        ->body();


        // 2) Parsing seperti sebelumnya
        $c = new Crawler($html);

        // buang elemen non konten
        $this->removeNodes($c, 'script, style, noscript, iframe, header, footer, nav, form, aside, .ads, .advertisement, [class*="ads"], [id*="ads"]');

        // container artikel
        $article = $this->firstNode($c, [
            'article',
            '[itemprop="articleBody"]',
            '.article-content',
            '.post-content',
            '.read__content',
            '.detail__body-text',
            '#content', '#contentx'
        ]) ?? $c;

        // 3) title & tanggal
        $title = $this->firstText($c, [
            'meta[property="og:title"]@content',
            'meta[name="twitter:title"]@content',
            'h1','.title','.article__title','.read__title','title',
        ]);

        $date = $this->firstText($c, [
            'meta[property="article:published_time"]@content',
            'meta[name="article:published_time"]@content',
            'meta[itemprop="datePublished"]@content',
            'meta[name="pubdate"]@content',
            'time[datetime]@datetime','time@datetime',
            '.date','.time','.read__time','.article__date','.entry-date',
        ]);

        $publishedAt = $this->normalizePublishedAtOrNull($date);

        // 4) isi teks artikel (gabungkan paragraf)
        $paragraphs = $this->manyText($article, [
            'p', '.paragraph', '.teaser__paragraph', '.read__content p', '.article-content p'
        ]);
        $content = trim(collect($paragraphs)->join("\n"));

        // 5) kutipan:
        // a) elemen yang biasanya berisi kutipan
        $quoteBlocks = $this->manyText($article, [
            'blockquote','.blockquote','.pullquote','.pull-quote','.quote'
        ]);

        // b) kalimat di konten yang berpola tanda kutip
        $quotedFromMarks = $this->extractQuoted($content);

        // c) kalimat berpola verba ujaran (tanpa tanda kutip)
        $saidVerbs = [];
        if ($content !== '') {
            $sentences = preg_split('/(?<=[\.\?\!])\s+/u', $content);
            foreach ($sentences as $s) {
                $st = $this->cleanLine($s);
                if ($st === '' || mb_strlen($st) < 30) continue;
                // mengandung kata kerja ujaran / menurut
                if (preg_match('/\b(kata|ujar|ucap|tutur|menurut|imbuh|lanjut)\b/iu', $st)) {
                    // hindari kalimat yang tampak seperti kredit/foto/promosi
                    if (!preg_match('/^(baca juga|baca|simak|editor:|penulis:|foto:|dok:)/iu', $st)) {
                        $saidVerbs[] = $st;
                    }
                }
            }
        }

        $rawTexts = array_merge($quoteBlocks, $quotedFromMarks, $saidVerbs);

        $quotes = collect($rawTexts)
            ->map(fn($t) => $this->cleanLine($t))
            ->filter(fn($t) => $this->looksLikeHumanSentence($t))
            ->filter(fn($t) => mb_strlen($t) > 30)
            ->filter(fn($t) => !preg_match('/https?:\/\//i', $t))
            ->reject(fn($t) => preg_match('/^(baca juga|baca|simak|editor:|penulis:|foto:|dok:)/iu', $t))
            ->unique()
            ->map(function ($q){
                return [
                    'original_text' => $q,
                    'cleaned_text'  => $this->cleanLine($q),
                    'speaker'       => $this->extractSpeakerFromQuote($q), // jika kamu sudah punya helper ini
                ];
            })
            ->values()
            ->all();

        // coba speaker dari DOM / fallback regex
        $speaker = $this->firstText($article, ['cite','.author','.penulis','[itemprop="author"]']);
        if (!$speaker && preg_match('/\b(?:kata|ujar|ucap|tutur|menurut|imbuh|lanjut)\s+([A-Z][\p{L}\'\.\- ]{1,80})/u', $content, $m)) {
            $speaker = trim(preg_split('/[,”".]/u', $m[1])[0]);
        }

        return [
            'title'   => $title,
            'source'  => $host,
            'date'    => $date,
            'content' => $content,
            'quotes'  => $quotes,
            'speaker' => $speaker,
            'date_raw'     => $date,        // opsional: mentahan apa adanya
            'published_at' => $publishedAt, // hasil normalisasi ISO atau null
        ];
    }

    /**
     * Download HTML dengan beberapa fallback untuk mengatasi
     * SSL timeout / IPv6 / HTTP2 isu di Windows/Laragon.
     */
    private function downloadHtml(string $url): string
    {
        // strategi 1: default (HTTP/2/IPv6 jika ada)
        try {
            return $this->httpClient()->retry(3, 800)
                ->connectTimeout(10)->timeout(30)
                ->get($url)->throw()->body();
        } catch (\Throwable $e) {
            \Log::warning('[Scraper] default fetch failed: '.$e->getMessage().' url='.$url);
        }

        // strategi 2: paksa IPv4 & HTTP/1.1 (sering menyembuhkan timeouts di Windows)
        try {
            return $this->httpClient([
                    'force_ip_resolve' => 'v4',
                    'version'          => 1.1,
                ])->retry(3, 800)
                ->connectTimeout(10)->timeout(30)
                ->get($url)->throw()->body();
        } catch (\Throwable $e) {
            \Log::warning('[Scraper] v4/h1 fetch failed: '.$e->getMessage().' url='.$url);
        }

        // strategi 3 (debug ONLY): non-verify CA (jika CA bundle bermasalah)
        // aktifkan sementara ketika lokal bermasalah, jangan dipakai di produksi.
        try {
            return $this->httpClient([
                    'verify'           => false,   // ⚠️ hanya untuk debugging lokal
                    'force_ip_resolve' => 'v4',
                    'version'          => 1.1,
                ])->retry(2, 800)
                ->connectTimeout(10)->timeout(30)
                ->get($url)->throw()->body();
        } catch (\Throwable $e) {
            \Log::error('[Scraper] all strategies failed: '.$e->getMessage().' url='.$url);
            throw $e; // biar job terlihat gagal & bisa di-retry queue
        }
    }

    /** Factory PendingRequest dengan header yang lebih “real browser”. */
    private function httpClient(array $extraOptions = []): \Illuminate\Http\Client\PendingRequest
    {
        $defaultOptions = [
            // beberapa server rewel terhadap TLS versi lama di Windows
            'curl' => [
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            ],
        ];

        return \Illuminate\Support\Facades\Http::withOptions(array_replace_recursive($defaultOptions, $extraOptions))
            ->withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                'Referer'         => 'https://www.google.com/',
                'Cache-Control'   => 'no-cache',
            ]);
    }

    /* ---------- helpers ---------- */

    private function removeNodes(Crawler $c, string $selector): void
    {
        $c->filter($selector)->each(function (Crawler $n) {
            $node = $n->getNode(0);
            if ($node && $node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        });
    }

    private function firstNode(Crawler $c, array $selectors): ?Crawler
    {
        foreach ($selectors as $sel) {
            if ($c->filter($sel)->count()) return $c->filter($sel)->eq(0);
        }
        return null;
    }

    private function firstText(Crawler $c, array $selectors): ?string
    {
        foreach ($selectors as $sel) {
            [$css, $attr] = str_contains($sel, '@') ? explode('@', $sel, 2) : [$sel, null];
            if ($c->filter($css)->count()) {
                return $attr ? trim((string)$c->filter($css)->attr($attr))
                             : trim($c->filter($css)->eq(0)->text());
            }
        }
        return null;
    }

    private function cleanQuote(string $text): string
    {
        // hapus spasi ganda
        $text = preg_replace('/\s+/u', ' ', $text);
        // hapus karakter non-alfabet/angka dasar
        $text = preg_replace('/[^a-zA-Z0-9\s.,?!]/u', '', $text);
        $text = trim($text);
        return trim($text);
    }
    private function manyText(Crawler $c, array $selectors): array
    {
        foreach ($selectors as $sel) {
            if ($c->filter($sel)->count()) {
                return $c->filter($sel)->each(fn($n) => trim($n->text()));
            }
        }
        return [];
    }

    
        private function extractQuoted(string $text): array
{
    $out = [];
    // “ … ” atau " … "
    if (preg_match_all('/“([^”]+)”|"([^"]+)"/u', $text, $m)) {
        foreach ($m[1] as $i => $a) {
            $out[] = $a ?: ($m[2][$i] ?? '');
        }
    }
    return array_values(array_filter($out));
}

//         $out = [];
//         // “ … ” atau " … "
//         if (preg_match('/\b(?:kata|ujar|ucap|tutur|menurut|imbuh|lanjut)\s+([A-Z][\p{L}\.\'\- ]{1,80})/u', $quote, $m)) {
//     return trim(preg_split('/[,”".]/u', $m[1])[0]);
// }

//         return array_values(array_filter($out));
//     }
    private function extractSpeakerFromQuote(string $quote): ?string
    {
        // pola: "..., kata/ujar/ucap/menurut Nama"
        if (preg_match('/\b(?:kata|ujar|ucap|menurut)\s+([A-Z][\p{L}\.\'\- ]{2,50})/u', $quote, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function cleanLine(string $t): string
    {
        $t = preg_replace('/\s+/u', ' ', $t);
        return trim($t);
    }

    private function looksLikeHumanSentence(string $t): bool
    {
        if ($t === '') return false;
        if (mb_strlen($t) < 15) return false; // terlalu pendek → buang
        // buang yang terlihat seperti kode/asset
        $bad = ['.js', '.css', '{', '}', 'function(', 'var ', '#', 'http://', 'https://', '/vendor/'];
        foreach ($bad as $b) if (mb_stripos($t, $b) !== false) return false;
        return true;
    }

    private function isLikelyQuote(string $t): bool {
    return (bool) preg_match('/[“"].+?[”"]|(?:\b(?:kata|ujar|ucap|tutur|menurut|imbuh|lanjut)\b)/iu', $t);
    }

    // ---- TARUH DI PALING BAWAH DALAM CLASS NewsScraper ----
    private function normalizePublishedAtOrNull(?string $raw): ?string
    {
        if (!$raw) return null;

        // WIB/WITA/WIT → offset
        $raw = preg_replace('/\bWIB\b/i',  '+07:00', $raw);
        $raw = preg_replace('/\bWITA\b/i', '+08:00', $raw);
        $raw = preg_replace('/\bWIT\b/i',  '+09:00', $raw);

        // Bulan Indonesia → Inggris (umum saja)
        $map = [
            'Januari'=>'January','Februari'=>'February','Maret'=>'March','April'=>'April',
            'Mei'=>'May','Juni'=>'June','Juli'=>'July','Agustus'=>'August',
            'September'=>'September','Oktober'=>'October','November'=>'November','Desember'=>'December',
        ];
        $clean = str_ireplace(array_keys($map), array_values($map), trim($raw));

        // ISO/umum
        try { return \Carbon\Carbon::parse($clean)->toIso8601String(); } catch (\Throwable $e) {}

        foreach (['d F Y H:i:s','d F Y H:i','Y-m-d H:i:s','Y-m-d H:i','d-m-Y H:i','d/m/Y H:i'] as $f) {
            try { return \Carbon\Carbon::createFromFormat($f, $clean, 'Asia/Jakarta')->toIso8601String(); }
            catch (\Throwable $e) {}
        }

        // Pola relatif (x menit/jam/hari lalu)
        if (preg_match('/(\d+)\s*(menit|jam|hari)\s+lalu/i', $clean, $m)) {
            $n = (int)$m[1]; $unit = strtolower($m[2]);
            $c = \Carbon\Carbon::now('Asia/Jakarta');
            return match ($unit) {
                'menit' => $c->subMinutes($n)->toIso8601String(),
                'jam'   => $c->subHours($n)->toIso8601String(),
                default => $c->subDays($n)->toIso8601String(),
            };
        }

        return null; // gagal parse → biarkan null (jangan pakai now)
    }

}

<?php

namespace App\Services;

use Google\Client;
use Google\Service\YouTube;
use GuzzleHttp\Client as Guzzle;

class YoutubeScraper
{
    protected YouTube $yt;

    public function __construct()
    {
        $client = new Client();
        $client->setDeveloperKey(env('YOUTUBE_API_KEY'));

        // Opsi Guzzle yang aman di Windows; fallback jika cacert tidak ada
        $cacert = base_path('storage/cacert.pem');
        $verify = file_exists($cacert) ? $cacert : true;

        $guzzle = new Guzzle([
            'timeout'         => 20,
            'connect_timeout' => 10,
            'verify'          => $verify,
            'curl'            => [
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            ],
        ]);

        $client->setHttpClient($guzzle);

        // FIX: isi properti yang benar ($yt), bukan $this->youtube
        $this->yt = new YouTube($client);
    }

    /**
     * Ekstrak videoId dari berbagai bentuk URL YouTube.
     */
    public function videoId(string $url): ?string
    {
        $url = trim($url);
        $patterns = [
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/i',
            '/[?&]v=([a-zA-Z0-9_-]{11})/i',
            '/\/embed\/([a-zA-Z0-9_-]{11})/i',
            '/\/shorts\/([a-zA-Z0-9_-]{11})/i',
        ];
        foreach ($patterns as $p) {
            if (preg_match($p, $url, $m)) return $m[1];
        }
        return preg_match('/^[a-zA-Z0-9_-]{11}$/', $url) ? $url : null;
    }
    /**
     * Ambil metadata video.
     */
    public function meta(string $videoId): array
    {
        $res  = $this->yt->videos->listVideos('snippet,statistics', ['id' => $videoId]);
        $item = $res->getItems()[0] ?? null;

        return [
            'title'   => $item?->getSnippet()?->getTitle(),
            'channel' => $item?->getSnippet()?->getChannelTitle(),
            'stats'   => $item?->getStatistics(), // objek Google\Service\YouTube\VideoStatistics
        ];
    }

    /**
     * Stream komentar (top-level). Kembalikan juga comment_id.
     */
    public function comments(string $videoId): \Generator
    {
        $token = null;
        do {
            $resp = $this->yt->commentThreads->listCommentThreads('snippet', [
                'videoId'    => $videoId,
                'maxResults' => 100,
                'textFormat' => 'plainText',
                'pageToken'  => $token,
                // 'order'   => 'time', // opsional: urut terbaru
            ]);

            foreach ($resp->getItems() as $thread) {
                $top      = $thread->getSnippet()->getTopLevelComment();
                $sn       = $top->getSnippet();
                $commentId = $top->getId(); // FIX: kirim id komentar

                yield [
                    'id'            => $commentId,
                    'text'          => $sn->getTextDisplay(),
                    'author'        => $sn->getAuthorDisplayName(),
                    'likes'         => (int) $sn->getLikeCount(),
                    'published_at'  => $sn->getPublishedAt(),
                    'replies_count' => (int) $thread->getSnippet()->getTotalReplyCount(),
                ];
            }

            $token = $resp->getNextPageToken();
        } while ($token);
    }
}

<?php

return [
    'default' => [
        'title'   => ['meta[property="og:title"]@content', 'title'],
        'content' => ['article', '.article-content', '.post-content'],
        'quotes'  => ['blockquote'],
        'date'    => ['meta[property="article:published_time"]@content'],
    ],

    // contoh profil untuk detik.com
    'detik.com' => [
        'title'   => ['h1.detail__title', 'meta[property="og:title"]@content'],
        'content' => ['div.detail__body-text'],
        'quotes'  => ['blockquote', '.pull-quote'],
        'date'    => ['div.detail__date', 'meta[property="article:published_time"]@content'],
    ],
];

<?php
$kw = trim((string) ($q ?? ''));
$hl = function (?string $text) use ($kw) {
$text = (string) ($text ?? '');
if ($kw === '') return e($text);
$safe = e($text);
return preg_replace('/'.preg_quote($kw, '/').'/iu', '<mark class="bg-yellow-200">$0</mark>', $safe);
};

?>

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
        <?php $__empty_1 = true; $__currentLoopData = $newsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $news): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
        $url = $news->url ?? null;
        $host = null;
        if ($url) {
        $tmp = preg_match('~^https?://~i', $url) ? $url : ('http://' . $url);
        $parsed = parse_url($tmp, PHP_URL_HOST);
        $host = $parsed ?: null;
        }
        ?>

        <tr class="border-t hover:bg-gray-50">
            
            <td class="p-3 font-medium text-gray-800">
                <?php echo $hl($news->title ?? 'Tanpa Judul'); ?>

            </td>

            
            <td class="p-3 text-gray-700">
                <?php if($host): ?>
                <span class="font-medium text-gray-800"><?php echo e($host); ?></span>
                <?php else: ?>
                <span class="text-gray-400">—</span>
                <?php endif; ?>
            </td>

            
            <td class="p-3 text-gray-600">
                <?php
                $whenRaw = $news->published_at ?? $news->created_at ?? null;
                try {
                echo $whenRaw ? \Carbon\Carbon::parse($whenRaw)->diffForHumans() : '-';
                } catch (\Throwable $e) {
                echo '-';
                }
                ?>
            </td>


            
            <td class="p-3">
                <?php if($url): ?>
                <a href="<?php echo e($url); ?>" target="_blank" rel="noopener" class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded">
                    Lihat
                </a>
                <?php else: ?>
                <span class="text-gray-400">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="4" class="p-6 text-center text-gray-500">Tidak ada data.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table><?php /**PATH C:\laragon\www\project_magang\resources\views/partials/_news_table_dashboard.blade.php ENDPATH**/ ?>
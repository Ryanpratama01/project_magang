<?php use Carbon\Carbon; ?>

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
        <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
        $rowId = 'row-' . $r->id;
        $fname = $r->file_name ?: ($r->file_path ? basename($r->file_path) : null);
        ?>
        <tr id="<?php echo e($rowId); ?>" class="border-b border-gray-100 hover:bg-gray-50">
            <td class="py-3 px-3">
                <div class="flex items-center gap-2">
                    <i class="far fa-file-pdf text-indigo-500"></i>
                    <?php if($fname): ?>
                    <a class="text-indigo-700 hover:underline" href="<?php echo e(route('reports.preview', ['filename' => $fname])); ?>" target="_blank" rel="noopener">
                        <?php echo e($r->report_name); ?>

                    </a>
                    <?php else: ?>
                    <span class="text-gray-500">
                        <?php echo e($r->report_name); ?> <em class="text-xs">(belum siap)</em>
                    </span>
                    <?php endif; ?>
                </div>
            </td>
            <td class="py-3 px-3">
                <?php if($r->source === 'youtube'): ?>
                <span class="inline-flex items-center gap-1 text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-md text-xs">
                    <i class="fab fa-youtube"></i> YouTube
                </span>
                <?php else: ?>
                <span class="inline-flex items-center gap-1 text-indigo-600 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-md text-xs">
                    <i class="far fa-newspaper"></i> Berita
                </span>
                <?php endif; ?>
            </td>
            <td class="py-3 px-3">
                <?php echo e(\Carbon\Carbon::parse($r->start_date)->format('d M Y')); ?> – <?php echo e(\Carbon\Carbon::parse($r->end_date)->format('d M Y')); ?>

            </td>
            <td class="py-3 px-3"><?php echo e($r->size_human ?? '-'); ?></td>
            <td class="py-3 px-3"><?php echo e(optional($r->created_at)->timezone(config('app.timezone'))->format('d M Y, H:i')); ?></td>
            <td class="py-3 px-3">
                <div class="flex items-center gap-2">
                    <?php if($fname): ?>
                    <a class="p-2 rounded-md border hover:bg-gray-50" title="Preview" href="<?php echo e(route('reports.preview', ['filename' => $fname])); ?>" target="_blank" rel="noopener">
                        <i class="far fa-eye"></i>
                    </a>
                    <a class="p-2 rounded-md border hover:bg-gray-50" title="Download" href="<?php echo e(route('reports.download', ['filename' => $fname])); ?>">
                        <i class="fas fa-download"></i>
                    </a>
                    <?php else: ?>
                    <span class="p-2 rounded-md border text-gray-400 cursor-not-allowed" title="File belum tersedia">
                        <i class="far fa-eye"></i>
                    </span>
                    <span class="p-2 rounded-md border text-gray-400 cursor-not-allowed" title="File belum tersedia">
                        <i class="fas fa-download"></i>
                    </span>
                    <?php endif; ?>

                    
                    <button class="p-2 rounded-md border hover:bg-rose-50 text-rose-600 js-del-report" data-action="delete" title="Hapus" data-url="<?php echo e(route('reports.destroy', $r)); ?>" data-row="#<?php echo e($rowId); ?>">
                        <i class="fas fa-trash"></i>
                    </button>

                </div>
            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="6" class="py-6 text-center text-gray-500">Belum ada laporan.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if(method_exists($reports, 'links')): ?>
<div class="mt-3">
    
    <?php echo e($reports->withQueryString()->links('pagination::tailwind')); ?>


</div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\project_magang\resources\views/reports/_table.blade.php ENDPATH**/ ?>
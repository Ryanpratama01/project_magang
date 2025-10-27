<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Grafik Sentimen - <?php echo e($source === 'news' ? 'Berita' : 'YouTube'); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #1F2937;
            line-height: 1.5;
            background: #ffffff;
            padding: 20px;
        }

        /* Header - Sama seperti Laporan Tabel */
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #4F46E5;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .report-meta {
            font-size: 10px;
            color: #6B7280;
            line-height: 1.6;
        }

        .report-meta strong {
            color: #374151;
        }

        /* Chart Section */
        .chart-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .chart-title {
            background: #F3F4F6;
            padding: 8px 12px;
            border-left: 4px solid #6366F1;
            font-size: 12px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 12px;
        }

        .chart-wrapper {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }

        .chart-wrapper img {
            max-width: 100%;
            width: 550px;
            height: auto;
        }

        /* Summary Table */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 9px;
        }

        .summary-table th {
            background: #4F46E5;
            color: white;
            padding: 7px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #4338CA;
            font-size: 9px;
        }

        .summary-table td {
            padding: 6px;
            text-align: center;
            border: 1px solid #E5E7EB;
        }

        .summary-table tr:nth-child(even) {
            background: #F9FAFB;
        }

        .summary-table .total-row {
            background: #EEF2FF;
            font-weight: bold;
        }

        .stat-positive {
            color: #059669;
            font-weight: bold;
        }

        .stat-neutral {
            color: #D97706;
            font-weight: bold;
        }

        .stat-negative {
            color: #DC2626;
            font-weight: bold;
        }

        /* Sample Data Section */
        .sample-section {
            margin-top: 25px;
            page-break-before: always;
        }

        .sample-title {
            background: #F3F4F6;
            padding: 8px 12px;
            border-left: 4px solid #10B981;
            font-size: 12px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 12px;
        }

        .sample-item {
            background: #FAFAFA;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .sample-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid #E5E7EB;
        }

        .sample-date {
            font-size: 8px;
            color: #6B7280;
            font-weight: 600;
        }

        .sample-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-positive {
            background: #D1FAE5;
            color: #065F46;
        }

        .badge-neutral {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-negative {
            background: #FEE2E2;
            color: #991B1B;
        }

        .sample-title-text {
            font-size: 9px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .sample-content {
            font-size: 8px;
            color: #4B5563;
            line-height: 1.4;
            font-style: italic;
            margin-top: 4px;
            padding: 6px;
            background: white;
            border-left: 2px solid #E5E7EB;
        }

        .sample-stats {
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #E5E7EB;
            font-size: 7px;
            color: #6B7280;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 8px;
            color: #9CA3AF;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            background: #FEF3C7;
            border-radius: 6px;
            color: #92400E;
            font-size: 9px;
        }

        /* ===== TAMBAHAN CSS UNTUK HALAMAN 2: DETAIL KONTEN ===== */
        .content-item {
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .content-item .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #E5E7EB;
        }

        .content-item .item-date {
            font-size: 10px;
            color: #6B7280;
            font-weight: 600;
        }

        .content-item .item-title {
            font-size: 12px;
            font-weight: bold;
            color: #1F2937;
            margin: 6px 0;
        }

        .content-item .item-snippet {
            font-size: 10px;
            color: #4B5563;
            line-height: 1.6;
            margin: 8px 0;
            padding: 10px;
            background: #F9FAFB;
            border-left: 3px solid #E5E7EB;
            border-radius: 4px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge.positive {
            background: #D1FAE5;
            color: #065F46;
        }

        .badge.neutral {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge.negative {
            background: #FEE2E2;
            color: #991B1B;
        }

        .sentiment-stats {
            font-size: 10px;
            color: #6B7280;
            margin-top: 8px;
        }

        .sentiment-stats strong {
            font-weight: 600;
        }

        .content-section {
            margin-top: 30px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    
    <div class="report-header">
        <div class="report-title"><?php echo e($report_name ?? 'Laporan Analisis Sentimen'); ?></div>
        <div class="report-meta">
            <strong>Sumber:</strong> <?php echo e($source === 'youtube' ? 'YouTube' : 'Artikel Berita'); ?>

            <?php if(isset($start) && isset($end)): ?>
            • <strong>Periode:</strong> <?php echo e($start->format('d M Y')); ?> – <?php echo e($end->format('d M Y')); ?>

            <?php endif; ?>
            • <strong>Jenis:</strong> <?php echo e($rtype === 'monthly' ? 'Bulanan' : 'Tahunan'); ?>

            • <strong>Dibuat:</strong> <?php echo e($generated_at->format('d M Y, H:i')); ?> WIB
        </div>
    </div>

    
    <?php
    $summaryData = $source === 'news'
    ? ($summary['news'] ?? [])
    : ($summary['youtube'] ?? []);
    $totalData = $summaryData['total'] ?? 0;
    ?>

    
    <div class="chart-section">
        <div class="chart-title">
            Grafik Trend Sentimen <?php echo e($rtype === 'monthly' ? 'Harian' : 'Bulanan'); ?>

        </div>
        <div class="chart-wrapper">
            <?php if(!empty($chartImg['bar'])): ?>
            <img src="<?php echo e($chartImg['bar']); ?>" alt="Bar Chart">
            <?php else: ?>
            <div class="no-data">Grafik tidak tersedia</div>
            <?php endif; ?>
        </div>

        
        <?php if(isset($chartUrls['monthly']) && !empty($chartUrls['monthly']['labels'])): ?>
        <table class="summary-table">
            <thead>
                <tr>
                    <th><?php echo e($rtype === 'monthly' ? 'Tanggal' : 'Bulan'); ?></th>
                    <th>Positif</th>
                    <th>Netral</th>
                    <th>Negatif</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $chartUrls['monthly']['labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                $pos = $chartUrls['monthly']['positive'][$index] ?? 0;
                $neu = $chartUrls['monthly']['neutral'][$index] ?? 0;
                $neg = $chartUrls['monthly']['negative'][$index] ?? 0;
                $tot = $pos + $neu + $neg;
                ?>
                <tr>
                    <td><strong><?php echo e($label); ?></strong></td>
                    <td class="stat-positive"><?php echo e(number_format($pos)); ?></td>
                    <td class="stat-neutral"><?php echo e(number_format($neu)); ?></td>
                    <td class="stat-negative"><?php echo e(number_format($neg)); ?></td>
                    <td><?php echo e(number_format($tot)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="stat-positive"><?php echo e(number_format(array_sum($chartUrls['monthly']['positive'] ?? []))); ?></td>
                    <td class="stat-neutral"><?php echo e(number_format(array_sum($chartUrls['monthly']['neutral'] ?? []))); ?></td>
                    <td class="stat-negative"><?php echo e(number_format(array_sum($chartUrls['monthly']['negative'] ?? []))); ?></td>
                    <td><?php echo e(number_format($totalData)); ?></td>
                </tr>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    
    <div class="page-break"></div>

    
    <div class="chart-section">
        <div class="chart-title">
            Grafik Distribusi Sentimen Keseluruhan
        </div>
        <div class="chart-wrapper">
            <?php if(!empty($chartImg['pie'])): ?>
            <img src="<?php echo e($chartImg['pie']); ?>" alt="Pie Chart">
            <?php else: ?>
            <div class="no-data">Grafik tidak tersedia</div>
            <?php endif; ?>
        </div>

        
        <table class="summary-table" style="margin-top:12px;">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Jumlah</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $posPct = $totalData > 0 ? round((($summaryData['positive'] ?? 0) / $totalData) * 100, 1) : 0;
                $neuPct = $totalData > 0 ? round((($summaryData['neutral'] ?? 0) / $totalData) * 100, 1) : 0;
                $negPct = $totalData > 0 ? round((($summaryData['negative'] ?? 0) / $totalData) * 100, 1) : 0;
                ?>
                <tr>
                    <td><strong>Positif</strong></td>
                    <td class="stat-positive"><?php echo e(number_format($summaryData['positive'] ?? 0)); ?></td>
                    <td class="stat-positive"><?php echo e($posPct); ?>%</td>
                </tr>
                <tr>
                    <td><strong>Netral</strong></td>
                    <td class="stat-neutral"><?php echo e(number_format($summaryData['neutral'] ?? 0)); ?></td>
                    <td class="stat-neutral"><?php echo e($neuPct); ?>%</td>
                </tr>
                <tr>
                    <td><strong>Negatif</strong></td>
                    <td class="stat-negative"><?php echo e(number_format($summaryData['negative'] ?? 0)); ?></td>
                    <td class="stat-negative"><?php echo e($negPct); ?>%</td>
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL</strong></td>
                    <td><?php echo e(number_format($totalData)); ?></td>
                    <td>100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    
    <?php if(!empty($items) && count($items) > 0): ?>
    <div class="sample-section">
        <div class="sample-title">
            <?php echo e($source === 'news' ? 'Sample Artikel dengan Kutipan (Top 10)' : 'Sample Video dengan Komentar (Top 10)'); ?>

        </div>

        <?php $__currentLoopData = array_slice($items, 0, 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="sample-item">
            <div class="sample-header">
                <span class="sample-date">
                    <?php echo e(\Carbon\Carbon::parse($item['date'])->format('d M Y')); ?>

                </span>
                <span class="sample-badge badge-<?php echo e($item['label'] ?? 'neutral'); ?>">
                    <?php echo e(ucfirst($item['label'] ?? 'neutral')); ?>

                </span>
            </div>

            <div class="sample-title-text">
                <?php echo e(Str::limit($item['title'] ?? 'Tanpa Judul', 120)); ?>

            </div>

            
            <?php if($source === 'news' && !empty($item['snippet'])): ?>
            <div class="sample-content">
                "<?php echo e(Str::limit($item['snippet'], 200)); ?>"
            </div>
            <?php endif; ?>

            
            <?php if($source === 'youtube' && !empty($item['comment_text'])): ?>
            <div class="sample-content">
                "<?php echo e(Str::limit($item['comment_text'], 180)); ?>"
            </div>
            <?php endif; ?>

            <div class="sample-stats">
                <strong>Sentimen:</strong>
                <span class="stat-positive">Positif: <?php echo e($item['pos'] ?? 0); ?></span> •
                <span class="stat-neutral">Netral: <?php echo e($item['neu'] ?? 0); ?></span> •
                <span class="stat-negative">Negatif: <?php echo e($item['neg'] ?? 0); ?></span>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Analisis Sentimen</p>
        <p>Menampilkan <?php echo e($totalData); ?> <?php echo e($source === 'youtube' ? 'video' : 'artikel'); ?></p>
    </div>

    
    <div class="page-break"></div>

    <div class="header">
        <h1>📋 DETAIL KONTEN</h1>
        <p><strong><?php echo e($source === 'news' ? 'Kutipan Artikel' : 'Sample Komentar YouTube'); ?></strong></p>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td class="label">Total <?php echo e($source === 'news' ? 'Artikel' : 'Video'); ?> Ditampilkan</td>
                <?php
                $summaryKey = $source === 'news' ? 'news' : 'youtube';
                $totalData = ($summary[$summaryKey]['total'] ?? 0);
                $itemsCount = isset($items) ? (is_countable($items) ? count($items) : 0) : 0;
                ?>
                <td>: <strong><?php echo e(number_format(min(50, $itemsCount))); ?></strong> dari <?php echo e(number_format($totalData)); ?></td>
            </tr>
            <tr>
                <td class="label">Catatan</td>
                <td>: Menampilkan <?php echo e($source === 'news' ? 'kutipan ringkasan' : 'sample komentar'); ?> terbaru</td>
            </tr>
        </table>
    </div>

    <div class="content-section">
        <?php
        $displayItems = [];
        if (isset($items)) {
        if (is_array($items)) {
        $displayItems = array_slice($items, 0, 50);
        } elseif (is_object($items) && method_exists($items, 'take')) {
        $displayItems = $items->take(50);
        }
        }
        ?>

        <?php $__empty_1 = true; $__currentLoopData = $displayItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="content-item">
            
            <div class="item-header">
                <span class="item-date">
                    📅 <?php echo e(\Carbon\Carbon::parse($item['date'] ?? now())->format('d M Y, H:i')); ?>

                </span>
                <?php
                $pos = (int) ($item['pos'] ?? 0);
                $neu = (int) ($item['neu'] ?? 0);
                $neg = (int) ($item['neg'] ?? 0);
                $label = ($pos > $neu && $pos > $neg) ? 'positive' : (($neg > $pos && $neg > $neu) ? 'negative' : 'neutral');
                ?>
                <span class="badge <?php echo e($label); ?>"><?php echo e(ucfirst($label)); ?></span>
            </div>

            
            <div class="item-title">
                <?php if($source === 'news'): ?>
                📰 <?php echo e(\Illuminate\Support\Str::limit($item['title'] ?? 'Tanpa Judul', 100)); ?>

                <?php else: ?>
                ▶️ <?php echo e(\Illuminate\Support\Str::limit($item['title'] ?? 'Tanpa Judul', 100)); ?>

                <?php endif; ?>
            </div>

            
            <?php if($source === 'news'): ?>
            
            <?php if(!empty($item['snippet'])): ?>
            <div class="item-snippet">
                <?php echo e(\Illuminate\Support\Str::limit($item['snippet'], 300)); ?>

            </div>
            <?php else: ?>
            <div class="item-snippet" style="color:#9CA3AF; font-style:italic;">
                (Tidak ada ringkasan tersedia)
            </div>
            <?php endif; ?>
            <?php else: ?>
            
            <?php if(!empty($item['comment_text'])): ?>
            <div class="item-snippet">
                <strong style="color:#6B7280;">💬 Sample Komentar:</strong><br>
                "<?php echo e(\Illuminate\Support\Str::limit($item['comment_text'], 250)); ?>"
            </div>
            <?php else: ?>
            <div class="item-snippet" style="color:#9CA3AF; font-style:italic;">
                (Tidak ada komentar tersedia)
            </div>
            <?php endif; ?>
            <?php endif; ?>

            
            <div class="sentiment-stats">
                <strong style="color:#10B981;">Positif: <?php echo e($item['pos'] ?? 0); ?></strong> •
                <strong style="color:#F59E0B;">Netral: <?php echo e($item['neu'] ?? 0); ?></strong> •
                <strong style="color:#EF4444;">Negatif: <?php echo e($item['neg'] ?? 0); ?></strong>
                <?php if($source === 'youtube'): ?>
                • Total Komentar: <?php echo e($item['total'] ?? 0); ?>

                <?php endif; ?>
            </div>
        </div>

        
        <?php if(($index + 1) % 10 === 0 && $index + 1 < count($displayItems)): ?> <div class="page-break">
    </div>
    <?php endif; ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="content-item">
        <p style="text-align:center; color:#6B7280; padding:20px;">
            Tidak ada data konten untuk ditampilkan
        </p>
    </div>
    <?php endif; ?>
    </div>

    <div class="footer">
        <p><strong>Halaman 2 dari 2</strong> - Detail Konten</p>
        <p style="margin-top:10px;">Dokumen ini digenerate otomatis oleh Sistem Analisis Sentimen</p>
        <p>© <?php echo e(now()->year); ?> - Laporan ini bersifat rahasia dan hanya untuk penggunaan internal</p>
    </div>
</body>

</html><?php /**PATH C:\xampp\htdocs\project_magang\resources\views/partials/_pdf_charts.blade.php ENDPATH**/ ?>
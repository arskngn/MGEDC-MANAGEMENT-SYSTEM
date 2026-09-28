<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Executive Dashboard Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #374151; margin: 0; padding: 20px 28px; background: #fff; }

        /* ── Header row ── */
        .hdr { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .hdr td { vertical-align: middle; padding: 0; }
        .hdr .left .title  { font-size: 15px; font-weight: bold; color: #111827; }
        .hdr .left .meta   { font-size: 8px; color: #9ca3af; margin-top: 2px; }
        .hdr .right        { text-align: right; }
        .hdr .right img    { max-height: 30px; max-width: 100px; display: block; margin-left: auto; }
        .hdr .right .bname { font-size: 9px; font-weight: bold; color: #374151; margin-top: 3px; }

        /* ── KPI summary strip ── */
        .strip { width: 100%; border-collapse: collapse; margin-bottom: 18px;
                 border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; }
        .strip td { padding: 8px 10px; vertical-align: middle; text-align: center; }
        .strip td:first-child { border-left: none; }
        .strip .stat { border-left: 1px solid #e5e7eb; }
        .strip .slbl { font-size: 7.5px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.3px; font-weight: bold; }
        .strip .sval { font-size: 12px; font-weight: bold; color: #111827; margin-top: 2px; }
        .strip .ssub { font-size: 7.5px; color: #6b7280; margin-top: 2px; }

        /* ── Section header ── */
        .sec-hdr { font-size: 10px; font-weight: bold; color: #111827; text-transform: uppercase; letter-spacing: 0.4px; margin-top: 16px; margin-bottom: 6px; }

        /* ── Tables ── */
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 16px; }
        table.data th {
            font-size: 7.5px; font-weight: bold; color: #6b7280;
            text-transform: uppercase; letter-spacing: 0.3px;
            padding: 6px 5px; border-bottom: 1.5px solid #374151;
            white-space: nowrap; overflow: hidden;
        }
        table.data td {
            font-size: 8.5px; padding: 5px 5px;
            border-bottom: 1px solid #f3f4f6;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        table.data tbody tr:last-child td { border-bottom: none; }

        .l  { text-align: left; }
        .r  { text-align: right; }
        .c  { text-align: center; }

        .footer { margin-top: 24px; border-top: 1px solid #f3f4f6;
                  padding-top: 6px; font-size: 7.5px; color: #d1d5db; text-align: center; }
    </style>
</head>
<body>
    
    <table class="hdr">
        <tr>
            <td class="left">
                <div class="title">Executive Dashboard Analytics Report</div>
                <div class="meta">
                    Period: <?php echo e(\Carbon\Carbon::parse($analytics['date_from'])->format('M d, Y')); ?> &ndash; <?php echo e(\Carbon\Carbon::parse($analytics['date_to'])->format('M d, Y')); ?>

                    &bull; Generated: <?php echo e(now()->format('M d, Y h:i A')); ?>

                </div>
            </td>
            <td class="right">
                <?php if(!empty($logoSrc)): ?>
                    <img src="<?php echo e($logoSrc); ?>" alt="Logo">
                <?php endif; ?>
                <div class="bname"><?php echo e($generalSetting?->site_title ?? $generalSetting?->company_name ?? 'GE-System'); ?></div>
            </td>
        </tr>
    </table>

    
    <table class="strip">
        <tr>
            <td class="stat" style="border-left: none; width: 25%;">
                <div class="slbl">Sales Revenue</div>
                <div class="sval"><?php echo e(formatCurrency($analytics['total_sales'])); ?></div>
                <div class="ssub"><?php echo e(number_format($analytics['sales_count'])); ?> orders processed</div>
            </td>
            <td class="stat" style="width: 25%;">
                <div class="slbl">Purchasing Cost</div>
                <div class="sval"><?php echo e(formatCurrency($analytics['total_purchases'])); ?></div>
                <div class="ssub"><?php echo e(number_format($analytics['purchases_count'])); ?> orders placed</div>
            </td>
            <td class="stat" style="width: 25%;">
                <div class="slbl">Gross Profit Margin</div>
                <div class="sval"><?php echo e(formatCurrency($analytics['gross_profit'])); ?></div>
                <div class="ssub"><?php echo e($analytics['profit_margin']); ?>% margin</div>
            </td>
            <td class="stat" style="width: 25%;">
                <div class="slbl">Outstanding Receivables</div>
                <div class="sval"><?php echo e(formatCurrency($analytics['sales_due'])); ?></div>
                <div class="ssub">Pending customer collections</div>
            </td>
        </tr>
    </table>

    
    <div class="sec-hdr">Top 10 Selling Products</div>
    <table class="data">
        <colgroup>
            <col style="width: 32px;">
            <col style="width: 280px;">
            <col style="width: 170px;">
            <col style="width: 110px;">
            <col style="width: 150px;">
        </colgroup>
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="l">Product Name</th>
                <th class="l">SKU</th>
                <th class="r">Quantity Sold</th>
                <th class="r">Sales Value</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $analytics['top_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="c"><?php echo e($idx + 1); ?></td>
                    <td class="l"><strong><?php echo e($p->name); ?></strong></td>
                    <td class="l" style="color: #6b7280;"><?php echo e($p->sku ?: '—'); ?></td>
                    <td class="r"><?php echo e(number_format($p->total_quantity)); ?></td>
                    <td class="r" style="font-weight: bold;"><?php echo e(formatCurrency($p->total_amount)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="c" style="padding: 14px; color: #9ca3af;">No sales records found for this period.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    
    <div class="sec-hdr">Sales Breakdown by Category</div>
    <table class="data">
        <colgroup>
            <col style="width: 32px;">
            <col style="width: 340px;">
            <col style="width: 120px;">
            <col style="width: 140px;">
            <col style="width: 110px;">
        </colgroup>
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="l">Category</th>
                <th class="r">Quantity Sold</th>
                <th class="r">Total Sales</th>
                <th class="r">Contribution %</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $analytics['category_breakdown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $pct = $analytics['total_sales'] > 0 ? round(($cat->total_amount / $analytics['total_sales']) * 100, 1) : 0;
                ?>
                <tr>
                    <td class="c"><?php echo e($idx + 1); ?></td>
                    <td class="l"><strong><?php echo e($cat->category_name); ?></strong></td>
                    <td class="r"><?php echo e(number_format($cat->total_quantity)); ?></td>
                    <td class="r" style="font-weight: bold;"><?php echo e(formatCurrency($cat->total_amount)); ?></td>
                    <td class="r"><?php echo e($pct); ?>%</td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="c" style="padding: 14px; color: #9ca3af;">No category sales records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Generated by <?php echo e($generalSetting?->site_title ?? $generalSetting?->company_name ?? 'GE-System'); ?> &bull; Confirmed Official Executive Brief
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/reports/pdf/dashboard-report-pdf.blade.php ENDPATH**/ ?>
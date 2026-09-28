<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Performance & Overall Quota Report</title>
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

        /* ── Summary block ── */
        .summary { width: 250px; margin-left: auto; margin-top: 18px; font-size: 10px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 4px 0; }
        .summary td:first-child { color: #6b7280; }
        .summary td:last-child { text-align: right; font-weight: bold; color: #111827; }

        /* ── Data table ── */
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.data col.c-rank        { width: 26px; }
        table.data col.c-staff       { width: 170px; }
        table.data col.c-sales       { width: 105px; }
        table.data col.c-share       { width: 85px; }
        table.data col.c-invoices    { width: 65px; }
        table.data col.c-commission  { width: 105px; }
        table.data col.c-status      { width: 95px; }

        table.data th {
            font-size: 7.5px; font-weight: bold; color: #6b7280;
            text-transform: uppercase; letter-spacing: 0.3px;
            padding: 6px 5px; border-bottom: 1.5px solid #374151;
            white-space: nowrap; overflow: hidden;
        }
        table.data td {
            font-size: 8.5px; padding: 6px 5px;
            border-bottom: 1px solid #f3f4f6;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        table.data tbody tr:last-child td { border-bottom: none; }

        .l  { text-align: left; }
        .r  { text-align: right; }
        .c  { text-align: center; }
    </style>
</head>
<body>

    
    <table class="hdr">
        <tr>
            <td class="left">
                <div class="title">Staff Performance & Overall Quota Report</div>
                <div class="meta">
                    Generated: <?php echo e(now()->format('M d, Y h:i A')); ?>

                    &bull; Period: <?php echo e($startDate->format('M d, Y')); ?> &ndash; <?php echo e($endDate->format('M d, Y')); ?>

                    <?php if($overallQuota?->name): ?>
                        &bull; Quota: <?php echo e($overallQuota->name); ?>

                    <?php endif; ?>
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

    
    <table class="data">
        <colgroup>
            <col class="c-rank">
            <col class="c-staff">
            <col class="c-sales">
            <col class="c-share">
            <col class="c-invoices">
            <col class="c-commission">
            <col class="c-status">
        </colgroup>
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="l">Salesperson</th>
                <th class="r">Total Contributed</th>
                <th class="c">% Share of Sales</th>
                <th class="c">Invoices</th>
                <th class="r">Commission</th>
                <th class="c">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $perf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="c"><?php echo e($idx + 1); ?></td>
                    <td class="l">
                        <strong><?php echo e($perf->user->name); ?></strong>
                        <div style="font-size: 7.5px; color: #9ca3af;"><?php echo e($perf->user->email ?? 'N/A'); ?></div>
                    </td>
                    <td class="r" style="font-weight: bold;">
                        <?php echo e(formatCurrency($perf->actual_sales)); ?>

                    </td>
                    <td class="c">
                        <?php echo e($perf->contribution_pct); ?>%
                    </td>
                    <td class="c">
                        <?php echo e($perf->invoices_count); ?>

                    </td>
                    <td class="r" style="font-weight: bold;">
                        <?php echo e(formatCurrency($perf->commission_earned)); ?>

                    </td>
                    <td class="c">
                        <?php if($isAchieved): ?>
                            <span style="font-weight: bold;">Awarded</span>
                        <?php else: ?>
                            <span>Pending</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="c" style="padding: 16px; color: #9ca3af;">No staff members found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    
    <div class="summary">
        <table>
            <tr>
                <td>Overall Target</td>
                <td><?php echo e(formatCurrency($targetAmount)); ?></td>
            </tr>
            <tr>
                <td>Team Total Sales</td>
                <td><?php echo e(formatCurrency($totalSales)); ?></td>
            </tr>
            <tr>
                <td>Quota Progress</td>
                <td><?php echo e($progress); ?>% <?php echo e($isAchieved ? '✓' : ''); ?></td>
            </tr>
            <tr>
                <td>Commission Status</td>
                <td><?php echo e($isAchieved ? 'Awarded' : 'Pending Quota'); ?></td>
            </tr>
            <tr>
                <td>Total Commission Pool</td>
                <td><?php echo e(formatCurrency($performance['totalCommissionPool'])); ?></td>
            </tr>
        </table>
    </div>

</body>
</html>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/staff/performance/pdf.blade.php ENDPATH**/ ?>
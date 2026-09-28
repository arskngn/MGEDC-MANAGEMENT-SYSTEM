<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchasing Report</title>
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
        .summary { width: 230px; margin-left: auto; margin-top: 18px; font-size: 10px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 4px 0; }
        .summary td:first-child { color: #6b7280; }
        .summary td:last-child { text-align: right; font-weight: bold; color: #111827; }

        /* ── Data table ── */
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.data col.c-no      { width: 22px; }
        table.data col.c-invoice { width: 82px; }
        table.data col.c-date    { width: 68px; }
        table.data col.c-supp    { width: 110px; }
        table.data col.c-ostatus { width: 62px; }
        table.data col.c-payable { width: 82px; }
        table.data col.c-paid    { width: 82px; }
        table.data col.c-due     { width: 74px; }
        table.data col.c-pstatus { width: 50px; }

        table.data th {
            font-size: 7.5px; font-weight: bold; color: #6b7280;
            text-transform: uppercase; letter-spacing: 0.3px;
            padding: 5px 5px 5px; border-bottom: 1.5px solid #374151;
            white-space: nowrap; overflow: hidden;
        }
        table.data td {
            font-size: 9px; padding: 5px 5px;
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
                <div class="title">Purchasing Report</div>
                <div class="meta">
                    Generated: <?php echo e(now()->format('M d, Y h:i A')); ?>

                    <?php
                        $filterParts = array_filter([
                            ($filters['date_from'] || $filters['date_to']) ? ($filters['date_from'] ?: 'Start') . ' – ' . ($filters['date_to'] ?: 'Today') : null,
                            !empty($filters['status']) ? ucfirst($filters['status']) : null,
                            !empty($filters['payment_status']) ? ucfirst($filters['payment_status']) : null,
                            !empty($filters['supplier']) ? $filters['supplier'] : null,
                        ]);
                    ?>
                    <?php if(!empty($filterParts)): ?>
                        &bull; Filters: <?php echo e(implode(' | ', $filterParts)); ?>

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
            <col class="c-no">
            <col class="c-invoice">
            <col class="c-date">
            <col class="c-supp">
            <col class="c-ostatus">
            <col class="c-payable">
            <col class="c-paid">
            <col class="c-due">
            <col class="c-pstatus">
        </colgroup>
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="l">Invoice No</th>
                <th class="l">Date</th>
                <th class="l">Supplier</th>
                <th class="c">Order Status</th>
                <th class="r">Payable</th>
                <th class="r">Paid</th>
                <th class="r">Due</th>
                <th class="c">Payment</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $purchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $due = max(0, (float) $p->payable_amount - (float) $p->paid_amount); ?>
                <tr>
                    <td class="c"><?php echo e($idx + 1); ?></td>
                    <td class="l"><strong><?php echo e($p->invoice_no); ?></strong></td>
                    <td class="l"><?php echo e($p->purchase_date?->format('Y-m-d')); ?></td>
                    <td class="l"><?php echo e($p->supplier?->name ?? 'N/A'); ?></td>
                    <td class="c"><?php echo e(ucfirst($p->status ?? 'Ordered')); ?></td>
                    <td class="r"><?php echo e(formatCurrency($p->payable_amount)); ?></td>
                    <td class="r"><?php echo e(formatCurrency($p->paid_amount)); ?></td>
                    <td class="r"><?php echo e(formatCurrency($due)); ?></td>
                    <td class="c">
                        <?php if($due <= 0): ?>
                            <span style="font-weight:bold;">Paid</span>
                        <?php elseif($p->paid_amount > 0): ?>
                            <span style="font-weight:bold;">Partial</span>
                        <?php else: ?>
                            <span style="font-weight:bold;">Due</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" class="c" style="padding:16px;color:#9ca3af;">No records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Orders</td>
                <td><?php echo e(number_format($kpis['total_count'])); ?></td>
            </tr>
            <tr>
                <td>Payable</td>
                <td><?php echo e(formatCurrency($kpis['total_amount'])); ?></td>
            </tr>
            <tr>
                <td>Paid</td>
                <td><?php echo e(formatCurrency($kpis['total_paid'])); ?></td>
            </tr>
            <tr>
                <td>Due</td>
                <td><?php echo e(formatCurrency($kpis['total_due'])); ?></td>
            </tr>
        </table>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/reports/pdf/purchases-report-pdf.blade.php ENDPATH**/ ?>
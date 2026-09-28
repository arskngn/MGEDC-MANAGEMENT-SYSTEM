<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Payments Report</title>
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
        table.data col.c-no      { width: 24px; }
        table.data col.c-invoice { width: 80px; }
        table.data col.c-date    { width: 70px; }
        table.data col.c-party   { width: 130px; }
        table.data col.c-trx     { width: 80px; }
        table.data col.c-method  { width: 55px; }
        table.data col.c-check   { width: 100px; }
        table.data col.c-reason  { width: 110px; }
        table.data col.c-amount  { width: 85px; }

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
    </style>
</head>
<body>

    
    <table class="hdr">
        <tr>
            <td class="left">
                <div class="title">Supplier Payments Report</div>
                <div class="meta">
                    Generated: <?php echo e(now()->format('M d, Y h:i A')); ?>

                    <?php
                        $filterParts = array_filter([
                            ($startDate || $endDate) ? ($startDate ?: 'Start') . ' – ' . ($endDate ?: 'Today') : null,
                            (!empty($filter) && $filter !== 'all') ? ucwords(str_replace('_', ' ', $filter)) : null,
                            (!empty($paymentMethod) && $paymentMethod !== 'all') ? ucfirst($paymentMethod) : null,
                            !empty($search) ? '"' . $search . '"' : null,
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

    <?php
        $totalCount = $kpis['total_count'] ?? count($payments);
        $totalPaid = $kpis['total_paid'] ?? (float) $payments->where('type', 'payment')->sum('amount');
        $totalRefunded = $kpis['total_refunded'] ?? (float) $payments->where('type', 'receipt')->sum('amount');
        $netTotal = $kpis['net_total'] ?? ($totalPaid - $totalRefunded);
    ?>

    
    <table class="data">
        <colgroup>
            <col class="c-no">
            <col class="c-invoice">
            <col class="c-date">
            <col class="c-party">
            <col class="c-trx">
            <col class="c-method">
            <col class="c-check">
            <col class="c-reason">
            <col class="c-amount">
        </colgroup>
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="l">Invoice / PO</th>
                <th class="l">Date</th>
                <th class="l">Supplier</th>
                <th class="l">TRX ID</th>
                <th class="c">Method</th>
                <th class="l">Check Details</th>
                <th class="l">Type</th>
                <th class="r">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $isPayment = ($payment['type'] ?? '') === 'payment';
                    $dateStr = $payment['date'] instanceof \DateTimeInterface
                        ? $payment['date']->format('Y-m-d')
                        : ($payment['date'] ?? '—');
                    $supplierName = is_object($payment['supplier'])
                        ? ($payment['supplier']->name ?? 'N/A')
                        : ($payment['supplier'] ?? 'N/A');

                    $checkInfo = '—';
                    if (!empty($payment['check_number']) || !empty($payment['check_date'])) {
                        $cDate = null;
                        if (!empty($payment['check_date'])) {
                            $cDate = $payment['check_date'] instanceof \DateTimeInterface
                                ? $payment['check_date']->format('Y-m-d')
                                : $payment['check_date'];
                        }
                        $parts = array_filter([$payment['check_number'] ?? null, $cDate]);
                        $checkInfo = implode(' • ', $parts);
                    }
                ?>
                <tr>
                    <td class="c"><?php echo e($idx + 1); ?></td>
                    <td class="l"><strong><?php echo e($payment['invoice_no'] ?? 'N/A'); ?></strong></td>
                    <td class="l"><?php echo e($dateStr); ?></td>
                    <td class="l"><?php echo e($supplierName); ?></td>
                    <td class="l" style="font-family: monospace; font-size: 8px;"><?php echo e($payment['trx'] ?? '—'); ?></td>
                    <td class="c"><?php echo e(isset($payment['payment_method']) ? ucfirst($payment['payment_method']) : '—'); ?></td>
                    <td class="l"><?php echo e($checkInfo); ?></td>
                    <td class="l">
                        <span style="font-weight: bold; color: #111827;">
                            <?php echo e($isPayment ? 'Paid' : 'Refunded'); ?>

                        </span>
                    </td>
                    <td class="r" style="font-weight: bold;">
                        <?php echo e(formatCurrency($payment['amount'])); ?>

                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" class="c" style="padding: 16px; color: #9ca3af;">No supplier payments found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Transactions</td>
                <td><?php echo e(number_format($totalCount)); ?></td>
            </tr>
            <tr>
                <td>Total Paid</td>
                <td><?php echo e(formatCurrency($totalPaid)); ?></td>
            </tr>
            <tr>
                <td>Total Refunded</td>
                <td><?php echo e(formatCurrency($totalRefunded)); ?></td>
            </tr>
            <tr>
                <td>Net Paid Out</td>
                <td><?php echo e(formatCurrency($netTotal)); ?></td>
            </tr>
        </table>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/supplier-payments/pdf.blade.php ENDPATH**/ ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #000; margin: 16px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 11px; page-break-inside: auto; }
        th, td { border: 1px solid #000; padding: 4px 5px; text-align: left; }
        thead th { font-weight: 700; }
        thead { display: table-header-group; }
        tbody tr { page-break-inside: avoid; page-break-after: auto; }
        .section { page-break-after: always; page-break-inside: avoid; margin-bottom: 12px; }
        .section:last-child { page-break-after: auto; }
        .summary { page-break-inside: avoid; margin-top: 8px; text-align: right; }
    </style>
</head>
<body>
    <?php
        $titleMonth = '';
        if ($start && $end) {
            $startDate = \Carbon\Carbon::parse($start);
            $endDate = \Carbon\Carbon::parse($end);
            if ($startDate->format('Y-m') === $endDate->format('Y-m')) {
                $titleMonth = $startDate->format('M Y');
            } else {
                $titleMonth = $startDate->format('M Y') . ' - ' . $endDate->format('M Y');
            }
        }
    ?>

    <h2 style="margin:0 0 8px 0">
        <?php echo e($chart === 'chart1' ? 'Sales & Sales Return Report' : ($chart === 'chart2' ? 'Purchases & Purchase Return Report' : 'Payments Report')); ?>

    </h2>

    <?php
        $labels = $data['labels'] ?? [];
        // group labels by month key
        $groups = [];
        foreach ($labels as $i => $lbl) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $lbl)) {
                $key = substr($lbl, 0, 7);
            } else {
                $ts = strtotime($lbl);
                if ($ts !== false) {
                    $key = date('Y-m', $ts);
                } else {
                    $key = 'all';
                }
            }
            $groups[$key][] = $i;
        }
        $multiMonth = count($groups) > 1;
    ?>

    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupKey => $indexes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="section">
            <?php
                $isMonthKey = preg_match('/^\d{4}-\d{2}$/', $groupKey);
                if ($isMonthKey) {
                    [$gy, $gm] = explode('-', $groupKey);
                    $monthStart = \Carbon\Carbon::create((int)$gy, (int)$gm, 1)->startOfDay();
                    $sectionTitle = $monthStart->format('M Y');
                    $sectionRange = $start && $end ? \Carbon\Carbon::parse($start)->format('Y-m-d') . ' to ' . \Carbon\Carbon::parse($end)->format('Y-m-d') : 'All Time';
                } else {
                    $sectionTitle = 'Overview';
                    $sectionRange = 'All Time';
                }
            ?>

            <div style="margin-bottom: 6px; font-size: 12px; font-weight:700;">
                <?php echo e($chart === 'chart1' ? 'Sales & Sales Return Report - ' : ($chart === 'chart2' ? 'Purchases & Purchase Return Report - ' : 'Payments Report - ')); ?><?php echo e($sectionTitle); ?>

            </div>
            <div style="margin-bottom: 8px; font-size: 11px;">
                Period: <?php echo e($sectionRange); ?>

            </div>

            <table>
                <thead>
                    <?php if($chart === 'chart3'): ?>
                        <tr>
                            <th>Date</th>
                            <th>Invoice No.</th>
                            <th>Payment Sent</th>
                            <th>Invoice No. (Received)</th>
                            <th>Payment Received</th>
                        </tr>
                    <?php elseif($chart === 'chart1'): ?>
                        <tr>
                            <th>Date</th>
                            <th>Invoice (Sales)</th>
                            <th>Sales</th>
                            <th>Invoice (Sales Return)</th>
                            <th>Sales Returns</th>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <th>Date</th>
                            <th>Invoice (Purchase)</th>
                            <th>Purchases</th>
                            <th>Invoice (Purchase Return)</th>
                            <th>Purchase Returns</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                        <?php $__currentLoopData = $indexes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $label = $data['labels'][$i] ?? '';
                                $mainRef = $data['invoice_main'][$i] ?? ($data['invoice'][$i] ?? ($data['invoices'][$i] ?? ''));
                                $returnRef = $data['invoice_return'][$i] ?? ($data['return_invoice'][$i] ?? ($data['return_invoices'][$i] ?? ''));
                            ?>
                            <tr>
                                <td><?php echo e($label); ?></td>
                                <?php if($chart === 'chart3'): ?>
                                    <td><?php echo e($mainRef); ?></td>
                                    <td><?php echo e(number_format($data['payments_sent'][$i] ?? 0, 2)); ?></td>
                                    <td><?php echo e($returnRef); ?></td>
                                    <td><?php echo e(number_format($data['payments_received'][$i] ?? 0, 2)); ?></td>
                                <?php elseif($chart === 'chart1'): ?>
                                    <td><?php echo e($mainRef); ?></td>
                                    <td><?php echo e(number_format($data['sales'][$i] ?? 0, 2)); ?></td>
                                    <td><?php echo e($returnRef); ?></td>
                                    <td><?php echo e(number_format($data['sales_returns'][$i] ?? 0, 2)); ?></td>
                                <?php else: ?>
                                    <td><?php echo e($mainRef); ?></td>
                                    <td><?php echo e(number_format($data['purchases'][$i] ?? 0, 2)); ?></td>
                                    <td><?php echo e($returnRef); ?></td>
                                    <td><?php echo e(number_format($data['purchases_returns'][$i] ?? 0, 2)); ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

            <?php if($isMonthKey): ?>
                <?php
                    $sectionSales = 0; $sectionSalesReturns = 0;
                    $sectionPurchases = 0; $sectionPurchasesReturns = 0;
                    $sectionPaymentsSent = 0; $sectionPaymentsReceived = 0;
                    foreach ($indexes as $i) {
                        $sectionSales += $data['sales'][$i] ?? 0;
                        $sectionSalesReturns += $data['sales_returns'][$i] ?? 0;
                        $sectionPurchases += $data['purchases'][$i] ?? 0;
                        $sectionPurchasesReturns += $data['purchases_returns'][$i] ?? 0;
                        $sectionPaymentsSent += $data['payments_sent'][$i] ?? 0;
                        $sectionPaymentsReceived += $data['payments_received'][$i] ?? 0;
                    }
                ?>
                <div class="summary">
                    <?php if($chart === 'chart1'): ?>
                        <div style="font-weight:700"><?php echo e($monthStart->format('M Y')); ?> Totals:</div>
                        <div><strong>Sales:</strong> <?php echo e(number_format($sectionSales, 2)); ?></div>
                        <div><strong>Sales Returns:</strong> <?php echo e(number_format($sectionSalesReturns, 2)); ?></div>
                    <?php elseif($chart === 'chart2'): ?>
                        <div style="font-weight:700"><?php echo e($monthStart->format('M Y')); ?> Totals:</div>
                        <div><strong>Purchases:</strong> <?php echo e(number_format($sectionPurchases, 2)); ?></div>
                        <div><strong>Purchase Returns:</strong> <?php echo e(number_format($sectionPurchasesReturns, 2)); ?></div>
                    <?php else: ?>
                        <div style="font-weight:700"><?php echo e($monthStart->format('M Y')); ?> Totals:</div>
                        <div><strong>Payment Sent:</strong> <?php echo e(number_format($sectionPaymentsSent, 2)); ?></div>
                        <div><strong>Payment Received:</strong> <?php echo e(number_format($sectionPaymentsReceived, 2)); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if(count($groups) > 1): ?>
        <div class="summary" style="page-break-inside: avoid;">
            <?php if($chart === 'chart1'): ?>
                <div><strong>Grand Total Sales:</strong> <?php echo e(number_format(array_sum($data['sales'] ?? []), 2)); ?></div>
                <div><strong>Grand Total Sales Returns:</strong> <?php echo e(number_format(array_sum($data['sales_returns'] ?? []), 2)); ?></div>
            <?php elseif($chart === 'chart2'): ?>
                <div><strong>Grand Total Purchases:</strong> <?php echo e(number_format(array_sum($data['purchases'] ?? []), 2)); ?></div>
                <div><strong>Grand Total Purchase Returns:</strong> <?php echo e(number_format(array_sum($data['purchases_returns'] ?? []), 2)); ?></div>
            <?php else: ?>
                <div><strong>Grand Total Payment Sent:</strong> <?php echo e(number_format(array_sum($data['payments_sent'] ?? []), 2)); ?></div>
                <div><strong>Grand Total Payment Received:</strong> <?php echo e(number_format(array_sum($data['payments_received'] ?? []), 2)); ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</body>
</html><?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/reports/dashboard-report-pdf.blade.php ENDPATH**/ ?>
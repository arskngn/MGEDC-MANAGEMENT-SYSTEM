<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Top Selling Products Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #000; margin: 16px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 11px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        thead th { font-weight: 700; }
        .summary { margin-top: 12px; text-align: right; font-weight: 700; }
        .title { margin-bottom: 8px; }
        .month-section { page-break-after: always; margin-bottom: 16px; }
        .month-section:last-child { page-break-after: auto; }
    </style>
</head>
<body>
    <?php
        $title = 'Top Selling Products Report';
        if ($selectedProduct) {
            $title = 'Top Selling Product Breakdown';
        }
    ?>

    <h2 class="title"><?php echo e($title); ?></h2>

    <?php if($start && $end): ?>
        <div style="margin-bottom: 8px;">Period: <?php echo e($start); ?> to <?php echo e($end); ?></div>
    <?php endif; ?>

    <?php if($selectedProduct): ?>
        <div style="margin-bottom: 8px; font-weight:700;">Product: <?php echo e($selectedProduct->product_name ?: 'N/A'); ?> (SKU: <?php echo e($selectedProduct->sku ?: 'N/A'); ?>)</div>
        <?php
            $labels = $breakdown['labels'] ?? [];
            $quantities = $breakdown['quantities'] ?? [];
            $amounts = $breakdown['amounts'] ?? [];
            $groupBy = $breakdown['group_by'] ?? 'date';
            $monthlyGroups = [];

            foreach ($labels as $index => $label) {
                if ($groupBy === 'month') {
                    $date = \Carbon\Carbon::createFromFormat('M Y', $label);
                } else {
                    $date = \Carbon\Carbon::createFromFormat('Y-m-d', $label);
                }
                $monthKey = $date->format('Y-m');
                $monthlyGroups[$monthKey][] = [
                    'date' => $label,
                    'qty' => $quantities[$index] ?? 0,
                    'amt' => $amounts[$index] ?? 0,
                ];
            }

            $grandTotalQty = 0;
            $grandTotalAmt = 0;
            $monthKeys = array_keys($monthlyGroups);
            $lastMonthKey = end($monthKeys);
        ?>

        <?php $__currentLoopData = $monthlyGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthKey => $rows): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $monthTotalQty = array_sum(array_column($rows, 'qty'));
                $monthTotalAmt = array_sum(array_column($rows, 'amt'));
                $grandTotalQty += $monthTotalQty;
                $grandTotalAmt += $monthTotalAmt;
                $monthName = \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('F Y');
                $isLastMonth = $monthKey === $lastMonthKey;
            ?>
            <div class="month-section">
                <div style="margin-bottom: 8px; font-weight:700;"><?php echo e($monthName); ?></div>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Price Sold</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($row['date']); ?></td>
                                <td><?php echo e(number_format($row['qty'] > 0 ? $row['amt'] / $row['qty'] : 0, 2)); ?></td>
                                <td><?php echo e(number_format($row['qty'], 2)); ?></td>
                                <td><?php echo e(number_format($row['amt'], 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <div class="summary">
                    Month Total Qty: <?php echo e(number_format($monthTotalQty, 2)); ?><br>
                    Month Total Amount: <?php echo e(number_format($monthTotalAmt, 2)); ?>

                </div>

                <?php if($isLastMonth): ?>
                    <div class="summary" style="margin-top: 16px;">
                        Grand Total Qty: <?php echo e(number_format($grandTotalQty, 2)); ?><br>
                        Grand Total Amount: <?php echo e(number_format($grandTotalAmt, 2)); ?>

                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Qty Sold</th>
                    <th>Total Amount</th>
                    <th>Price Sold</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $priceSold = $product->total_quantity > 0 ? $product->total_amount / $product->total_quantity : 0;
                    ?>
                    <tr>
                        <td><?php echo e($index + 1); ?></td>
                        <td><?php echo e($product->product_name ?: 'N/A'); ?></td>
                        <td><?php echo e($product->sku ?: 'N/A'); ?></td>
                        <td><?php echo e(number_format($product->total_quantity, 2)); ?></td>
                        <td><?php echo e(number_format($product->total_amount, 2)); ?></td>
                        <td><?php echo e(number_format($priceSold, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div class="summary">
            Total Qty: <?php echo e(number_format($products->sum('total_quantity'), 2)); ?><br>
            Total Amount: <?php echo e(number_format($products->sum('total_amount'), 2)); ?>

        </div>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/reports/dashboard-top-selling-pdf.blade.php ENDPATH**/ ?>
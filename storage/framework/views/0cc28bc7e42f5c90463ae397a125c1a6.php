<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Expenses</title>
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
        table.data { width: 100%; border-collapse: collapse; }
        table.data th {
            font-size: 7.5px; font-weight: bold; color: #6b7280;
            text-transform: uppercase; letter-spacing: 0.3px;
            padding: 5px 6px; border-bottom: 1.5px solid #374151;
            text-align: left; white-space: nowrap;
        }
        table.data td {
            font-size: 9px; padding: 5px 6px;
            border-bottom: 1px solid #f3f4f6;
        }
        table.data tbody tr:last-child td { border-bottom: none; }
        table.data th.r, table.data td.r { text-align: right; }
        table.data th.c, table.data td.c { text-align: center; }
    </style>
</head>
<body>

    
    <table class="hdr">
        <tr>
            <td class="left">
                <div class="title">All Expenses</div>
                <div class="meta">Generated: <?php echo e(now()->format('M d, Y h:i A')); ?></div>
            </td>
            <td class="right">
                <?php if(!empty($logoSrc)): ?>
                    <img src="<?php echo e($logoSrc); ?>" alt="Logo">
                <?php endif; ?>
                <div class="bname"><?php echo e($generalSetting->site_title ?? $generalSetting->whole_company_name ?? 'MGEDC'); ?></div>
            </td>
        </tr>
    </table>

    
    <table class="data">
        <thead>
            <tr>
                <th style="width:28px;" class="c">#</th>
                <th style="width:25%;">Type / Category</th>
                <th style="width:18%;">Date</th>
                <th style="width:38%;">Note</th>
                <th class="r" style="width:19%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $totalAmount = 0; ?>
            <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $totalAmount += (float) $expense->amount; ?>
                <tr>
                    <td class="c"><?php echo e($i + 1); ?></td>
                    <td><strong><?php echo e($expense->expenseType->name ?? '—'); ?></strong></td>
                    <td><?php echo e($expense->date->format('M d, Y')); ?></td>
                    <td><?php echo e(Illuminate\Support\Str::limit($expense->description ?? '—', 60)); ?></td>
                    <td class="r"><?php echo e(formatCurrency($expense->amount)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="c" style="padding:16px; color:#9ca3af;">No expenses found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Total Entries</td>
                <td><?php echo e(number_format($expenses->count())); ?></td>
            </tr>
            <tr>
                <td>Total Amount</td>
                <td><?php echo e(formatCurrency($totalAmount)); ?></td>
            </tr>
        </table>
    </div>

</body>
</html>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/expenses/pdf/all.blade.php ENDPATH**/ ?>
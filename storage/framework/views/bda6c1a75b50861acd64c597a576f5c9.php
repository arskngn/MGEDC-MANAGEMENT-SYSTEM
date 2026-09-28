<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loading List / Setlist</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 40px;
            line-height: 1.4;
        }

        /* ── TOP HEADER ── */
        .top-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .top-header-table td {
            vertical-align: top;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .setlist-note {
            font-size: 10px;
            color: #333;
            margin-top: 6px;
            line-height: 1.45;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
            color: #333;
        }

        /* ── DETAILS TABLE ── */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            padding: 8px 0;
            margin-bottom: 30px;
        }
        .details-table td {
            padding: 4px 0;
            font-size: 10px;
        }
        .lbl { font-weight: bold; color: #555; width: 12%; }
        .val { width: 38%; }

        /* ── ITEMS TABLE ── */
        table.items {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        table.items th {
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        table.items td {
            padding: 8px 4px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .code-col  { width: 15%; }
        .desc-col  { width: 40%; }
        .unit-col  { width: 10%; text-align: center; }
        .qty-col   { width: 11%; text-align: right; font-weight: 500; }
        .total-col { width: 13%; text-align: right; font-weight: bold; }

        /* ── FOOTER SIGNATURES ── */
        .footer-section {
            margin-top: 50px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 33%;
            padding: 20px 10px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <!-- TOP HEADER -->
    <table class="top-header-table">
        <tr>
            <td>
                <div class="company-name"><?php echo e($generalSetting->company_name ?? 'MGEDC'); ?></div>
                <?php if(!empty($note)): ?>
                    <div class="setlist-note"><?php echo e($note); ?></div>
                <?php endif; ?>
                <div style="font-size: 10px; color: #666; margin-top: 2px;">
                    <?php echo e($generalSetting->company_address ?? ''); ?>

                </div>
            </td>
            <td>
                <div class="document-title">Loading List / Setlist</div>
            </td>
        </tr>
    </table>

    <!-- META DETAILS -->
    <table class="details-table">
        <tr>
            <td class="lbl">Date Generated:</td>
            <td class="val"><?php echo e(date('F d, Y h:i A')); ?></td>
            <td class="lbl">Total Sales:</td>
            <td class="val"><?php echo e($salesCount); ?> sales selected</td>
        </tr>
        <tr>
            <td class="lbl">Invoice Nos:</td>
            <td class="val" colspan="3" style="word-break: break-all;">
                <?php echo e($invoiceNos); ?>

            </td>
        </tr>
    </table>

    <!-- ITEMS TABLE -->
    <table class="items">
        <thead>
            <tr>
                <th class="code-col">ITEM CODE</th>
                <th class="desc-col">DESCRIPTION</th>
                <th class="unit-col" style="text-align: center;">UNIT</th>
                <th class="qty-col" style="text-align: right;">PAID QTY</th>
                <th class="qty-col" style="text-align: right;">FREE QTY</th>
                <th class="total-col" style="text-align: right;">TOTAL TO LOAD</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="code-col" style="font-family: monospace;"><?php echo e($item['sku']); ?></td>
                    <td class="desc-col" style="font-weight: bold; color: #333;"><?php echo e($item['product_name']); ?></td>
                    <td class="unit-col" style="text-align: center; text-transform: uppercase; font-size: 10px; font-weight: bold; color: #666;"><?php echo e($item['unit_label']); ?></td>
                    <td class="qty-col" style="text-align: right;"><?php echo e(number_format($item['paid_qty'], 2)); ?></td>
                    <td class="qty-col" style="text-align: right; color: #16a34a; font-weight: bold;">
                        <?php if($item['free_qty'] > 0): ?>
                            +<?php echo e(number_format($item['free_qty'], 2)); ?>

                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td class="total-col" style="text-align: right; font-size: 12px; color: #111;"><?php echo e(number_format($item['total_qty'], 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <!-- FOOTER SIGNATURES -->
    <div class="footer-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line">Prepared By</div>
                </td>
                <td>
                    <div class="signature-line">Checked By</div>
                </td>
                <td>
                    <div class="signature-line">Received By (Driver/Courier)</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/sales/pdf/loading-list.blade.php ENDPATH**/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Purchase Return</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .header-table { width: 100%; border-collapse: collapse; }
        .title { font-size: 18px; font-weight: bold; color: #0a1233; font-family: DejaVu Serif, serif; }
        .brand { text-align: right; vertical-align: top; }
        .brand img { max-height: 40px; max-width: 140px; }
        .brand-name { font-size: 14px; font-weight: bold; color: #111; margin-top: 4px; }
        .company { font-size: 11px; color: #374151; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th { background: transparent; color: #111; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; font-family: DejaVu Serif, serif; border-bottom: 1px solid #e5e7eb; }
        table.data td { padding: 6px; border-bottom: 1px solid #e5e7eb; font-family: DejaVu Serif, serif; }
        table.data tr:nth-child(even) td { background: #fbfcfd; }
        .summary { width: 360px; margin-left: auto; margin-top: 12px; font-size: 11px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 6px 0; }
        .summary td:first-child { color: #6b7280; }
        .summary td:last-child { text-align: right; font-weight: bold; }
        .num { text-align: right; }
    </style>
</head>
<body>
    @php
        // Pre-load purchaseReturns (ordered by id asc) per purchase so we can
        // replicate the same progressive offset ledger used in the index view.
        $returns->load(['purchase.purchaseReturns' => function ($q) {
            $q->orderBy('id', 'asc');
        }]);

        // Compute effective due per return using the same ledger-offset logic as the index
        $effectiveDues = [];
        foreach ($returns as $r) {
            $remainingUnpaid = max(0, (float)$r->purchase->payable_amount - (float)$r->purchase->paid_amount);
            $effectiveDue = 0;

            foreach ($r->purchase->purchaseReturns as $pr) {
                $receivable = (float)$pr->receivable_amount;
                $offset     = min($receivable, $remainingUnpaid);
                $cashOwed   = $receivable - $offset;
                $remainingUnpaid -= $offset;

                if ($pr->id === $r->id) {
                    $effectiveDue = max(0, $cashOwed - (float)$pr->received_amount);
                    break;
                }
            }

            $effectiveDues[$r->id] = $effectiveDue;
        }

        $totalEffectiveDue = array_sum($effectiveDues);
    @endphp

    <table class="header-table">
        <tr>
            <td class="title">All Purchase Return</td>
                <td class="brand">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo">
                @endif
                <div class="brand-name">{{ $generalSetting->site_title ?? 'MGEDC' }}</div>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:28px;">S.N.</th>
                <th>Invoice</th>
                <th>Date</th>
                <th>Supplier</th>
                <th class="num">Receivable</th>
                <th class="num">Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returns as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->return_invoice_no }}</td>
                    <td>{{ $r->return_date->format('m/d/Y') }}</td>
                    <td>{{ $r->purchase->supplier->name }}</td>
                    <td class="num">{{ formatCurrency($r->receivable_amount) }}</td>
                    <td class="num">{{ formatCurrency($effectiveDues[$r->id]) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Records</td>
                <td>{{ $returns->count() }}</td>
            </tr>
            <tr>
                <td>Subtotal</td>
                <td>{{ formatCurrency($returns->sum('subtotal')) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td>{{ formatCurrency($returns->sum('discount')) }}</td>
            </tr>
            <tr>
                <td>Receivable</td>
                <td>{{ formatCurrency($returns->sum('receivable_amount')) }}</td>
            </tr>
            <tr>
                <td>Due</td>
                <td>{{ formatCurrency($totalEffectiveDue) }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin-top: 18px; font-size: 9px; color: #9ca3af;">{{ $generalSetting->whole_company_name ?? $generalSetting->site_title ?? 'MGEDC' }}</div>
</body>
</html>

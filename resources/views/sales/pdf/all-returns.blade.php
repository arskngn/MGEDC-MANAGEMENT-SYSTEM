<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Sales Return</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .header { width: 100%; margin-bottom: 16px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .title { font-size: 18px; font-weight: bold; color: #0a1233; }
        .brand { text-align: right; vertical-align: top; }
        .brand img { max-height: 40px; max-width: 140px; }
        .brand-name { font-size: 14px; font-weight: bold; color: #111; margin-top: 4px; }
        .company { font-size: 11px; color: #374151; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th { background: transparent; color: #111; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
        table.data td { padding: 6px; border-bottom: 1px solid #e5e7eb; }
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
    <table class="header-table">
        <tr>
            <td class="title">All Sales Return</td>
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
                <th>Return Invoice</th>
                <th>Date</th>
                <th>Customer</th>
                <th class="num">Payable</th>
                <th class="num">Due</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalEffectiveDue = 0;
            @endphp
            @foreach($salesReturns as $i => $sr)
                @php
                    $remainingUnpaid = max(0, (float)$sr->sale->receivable_amount - (float)$sr->sale->paid_amount);
                    $effectiveDue = 0;

                    foreach ($sr->sale->saleReturns as $otherSr) {
                        $payable = (float)$otherSr->payable_amount;
                        $offset = min($payable, $remainingUnpaid);
                        $cashOwed = $payable - $offset;
                        $remainingUnpaid -= $offset;

                        if ($otherSr->id === $sr->id) {
                            $effectiveDue = max(0, $cashOwed - (float)$sr->paid_amount);
                            break;
                        }
                    }
                    $totalEffectiveDue += $effectiveDue;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $sr->return_invoice_no }}</td>
                    <td>{{ $sr->return_date ? $sr->return_date->format('m/d/Y') : '' }}</td>
                    <td>{{ $sr->sale->customer->name ?? 'N/A' }}</td>
                    <td class="num">{{ formatCurrency($sr->payable_amount) }}</td>
                    <td class="num">{{ formatCurrency($effectiveDue) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Records</td>
                <td>{{ $salesReturns->count() }}</td>
            </tr>
            <tr>
                <td>Subtotal</td>
                <td>{{ formatCurrency($salesReturns->sum('subtotal')) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td>{{ formatCurrency($salesReturns->sum('discount')) }}</td>
            </tr>
            <tr>
                <td>Payable</td>
                <td>{{ formatCurrency($salesReturns->sum('payable_amount')) }}</td>
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

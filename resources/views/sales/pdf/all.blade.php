<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Sales</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; margin: 20px; }
        .header { width: 100%; margin-bottom: 16px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .title { font-size: 18px; font-weight: bold; color: #0a1233; }
        .brand { text-align: right; vertical-align: top; }
        .brand img { max-height: 40px; max-width: 140px; }
        .brand-name { font-size: 14px; font-weight: bold; color: #0a1233; margin-top: 4px; }
        .company { font-size: 10px; color: #374151; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.data th { background: transparent; color: #0a1233; padding: 7px 5px; text-align: left; font-size: 9px; text-transform: uppercase; font-weight: 700; border-bottom: 2px solid #e5e7eb; }
        table.data td { padding: 6px 5px; border-bottom: 1px solid #e5e7eb; font-size: 9.5px; }
        table.data tr:nth-child(even) td { background: #fbfcfd; }
        table.data th.num, table.data td.num { text-align: right; }
        .summary { width: 220px; margin-left: auto; margin-top: 24px; font-size: 10px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 4px 0; }
        .summary td:first-child { color: #6b7280; }
        .summary td:last-child { text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="title">All Sales</td>
            <td class="brand">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo">
                @endif
                <div class="brand-name">{{ $generalSetting->site_title ?? 'MGEDC' }}</div>
                <div class="company">{{ $generalSetting->whole_company_name ?? $generalSetting->site_title ?? 'Mindoro Golden Eagle Distribution Corporation' }}</div>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:24px;">S.N.</th>
                <th>INVOICE</th>
                <th>DATE</th>
                <th>CUSTOMER</th>
                <th>SALESPERSON</th>
                <th class="num">SUBTOTAL</th>
                <th class="num">DISCOUNT</th>
                <th class="num">RETURN</th>
                <th class="num">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalGrossSubtotal = 0;
                $totalOverallDiscount = 0;
                $totalReturns = 0;
                $totalNetReceivable = 0;
            @endphp
            @foreach($sales as $i => $s)
                @php
                    $lineGrossSubtotal = $s->items->sum(fn($item) => $item->is_free ? 0 : ((float)$item->quantity * (float)$item->unit_price));
                    $lineItemDiscounts = $s->items->sum(fn($item) => $item->is_free ? 0 : max(0, ((float)$item->quantity * (float)$item->unit_price) - (float)$item->line_total));
                    $invDiscount = $lineItemDiscounts + (float)$s->discount;
                    $returnAmt = (float)$s->saleReturns->sum('payable_amount');
                    // Clamp to 0: returns cannot make a row's net go negative
                    $netTotal = max(0, (float)$s->receivable_amount - $returnAmt);

                    $totalGrossSubtotal += $lineGrossSubtotal;
                    $totalOverallDiscount += $invDiscount;
                    $totalReturns += $returnAmt;
                    $totalNetReceivable += $netTotal;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $s->invoice_no }}</td>
                    <td>{{ $s->sale_date ? $s->sale_date->format('m/d/Y') : '' }}</td>
                    <td>{{ $s->customer->name ?? '' }}</td>
                    <td>{{ $s->salesperson->name ?? $s->user->name ?? '' }}</td>
                    <td class="num">{{ formatCurrency($lineGrossSubtotal) }}</td>
                    <td class="num">{{ formatCurrency($invDiscount) }}</td>
                    <td class="num">{{ formatCurrency($returnAmt) }}</td>
                    <td class="num">{{ formatCurrency($netTotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Subtotal</td>
                <td>{{ formatCurrency($totalGrossSubtotal) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td>{{ formatCurrency($totalOverallDiscount) }}</td>
            </tr>
            <tr>
                <td>Return</td>
                <td>{{ formatCurrency($totalReturns) }}</td>
            </tr>
            <tr>
                <td>Total</td>
                <td>{{ formatCurrency($totalNetReceivable) }}</td>
            </tr>
        </table>
    </div>
    <div style="text-align: center; margin-top: 18px; font-size: 9px; color: #9ca3af;">{{ $generalSetting->whole_company_name ?? $generalSetting->site_title ?? 'MGEDC' }}</div>
</body>
</html>




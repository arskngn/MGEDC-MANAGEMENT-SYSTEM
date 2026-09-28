<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Sales Performance Report</title>
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

        /* ── Summary strip (Product details + KPIs in one clean bar) ── */
        .strip { width: 100%; border-collapse: collapse; margin-bottom: 14px;
                 border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; }
        .strip td { padding: 6px 10px; vertical-align: middle; }
        .strip .filters { font-size: 8px; color: #6b7280; width: 36%; }
        .strip .filters strong { color: #374151; }
        .strip .stat { text-align: center; border-left: 1px solid #e5e7eb; }
        .strip .stat .slbl { font-size: 7px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.3px; }
        .strip .stat .sval { font-size: 11px; font-weight: bold; color: #111827; margin-top: 1px; }

        /* ── Data table ── */
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.data col.c-no       { width: 32px; }
        table.data col.c-date     { width: 190px; }
        table.data col.c-qty      { width: 110px; }
        table.data col.c-price    { width: 130px; }
        table.data col.c-amount   { width: 150px; }
        table.data col.c-status   { width: 100px; }

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
        table.data tfoot tr td {
            font-size: 9px; font-weight: bold; padding: 6px 5px;
            border-top: 1.5px solid #374151; border-bottom: 1.5px solid #374151;
            color: #111827;
        }

        .l  { text-align: left; }
        .r  { text-align: right; }
        .c  { text-align: center; }

        .footer { margin-top: 16px; border-top: 1px solid #f3f4f6;
                  padding-top: 6px; font-size: 7.5px; color: #d1d5db; text-align: center; }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="hdr">
        <tr>
            <td class="left">
                <div class="title">Product Sales Performance Report</div>
                <div class="meta">Generated: {{ now()->format('M d, Y h:i A') }}</div>
            </td>
            <td class="right">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo">
                @endif
                <div class="bname">{{ $generalSetting?->site_title ?? $generalSetting?->company_name ?? 'GE-System' }}</div>
            </td>
        </tr>
    </table>

    @php
        $labels = $breakdown['labels'] ?? [];
        $quantities = $breakdown['quantities'] ?? [];
        $amounts = $breakdown['amounts'] ?? [];
        $peakDate = $breakdown['peak_date'] ?? null;
        $peakQty = (float)($breakdown['peak_quantity'] ?? 0);
        $peakAmt = (float)($breakdown['peak_amount'] ?? 0);
        $totalQty = (float)($breakdown['total_quantity'] ?? 0);
        $totalAmt = (float)($breakdown['total_amount'] ?? 0);
        $avgPrice = $totalQty > 0 ? ($totalAmt / $totalQty) : 0;
    @endphp

    {{-- Summary strip: product details + 4 clean stats --}}
    <table class="strip">
        <tr>
            <td class="filters">
                <strong>Product:</strong> {{ $product?->name ?? 'N/A' }}
                @if(!empty($product?->sku)) &bull; SKU: {{ $product->sku }} @endif
                @if(!empty($product?->category?->name)) &bull; {{ $product->category->name }} @endif
                <br><strong>Period:</strong> {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('M d, Y') : 'Start' }} &ndash; {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('M d, Y') : 'Today' }}
            </td>
            <td class="stat">
                <div class="slbl">Peak Sales Date</div>
                <div class="sval">{{ $peakDate ? \Carbon\Carbon::parse($peakDate)->format('M d, Y') : '—' }}</div>
            </td>
            <td class="stat">
                <div class="slbl">Peak Day Sales</div>
                <div class="sval">{{ formatCurrency($peakAmt) }}</div>
            </td>
            <td class="stat">
                <div class="slbl">Total Qty Sold</div>
                <div class="sval">{{ number_format($totalQty) }}</div>
            </td>
            <td class="stat">
                <div class="slbl">Total Revenue</div>
                <div class="sval">{{ formatCurrency($totalAmt) }}</div>
            </td>
        </tr>
    </table>

    {{-- Data table --}}
    <table class="data">
        <colgroup>
            <col class="c-no">
            <col class="c-date">
            <col class="c-qty">
            <col class="c-price">
            <col class="c-amount">
            <col class="c-status">
        </colgroup>
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="l">Date</th>
                <th class="r">Qty Sold</th>
                <th class="r">Unit Price</th>
                <th class="r">Sales Amount</th>
                <th class="c">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $count = 0; @endphp
            @foreach($labels as $i => $label)
                @php
                    $q = (float)($quantities[$i] ?? 0);
                    $a = (float)($amounts[$i] ?? 0);
                    if ($q == 0 && $a == 0) continue;
                    $count++;
                    $unitPrice = $q > 0 ? ($a / $q) : 0;
                    $isPeak = ($peakDate && $label === $peakDate);
                    $formattedLabel = $label;
                    try {
                        if (strlen($label) === 10 && str_contains($label, '-')) {
                            $formattedLabel = \Carbon\Carbon::parse($label)->format('M d, Y (D)');
                        }
                    } catch (\Exception $e) {}
                @endphp
                <tr>
                    <td class="c">{{ $count }}</td>
                    <td class="l">{{ $formattedLabel }}</td>
                    <td class="r">{{ number_format($q) }}</td>
                    <td class="r">{{ formatCurrency($unitPrice) }}</td>
                    <td class="r" style="font-weight: bold;">{{ formatCurrency($a) }}</td>
                    <td class="c">
                        @if($isPeak)
                            <span style="font-weight: bold; color: #111827;">Peak Day</span>
                        @else
                            <span style="color: #9ca3af;">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach

            @if($count === 0)
                <tr>
                    <td colspan="6" class="c" style="padding: 16px; color: #9ca3af;">
                        No sales recorded for this product within the selected period.
                    </td>
                </tr>
            @endif
        </tbody>
        @if($count > 0)
            <tfoot>
                <tr>
                    <td colspan="2" class="l">Total:</td>
                    <td class="r">{{ number_format($totalQty) }}</td>
                    <td class="r">{{ formatCurrency($avgPrice) }} (avg)</td>
                    <td class="r">{{ formatCurrency($totalAmt) }}</td>
                    <td class="c">—</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Generated by {{ $generalSetting?->site_title ?? $generalSetting?->company_name ?? 'GE-System' }} &bull; Confirmed Official Reporting Document
    </div>

</body>
</html>

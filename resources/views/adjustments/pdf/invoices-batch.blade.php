<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Adjustment Invoices</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
            color: #000;
            line-height: 1.4;
        }
        .page {
            margin: 40px;
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }

        /* ── TOP HEADER ── */
        .top-header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .top-header-table > tbody > tr > td {
            vertical-align: top;
        }
        .td-left  { width: 55%; }
        .td-right { width: 45%; }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #0a1233;
            margin-bottom: 3px;
        }
        .company-sub {
            font-size: 10px;
            color: #555;
        }
        .logo-img {
            max-height: 55px;
            max-width: 130px;
            margin-bottom: 6px;
        }

        .doc-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0a1233;
            text-align: right;
            margin-bottom: 8px;
        }

        .details-table {
            border-collapse: collapse;
            float: right;
        }
        .details-table td {
            font-size: 11px;
            padding: 2px 0;
            vertical-align: top;
        }
        .dt-lbl { text-align: left; padding-right: 20px; font-weight: bold; color: #333; }
        .dt-val { text-align: right; }

        /* ── ITEMS TABLE ── */
        .items-wrapper {
            min-height: 360px;
            margin-top: 30px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        table.items thead th {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.items tbody td {
            padding: 7px 4px;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
        }

        .sn-col    { width: 6%; text-align: center !important; }
        .code-col  { width: 14%; }
        .desc-col  { width: 38%; }
        .batch-col { width: 18%; }
        .type-col  { width: 12%; text-align: center !important; }
        .qty-col   { width: 12%; text-align: right !important; }

        .type-badge-add {
            font-weight: bold;
            color: #059669;
        }
        .type-badge-sub {
            font-weight: bold;
            color: #dc2626;
        }

        /* ── BOTTOM SECTION ── */
        .bottom-section {
            width: 100%;
            margin-top: 25px;
            border-top: 1px solid #000;
            padding-top: 15px;
        }
        .notes-box {
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        .notes-box strong {
            color: #000;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 50px;
        }
        .signatures-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 20px;
        }
        .sign-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    @foreach($adjustments as $adjustment)
    <div class="page">
        <!-- TOP HEADER -->
        <table class="top-header-table">
            <tbody>
                <tr>
                    <td class="td-left">
                        @if(!empty($logoSrc))
                            <img src="{{ $logoSrc }}" alt="Logo" class="logo-img">
                        @endif
                        <div class="company-name">{{ $generalSetting->site_title ?? 'MGEDC' }}</div>
                        @if($generalSetting && $generalSetting->address)
                            <div class="company-sub">{{ $generalSetting->address }}</div>
                        @endif
                        @if($generalSetting && $generalSetting->phone)
                            <div class="company-sub">Tel: {{ $generalSetting->phone }}</div>
                        @endif
                    </td>
                    <td class="td-right">
                        <div class="doc-title">Adjustment Slip</div>
                        <table class="details-table">
                            <tr>
                                <td class="dt-lbl">Tracking No.:</td>
                                <td class="dt-val"><strong>{{ $adjustment->tracking_no }}</strong></td>
                            </tr>
                            <tr>
                                <td class="dt-lbl">Date:</td>
                                <td class="dt-val">{{ $adjustment->adjustment_date->format('d M, Y') }}</td>
                            </tr>
                            @if($adjustment->reason)
                            <tr>
                                <td class="dt-lbl">Reason:</td>
                                <td class="dt-val">{{ $adjustment->reason }}</td>
                            </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- ITEMS TABLE -->
        <div class="items-wrapper">
            <table class="items">
                <thead>
                    <tr>
                        <th class="sn-col">#</th>
                        <th class="code-col">SKU</th>
                        <th class="desc-col">Product Description</th>
                        <th class="batch-col">Batch / Lot</th>
                        <th class="type-col">Type</th>
                        <th class="qty-col">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustment->items as $i => $item)
                        @php
                            $isAdd = in_array(strtolower($item->type ?? ''), ['added', 'addition', 'add', '+'], true);
                        @endphp
                        <tr>
                            <td class="sn-col">{{ $i + 1 }}</td>
                            <td class="code-col">{{ $item->sku ?? $item->product?->sku ?? '—' }}</td>
                            <td class="desc-col">
                                <strong>{{ $item->product_name ?? $item->product?->name }}</strong>
                            </td>
                            <td class="batch-col">
                                {{ $item->batch?->batch_number ?? '—' }}
                            </td>
                            <td class="type-col">
                                <span class="{{ $isAdd ? 'type-badge-add' : 'type-badge-sub' }}">
                                    {{ ucfirst($item->type) }}
                                </span>
                            </td>
                            <td class="qty-col">
                                <strong>{{ $isAdd ? '+' : '-' }}{{ number_format((float)$item->quantity, 0) }}</strong>
                                <span style="font-size: 9px; color: #666;">{{ $item->unit_label ?? $item->product?->unit?->short_name }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: #888;">No items found in this adjustment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- BOTTOM SECTION -->
        @if($adjustment->note)
        <div class="bottom-section">
            <div class="notes-box">
                <strong>Remarks / Notes:</strong><br>
                {{ $adjustment->note }}
            </div>
        </div>
        @endif
    </div>
    @endforeach
</body>
</html>

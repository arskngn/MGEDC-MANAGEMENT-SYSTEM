<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Products Inventory Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 24px;
            font-size: 11px;
            line-height: 1.4;
        }

        /* ── HEADER ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .header-table td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .report-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 2px 0;
            letter-spacing: -0.2px;
        }
        .report-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin: 0;
        }
        .header-logo {
            max-height: 38px;
            width: auto;
        }

        /* ── META & SUMMARY METRICS ── */
        .meta-row {
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
        }
        .meta-row table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-row td {
            border: none;
            padding: 0;
        }

        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }
        .metrics-table td {
            width: 25%;
            padding: 10px 12px;
            border: none;
            border-right: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .metrics-table td:last-child {
            border-right: none;
        }
        .metric-label {
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .metric-value {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        /* ── DATA TABLE ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .data-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9px;
            color: #6b7280;
            letter-spacing: 0.5px;
            padding: 8px 8px;
            border-top: 1px solid #111827;
            border-bottom: 1.5px solid #111827;
            text-align: left;
            background-color: #ffffff;
        }
        .data-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }
        .data-table tr:last-child td {
            border-bottom: 1px solid #e5e7eb;
        }

        .sku-code {
            font-family: Monaco, Consolas, monospace;
            font-size: 9.5px;
            color: #4b5563;
        }
        .product-name {
            font-weight: 600;
            color: #111827;
        }

        .stock-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
        }
        .stock-in {
            color: #065f46;
            background-color: #ecfdf5;
        }
        .stock-out {
            color: #991b1b;
            background-color: #fef2f2;
        }

        /* ── UTILITIES ── */
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .footer {
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid #f3f4f6;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    @php
        $totalCount = $products->count();
        $inStockCount = $products->where('current_stock', '>', 0)->count();
        $outOfStockCount = $products->where('current_stock', '<=', 0)->count();
        $totalUnits = $products->sum('current_stock');
    @endphp

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td>
                <h1 class="report-title">Products Inventory Report</h1>
                <p class="report-subtitle">{{ $generalSetting->site_title ?? 'MGEDC' }} &bull; Catalog &amp; Stock Summary</p>
            </td>
            <td style="text-align: right;">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" class="header-logo" alt="Logo">
                @endif
            </td>
        </tr>
    </table>

    {{-- Meta Info --}}
    <div class="meta-row">
        <table>
            <tr>
                <td><strong style="color: #374151;">Total Items:</strong> {{ number_format($totalCount) }} products</td>
                <td style="text-align: right;"><strong style="color: #374151;">Generated:</strong> {{ now()->format('M d, Y h:i A') }}</td>
            </tr>
        </table>
    </div>

    {{-- Metrics Summary Bar --}}
    <table class="metrics-table">
        <tr>
            <td>
                <div class="metric-label">Total Catalog Products</div>
                <div class="metric-value">{{ number_format($totalCount) }}</div>
            </td>
            <td>
                <div class="metric-label">In Stock Products</div>
                <div class="metric-value" style="color: #059669;">{{ number_format($inStockCount) }}</div>
            </td>
            <td>
                <div class="metric-label">Out of Stock</div>
                <div class="metric-value" style="color: #dc2626;">{{ number_format($outOfStockCount) }}</div>
            </td>
            <td>
                <div class="metric-label">Total Units In Stock</div>
                <div class="metric-value">{{ number_format($totalUnits) }}</div>
            </td>
        </tr>
    </table>

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">S.N.</th>
                <th style="width: 22%;">SKU</th>
                <th style="width: 33%;">Product Name</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 13%;">Brand</th>
                <th style="width: 12%; text-align: right;">Current Stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $i => $p)
                @php
                    $u = $p->unit->short_name ?? $p->unit->name ?? 'pcs';
                    $stockVal = (float) $p->current_stock;
                    $stockFormatted = rtrim(rtrim(number_format($stockVal, 4, '.', ''), '0'), '.') ?: '0';
                    $isOut = $stockVal <= 0;
                @endphp
                <tr>
                    <td class="text-center" style="color: #9ca3af;">{{ $i + 1 }}</td>
                    <td class="sku-code">{{ $p->sku ?: 'N/A' }}</td>
                    <td class="product-name">{{ $p->name }}</td>
                    <td style="color: #4b5563;">{{ $p->category?->name ?? '—' }}</td>
                    <td style="color: #4b5563;">{{ $p->brand?->name ?? '—' }}</td>
                    <td class="text-right">
                        <span class="stock-badge {{ $isOut ? 'stock-out' : 'stock-in' }}">
                            {{ $stockFormatted }} {{ $u }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #9ca3af; font-style: italic;">
                        No product records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Confidential &bull; Powered by {{ $generalSetting->site_title ?? 'MGEDC' }}
    </div>
</body>
</html>

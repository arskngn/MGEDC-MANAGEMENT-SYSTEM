<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Category Sales Performance Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #000;
            margin: 16px;
            font-size: 11px;
            line-height: 1.4;
        }

        .title {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 4px 0;
        }

        .subtitle {
            font-size: 11px;
            color: #333;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 11px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px 6px;
            text-align: left;
        }

        thead th {
            font-weight: 700;
            background-color: #f8f9fa;
        }

        .subtotal-row td {
            font-weight: 700;
            background-color: #f8f9fa;
            text-align: right;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .summary {
            margin-top: 14px;
            text-align: right;
            font-weight: 700;
            font-size: 11px;
            line-height: 1.6;
        }

        .category-block {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    @php
        $currency = $settings->currency_symbol ?? '$';
        $formattedStart = $startDate ? \Carbon\Carbon::parse($startDate)->format('M d, Y') : null;
        $formattedEnd = $endDate ? \Carbon\Carbon::parse($endDate)->format('M d, Y') : null;
    @endphp

    <div class="title">Category Sales Performance Report</div>
    <div class="subtitle">
        @if($formattedStart && $formattedEnd)
            Period: {{ $formattedStart }} to {{ $formattedEnd }} &nbsp;&bull;&nbsp;
        @endif
        Generated: {{ now()->format('M d, Y h:i A') }}
    </div>

    @forelse(($analytics['all_items'] ?? $analytics['items'] ?? []) as $index => $item)
        <div class="category-block">
            <div style="font-weight: 700; font-size: 12px; margin-bottom: 6px;">
                #{{ $index + 1 }} {{ $item['name'] }}
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 6%; text-align: center;">#</th>
                        <th style="width: 44%;">Product Name</th>
                        <th style="width: 20%; text-align: center;">SKU</th>
                        <th style="width: 15%; text-align: right;">Qty Sold</th>
                        <th style="width: 15%; text-align: right;">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($item['products'] ?? []) as $pIndex => $p)
                        <tr>
                            <td class="text-center">{{ $pIndex + 1 }}</td>
                            <td>{{ $p['name'] }}</td>
                            <td class="text-center">{{ $p['sku'] ?: 'N/A' }}</td>
                            <td class="text-right">{{ number_format($p['qty_sold']) }}</td>
                            <td class="text-right">{{ $currency }}{{ number_format($p['total_amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="color: #666; font-style: italic;">
                                No sales recorded for this category in the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!empty($item['products'] ?? []))
                    <tfoot>
                        <tr class="subtotal-row">
                            <td colspan="3">Category Subtotal:</td>
                            <td>{{ number_format($item['total_sold_qty']) }}</td>
                            <td>{{ $currency }}{{ number_format($item['total_sales_amount'], 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @empty
        <div style="text-align: center; padding: 20px; color: #666; font-style: italic;">
            No category sales records found for the specified period.
        </div>
    @endforelse

    @if(!empty($analytics['all_items'] ?? $analytics['items'] ?? []))
        <div class="summary">
            <div>Total Units Sold: {{ number_format($analytics['total_period_qty'] ?? 0) }}</div>
            <div>Total Category Sales: {{ $currency }}{{ number_format($analytics['total_period_sales'] ?? 0, 2) }}</div>
        </div>
    @endif
</body>
</html>

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
    @php
        $title = 'Top Selling Products Report';
        if ($selectedProduct) {
            $title = 'Top Selling Product Breakdown';
        }
    @endphp

    <h2 class="title">{{ $title }}</h2>

    @if ($start && $end)
        <div style="margin-bottom: 8px;">Period: {{ $start }} to {{ $end }}</div>
    @endif

    @if ($selectedProduct)
        <div style="margin-bottom: 8px; font-weight:700;">Product: {{ $selectedProduct->product_name ?: 'N/A' }} (SKU: {{ $selectedProduct->sku ?: 'N/A' }})</div>
        @php
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
        @endphp

        @foreach ($monthlyGroups as $monthKey => $rows)
            @php
                $monthTotalQty = array_sum(array_column($rows, 'qty'));
                $monthTotalAmt = array_sum(array_column($rows, 'amt'));
                $grandTotalQty += $monthTotalQty;
                $grandTotalAmt += $monthTotalAmt;
                $monthName = \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('F Y');
                $isLastMonth = $monthKey === $lastMonthKey;
            @endphp
            <div class="month-section">
                <div style="margin-bottom: 8px; font-weight:700;">{{ $monthName }}</div>
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
                        @foreach ($rows as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ number_format($row['qty'] > 0 ? $row['amt'] / $row['qty'] : 0, 2) }}</td>
                                <td>{{ number_format($row['qty'], 2) }}</td>
                                <td>{{ number_format($row['amt'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="summary">
                    Month Total Qty: {{ number_format($monthTotalQty, 2) }}<br>
                    Month Total Amount: {{ number_format($monthTotalAmt, 2) }}
                </div>

                @if ($isLastMonth)
                    <div class="summary" style="margin-top: 16px;">
                        Grand Total Qty: {{ number_format($grandTotalQty, 2) }}<br>
                        Grand Total Amount: {{ number_format($grandTotalAmt, 2) }}
                    </div>
                @endif
            </div>
        @endforeach
    @else
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
                @foreach ($products as $index => $product)
                    @php
                        $priceSold = $product->total_quantity > 0 ? $product->total_amount / $product->total_quantity : 0;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product->product_name ?: 'N/A' }}</td>
                        <td>{{ $product->sku ?: 'N/A' }}</td>
                        <td>{{ number_format($product->total_quantity, 2) }}</td>
                        <td>{{ number_format($product->total_amount, 2) }}</td>
                        <td>{{ number_format($priceSold, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="summary">
            Total Qty: {{ number_format($products->sum('total_quantity'), 2) }}<br>
            Total Amount: {{ number_format($products->sum('total_amount'), 2) }}
        </div>
    @endif
</body>
</html>

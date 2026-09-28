<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Invoices</title>
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
        .td-left  { width: 60%; }
        .td-right { width: 40%; }

        .dr-details-table {
            border-collapse: collapse;
            float: right;
        }
        .dr-details-table td {
            font-size: 11px;
            padding: 2px 0;
            vertical-align: top;
        }
        .dr-lbl { text-align: left; padding-right: 25px; }
        .dr-val { text-align: right; }

        /* ── ITEMS TABLE ── */
        .items-wrapper {
            min-height: 400px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 40px;
        }
        table.items thead th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px 2px;
            text-align: left;
            font-weight: normal;
        }
        table.items td { padding: 5px 2px; vertical-align: top; }

        .qty-col   { width: 10%; text-align: center !important; }
        .code-col  { width: 15%; }
        .desc-col  { width: 45%; }
        .price-col { width: 15%; text-align: right !important; }
        .amt-col   { width: 15%; text-align: right !important; }

        /* ── BOTTOM SECTION ── */
        .bottom-section {
            width: 100%;
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 15px;
        }
        table.bottom-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.bottom-table > tbody > tr > td {
            vertical-align: top;
            padding: 0;
        }
        .td-bottom-left  { width: 60%; }
        .td-bottom-right { width: 40%; }

        /* Left column inner table */
        table.left-inner {
            width: 100%;
            border-collapse: collapse;
        }
        table.left-inner td {
            font-size: 11px;
            padding: 2px 0;
            vertical-align: top;
        }
        .sig-left-td {
            padding-top: 25px !important;
            width: 200px;
        }
        .sig-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 30px;
            padding-top: 4px;
        }

        /* Right column summary table */
        table.summary {
            width: 100%;
            border-collapse: collapse;
        }
        table.summary td {
            font-size: 11px;
            padding: 3px 0;
        }
        .s-lbl { text-align: left; }
        .s-val { text-align: right; }
        
        .balance-row .s-lbl {
            font-weight: bold;
            padding-top: 15px;
        }
        .balance-row .s-val {
            font-weight: bold;
            font-size: 13px;
            padding-top: 15px;
        }
        .balance-line {
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }
    </style>
</head>
<body>
    @foreach($sales as $sale)
    @php
        $saleStoreCategory = $sale->storeCategory?->name
            ?? $sale->store_category
            ?? $sale->customer?->storeCategory?->name
            ?? $sale->customer?->store_category;

        $grossSubtotal = $sale->items->sum(function($line) {
            return $line->is_free ? 0 : ((float)$line->quantity * (float)$line->unit_price);
        });

        $itemDiscounts = $sale->items->sum(function($line) {
            if ($line->is_free) return 0;
            $lineGross = (float)$line->quantity * (float)$line->unit_price;
            return max(0, $lineGross - (float)$line->line_total);
        });

        $overallTotalDiscount = $itemDiscounts + (float) $sale->discount;
    @endphp
    <div class="page">
        <!-- TOP HEADER -->
        <table class="top-header-table">
            <tr>
                <td class="td-left">
                    Delivery Address:<br>
                    {{ strtoupper($sale->customer->name ?? '') }}<br>
                    @if($sale->customer->address ?? null)
                    {{ strtoupper($sale->customer->address) }}<br>
                    @endif
                    @if($saleStoreCategory)
                    <strong>CATEGORY: {{ strtoupper($saleStoreCategory) }}</strong>
                    @endif
                </td>
                <td class="td-right">
                    <table class="dr-details-table">
                        <tr><td class="dr-lbl">DR #:</td><td class="dr-val">{{ $sale->invoice_no }}</td></tr>
                        <tr><td class="dr-lbl">Date:</td><td class="dr-val">{{ $sale->sale_date ? $sale->sale_date->format('n/j/Y') : date('n/j/Y') }}</td></tr>
                        <tr><td class="dr-lbl">Page:</td><td class="dr-val">1</td></tr>
                        <tr><td class="dr-lbl">Terms:</td><td class="dr-val">{{ $sale->payment_method ? strtoupper($sale->payment_method) : 'C.O.D.' }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- ITEMS TABLE -->
        <div class="items-wrapper">
            <table class="items">
                <thead>
                    <tr>
                        <th class="qty-col">QUANTITY</th>
                        <th class="code-col">ITEM CODE</th>
                        <th class="desc-col">DESCRIPTION</th>
                        <th class="price-col">UNIT PRICE WT</th>
                        <th class="amt-col">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $line)
                    <tr>
                        <td class="qty-col">{{ number_format((float) $line->quantity, 0) }}</td>
                        <td class="code-col">{{ $line->sku }}</td>
                        <td class="desc-col">{{ $line->product_name }} @if($line->is_free) (FREE) @endif</td>
                        <td class="price-col">{{ $line->is_free ? 'Free' : number_format((float) $line->unit_price, 2) }}</td>
                        <td class="amt-col">{{ $line->is_free ? 'Free' : number_format((float) $line->quantity * (float) $line->unit_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- BOTTOM SECTION -->
        <div class="bottom-section">
            <table class="bottom-table">
                <tr>
                    <!-- LEFT COLUMN -->
                    <td class="td-bottom-left">
                        <table class="left-inner">
                            <tr><td>Delivery</td></tr>
                            <tr><td>Salesperson: {{ $sale->salesperson->name ?? $sale->user->name ?? 'Sales Person' }}</td></tr>
                            <tr><td style="padding-top: 8px;"><em><strong>VAT INCLUSIVE</strong></em></td></tr>
                            <tr><td>Received above listed merchandise in good</td></tr>
                            <tr>
                                <td>
                                    <div class="sig-line">Signature over printed</div>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <!-- RIGHT COLUMN -->
                    <td class="td-bottom-right">
                        <table class="summary">
                            <tr>
                                <td class="s-lbl">Subtotal:</td>
                                <td class="s-val">{{ number_format($grossSubtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="s-lbl">Discount:</td>
                                <td class="s-val">{{ number_format($overallTotalDiscount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="s-lbl">Total Amount:</td>
                                <td class="s-val">{{ number_format((float) $sale->receivable_amount, 2) }}</td>
                            </tr>
                            @php
                                $totalReturnsAmount = (float) $sale->saleReturns->sum('payable_amount');
                            @endphp
                            @if($totalReturnsAmount > 0)
                            <tr>
                                <td class="s-lbl">Returns / Credits:</td>
                                <td class="s-val">{{ number_format($totalReturnsAmount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="s-lbl">Paid to Date:</td>
                                <td class="s-val">{{ number_format((float) $sale->paid_amount, 2) }}</td>
                            </tr>
                            <tr class="balance-row">
                                <td class="s-lbl">Balance Due:</td>
                                <td class="s-val"><span class="balance-line">{{ number_format((float) $sale->due_amount, 2) }}</span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endforeach
</body>
</html>


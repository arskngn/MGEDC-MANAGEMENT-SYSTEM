<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
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

        .summary { width: 230px; margin-left: auto; margin-top: 18px; font-size: 10px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 4px 0; }
        .summary td:first-child { color: #6b7280; }
        .summary td:last-child { text-align: right; font-weight: bold; color: #111827; }

        /* ── Data table ── */
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.data col.c-no         { width: 22px; }
        table.data col.c-invoice    { width: 80px; }
        table.data col.c-date       { width: 68px; }
        table.data col.c-customer   { width: 110px; }
        table.data col.c-sales      { width: 80px; }
        table.data col.c-receivable { width: 82px; }
        table.data col.c-paid       { width: 82px; }
        table.data col.c-due        { width: 74px; }
        table.data col.c-status     { width: 44px; }

        table.data th {
            font-size: 7.5px; font-weight: bold; color: #6b7280;
            text-transform: uppercase; letter-spacing: 0.3px;
            padding: 5px 5px 5px; border-bottom: 1.5px solid #374151;
            white-space: nowrap; overflow: hidden;
        }
        table.data td {
            font-size: 9px; padding: 5px 5px;
            border-bottom: 1px solid #f3f4f6;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        table.data tbody tr:last-child td { border-bottom: none; }

        .l  { text-align: left; }
        .r  { text-align: right; }
        .c  { text-align: center; }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="hdr">
        <tr>
            <td class="left">
                <div class="title">Sales Report</div>
                <div class="meta">
                    Generated: {{ now()->format('M d, Y h:i A') }}
                    @php
                        $filterParts = array_filter([
                            ($filters['date_from'] || $filters['date_to']) ? ($filters['date_from'] ?: 'Start') . ' – ' . ($filters['date_to'] ?: 'Today') : null,
                            !empty($filters['payment_status']) ? ucfirst($filters['payment_status']) : null,
                            !empty($filters['salesperson']) ? $filters['salesperson'] : null,
                            !empty($filters['customer']) ? $filters['customer'] : null,
                        ]);
                    @endphp
                    @if(!empty($filterParts))
                        &bull; Filters: {{ implode(' | ', $filterParts) }}
                    @endif
                </div>
            </td>
            <td class="right">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo">
                @endif
                <div class="bname">{{ $generalSetting?->site_title ?? $generalSetting?->company_name ?? 'GE-System' }}</div>
            </td>
        </tr>
    </table>

    {{-- Data Table --}}
    <table class="data">
        <colgroup>
            <col class="c-no">
            <col class="c-invoice">
            <col class="c-date">
            <col class="c-customer">
            <col class="c-sales">
            <col class="c-receivable">
            <col class="c-paid">
            <col class="c-due">
            <col class="c-status">
        </colgroup>
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="l">Invoice No</th>
                <th class="l">Date</th>
                <th class="l">Customer</th>
                <th class="l">Salesperson</th>
                <th class="r">Receivable</th>
                <th class="r">Paid</th>
                <th class="r">Due</th>
                <th class="c">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $idx => $s)
                @php $due = max(0, (float) $s->receivable_amount - (float) $s->paid_amount); @endphp
                <tr>
                    <td class="c">{{ $idx + 1 }}</td>
                    <td class="l"><strong>{{ $s->invoice_no }}</strong></td>
                    <td class="l">{{ $s->sale_date?->format('Y-m-d') }}</td>
                    <td class="l">{{ $s->customer?->name ?? 'Walk-in' }}</td>
                    <td class="l">{{ $s->salesperson?->name ?? ($s->user?->name ?? 'N/A') }}</td>
                    <td class="r">{{ formatCurrency($s->receivable_amount) }}</td>
                    <td class="r">{{ formatCurrency($s->paid_amount) }}</td>
                    <td class="r">{{ formatCurrency($due) }}</td>
                    <td class="c">
                        @if($due <= 0)
                            <span style="font-weight:bold;">Paid</span>
                        @elseif($s->paid_amount > 0)
                            <span style="font-weight:bold;">Partial</span>
                        @else
                            <span style="font-weight:bold;">Due</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="c" style="padding:16px;color:#9ca3af;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Invoices</td>
                <td>{{ number_format($kpis['total_count']) }}</td>
            </tr>
            <tr>
                <td>Receivable</td>
                <td>{{ formatCurrency($kpis['total_amount']) }}</td>
            </tr>
            <tr>
                <td>Received</td>
                <td>{{ formatCurrency($kpis['total_paid']) }}</td>
            </tr>
            <tr>
                <td>Due</td>
                <td>{{ formatCurrency($kpis['total_due']) }}</td>
            </tr>
        </table>
    </div>
</body>
</html>

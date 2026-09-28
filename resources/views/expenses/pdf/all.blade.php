<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Expenses</title>
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

        /* ── Summary block ── */
        .summary { width: 230px; margin-left: auto; margin-top: 18px; font-size: 10px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 4px 0; }
        .summary td:first-child { color: #6b7280; }
        .summary td:last-child { text-align: right; font-weight: bold; color: #111827; }

        /* ── Data table ── */
        table.data { width: 100%; border-collapse: collapse; }
        table.data th {
            font-size: 7.5px; font-weight: bold; color: #6b7280;
            text-transform: uppercase; letter-spacing: 0.3px;
            padding: 5px 6px; border-bottom: 1.5px solid #374151;
            text-align: left; white-space: nowrap;
        }
        table.data td {
            font-size: 9px; padding: 5px 6px;
            border-bottom: 1px solid #f3f4f6;
        }
        table.data tbody tr:last-child td { border-bottom: none; }
        table.data th.r, table.data td.r { text-align: right; }
        table.data th.c, table.data td.c { text-align: center; }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="hdr">
        <tr>
            <td class="left">
                <div class="title">All Expenses</div>
                <div class="meta">Generated: {{ now()->format('M d, Y h:i A') }}</div>
            </td>
            <td class="right">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo">
                @endif
                <div class="bname">{{ $generalSetting->site_title ?? $generalSetting->whole_company_name ?? 'MGEDC' }}</div>
            </td>
        </tr>
    </table>

    {{-- Data Table --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width:28px;" class="c">#</th>
                <th style="width:25%;">Type / Category</th>
                <th style="width:18%;">Date</th>
                <th style="width:38%;">Note</th>
                <th class="r" style="width:19%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $totalAmount = 0; @endphp
            @forelse($expenses as $i => $expense)
                @php $totalAmount += (float) $expense->amount; @endphp
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td><strong>{{ $expense->expenseType->name ?? '—' }}</strong></td>
                    <td>{{ $expense->date->format('M d, Y') }}</td>
                    <td>{{ Illuminate\Support\Str::limit($expense->description ?? '—', 60) }}</td>
                    <td class="r">{{ formatCurrency($expense->amount) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="c" style="padding:16px; color:#9ca3af;">No expenses found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Total Entries</td>
                <td>{{ number_format($expenses->count()) }}</td>
            </tr>
            <tr>
                <td>Total Amount</td>
                <td>{{ formatCurrency($totalAmount) }}</td>
            </tr>
        </table>
    </div>

</body>
</html>

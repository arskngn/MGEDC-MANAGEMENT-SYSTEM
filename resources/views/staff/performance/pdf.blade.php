<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Performance & Overall Quota Report</title>
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
        .summary { width: 250px; margin-left: auto; margin-top: 18px; font-size: 10px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 4px 0; }
        .summary td:first-child { color: #6b7280; }
        .summary td:last-child { text-align: right; font-weight: bold; color: #111827; }

        /* ── Data table ── */
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.data col.c-rank        { width: 26px; }
        table.data col.c-staff       { width: 170px; }
        table.data col.c-sales       { width: 105px; }
        table.data col.c-share       { width: 85px; }
        table.data col.c-invoices    { width: 65px; }
        table.data col.c-commission  { width: 105px; }
        table.data col.c-status      { width: 95px; }

        table.data th {
            font-size: 7.5px; font-weight: bold; color: #6b7280;
            text-transform: uppercase; letter-spacing: 0.3px;
            padding: 6px 5px; border-bottom: 1.5px solid #374151;
            white-space: nowrap; overflow: hidden;
        }
        table.data td {
            font-size: 8.5px; padding: 6px 5px;
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
                <div class="title">Staff Performance & Overall Quota Report</div>
                <div class="meta">
                    Generated: {{ now()->format('M d, Y h:i A') }}
                    &bull; Period: {{ $startDate->format('M d, Y') }} &ndash; {{ $endDate->format('M d, Y') }}
                    @if($overallQuota?->name)
                        &bull; Quota: {{ $overallQuota->name }}
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

    {{-- Data Table: Salesperson Contributions --}}
    <table class="data">
        <colgroup>
            <col class="c-rank">
            <col class="c-staff">
            <col class="c-sales">
            <col class="c-share">
            <col class="c-invoices">
            <col class="c-commission">
            <col class="c-status">
        </colgroup>
        <thead>
            <tr>
                <th class="c">#</th>
                <th class="l">Salesperson</th>
                <th class="r">Total Contributed</th>
                <th class="c">% Share of Sales</th>
                <th class="c">Invoices</th>
                <th class="r">Commission</th>
                <th class="c">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staff as $idx => $perf)
                <tr>
                    <td class="c">{{ $idx + 1 }}</td>
                    <td class="l">
                        <strong>{{ $perf->user->name }}</strong>
                        <div style="font-size: 7.5px; color: #9ca3af;">{{ $perf->user->email ?? 'N/A' }}</div>
                    </td>
                    <td class="r" style="font-weight: bold;">
                        {{ formatCurrency($perf->actual_sales) }}
                    </td>
                    <td class="c">
                        {{ $perf->contribution_pct }}%
                    </td>
                    <td class="c">
                        {{ $perf->invoices_count }}
                    </td>
                    <td class="r" style="font-weight: bold;">
                        {{ formatCurrency($perf->commission_earned) }}
                    </td>
                    <td class="c">
                        @if($isAchieved)
                            <span style="font-weight: bold;">Awarded</span>
                        @else
                            <span>Pending</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="c" style="padding: 16px; color: #9ca3af;">No staff members found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary block --}}
    <div class="summary">
        <table>
            <tr>
                <td>Overall Target</td>
                <td>{{ formatCurrency($targetAmount) }}</td>
            </tr>
            <tr>
                <td>Team Total Sales</td>
                <td>{{ formatCurrency($totalSales) }}</td>
            </tr>
            <tr>
                <td>Quota Progress</td>
                <td>{{ $progress }}% {{ $isAchieved ? '✓' : '' }}</td>
            </tr>
            <tr>
                <td>Commission Status</td>
                <td>{{ $isAchieved ? 'Awarded' : 'Pending Quota' }}</td>
            </tr>
            <tr>
                <td>Total Commission Pool</td>
                <td>{{ formatCurrency($performance['totalCommissionPool']) }}</td>
            </tr>
        </table>
    </div>

</body>
</html>

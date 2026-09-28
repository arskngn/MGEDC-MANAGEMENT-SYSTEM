<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Adjustments</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10px;
            color: #000;
            margin: 30px;
            line-height: 1.4;
        }

        /* ── HEADER ── */
        .top-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .top-header-table td {
            vertical-align: top;
        }
        .td-left { width: 60%; }
        .td-right { width: 40%; text-align: right; }

        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #0a1233;
            margin-bottom: 2px;
        }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0a1233;
        }
        .meta-info {
            font-size: 9px;
            color: #555;
            margin-top: 3px;
        }
        .logo-img {
            max-height: 45px;
            max-width: 110px;
            margin-bottom: 5px;
        }

        /* ── TABLE ── */
        table.summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 10px;
        }
        table.summary-table thead th {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.summary-table tbody td {
            padding: 6px 4px;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
        }

        .sn-col    { width: 6%; text-align: center !important; }
        .track-col { width: 22%; font-weight: bold; }
        .date-col  { width: 16%; }
        .reas-col  { width: 22%; }
        .items-col { width: 34%; }

        .item-row {
            font-size: 9px;
            line-height: 1.3;
            margin-bottom: 2px;
        }
        .add-text { color: #059669; font-weight: bold; }
        .sub-text { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>

    <table class="top-header-table">
        <tr>
            <td class="td-left">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo" class="logo-img">
                @endif
                <div class="company-name">{{ $generalSetting->site_title ?? 'MGEDC' }}</div>
                <div class="meta-info">All Adjustments Report</div>
            </td>
            <td class="td-right">
                <div class="doc-title">Adjustments Summary</div>
                <div class="meta-info">Generated: {{ now()->format('d M, Y h:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="summary-table">
        <thead>
            <tr>
                <th class="sn-col">#</th>
                <th class="track-col">Tracking No.</th>
                <th class="date-col">Date</th>
                <th class="reas-col">Reason</th>
                <th class="items-col">Products / Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($adjustments as $i => $adjustment)
                <tr>
                    <td class="sn-col">{{ $i + 1 }}</td>
                    <td class="track-col">{{ $adjustment->tracking_no }}</td>
                    <td class="date-col">{{ $adjustment->adjustment_date->format('d M, Y') }}</td>
                    <td class="reas-col">{{ $adjustment->reason ?? '—' }}</td>
                    <td class="items-col">
                        @foreach($adjustment->items as $item)
                            @php
                                $isAdd = in_array(strtolower($item->type ?? ''), ['added', 'addition', 'add', '+'], true);
                            @endphp
                            <div class="item-row">
                                • {{ $item->product_name ?? $item->product?->name }} 
                                <span class="{{ $isAdd ? 'add-text' : 'sub-text' }}">
                                    ({{ $isAdd ? '+' : '-' }}{{ number_format((float)$item->quantity, 0) }} {{ $item->unit_label ?? $item->product?->unit?->short_name }})
                                </span>
                            </div>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 15px; color: #888;">No adjustments found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

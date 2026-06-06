<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8px; color: #1e293b; background: #fff; }

    .title-block { background: #1e293b; color: #fff; padding: 10px 14px; margin-bottom: 6px; }
    .title-block h1 { font-size: 13px; font-weight: bold; letter-spacing: 0.5px; }
    .title-block p  { font-size: 7.5px; margin-top: 3px; opacity: .75; }

    table { width: 100%; border-collapse: collapse; margin-top: 4px; }

    /* Group header row */
    thead tr.group-row th {
        font-size: 7.5px;
        font-weight: bold;
        text-align: center;
        padding: 5px 4px;
        border: 1px solid #cbd5e1;
    }
    thead tr.group-row th.green  { background: #d1fae5; color: #065f46; }
    thead tr.group-row th.yellow { background: #fef3c7; color: #92400e; }
    thead tr.group-row th.blue   { background: #dbeafe; color: #1e40af; }
    thead tr.group-row th.pink   { background: #fce7f3; color: #9d174d; }

    /* Sub-header row */
    thead tr.sub-row th {
        font-size: 7px;
        font-weight: bold;
        padding: 4px 4px;
        border: 1px solid #cbd5e1;
        white-space: nowrap;
    }
    thead tr.sub-row th.green  { background: #ecfdf5; color: #047857; }
    thead tr.sub-row th.yellow { background: #fffbeb; color: #b45309; text-align: center; }
    thead tr.sub-row th.blue   { background: #eff6ff; color: #1d4ed8; text-align: right; }
    thead tr.sub-row th.pink   { background: #fdf2f8; color: #be185d; text-align: right; }
    thead tr.sub-row th.green-left   { background: #ecfdf5; color: #047857; text-align: left; }
    thead tr.sub-row th.green-center { background: #ecfdf5; color: #047857; text-align: center; }

    /* Data rows */
    tbody tr td {
        font-size: 7.5px;
        padding: 4px 5px;
        border: 1px solid #cbd5e1;
        vertical-align: middle;
    }
    tbody tr:nth-child(even) td.green  { background: #f0fdf4; }
    tbody tr:nth-child(even) td.yellow { background: #fefce8; }
    tbody tr:nth-child(even) td.blue   { background: #eff6ff; }
    tbody tr:nth-child(even) td.pink   { background: #fdf4ff; }
    tbody tr:nth-child(odd) td { background: #ffffff; }

    td.left   { text-align: left; }
    td.center { text-align: center; }
    td.right  { text-align: right; }
    td.bold   { font-weight: bold; }
    td.muted  { color: #94a3b8; text-align: center; }

    /* Totals row */
    tr.totals-row td {
        background: #1e293b !important;
        color: #ffffff;
        font-weight: bold;
        font-size: 7.5px;
        padding: 5px 5px;
        border: 1px solid #0f172a;
    }

    .footer { margin-top: 10px; font-size: 7px; color: #94a3b8; text-align: right; }
    .no-records { text-align: center; padding: 20px; color: #94a3b8; font-style: italic; }
</style>
</head>
<body>
    <div class="title-block">
        <h1>MOWER PERFORMANCE REPORT</h1>
        <p>Generated: {{ now()->format('d M Y, H:i') }} &nbsp;&bull;&nbsp; Total Mowers: {{ count($reportData) }}</p>
    </div>

    @php $showBonus = !($hideBonusColumn ?? false); @endphp
    <table>
        <colgroup>
            <col style="width:{{ $showBonus ? '22%' : '27%' }}">  {{-- Name --}}
            <col style="width:{{ $showBonus ? '13%' : '17%' }}">  {{-- Total Hours --}}
            <col style="width:{{ $showBonus ? '13%' : '17%' }}">  {{-- Working Days --}}
            <col style="width:{{ $showBonus ? '13%' : '17%' }}">  {{-- Jobs Completed --}}
            <col style="width:{{ $showBonus ? '17%' : '22%' }}">  {{-- Earnings Completed --}}
            @if($showBonus)
                <col style="width:12%">  {{-- Bonus --}}
            @endif
        </colgroup>
        <thead>
            <tr class="group-row">
                <th colspan="3" class="green">Mower</th>
                <th colspan="1" class="yellow">Jobs</th>
                <th colspan="1" class="blue">Total Sale</th>
                @if($showBonus)
                    <th colspan="1" class="pink">Bonus</th>
                @endif
            </tr>
            <tr class="sub-row">
                <th class="green-left">Name</th>
                <th class="green-center">Total Hours</th>
                <th class="green-center">Working Days</th>
                <th class="yellow">Completed</th>
                <th class="blue">Completed</th>
                @if($showBonus)
                    <th class="pink">Bonus</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (is_countable($reportData) && count($reportData) > 0)
                @php
                    $totals = [
                        'total_working_hours'  => 0,
                        'working_days'         => 0,
                        'total_jobs_completed' => 0,
                        'completed_earnings'   => 0,
                        'bonus'                => 0,
                    ];
                @endphp
                @foreach ($reportData as $mower)
                    @php
                        foreach (array_keys($totals) as $k) {
                            $totals[$k] += data_get($mower, $k, 0);
                        }
                    @endphp
                    <tr>
                        <td class="left green">{{ data_get($mower, 'name') }}</td>
                        <td class="center green">{{ number_format((float) data_get($mower, 'total_working_hours', 0), 2) }}</td>
                        <td class="center green">{{ data_get($mower, 'working_days', 0) }}</td>
                        <td class="center yellow">{{ data_get($mower, 'total_jobs_completed') }}</td>
                        <td class="right blue">${{ number_format((float) data_get($mower, 'completed_earnings', 0), 2) }}</td>
                        @if($showBonus)
                            <td class="right pink">${{ number_format((float) data_get($mower, 'bonus', 0), 2) }}</td>
                        @endif
                    </tr>
                @endforeach
                <tr class="totals-row">
                    <td class="left">TOTALS</td>
                    <td class="center">{{ number_format($totals['total_working_hours'], 2) }}</td>
                    <td class="center">{{ $totals['working_days'] }}</td>
                    <td class="center">{{ $totals['total_jobs_completed'] }}</td>
                    <td class="right">${{ number_format($totals['completed_earnings'], 2) }}</td>
                    @if($showBonus)
                        <td class="right">${{ number_format($totals['bonus'], 2) }}</td>
                    @endif
                </tr>
            @else
                <tr><td colspan="{{ $showBonus ? 6 : 5 }}" class="no-records">No mower report data available.</td></tr>
            @endif
        </tbody>
    </table>

    <div class="footer">{{ config('app.name') }} &bull; Mower Performance Report &bull; {{ now()->format('d M Y') }}</div>
</body>
</html>

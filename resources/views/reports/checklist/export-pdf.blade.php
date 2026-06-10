<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 14px; font-weight: bold; text-align: center; padding: 10px 0; background: #f8fafc; border-bottom: 1px solid #cbd5e1; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 8px; }
        thead tr:first-child th { font-size: 11px; font-weight: bold; }
        thead tr:last-child th { font-size: 9px; }

        /* Employee group – green */
        .g-green     { background: #d1fae5; color: #065f46; text-align: center; }
        .g-green-sub { background: #ecfdf5; color: #047857; }

        /* Date group – blue */
        .g-blue     { background: #dbeafe; color: #1e40af; text-align: center; }
        .g-blue-sub { background: #eff6ff; color: #1d4ed8; text-align: center; }

        td.name  { font-weight: 500; }
        td.num   { text-align: center; }
        td.empty { color: #cbd5e1; text-align: center; }

        tr.alt    { background: #f8fafc; }
        tr.totals { background: #1e293b; color: #fff; font-weight: bold; }
        tr.totals td { border-color: #0f172a; text-align: center; }
        tr.totals td:first-child { text-align: left; }
    </style>
</head>
<body>
    <h1>Checklist Submission Report &mdash; {{ now()->format('d M Y') }}</h1>

    @php
        $allDates = [];
        if (isset($reportData) && count($reportData) > 0) {
            foreach ($reportData as $row) {
                foreach (array_keys(data_get($row, 'submissions_by_date', [])) as $date) {
                    $allDates[$date] = true;
                }
            }
            ksort($allDates);
            $allDates = array_keys($allDates);
        }
        $dateCount = count($allDates);
    @endphp

    <table>
        <colgroup>
            <col style="width:180px">
            <col style="width:100px">
            <col style="width:90px">
            @foreach ($allDates as $date)
                <col style="width:80px">
            @endforeach
        </colgroup>
        <thead>
            <tr>
                <th colspan="3" class="g-green">Employee</th>
                @if ($dateCount > 0)
                    <th colspan="{{ $dateCount }}" class="g-blue">Checklist Submissions</th>
                @endif
            </tr>
            <tr>
                <th class="g-green-sub" style="text-align:left">Name</th>
                <th class="g-green-sub">Total Submissions</th>
                <th class="g-green-sub">Days Submitted</th>
                @foreach ($allDates as $date)
                    <th class="g-blue-sub">{{ \Carbon\Carbon::parse($date)->format('d M') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if (isset($reportData) && count($reportData) > 0)
                @foreach ($reportData as $i => $row)
                    <tr class="{{ $i % 2 === 0 ? '' : 'alt' }}">
                        <td class="name">{{ data_get($row, 'name') }}</td>
                        <td class="num">{{ data_get($row, 'total_submissions', 0) }}</td>
                        <td class="num">{{ data_get($row, 'days_submitted', 0) }}</td>
                        @foreach ($allDates as $date)
                            @php $count = data_get($row, 'submissions_by_date.' . $date, null); @endphp
                            <td class="{{ $count ? 'num' : 'empty' }}">{{ $count ?? '—' }}</td>
                        @endforeach
                    </tr>
                @endforeach

                {{-- Totals row --}}
                <tr class="totals">
                    <td>TOTALS</td>
                    <td>{{ collect($reportData)->sum(fn($r) => data_get($r, 'total_submissions', 0)) }}</td>
                    <td>{{ collect($reportData)->sum(fn($r) => data_get($r, 'days_submitted', 0)) }}</td>
                    @foreach ($allDates as $date)
                        <td>{{ collect($reportData)->sum(fn($r) => data_get($r, 'submissions_by_date.' . $date, 0)) }}</td>
                    @endforeach
                </tr>
            @else
                <tr>
                    <td colspan="{{ 3 + $dateCount }}" style="text-align:center; padding: 20px; color:#64748b;">
                        No checklist submissions found.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>

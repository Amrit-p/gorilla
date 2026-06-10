<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1e293b; }
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

        td.name      { font-weight: 600; vertical-align: middle; }
        td.checklist { text-align: left; }
        td.submitted { color: #15803d; font-weight: bold; text-align: center; }
        td.empty     { color: #cbd5e1; text-align: center; }

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
        if (isset($startDate) && $startDate && isset($endDate) && $endDate) {
            foreach (\Carbon\CarbonPeriod::create($startDate, $endDate) as $day) {
                $allDates[] = $day->toDateString();
            }
        } elseif (isset($reportData) && count($reportData) > 0) {
            foreach ($reportData as $row) {
                foreach (array_keys(data_get($row, 'submissions_by_date', [])) as $date) {
                    $allDates[$date] = true;
                }
            }
            ksort($allDates);
            $allDates = array_keys($allDates);
        }
        $dateCount  = count($allDates);
        $grouped    = collect($reportData ?? [])->groupBy('user_id');
        $totalCols  = 2 + $dateCount;
        $colorIndex = 0;
    @endphp

    <table>
        <colgroup>
            <col style="width:150px">
            <col style="width:180px">
            @foreach ($allDates as $date)
                <col style="width:70px">
            @endforeach
        </colgroup>
        <thead>
            <tr>
                <th colspan="2" class="g-green">Employee</th>
                @if ($dateCount > 0)
                    <th colspan="{{ $dateCount }}" class="g-blue">Submission Dates</th>
                @endif
            </tr>
            <tr>
                <th class="g-green-sub" style="text-align:left">Mower</th>
                <th class="g-green-sub" style="text-align:left">Checklist</th>
                @foreach ($allDates as $date)
                    <th class="g-blue-sub">{{ \Carbon\Carbon::parse($date)->format('d M') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if (isset($reportData) && count($reportData) > 0)
                @foreach ($grouped as $userRows)
                    @php
                        $rowCount = count($userRows);
                        $rowBg    = ($colorIndex % 2 !== 0) ? 'alt' : '';
                        $colorIndex++;
                    @endphp
                    @foreach ($userRows->values() as $rowIndex => $row)
                        <tr class="{{ $rowBg }}">
                            @if ($rowIndex === 0)
                                <td rowspan="{{ $rowCount }}" class="name">{{ data_get($row, 'name') }}</td>
                            @endif
                            <td class="checklist">{{ data_get($row, 'checklist_name') }}</td>
                            @foreach ($allDates as $date)
                                @php $submitted = data_get($row, 'submissions_by_date.' . $date, false); @endphp
                                <td class="{{ $submitted ? 'submitted' : 'empty' }}">{{ $submitted ? '✓' : '—' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach

                {{-- Totals row --}}
                <tr class="totals">
                    <td colspan="2">TOTALS</td>
                    @foreach ($allDates as $date)
                        @php
                            $count = collect($reportData)->filter(fn ($r) => data_get($r, 'submissions_by_date.' . $date, false))->count();
                        @endphp
                        <td>{{ $count > 0 ? $count : '—' }}</td>
                    @endforeach
                </tr>
            @else
                <tr>
                    <td colspan="{{ $totalCols }}" style="text-align:center; padding: 20px; color:#64748b;">
                        No checklist submissions found.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>

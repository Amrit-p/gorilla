@php
    $allDates = [];
    if (isset($startDate) && $startDate && isset($endDate) && $endDate) {
        foreach (\Carbon\CarbonPeriod::create($startDate, $endDate) as $day) {
            $allDates[] = $day->toDateString();
        }
    } elseif (isset($reportData) && is_countable($reportData) && count($reportData) > 0) {
        foreach ($reportData as $row) {
            foreach (array_keys(data_get($row, 'submissions_by_date', [])) as $date) {
                $allDates[$date] = true;
            }
        }
        ksort($allDates);
        $allDates = array_keys($allDates);
    }
    $grouped       = collect($reportData ?? [])->groupBy('user_id');
    $totalCols     = 2 + count($allDates);
    $colorIndex    = 0;
    // Mower=11rem + Checklist=15rem + each date column=3.5rem
    $tableMinWidth = count($allDates) > 0
        ? 'calc(26rem + ' . count($allDates) . ' * 3.5rem)'
        : null;
@endphp

<table class="w-full table-fixed text-sm divide-y divide-slate-200" @if ($tableMinWidth) style="min-width: {{ $tableMinWidth }}" @endif>
    <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
        <tr class="divide-x divide-slate-200">
            <th class="px-4 py-3 text-center font-semibold tracking-wide bg-green-100 text-green-800 sticky left-0 z-20 w-44">Employee</th>
            <th class="bg-green-100 sticky left-44 z-20 w-60"></th>
            @if (count($allDates) > 0)
                <th colspan="{{ count($allDates) }}" class="px-4 py-3 text-center font-semibold tracking-wide bg-blue-100 text-blue-800">Submission Dates</th>
            @endif
        </tr>
        <tr class="divide-x divide-slate-200 border-t border-slate-200">
            <th class="px-4 py-2 text-left font-medium bg-green-50 text-green-700 w-44 sticky left-0 z-20">Mower</th>
            <th class="px-4 py-2 text-left font-medium bg-green-50 text-green-700 w-60 sticky left-44 z-20">Checklist</th>
            @foreach ($allDates as $date)
                <th class="px-2 py-2 text-center font-medium bg-blue-50 text-blue-700 whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($date)->format('d M') }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-200 bg-white">
        @if (isset($reportData))
            @if ($grouped->isNotEmpty())
                @foreach ($grouped as $userRows)
                    @php
                        $rowCount = count($userRows);
                        $rowBg    = ($colorIndex % 2 === 0) ? '' : 'bg-slate-50/60';
                        $stickyBg = ($colorIndex % 2 === 0) ? 'bg-white' : 'bg-slate-50';
                        $colorIndex++;
                    @endphp
                    @foreach ($userRows->values() as $rowIndex => $row)
                        <tr class="divide-x divide-slate-200 {{ $rowBg }} hover:bg-indigo-50/30 transition-colors">
                            @if ($rowIndex === 0)
                                <td rowspan="{{ $rowCount }}"
                                    class="px-4 py-3 font-semibold text-slate-800 align-middle border-r border-slate-200 truncate sticky left-0 z-10 {{ $stickyBg }}">
                                    {{ data_get($row, 'name') }}
                                </td>
                            @endif
                            <td class="px-4 py-3 text-slate-600 truncate sticky left-44 z-10 {{ $stickyBg }}">
                                {{ data_get($row, 'checklist_name') }}
                            </td>
                            @foreach ($allDates as $date)
                                @php $submitted = data_get($row, 'submissions_by_date.' . $date, false); @endphp
                                <td class="px-2 py-3 text-center">
                                    @if ($submitted)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-700 font-bold text-xs" title="Submitted">✓</span>
                                    @else
                                        <span class="text-slate-300 text-base select-none">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            @else
                <tr>
                    <td colspan="{{ $totalCols }}" class="px-4 py-8 text-center text-slate-500">No checklist submissions found for the selected filters.</td>
                </tr>
            @endif
        @else
            <tr>
                <td colspan="{{ $totalCols }}" class="px-4 py-6 text-center text-slate-500">Loading checklist report...</td>
            </tr>
        @endif
    </tbody>
</table>

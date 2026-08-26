@php
    $totals = ['total_sales' => 0, 'total_bonus' => 0, 'total_payout' => 0, 'total_hours' => 0, 'payout_count' => 0];
    foreach ($rows as $row) {
        $totals['total_sales'] += $row->total_sales;
        $totals['total_bonus'] += $row->total_bonus;
        $totals['total_payout'] += $row->total_payout;
        $totals['total_hours'] += $row->total_hours;
        $totals['payout_count'] += $row->payout_count;
    }
    $currentMonth = (int) now()->month;
    $currentYear = (int) now()->year;
@endphp

<div class="space-y-4">
    {{-- Year summary --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Total Sales</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">${{ number_format($totals['total_sales'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Total Bonus</p>
            <p class="mt-1 text-xl font-semibold text-pink-700">${{ number_format($totals['total_bonus'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <p class="text-[11px] font-medium uppercase tracking-wider text-emerald-700">Total Payout</p>
            <p class="mt-1 text-xl font-semibold text-emerald-800">${{ number_format($totals['total_payout'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Total Hours</p>
            <p class="mt-1 text-xl font-semibold text-slate-900">{{ number_format($totals['total_hours'], 2) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-3">Month</th>
                    <th class="px-5 py-3 text-right">Total Sales</th>
                    <th class="px-5 py-3 text-right">Total Bonus</th>
                    <th class="px-5 py-3 text-right">Total Payout</th>
                    <th class="px-5 py-3 text-center">Payouts</th>
                    <th class="px-5 py-3 text-right">Total Hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    @php
                        $isCurrent = $row->month === $currentMonth && $row->year === $currentYear;
                        $isEmpty = $row->total_sales == 0 && $row->total_payout == 0;
                    @endphp
                    <tr class="salary-month-row border-b border-slate-100 transition-colors hover:bg-indigo-50/40 {{ $isCurrent ? 'bg-indigo-50/60' : '' }} {{ $isEmpty ? 'text-slate-400' : '' }}"
                        data-month="{{ $row->month }}">
                        <td class="px-5 py-3">
                            <button type="button"
                                    class="salary-month-cell group inline-flex items-center gap-2 font-semibold {{ $isEmpty ? 'text-slate-400' : 'text-slate-800' }} hover:text-indigo-700"
                                    data-mower-id="{{ $row->mower_id }}"
                                    data-mower-name="{{ $row->mower_name }}"
                                    data-year="{{ $row->year }}"
                                    data-month="{{ $row->month }}">
                                <svg class="salary-month-chevron h-4 w-4 shrink-0 text-slate-400 transition-transform group-hover:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                </svg>
                                {{ $row->month_label }}
                                @if ($isCurrent)
                                    <span class="rounded-full bg-indigo-600 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white">Now</span>
                                @endif
                            </button>
                        </td>
                        <td class="px-5 py-3 text-right tabular-nums">${{ number_format($row->total_sales, 2) }}</td>
                        <td class="px-5 py-3 text-right tabular-nums {{ $row->total_bonus > 0 ? 'text-pink-700' : '' }}">${{ number_format($row->total_bonus, 2) }}</td>
                        <td class="px-5 py-3 text-right font-semibold tabular-nums {{ $row->total_payout > 0 ? 'text-emerald-700' : '' }}">
                            @if ($row->payout_count > 0)
                                <button type="button"
                                        class="salary-payouts-cell rounded transition-colors hover:text-emerald-900 hover:underline"
                                        data-mower-id="{{ $row->mower_id }}"
                                        data-mower-name="{{ $row->mower_name }}"
                                        data-year="{{ $row->year }}"
                                        data-month="{{ $row->month }}"
                                        title="View payouts for {{ $row->month_label }}">${{ number_format($row->total_payout, 2) }}</button>
                            @else
                                ${{ number_format($row->total_payout, 2) }}
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center tabular-nums">
                            @if ($row->payout_count > 0)
                                <button type="button"
                                        class="salary-payouts-cell inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 transition-colors hover:bg-indigo-100"
                                        data-mower-id="{{ $row->mower_id }}"
                                        data-mower-name="{{ $row->mower_name }}"
                                        data-year="{{ $row->year }}"
                                        data-month="{{ $row->month }}"
                                        title="View payouts for {{ $row->month_label }}">
                                    {{ $row->payout_count }}
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                    </svg>
                                </button>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ number_format($row->total_hours, 2) }}</td>
                    </tr>
                    {{-- The month's job sub-table is loaded into this row, directly under its month. --}}
                    <tr class="salary-jobs-row hidden" data-month="{{ $row->month }}">
                        <td colspan="6" class="border-b border-slate-200 bg-slate-50/60 p-0">
                            <div class="salary-jobs-slot px-4 py-4"></div>
                        </td>
                    </tr>
                @endforeach
                <tr class="bg-slate-800 text-white">
                    <td class="px-5 py-3 text-xs font-bold uppercase tracking-wider">Year total</td>
                    <td class="px-5 py-3 text-right font-semibold tabular-nums">${{ number_format($totals['total_sales'], 2) }}</td>
                    <td class="px-5 py-3 text-right font-semibold tabular-nums">${{ number_format($totals['total_bonus'], 2) }}</td>
                    <td class="px-5 py-3 text-right font-semibold tabular-nums">${{ number_format($totals['total_payout'], 2) }}</td>
                    <td class="px-5 py-3 text-center font-semibold tabular-nums">{{ $totals['payout_count'] }}</td>
                    <td class="px-5 py-3 text-right font-semibold tabular-nums">{{ number_format($totals['total_hours'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

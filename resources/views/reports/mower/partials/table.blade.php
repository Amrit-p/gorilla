<div class="overflow-x-auto rounded-lg border border-slate-200">
    <table class="w-full text-sm divide-y divide-slate-200">
        @php
            $showBonus = !($hideBonusColumn ?? false);
            $canEditPayout = $canEditPayout ?? false;
            $periodStart = $periodStart ?? null;
            $periodEnd = $periodEnd ?? null;
            $colspan = ($showBonus ? 6 : 5) + 1;
        @endphp
        <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
            <tr class="divide-x divide-slate-200">
                <th colspan="3" class="px-4 py-3 text-center font-semibold tracking-wide bg-green-100 text-green-800">Mower</th>
                <th colspan="1" class="px-4 py-3 text-center font-semibold tracking-wide bg-yellow-100 text-yellow-800">Jobs</th>
                <th colspan="1" class="px-4 py-3 text-center font-semibold tracking-wide bg-blue-100 text-blue-800">Total Sale</th>
                @if($showBonus)
                    <th colspan="1" class="px-4 py-3 text-center font-semibold tracking-wide bg-pink-100 text-pink-800">Bonus</th>
                @endif
                <th colspan="1" class="px-4 py-3 text-center font-semibold tracking-wide bg-emerald-100 text-emerald-800">Payout</th>
            </tr>
            <tr class="divide-x divide-slate-200 border-t border-slate-200">
                {{-- Mower --}}
                <th class="px-4 py-2 text-left font-medium bg-green-50 text-green-700">Name</th>
                <th class="px-4 py-2 text-center font-medium bg-green-50 text-green-700">Total Hours</th>
                <th class="px-4 py-2 text-center font-medium bg-green-50 text-green-700">Working Days</th>
                {{-- Jobs --}}
                <th class="px-4 py-2 text-center font-medium bg-yellow-50 text-yellow-700">Completed</th>
                {{-- Earnings --}}
                <th class="px-4 py-2 text-right font-medium bg-blue-50 text-blue-700">Completed</th>
                {{-- Bonus --}}
                @if($showBonus)
                    <th class="px-4 py-2 text-right font-medium bg-pink-50 text-pink-700">Bonus</th>
                @endif
                <th class="px-4 py-2 text-right font-medium bg-emerald-50 text-emerald-700">
                    {{ $canEditPayout ? 'Manual / Auto' : 'Amount' }}
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            @if (isset($reportData))
                @if (is_countable($reportData) && count($reportData) > 0)
                    @php
                        $totals = [
                            'total_working_hours'  => 0,
                            'working_days'         => 0,
                            'total_jobs_completed' => 0,
                            'completed_earnings'   => 0,
                            'bonus'                => 0,
                            'payout'               => 0,
                        ];
                    @endphp
                    @foreach ($reportData as $mower)
                        @php
                            foreach (array_keys($totals) as $k) {
                                $totals[$k] += data_get($mower, $k, 0);
                            }
                            $userId = data_get($mower, 'user_id');
                            $payout = (float) data_get($mower, 'payout', 0);
                            $calculatedPayout = (float) data_get($mower, 'calculated_payout', 0);
                            $payoutIsManual = (bool) data_get($mower, 'payout_is_manual', false);
                        @endphp
                        <tr class="divide-x divide-slate-200">
                            {{-- Mower --}}
                            <td class="px-4 py-3 font-medium">{{ data_get($mower, 'name') }}</td>
                            <td class="px-4 py-3 text-center {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-green-50 hover:text-green-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-status="completed"
                                data-label="Total Hours">
                                {{ number_format((float) data_get($mower, 'total_working_hours', 0), 2) }}
                            </td>
                            <td class="px-4 py-3 text-center {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-green-50 hover:text-green-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-status="completed"
                                data-label="Working Days">
                                {{ data_get($mower, 'working_days', 0) }}
                            </td>
                            {{-- Jobs --}}
                            <td class="px-4 py-3 text-center {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-yellow-50 hover:text-yellow-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-status="completed"
                                data-label="Completed Jobs">
                                {{ data_get($mower, 'total_jobs_completed') }}
                            </td>
                            {{-- Earnings --}}
                            <td class="px-4 py-3 text-right {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-blue-50 hover:text-blue-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-status="completed"
                                data-label="Completed Earnings">
                                ${{ number_format((float) data_get($mower, 'completed_earnings', 0), 2) }}
                            </td>
                            {{-- Bonus --}}
                            @if($showBonus)
                                <td class="px-4 py-3 text-right {{ $userId ? 'mower-bonus-cell cursor-pointer hover:bg-pink-50 hover:text-pink-700' : '' }}"
                                    data-user-id="{{ $userId }}"
                                    data-mower-name="{{ data_get($mower, 'name') }}">
                                    ${{ number_format((float) data_get($mower, 'bonus', 0), 2) }}
                                </td>
                            @endif
                            {{-- Payout --}}
                            <td class="px-4 py-3 text-right bg-emerald-50/40">
                                @if ($canEditPayout && $userId)
                                    <div class="flex items-center justify-end gap-1">
                                        <span class="text-slate-500">$</span>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="mower-payout-input w-24 rounded border border-emerald-300 bg-white px-2 py-1 text-right text-sm font-semibold text-emerald-800 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                            value="{{ number_format($payout, 2, '.', '') }}"
                                            data-user-id="{{ $userId }}"
                                            data-calculated="{{ number_format($calculatedPayout, 2, '.', '') }}"
                                            data-start-date="{{ $periodStart }}"
                                            data-end-date="{{ $periodEnd }}"
                                            title="{{ $payoutIsManual ? 'Manual payout (calculated: $'.number_format($calculatedPayout, 2).')' : 'Auto: incentive + bonus' }}"
                                        >
                                    </div>
                                    @if ($payoutIsManual)
                                        <p class="mt-1 text-[10px] font-medium text-emerald-700">Manual</p>
                                    @else
                                        <p class="mt-1 text-[10px] text-slate-400">Auto</p>
                                    @endif
                                @else
                                    <span class="font-semibold text-emerald-800">${{ number_format($payout, 2) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr class="divide-x divide-slate-200 bg-slate-800 text-white font-semibold">
                        <td class="px-4 py-3">TOTALS</td>
                        <td class="px-4 py-3 text-center">{{ number_format($totals['total_working_hours'], 2) }}</td>
                        <td class="px-4 py-3 text-center">{{ $totals['working_days'] }}</td>
                        <td class="px-4 py-3 text-center">{{ $totals['total_jobs_completed'] }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format($totals['completed_earnings'], 2) }}</td>
                        @if($showBonus)
                            <td class="px-4 py-3 text-right">${{ number_format($totals['bonus'], 2) }}</td>
                        @endif
                        <td class="px-4 py-3 text-right">${{ number_format($totals['payout'], 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="{{ $colspan }}" class="px-4 py-8 text-center text-slate-500">No mower reports available.</td>
                    </tr>
                @endif
            @else
                <tr>
                    <td colspan="{{ $colspan }}" class="px-4 py-6 text-center text-slate-500">Loading mower reports...</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

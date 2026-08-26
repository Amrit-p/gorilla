<div class="overflow-x-auto rounded-lg border border-slate-200">
    <table class="w-full text-sm divide-y divide-slate-200">
        @php
            $showBonus = !($hideBonusColumn ?? false);
            $showSales = !($hideSalesColumn ?? false);
            $colspan = 5 + ($showBonus ? 1 : 0) + ($showSales ? 1 : 0);
        @endphp
        <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
            <tr class="divide-x divide-slate-200">
                <th colspan="3" class="px-4 py-3 text-center font-semibold tracking-wide bg-green-100 text-green-800">Mower</th>
                <th colspan="1" class="px-4 py-3 text-center font-semibold tracking-wide bg-yellow-100 text-yellow-800">Jobs</th>
                @if($showSales)
                    <th colspan="1" class="px-4 py-3 text-center font-semibold tracking-wide bg-blue-100 text-blue-800">Total Sale</th>
                @endif
                <th colspan="{{ $showBonus ? 2 : 1 }}" class="px-4 py-3 text-center font-semibold tracking-wide bg-purple-100 text-purple-800">Payout</th>
            </tr>
            <tr class="divide-x divide-slate-200 border-t border-slate-200">
                {{-- Mower --}}
                <th class="px-4 py-2 text-left font-medium bg-green-50 text-green-700">Name</th>
                <th class="px-4 py-2 text-center font-medium bg-green-50 text-green-700">Total Hours</th>
                <th class="px-4 py-2 text-center font-medium bg-green-50 text-green-700">Working Days</th>
                {{-- Jobs --}}
                <th class="px-4 py-2 text-center font-medium bg-yellow-50 text-yellow-700">Completed</th>
                {{-- Earnings --}}
                @if($showSales)
                    <th class="px-4 py-2 text-right font-medium bg-blue-50 text-blue-700">Completed</th>
                @endif
                {{-- Payout --}}
                <th class="px-4 py-2 text-right font-medium bg-purple-50 text-purple-700">Payout</th>
                @if($showBonus)
                    <th class="px-4 py-2 text-right font-medium bg-purple-50 text-purple-700">Bonus</th>
                @endif
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
                            'payout'               => 0,
                            'bonus'                => 0,
                        ];
                    @endphp
                    @foreach ($reportData as $mower)
                        @php
                            foreach (array_keys($totals) as $k) {
                                $totals[$k] += data_get($mower, $k, 0);
                            }
                            $userId = data_get($mower, 'user_id');
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
                            @if($showSales)
                                <td class="px-4 py-3 text-right {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-blue-50 hover:text-blue-700' : '' }}"
                                    data-user-id="{{ $userId }}"
                                    data-mower-name="{{ data_get($mower, 'name') }}"
                                    data-status="completed"
                                    data-label="Completed Earnings">
                                    ${{ number_format((float) data_get($mower, 'completed_earnings', 0), 2) }}
                                </td>
                            @endif
                            {{-- Payout --}}
                            <td class="px-4 py-3 text-right {{ $userId ? 'mower-payout-cell cursor-pointer hover:bg-purple-50 hover:text-purple-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                title="View the payouts behind this total">
                                ${{ number_format((float) data_get($mower, 'payout', 0), 2) }}
                            </td>
                            @if($showBonus)
                                <td class="px-4 py-3 text-right {{ $userId ? 'mower-payout-cell cursor-pointer hover:bg-purple-50 hover:text-purple-700' : '' }}"
                                    data-user-id="{{ $userId }}"
                                    data-mower-name="{{ data_get($mower, 'name') }}"
                                    title="View the payouts behind this total">
                                    ${{ number_format((float) data_get($mower, 'bonus', 0), 2) }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    <tr class="divide-x divide-slate-200 bg-slate-800 text-white font-semibold">
                        <td class="px-4 py-3">TOTALS</td>
                        <td class="px-4 py-3 text-center">{{ number_format($totals['total_working_hours'], 2) }}</td>
                        <td class="px-4 py-3 text-center">{{ $totals['working_days'] }}</td>
                        <td class="px-4 py-3 text-center">{{ $totals['total_jobs_completed'] }}</td>
                        @if($showSales)
                            <td class="px-4 py-3 text-right">${{ number_format($totals['completed_earnings'], 2) }}</td>
                        @endif
                        <td class="px-4 py-3 text-right">${{ number_format($totals['payout'], 2) }}</td>
                        @if($showBonus)
                            <td class="px-4 py-3 text-right">${{ number_format($totals['bonus'], 2) }}</td>
                        @endif
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

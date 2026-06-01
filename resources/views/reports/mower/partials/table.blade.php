<div class="overflow-x-auto rounded-lg border border-slate-200">
    <table class="w-full text-sm divide-y divide-slate-200">
        <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
            <tr class="divide-x divide-slate-200">
                <th colspan="2" class="px-4 py-3 text-center font-semibold tracking-wide bg-green-100 text-green-800">Mower</th>
                <th colspan="3" class="px-4 py-3 text-center font-semibold tracking-wide bg-yellow-100 text-yellow-800">Jobs</th>
                <th colspan="4" class="px-4 py-3 text-center font-semibold tracking-wide bg-blue-100 text-blue-800">Earnings</th>
                <th colspan="2" class="px-4 py-3 text-center font-semibold tracking-wide bg-pink-100 text-pink-800">Commission</th>
            </tr>
            <tr class="divide-x divide-slate-200 border-t border-slate-200">
                {{-- Mower --}}
                <th class="px-4 py-2 text-left font-medium bg-green-50 text-green-700">Name</th>
                <th class="px-4 py-2 text-center font-medium bg-green-50 text-green-700">Total Hours</th>
                {{-- Jobs --}}
                <th class="px-4 py-2 text-center font-medium bg-yellow-50 text-yellow-700">Completed</th>
                <th class="px-4 py-2 text-center font-medium bg-yellow-50 text-yellow-700">Started</th>
                <th class="px-4 py-2 text-center font-medium bg-yellow-50 text-yellow-700">Pending</th>
                {{-- Earnings --}}
                <th class="px-4 py-2 text-right font-medium bg-blue-50 text-blue-700">Completed</th>
                <th class="px-4 py-2 text-right font-medium bg-blue-50 text-blue-700">Started</th>
                <th class="px-4 py-2 text-right font-medium bg-blue-50 text-blue-700">Pending</th>
                <th class="px-4 py-2 text-right font-medium bg-blue-50 text-blue-700">Total</th>
                {{-- Commission --}}
                <th class="px-4 py-2 text-right font-medium bg-pink-50 text-pink-700">Total Sales</th>
                <th class="px-4 py-2 text-right font-medium bg-pink-50 text-pink-700">Commission</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            @if (isset($reportData))
                @if (is_countable($reportData) && count($reportData) > 0)
                    @foreach ($reportData as $mower)
                        @php $userId = data_get($mower, 'user_id'); @endphp
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
                            {{-- Jobs --}}
                            <td class="px-4 py-3 text-center {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-yellow-50 hover:text-yellow-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-status="completed"
                                data-label="Completed Jobs">
                                {{ data_get($mower, 'total_jobs_completed') }}
                            </td>
                            <td class="px-4 py-3 text-center {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-yellow-50 hover:text-yellow-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-status="started"
                                data-label="Started Jobs">
                                {{ data_get($mower, 'total_jobs_started') }}
                            </td>
                            <td class="px-4 py-3 text-center {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-yellow-50 hover:text-yellow-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-status="hold"
                                data-label="Pending Jobs">
                                {{ data_get($mower, 'total_jobs_pending') }}
                            </td>
                            {{-- Earnings --}}
                            <td class="px-4 py-3 text-right {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-blue-50 hover:text-blue-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-status="completed"
                                data-label="Completed Earnings">
                                ${{ number_format((float) data_get($mower, 'completed_earnings', 0), 2) }}
                            </td>
                            <td class="px-4 py-3 text-right {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-blue-50 hover:text-blue-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-status="started"
                                data-label="Started Earnings">
                                ${{ number_format((float) data_get($mower, 'started_earnings', 0), 2) }}
                            </td>
                            <td class="px-4 py-3 text-right {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-blue-50 hover:text-blue-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-status="hold"
                                data-label="Pending Earnings">
                                ${{ number_format((float) data_get($mower, 'pending_earnings', 0), 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-blue-50 hover:text-blue-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-label="All Jobs">
                                ${{ number_format((float) data_get($mower, 'total_earnings', 0), 2) }}
                            </td>
                            {{-- Commission --}}
                            <td class="px-4 py-3 text-right {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-pink-50 hover:text-pink-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-payment-status="received"
                                data-label="Total Sales">
                                ${{ number_format((float) data_get($mower, 'total_sales', 0), 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold {{ $userId ? 'mower-stat-cell cursor-pointer hover:bg-pink-50 hover:text-pink-700' : '' }}"
                                data-user-id="{{ $userId }}"
                                data-mower-name="{{ data_get($mower, 'name') }}"
                                data-payment-status="received"
                                data-label="Commission">
                                ${{ number_format((float) data_get($mower, 'total_incentive_amount', 0), 2) }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-slate-500">No mower reports available.</td>
                    </tr>
                @endif
            @else
                <tr>
                    <td colspan="11" class="px-4 py-6 text-center text-slate-500">Loading mower reports...</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

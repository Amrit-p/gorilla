{{--
    Read-only list of the jobs a single payout settles, shown in the nested
    drill-down opened from the "Jobs" count inside the payouts modal.
--}}
@php
    $totalCharges = 0;
    $totalMinutes = 0;
    foreach ($jobs as $job) {
        $totalCharges += (float) ($job->charges ?? 0);
        $totalMinutes += (int) ($job->consumed_time_minutes ?? 0);
    }
@endphp

@if ($jobs->isEmpty())
    <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-400">
        This payout has no linked jobs.
    </div>
@else
    <div class="overflow-hidden rounded-xl border border-slate-200">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Services</th>
                    <th class="px-4 py-3 text-right">Charges</th>
                    <th class="px-4 py-3 text-right">Hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jobs as $job)
                    <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/60">
                        <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                            {{ optional($job->scheduled_date)->format('d M Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $job->customer_name ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ is_array($job->required_services) ? implode(', ', $job->required_services) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-slate-800">${{ number_format((float) $job->charges, 2) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-slate-600">{{ number_format($job->consumed_time_minutes / 60, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="bg-slate-50 font-semibold text-slate-700">
                    <td class="px-4 py-3" colspan="3">Total &middot; {{ $jobs->count() }} {{ \Illuminate\Support\Str::plural('job', $jobs->count()) }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">${{ number_format($totalCharges, 2) }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totalMinutes / 60, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endif

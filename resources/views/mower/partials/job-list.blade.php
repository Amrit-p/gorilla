@php
    use Carbon\Carbon;

    $thisWeekStart = Carbon::today()->startOfWeek(Carbon::MONDAY);

    // Group jobs by their Mon–Sun week start date (as a string key for groupBy)
    $grouped = $jobs->groupBy(fn ($job) =>
        Carbon::parse($job->scheduled_date)->startOfWeek(Carbon::MONDAY)->toDateString()
    );

    // Sort weeks most-recent first
    $grouped = $grouped->sortKeysDesc();

    $weekLabel = function (string $weekStartDate) use ($thisWeekStart): string {
        $start = Carbon::parse($weekStartDate);
        $end   = $start->copy()->endOfWeek(Carbon::SUNDAY);

        if ($start->eq($thisWeekStart)) {
            return 'This Week';
        }

        // Format: "25 May, 2026 – 31 May, 2026"
        $fmt = fn (Carbon $d) => $d->format('j M, Y');

        return $fmt($start) . ' – ' . $fmt($end);
    };
@endphp

@if ($grouped->isEmpty())
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
        No jobs in this list.
    </div>
@else
    <div class="space-y-5">
        @foreach ($grouped as $weekStart => $weekJobs)
            <div>
                <div class="mb-2 flex items-center gap-2 px-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        {{ $weekLabel($weekStart) }}
                    </span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">
                        {{ $weekJobs->count() }}
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach ($weekJobs as $job)
                        <a
                            href="{{ route('mower.jobs.show', $job) }}"
                            class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm active:bg-slate-50"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900">
                                        @if ($job->client?->customer_unique_id)
                                            #{{ $job->client->customer_unique_id }}
                                        @endif
                                        {{ $job->client?->name ?: 'Customer' }}
                                    </p>
                                    <p class="mt-1 truncate text-sm text-slate-500">{{ $job->client_address }}</p>
                                    <p class="mt-2 text-xs text-slate-600">
                                        {{ optional($job->scheduled_date)->format('M j') }}
                                        {{ $job->scheduled_time ? '• ' . \Illuminate\Support\Carbon::parse($job->scheduled_time)->format('g:i A') : '' }}
                                        @if ($job->estimated_duration_minutes)
                                            • {{ $job->estimated_duration_minutes }} min est.
                                        @endif
                                    </p>
                                </div>
                                <x-jobs.status-badge :status="$job->status" />
                            </div>
                            @if ($job->payment_status)
                                <p class="mt-2 text-xs text-slate-500">Payment: {{ $job->payment_status }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif

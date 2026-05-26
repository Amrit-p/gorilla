<div class="space-y-3">
    @forelse ($jobs as $job)
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
                        {{ $job->scheduled_time ? '• '.\Illuminate\Support\Carbon::parse($job->scheduled_time)->format('g:i A') : '' }}
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
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
            No jobs in this list.
        </div>
    @endforelse
</div>

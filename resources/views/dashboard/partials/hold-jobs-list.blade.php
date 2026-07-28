@if ($jobs->isEmpty())
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
        No jobs on hold.
    </div>
@else
    <div class="space-y-2">
        @foreach ($jobs as $job)
            @php
                $mowerNames = $job->assignedEmployees->pluck('name')
                    ->push($job->doneByUser?->name)
                    ->filter()
                    ->unique();
            @endphp
            <div
                class="hold-job-card select-none rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm transition-colors hover:bg-slate-50"
                data-job-id="{{ $job->id }}"
                @can('view-jobs') data-href="{{ route('admin.jobs.show', $job) }}" @endcan
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold leading-tight text-slate-900">
                            {{ $job->customerDisplayName() }}
                        </p>
                        <p class="mt-0.5 truncate text-xs leading-tight text-slate-500">{{ $job->client_address }}</p>
                        <p class="mt-1 text-xs leading-tight text-slate-600">
                            {{ optional($job->scheduled_date)->format('M j, Y') }}
                            {{ $job->scheduled_time ? '• ' . \Illuminate\Support\Carbon::parse($job->scheduled_time)->format('g:i A') : '' }}
                            @if ($job->estimated_duration_minutes)
                                • {{ $job->estimated_duration_minutes }} min est.
                            @endif
                        </p>
                    </div>
                    <x-jobs.status-badge :status="$job->status" />
                </div>
                <p class="mt-1 truncate text-xs leading-tight text-slate-500">
                    Assigned: {{ $mowerNames->isNotEmpty() ? $mowerNames->join(', ') : 'Unassigned' }}
                    @if ($job->payment_status)
                        <span class="text-slate-400">•</span> Payment: {{ $job->payment_status }}
                    @endif
                </p>
                @can('assign-jobs')
                    <div class="mt-2 flex justify-end border-t border-slate-100 pt-1.5">
                        <x-jobs.reschedule-button :job="$job" class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-1 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-50">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                            </svg>
                            Reschedule
                        </x-jobs.reschedule-button>
                    </div>
                @endcan
            </div>
        @endforeach
    </div>
@endif

@php
    $showEmptyState = $showEmptyState ?? true;
@endphp

{{-- Mobile job cards — search-results view for the mobile search modal. --}}
@forelse ($jobs as $job)
    <div class="job-card mb-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <a href="{{ route('admin.jobs.show', $job) }}"
                   data-job-id="{{ $job->id }}"
                   class="js-open-customer-details block truncate text-[15px] font-semibold leading-snug text-slate-900">
                    {{ $job->customerDisplayName() }}
                </a>
                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $job->client_address ?: '—' }}</p>
            </div>
            <div class="shrink-0"><x-jobs.status-badge :status="$job->status" /></div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-slate-600">
            <span class="inline-flex items-center gap-1">
                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
                {{ optional($job->scheduled_date)->format('d M Y') ?: 'No date' }}
            </span>
            @if ($job->zone)
                <span class="inline-flex items-center gap-1">
                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                    </svg>
                    {{ $job->zone->name }}
                </span>
            @endif
            @if ($job->phone)
                <a href="tel:{{ $job->phone }}" class="inline-flex items-center gap-1 text-emerald-700" onclick="event.stopPropagation()">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                    </svg>
                    {{ $job->phone }}
                </a>
            @endif
            @if ($job->charges !== null)
                <span class="inline-flex items-center gap-1 font-medium text-emerald-600">
                    ${{ number_format($job->charges, 2) }}
                </span>
            @endif
        </div>

        @if ($job->doneByUser || $job->assignedEmployees->isNotEmpty())
            <p class="mt-2 truncate text-xs text-slate-500">
                <span class="text-slate-400">Mower:</span>
                {{ $job->doneByUser?->name ?: $job->assignedEmployees->pluck('name')->join(', ') }}
            </p>
        @endif

        <div class="mt-3 flex items-center gap-1.5 border-t border-slate-100 pt-3">
            <a href="{{ route('admin.jobs.show', $job) }}"
               data-job-id="{{ $job->id }}"
               class="js-open-customer-details flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-slate-50 px-2 py-2 text-xs font-medium text-slate-700 active:bg-slate-100">
                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
                Details
            </a>
            @can('update', $job)
                <a href="{{ route('admin.jobs.edit', $job) }}"
                   class="js-with-return-url flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-slate-50 px-2 py-2 text-xs font-medium text-slate-700 active:bg-slate-100">
                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                    </svg>
                    Edit
                </a>
            @endcan
            <x-jobs.reschedule-button :job="$job" class="flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-slate-50 px-2 py-2 text-xs font-medium text-slate-700 active:bg-slate-100">
                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
                Reschedule
            </x-jobs.reschedule-button>
            @can('manage-job-records')
                <button type="button" class="delete-job flex shrink-0 items-center justify-center rounded-lg bg-red-50 px-2.5 py-2 text-red-600 active:bg-red-100" data-id="{{ $job->id }}" aria-label="Delete job">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                    </svg>
                </button>
            @endcan
        </div>
    </div>
@empty
    @if ($showEmptyState)
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <p class="mt-3 text-sm font-medium text-slate-500">No jobs found</p>
            <p class="text-xs text-slate-400">Try a different name, phone, or address.</p>
        </div>
    @endif
@endforelse

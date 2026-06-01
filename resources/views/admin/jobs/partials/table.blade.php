@php
    $canReorder = auth()->user()?->can('manage-job-records');
    $headers = ['Customer / Address', 'Zone', 'Schedule', 'Services', 'Recurrence', 'Payment', 'Mowers', 'Actions'];
    if ($canReorder) {
        array_unshift($headers, '');
    }
@endphp

<x-ui.table :headers="$headers">
    @forelse ($jobs as $job)
        <tr class="job-row group divide-x divide-slate-100 transition-colors hover:bg-slate-50/70" data-job-id="{{ $job->id }}">

            @if ($canReorder)
            {{-- Drag handle --}}
            <td class="w-8 px-2 py-4">
                <button type="button" class="drag-handle flex cursor-grab items-center justify-center rounded p-1 text-slate-300 transition-colors hover:bg-slate-100 hover:text-slate-500 active:cursor-grabbing" aria-label="Drag to reorder">
                    <svg class="h-4 w-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <circle cx="5.5" cy="3.5" r="1.25"/>
                        <circle cx="5.5" cy="8" r="1.25"/>
                        <circle cx="5.5" cy="12.5" r="1.25"/>
                        <circle cx="10.5" cy="3.5" r="1.25"/>
                        <circle cx="10.5" cy="8" r="1.25"/>
                        <circle cx="10.5" cy="12.5" r="1.25"/>
                    </svg>
                </button>
                <span class="mt-0.5 block text-center text-xs font-medium tabular-nums text-slate-400 opacity-0 transition-opacity group-hover:opacity-100">
                    {{ $job->numeric_priority ?? '—' }}
                </span>
            </td>
            @endif

            {{-- Customer --}}
            <td class="px-4 py-4">
                <a href="{{ route('admin.jobs.show', $job) }}" class="text-sm font-semibold leading-snug text-slate-800 hover:text-emerald-700 hover:underline">
                    {{ $job->client?->name ?: 'N/A' }}
                    @if ($job->client?->customer_unique_id)
                        <span class="font-normal text-slate-400">#{{ $job->client->customer_unique_id }}</span>
                    @endif
                </a>
                <p class="mt-0.5 text-xs text-slate-500">{{ $job->client_address ?: '—' }}</p>
            </td>

            {{-- Zone --}}
            <td class="whitespace-nowrap px-4 py-4">
                @if ($job->zone)
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                        </svg>
                        <span class="text-sm text-slate-600">{{ $job->zone->name }}</span>
                    </div>
                @else
                    <span class="text-sm text-slate-400">—</span>
                @endif
            </td>

            {{-- Schedule --}}
            <td class="whitespace-nowrap px-4 py-4">
                <p class="text-sm text-slate-700">{{ optional($job->scheduled_date)->format('d M Y') ?: '—' }}</p>
                <p class="text-xs text-slate-500">
                    {{ $job->scheduled_time ? \Illuminate\Support\Carbon::parse($job->scheduled_time)->format('h:i A') : '' }}
                    @if ($job->estimated_duration_minutes)
                        &bull; {{ $job->estimated_duration_minutes }} min
                    @endif
                </p>
                <div class="mt-1"><x-jobs.status-badge :status="$job->status" /></div>
            </td>

            {{-- Services --}}
            <td class="px-4 py-4">
                <p class="text-sm text-slate-700">{{ is_array($job->required_services) ? implode(', ', $job->required_services) : '—' }}</p>
                @if ($job->parking_status)
                    <span class="mt-0.5 inline-block rounded-md bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">{{ $job->parking_status }}</span>
                @endif
            </td>

            {{-- Recurrence --}}
            <td class="whitespace-nowrap px-4 py-4">
                <span class="text-sm text-slate-700">{{ $job->recurrence?->name ?? '—' }}</span>
            </td>

            {{-- Payment --}}
            <td class="whitespace-nowrap px-4 py-4">
                <p class="text-sm text-slate-700">{{ $job->payment_mode ?: '—' }}</p>
                @if ($job->payment_status)
                    <span class="text-xs text-slate-500">{{ $job->payment_status }}</span>
                @endif
                @if ($job->charges !== null)
                    <p class="text-xs font-medium text-emerald-600">${{ number_format($job->charges, 2) }}</p>
                @endif
            </td>

            {{-- Mowers --}}
            <td class="whitespace-nowrap px-4 py-4">
                <p class="text-sm text-slate-700">{{ $job->doneByUser?->name ?: '—' }}</p>
                @if ($job->assignedEmployees->isNotEmpty())
                    <p class="text-xs text-slate-500">{{ $job->assignedEmployees->pluck('name')->join(', ') }}</p>
                @endif
            </td>

            {{-- Actions --}}
            <td class="whitespace-nowrap px-4 py-4">
                <div class="relative inline-block">
                    <button class="job-actions-btn inline-flex items-center justify-center rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 focus:outline-none" data-id="{{ $job->id }}" aria-label="Actions">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM12 13.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM12 21a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                        </svg>
                    </button>
                    <div class="job-actions-menu hidden w-44 rounded-xl border border-slate-200 bg-white py-1 shadow-xl ring-1 ring-slate-900/5">
                        <a href="{{ route('admin.jobs.show', $job) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            View
                        </a>
                        @can('manage-job-records')
                            <a href="{{ route('admin.jobs.edit', $job) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                                </svg>
                                Edit
                            </a>
                        @endcan
                        @can('assign-jobs')
                            <button class="assign-job flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50" data-id="{{ $job->id }}">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                                </svg>
                                Assign
                            </button>
                            <button class="status-job flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50" data-id="{{ $job->id }}" data-status="{{ $job->status }}">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                                Status
                            </button>
                        @endcan
                        @can('manage-job-records')
                            <div class="my-1 border-t border-slate-100"></div>
                            <button class="delete-job flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-red-600 transition-colors hover:bg-red-50" data-id="{{ $job->id }}">
                                <svg class="h-3.5 w-3.5 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                </svg>
                                Delete
                            </button>
                        @endcan
                    </div>
                </div>
            </td>

        </tr>
    @empty
        <tr>
            <td colspan="{{ $canReorder ? 9 : 8 }}" class="px-4 py-10 text-center text-sm text-slate-400">No jobs found.</td>
        </tr>
    @endforelse
</x-ui.table>
<div class="mt-4">{{ $jobs->links() }}</div>

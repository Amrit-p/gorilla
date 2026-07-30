@php
    $viewingDeleted =
        request('list_scope') === \App\Support\CrmConstants::JOB_LIST_SCOPE_DELETED &&
        (auth()->user()?->hasRole(\App\Support\CrmRoles::OFFICE_MANAGER) ?? false);
    $canReorder = auth()->user()?->can('manage-job-records') && ! $viewingDeleted;
@endphp

<x-jobs.bulk-toolbar :viewing-deleted="$viewingDeleted" />

<div class="mb-4">{{ $jobs->links() }}</div>

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full text-left text-sm">
        <thead>
            {{-- Grouped header row --}}
            <tr class="divide-x divide-slate-200">
                <th rowspan="2" class="w-8 px-2 py-3 text-center align-middle bg-slate-100">
                    <input id="job-select-all" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" aria-label="Select all jobs">
                </th>
                @if ($canReorder)
                    <th rowspan="2" class="w-8 px-2 py-3 bg-slate-100"></th>
                @endif
                <th colspan="2" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide bg-green-100 text-green-800">Contact</th>
                <th colspan="4" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide bg-yellow-100 text-yellow-800">Schedule</th>
                <th colspan="2" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide bg-blue-100 text-blue-800">Payment &amp; Crew</th>
                <th colspan="1" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide bg-purple-100 text-purple-800">Notes</th>
                <th colspan="1" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide bg-slate-100 text-slate-700">Actions</th>
            </tr>
            {{-- Sub-header row --}}
            <tr class="divide-x divide-slate-200 border-t border-slate-200">
                {{-- Contact --}}
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider bg-green-50 text-green-700">Name / Address</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider bg-green-50 text-green-700">Zone</th>
                {{-- Schedule --}}
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider bg-yellow-50 text-yellow-700">Date &amp; Status</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider bg-yellow-50 text-yellow-700">Services</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider bg-yellow-50 text-yellow-700">Recurrence</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider bg-yellow-50 text-yellow-700">Last Job</th>
                {{-- Payment & Crew --}}
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider bg-blue-50 text-blue-700">Payment</th>
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider bg-blue-50 text-blue-700">Mowers</th>
                {{-- Notes --}}
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider bg-purple-50 text-purple-700">Remarks</th>
                {{-- Actions --}}
                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider bg-slate-50 text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
    @forelse ($jobs as $job)
        <tr class="job-row group divide-x divide-slate-100 transition-colors data-[selected=true]:bg-emerald-50/70 data-[selected=true]:shadow-[inset_3px_0_0_#10b981] data-[bulk-mode=true]:cursor-pointer {{ job_row_color_class($job) }}" data-job-id="{{ $job->id }}" data-est-minutes="{{ $job->estimated_duration_minutes ?? '' }}" data-selected="false" data-bulk-mode="false">

                <td class="w-8 px-2 py-4">
                <input type="checkbox" class="job-select-checkbox h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-job-id="{{ $job->id }}" aria-label="Select job">
            </td>

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
                @if ($job->trashed())
                    <span class="text-sm font-semibold leading-snug text-slate-800">
                        {{ $job->customerDisplayName() }}
                    </span>
                @else
                <a href="{{ route('admin.jobs.show', $job) }}" class="text-sm font-semibold leading-snug text-slate-800 hover:text-emerald-700 hover:underline">
                    {{ $job->customerDisplayName() }}
                </a>
                @endif
                <p class="mt-0.5 text-xs text-slate-500">{{ $job->client_address ?: '—' }}</p>
                @if ($job->phone || $job->email)
                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ collect([$job->phone, $job->email])->filter()->implode(' · ') }}
                    </p>
                @endif
                @if ($job->equipmentType)
                    <span class="mt-1 inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full" style="background-color: {{ $job->equipmentType->color_code ?? '#64748b' }}"></span>
                        {{ $job->equipmentType->name }}
                    </span>
                @endif
                @if ($job->jobLevel)
                    <span class="mt-1 inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-medium" style="background-color: {{ $job->jobLevel->color_code }}1a; color: {{ $job->jobLevel->color_code }}">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full" style="background-color: {{ $job->jobLevel->color_code }}"></span>
                        {{ $job->jobLevel->name }}
                    </span>
                @endif
                @if ($job->clientRating ?? $job->client?->clientRating)
                    @php $rating = $job->clientRating ?? $job->client?->clientRating; @endphp
                    <span class="mt-1 inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700" @if($rating->description) title="{{ $rating->description }}" @endif>
                        <svg class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.05 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 0 0-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.539 1.118l-3.366-2.446a1 1 0 0 0-1.176 0l-3.366 2.446c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 0 0-.364-1.118L2.356 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.286-3.957Z"/></svg>
                        {{ $rating->name }}
                    </span>
                @endif
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
                    @if ($job->estimated_duration_minutes)
                        {{ $job->estimated_duration_minutes }} min
                    @endif
                </p>
                <div class="mt-1"><x-jobs.status-badge :status="$job->status" /></div>
                @if ($job->isVerified())
                    <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700"
                        title="Verified{{ $job->verifier ? ' by '.$job->verifier->name : '' }} on {{ $job->verified_at->format('d M Y g:i A') }}">
                        <svg class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                        Verified{{ $job->verifier ? ' · '.$job->verifier->name : '' }}
                    </span>
                @endif
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

            {{-- Last Job --}}
            <td class="whitespace-nowrap px-4 py-4">
                @if ($job->client?->lastJob)
                    <a href="{{ route('admin.jobs.show', $job->client->lastJob) }}" class="text-sm font-medium text-emerald-700 underline underline-offset-2 hover:text-emerald-800">
                        {{ $job->client->lastJob->scheduled_date->format('d M Y') }}
                    </a>
                @else
                    <span class="text-sm text-slate-400">—</span>
                @endif
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
                @if ($job->contract && !auth()->user()?->hasRole(\App\Support\CrmRoles::MOWER))
                    <button type="button"
                        class="view-contractor-btn mt-1 inline-flex items-center gap-1 rounded-md bg-violet-50 px-1.5 py-0.5 text-xs font-medium text-violet-700 hover:bg-violet-100 transition-colors"
                        data-contractor-name="{{ $job->contract->contractor->name }}"
                        data-contractor-phone="{{ $job->contract->contractor->phone ?? '' }}"
                        data-contractor-email="{{ $job->contract->contractor->email ?? '' }}"
                        data-contract-name="{{ $job->contract->name }}"
                        data-contract-start="{{ $job->contract->start_date?->format('d M Y') ?? '' }}"
                        data-contract-end="{{ $job->contract->end_date?->format('d M Y') ?? '' }}"
                        data-contract-status="{{ $job->contract->status->value }}">
                        <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75m-4.5 5.25h10.5A2.25 2.25 0 0 0 21 20.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m9 4.5h.008v.008H17.25V12.75Zm0 3h.008v.008H17.25V15.75Zm0 3h.008v.008H17.25V18.75Z"/></svg>
                        {{ $job->contract->contractor->name }}
                    </button>
                @endif
            </td>

            {{-- Remarks --}}
            <td class="w-36 max-w-0 px-4 py-4">
                <button type="button" class="view-remarks-btn group/rem w-full overflow-hidden text-left"
                        data-id="{{ $job->id }}"
                        data-special-remarks="{{ $job->special_remarks ?? '' }}"
                        data-internal-notes="{{ $job->internal_notes ?? '' }}">
                    @if ($job->special_remarks)
                        <p class="truncate text-sm text-slate-700 group-hover/rem:text-emerald-600">{{ $job->special_remarks }}</p>
                    @else
                        <span class="text-sm text-slate-300 group-hover/rem:text-emerald-400">+ Add</span>
                    @endif
                </button>
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
                        @if ($job->trashed())
                            @can('manage-job-records')
                                <button class="restore-job flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-emerald-700 transition-colors hover:bg-emerald-50" data-id="{{ $job->id }}">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                                    </svg>
                                    Restore
                                </button>
                                <div class="my-1 border-t border-slate-100"></div>
                                <button class="force-delete-job flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-red-600 transition-colors hover:bg-red-50" data-id="{{ $job->id }}">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
                                    Delete permanently
                                </button>
                            @endcan
                        @else
                        <a href="{{ route('admin.jobs.show', $job) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            View
                        </a>
                        <a href="{{ route('admin.maps.index', ['highlight_job' => $job->id, 'list_scope' => '']) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                            </svg>
                            View in Map
                        </a>
                        @can('update', $job)
                            <a href="{{ route('admin.jobs.edit', $job) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                                </svg>
                                Edit
                            </a>
                        @endcan
                        <x-jobs.verify-button :job="$job" class="flex w-full items-center gap-2.5 px-3.5 py-2 text-sm {{ $job->isVerified() ? 'text-amber-600' : 'text-emerald-700' }} transition-colors hover:bg-slate-50">
                            @if ($job->isVerified())
                                <svg class="h-3.5 w-3.5 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                Unverify
                            @else
                                <svg class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                Verify
                            @endif
                        </x-jobs.verify-button>
                        @can('assign-jobs')
                            <x-jobs.assign-button :job="$job" class="flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                                </svg>
                                Assign
                            </x-jobs.assign-button>
                            {{-- NOTE: Status button hidden, not deleted — unsure if still needed. Re-enable by uncommenting.
                            <button class="status-job flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50" data-id="{{ $job->id }}" data-status="{{ $job->status }}">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                                Status
                            </button>
                            --}}
                            <x-jobs.reschedule-button :job="$job" class="flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                                </svg>
                                Schedule
                            </x-jobs.reschedule-button>
                        @endcan
                        @can('manage_followups')
                            <div class="my-1 border-t border-slate-100"></div>
                            <button class="followup-job flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50"
                                    data-id="{{ $job->id }}"
                                    data-followable-type="{{ \App\Models\Job::class }}"
                                    data-followable-id="{{ $job->id }}">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3.75 19.5a7.5 7.5 0 0 1 12.122-5.894"/>
                                </svg>
                                Follow Up
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
                        @endif
                    </div>
                </div>
            </td>

        </tr>
    @empty
        <tr>
            <td colspan="{{ $canReorder ? 12 : 11 }}" class="px-4 py-10 text-center text-sm text-slate-400">No jobs found.</td>
        </tr>
    @endforelse
        </tbody>
        @php
            $totalEstMins = $jobs->sum('estimated_duration_minutes');
            $estHours     = intdiv($totalEstMins, 60);
            $estMins      = $totalEstMins % 60;
            $estFormatted = $estHours > 0
                ? ($estMins > 0 ? "{$estHours}h {$estMins}m" : "{$estHours}h")
                : "{$estMins}m";
        @endphp
        @if ($totalEstMins > 0)
        <tfoot>
            <tr class="border-t-2 border-slate-200 bg-slate-50">
                <td colspan="{{ $canReorder ? 4 : 3 }}" class="px-4 py-2.5 text-xs font-medium text-slate-400 uppercase tracking-wide">Page total</td>
                <td class="whitespace-nowrap px-4 py-2.5">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        {{ $estFormatted }} estimated
                    </span>
                </td>
                <td colspan="7" class="px-4 py-2.5"></td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
<script>
    (function () {
        if (typeof window.cleanupJobBulkSelection === 'function') {
            window.cleanupJobBulkSelection();
        }

        const controller = new AbortController();
        const signal = controller.signal;

        function container() {
            return document.getElementById('jobs-table-container');
        }

        if (!container()) return;

        let bulkMode = false;
        let longPressTimer = null;
        let longPressStartedAt = 0;
        let longPressActivated = false;
        const longPressMs = 520;

        function checkboxes() {
            return Array.from(container()?.querySelectorAll('.job-select-checkbox') || []);
        }

        function selectedTableIds() {
            return checkboxes()
                .filter(el => el.checked)
                .map(el => Number(el.dataset.jobId))
                .filter(id => Number.isInteger(id) && id > 0);
        }

        function setBulkMode(active) {
            bulkMode = active;
            (container()?.querySelectorAll('.job-row') || []).forEach(row => {
                row.dataset.bulkMode = active ? 'true' : 'false';
            });
        }

        function syncBulkState() {
            const boxes = checkboxes();
            const selected = boxes.filter(el => el.checked);
            const master = document.getElementById('job-select-all');

            boxes.forEach(checkbox => {
                const row = checkbox.closest('.job-row');
                if (row) row.dataset.selected = checkbox.checked ? 'true' : 'false';
            });

            if (master) {
                master.checked = boxes.length > 0 && selected.length === boxes.length;
                master.indeterminate = selected.length > 0 && selected.length < boxes.length;
            }

            setBulkMode(selected.length > 0);
            if (window.crmJobSelection) {
                window.crmJobSelection.setIds(selectedTableIds());
            }
        }

        function setAllSelected(checked) {
            checkboxes().forEach(checkbox => {
                checkbox.checked = checked;
            });
            syncBulkState();
        }

        function clearLongPress() {
            clearTimeout(longPressTimer);
            longPressTimer = null;
        }

        window.selectedTableIds = selectedTableIds;
        window.cleanupJobBulkSelection = function () {
            controller.abort();
            clearLongPress();
        };

        // Uncheck all boxes when selection is cleared externally (e.g. from map view or toolbar).
        window.addEventListener('jobs:selection-changed', function (e) {
            if (e.detail.ids.length === 0 && checkboxes().some(function (cb) { return cb.checked; })) {
                checkboxes().forEach(function (cb) { cb.checked = false; });
                syncBulkState();
            }
        }, { signal });

        document.addEventListener('change', function (event) {
            const target = event.target;

            if (target.matches('#job-select-all')) {
                setAllSelected(target.checked);
                return;
            }

            if (target.matches('#jobs-table-container .job-select-checkbox')) {
                syncBulkState();
            }
        }, { signal });

        document.addEventListener('click', function (event) {
            const target = event.target;

            if (target.closest('#job-bulk-select-page')) {
                setAllSelected(true);
                return;
            }

            const row = target.closest('#jobs-table-container .job-row');
            if (!row || target.closest('a, button, input, select, textarea, .drag-handle')) return;
            if (longPressActivated) {
                longPressActivated = false;
                return;
            }
            if (Date.now() - longPressStartedAt < longPressMs + 80) return;
            if (!bulkMode) return;

            const checkbox = row.querySelector('.job-select-checkbox');
            if (!checkbox) return;
            checkbox.checked = !checkbox.checked;
            syncBulkState();
        }, { signal });

        document.addEventListener('pointerdown', function (event) {
            const row = event.target.closest('#jobs-table-container .job-row');
            if (!row || event.target.closest('a, button, input, select, textarea, .drag-handle')) return;

            longPressStartedAt = Date.now();
            longPressActivated = false;
            clearLongPress();
            longPressTimer = setTimeout(function () {
                const checkbox = row.querySelector('.job-select-checkbox');
                if (!checkbox) return;
                checkbox.checked = true;
                longPressActivated = true;
                syncBulkState();
            }, longPressMs);
        }, { signal });

        document.addEventListener('pointerup', clearLongPress, { signal });
        document.addEventListener('pointercancel', clearLongPress, { signal });
        document.addEventListener('pointerleave', clearLongPress, { signal });

        syncBulkState();
    })();
</script>
<div class="mt-4">{{ $jobs->links() }}</div>

@php
    $canReorder = auth()->user()?->can('manage-job-records');
    $headers = ['<input id="job-select-all" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" aria-label="Select all jobs">'];
    if ($canReorder) {
        $headers[] = '';
    }
    $headers = array_merge($headers, ['Customer / Address', 'Zone', 'Schedule', 'Services', 'Recurrence', 'Payment', 'Mowers', 'Remarks', 'Actions']);
@endphp

<div id="job-bulk-toolbar" class="fixed bottom-5 left-1/2 z-40 hidden w-[min(calc(100vw-2rem),64rem)] -translate-x-1/2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-xl shadow-slate-900/10 ring-1 ring-slate-900/5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-800"><span id="job-bulk-count">0</span> selected</p>
                <p class="text-xs text-slate-500">Long press any row to enter bulk mode.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" id="job-bulk-select-page" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/>
                </svg>
                Select page
            </button>
            @can('assign-jobs')
                <button type="button" id="job-bulk-assign" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-slate-700">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3.75 19.5a7.5 7.5 0 0 1 12.122-5.894"/>
                    </svg>
                    Assign
                </button>
                <button type="button" id="job-bulk-status" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992m-4.992 0v4.992m0-4.992 3.182-3.182M7.977 14.652H2.985m4.992 0V9.66m0 4.992-3.182 3.182"/>
                    </svg>
                    Status
                </button>
                <button type="button" id="job-bulk-schedule" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                    </svg>
                    Schedule
                </button>
            @endcan
            @can('manage-job-records')
                <button type="button" id="job-bulk-delete" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M4.772 5.79c.34-.059.68-.114 1.022-.165m13.434.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.667 48.667 0 0 0-14.456 0M8.25 5.25V4.875c0-1.036.84-1.875 1.875-1.875h3.75c1.036 0 1.875.84 1.875 1.875v.375"/>
                    </svg>
                    Delete
                </button>
            @endcan
            <button type="button" id="job-bulk-clear" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600" aria-label="Clear selected jobs">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<x-ui.table :headers="$headers">
    @forelse ($jobs as $job)
        @php $jobLevelColor = $job->jobLevel?->color_code; @endphp
        <tr class="job-row group divide-x divide-slate-100 transition-colors hover:bg-slate-50/70 data-[selected=true]:bg-emerald-50/70 data-[selected=true]:shadow-[inset_3px_0_0_#10b981] data-[bulk-mode=true]:cursor-pointer" data-job-id="{{ $job->id }}" data-selected="false" data-bulk-mode="false"
            @if ($jobLevelColor) style="background-color: {{ $jobLevelColor }}20" @endif>

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
                <a href="{{ route('admin.jobs.show', $job) }}" class="text-sm font-semibold leading-snug text-slate-800 hover:text-emerald-700 hover:underline">
                    {{ $job->client?->name ?: 'N/A' }}
                    @if ($job->client?->customer_unique_id)
                        <span class="font-normal text-slate-400">#{{ $job->client->customer_unique_id }}</span>
                    @endif
                </a>
                <p class="mt-0.5 text-xs text-slate-500">{{ $job->client_address ?: '—' }}</p>
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

            {{-- Remarks --}}
            <td class="px-4 py-4 max-w-[200px]">
                <button type="button" class="view-remarks-btn group/rem w-full text-left"
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
                        <a href="{{ route('admin.jobs.show', $job) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            View
                        </a>
                        <a target="_blank" href="{{ route('admin.maps.index', ['highlight_job' => $job->id, 'list_scope' => '']) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                            </svg>
                            View in Map
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
                            <button class="assign-job flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50"
                                    data-id="{{ $job->id }}"
                                    data-client-id="{{ $job->client_id ?? '' }}"
                                    data-done-by="{{ $job->done_by_user_id ?? '' }}"
                                    data-employee-ids="{{ json_encode($job->assignedEmployees->pluck('id')) }}">
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
                            <button class="schedule-job flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50"
                                    data-id="{{ $job->id }}"
                                    data-client-id="{{ $job->client_id ?? '' }}"
                                    data-scheduled-date="{{ $job->scheduled_date?->format('Y-m-d') ?? '' }}"
                                    data-scheduled-time="{{ $job->scheduled_time ?? '' }}">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                                </svg>
                                Schedule
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
            <td colspan="{{ $canReorder ? 11 : 10 }}" class="px-4 py-10 text-center text-sm text-slate-400">No jobs found.</td>
        </tr>
    @endforelse
</x-ui.table>
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
            const toolbar = document.getElementById('job-bulk-toolbar');
            const count = document.getElementById('job-bulk-count');
            const master = document.getElementById('job-select-all');

            boxes.forEach(checkbox => {
                const row = checkbox.closest('.job-row');
                if (row) row.dataset.selected = checkbox.checked ? 'true' : 'false';
            });

            if (master) {
                master.checked = boxes.length > 0 && selected.length === boxes.length;
                master.indeterminate = selected.length > 0 && selected.length < boxes.length;
            }

            if (count) count.textContent = selected.length;
            if (toolbar) toolbar.classList.toggle('hidden', selected.length === 0);
            setBulkMode(selected.length > 0);
            window.dispatchEvent(new CustomEvent('jobs:selection-changed', { detail: { ids: selectedTableIds() } }));
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
        window.clearJobBulkSelection = function () {
            setAllSelected(false);
        };
        window.cleanupJobBulkSelection = function () {
            controller.abort();
            clearLongPress();
        };

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

            if (target.closest('#job-bulk-clear')) {
                setAllSelected(false);
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

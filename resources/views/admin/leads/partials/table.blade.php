@php
    $convertedOnly = $convertedOnly ?? false;
    $tableHeaders = [
        '<input id="lead-select-all" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" aria-label="Select all leads">',
        'Lead', 'Contact', 'Job & Payment', 'Recurrence', 'Zone', 'Status', 'Created',
    ];
    if ($convertedOnly) {
        $tableHeaders[] = 'Converted';
    }
    $tableHeaders[] = 'Assigned';
    $tableHeaders[] = '';
@endphp

<x-leads.bulk-toolbar />

<x-ui.table :headers="$tableHeaders">
    @forelse ($leads as $lead)
        <tr class="lead-row divide-x divide-slate-100 transition-colors hover:bg-slate-50/70 data-[selected=true]:bg-emerald-50/70 data-[selected=true]:shadow-[inset_3px_0_0_#10b981] data-[bulk-mode=true]:cursor-pointer" data-lead-id="{{ $lead->id }}" data-selected="false" data-bulk-mode="false">

            {{-- Select --}}
            <td class="w-8 px-2 py-4">
                <input type="checkbox" class="lead-select-checkbox h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" data-lead-id="{{ $lead->id }}" aria-label="Select lead">
            </td>

            {{-- Lead / Service --}}
            <td class="px-4 py-4">
                <p class="text-sm font-semibold text-slate-800 leading-snug">{{ $lead->client_name ?: $lead->address }}</p>
                <div class="mt-1.5 flex flex-wrap items-center gap-1">
                    @if (is_array($lead->service_types) && $lead->service_types !== [])
                        @foreach ($lead->service_types as $type)
                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $type }}</span>
                        @endforeach
                    @else
                        <span class="text-xs text-slate-400">—</span>
                    @endif
                    @if ($lead->equipmentType)
                        <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full" style="background-color: {{ $lead->equipmentType->color_code ?? '#64748b' }}"></span>
                            {{ $lead->equipmentType->name }}
                        </span>
                    @endif
                </div>
            </td>

            {{-- Contact --}}
            <td class="whitespace-nowrap px-4 py-4">
                @if ($lead->email)
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                        <span class="text-xs text-slate-600">{{ $lead->email }}</span>
                    </div>
                @endif
                @if ($lead->mobile_number)
                    <div class="{{ $lead->email ? 'mt-1' : '' }} flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                        </svg>
                        <span class="text-xs text-slate-500">{{ $lead->mobile_number }}</span>
                    </div>
                @endif
                @if (! $lead->email && ! $lead->mobile_number)
                    <span class="text-xs text-slate-400">—</span>
                @endif
            </td>

            {{-- Job & Payment --}}
            <td class="whitespace-nowrap px-4 py-4">
                <div class="flex flex-wrap items-center gap-1">
                    <span class="text-sm text-slate-700">{{ $lead->job_type ?: '—' }}</span>
                    @if ($lead->charges !== null)
                        <span class="text-xs font-semibold text-slate-800">${{ number_format((float) $lead->charges, 2) }}</span>
                    @endif
                    @if ($lead->payment_mode)
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">{{ $lead->payment_mode }}</span>
                    @endif
                    @if ($lead->payment_status)
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">{{ $lead->payment_status }}</span>
                    @endif
                </div>
            </td>

            {{-- Recurrence --}}
            <td class="whitespace-nowrap px-4 py-4">
                <span class="text-sm text-slate-600">{{ $lead->recurrence?->name ?? '—' }}</span>
            </td>

            {{-- Zone --}}
            <td class="whitespace-nowrap px-4 py-4">
                @if ($lead->zone)
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                        </svg>
                        <span class="text-sm text-slate-600">{{ $lead->zone->name }}</span>
                    </div>
                @else
                    <span class="text-sm text-slate-400">—</span>
                @endif
            </td>

            {{-- Status --}}
            <td class="whitespace-nowrap px-4 py-4">
                <div class="flex flex-col items-start gap-1">
                    <x-ui.badge>{{ $lead->status }}</x-ui.badge>
                    @if ($lead->is_locked)
                        <x-ui.badge type="warning">Locked</x-ui.badge>
                    @endif
                    @if ($lead->client)
                        <x-ui.badge type="success">Customer</x-ui.badge>
                    @endif
                </div>
            </td>

            {{-- Created --}}
            <td class="whitespace-nowrap px-4 py-4">
                <span class="text-xs text-slate-500">{{ $lead->created_at?->format('M d, Y') ?? '—' }}</span>
            </td>

            @if ($convertedOnly)
                {{-- Converted --}}
                <td class="whitespace-nowrap px-4 py-4">
                    @if ($lead->converted_at)
                        <span class="text-xs font-medium text-emerald-600">{{ $lead->converted_at->format('M d, Y') }}</span>
                    @else
                        <span class="text-xs text-slate-400">—</span>
                    @endif
                </td>
            @endif

            {{-- Assigned --}}
            <td class="whitespace-nowrap px-4 py-4">
                @if ($lead->assignedSalesUser)
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700">
                            {{ strtoupper(substr($lead->assignedSalesUser->name, 0, 1)) }}
                        </span>
                        <span class="text-sm text-slate-700">{{ $lead->assignedSalesUser->name }}</span>
                    </div>
                @else
                    <span class="text-xs italic text-slate-400">Unassigned</span>
                @endif
            </td>

            {{-- Actions --}}
            <td class="whitespace-nowrap px-4 py-4">
                <div class="relative inline-block">
                    <button class="lead-actions-btn inline-flex items-center justify-center rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 focus:outline-none" data-id="{{ $lead->id }}" aria-label="Actions">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM12 13.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM12 21a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                        </svg>
                    </button>
                    <div class="lead-actions-menu hidden w-44 rounded-xl border border-slate-200 bg-white py-1 shadow-xl ring-1 ring-slate-900/5">
                        <button class="view-lead flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50" data-id="{{ $lead->id }}">
                            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            View Timeline
                        </button>
                        @can('manage-leads')
                            <a href="{{ route('admin.leads.edit', $lead) }}" class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                                </svg>
                                Edit
                            </a>
                            <button class="status-lead flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50" data-id="{{ $lead->id }}">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>
                                </svg>
                                Update Status
                            </button>
                            @if (! $lead->is_locked)
                                <div class="my-1 border-t border-slate-100"></div>
                                <button class="delete-lead flex w-full items-center gap-2.5 px-3.5 py-2 text-sm text-red-600 transition-colors hover:bg-red-50" data-id="{{ $lead->id }}">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
                                    Delete
                                </button>
                            @endif
                        @endcan
                    </div>
                </div>
            </td>

        </tr>
    @empty
        <tr>
            <td colspan="{{ count($tableHeaders) }}" class="px-4 py-10 text-center text-sm text-slate-400">No leads found.</td>
        </tr>
    @endforelse
</x-ui.table>

<script>
    (function () {
        if (typeof window.cleanupLeadBulkSelection === 'function') {
            window.cleanupLeadBulkSelection();
        }

        const controller = new AbortController();
        const signal = controller.signal;

        function container() {
            return document.getElementById('leads-table-container');
        }

        if (!container()) return;

        let bulkMode = false;
        let longPressTimer = null;
        let longPressStartedAt = 0;
        let longPressActivated = false;
        const longPressMs = 520;

        function checkboxes() {
            return Array.from(container()?.querySelectorAll('.lead-select-checkbox') || []);
        }

        function selectedTableIds() {
            return checkboxes()
                .filter(el => el.checked)
                .map(el => Number(el.dataset.leadId))
                .filter(id => Number.isInteger(id) && id > 0);
        }

        function syncToolbar() {
            const ids = selectedTableIds();
            const toolbar = document.getElementById('lead-bulk-toolbar');
            const count = document.getElementById('lead-bulk-count');

            if (count) { count.textContent = ids.length; }
            if (toolbar) { toolbar.classList.toggle('hidden', ids.length === 0); }
        }

        function setBulkMode(active) {
            bulkMode = active;
            (container()?.querySelectorAll('.lead-row') || []).forEach(row => {
                row.dataset.bulkMode = active ? 'true' : 'false';
            });
        }

        function syncBulkState() {
            const boxes = checkboxes();
            const selected = boxes.filter(el => el.checked);
            const master = document.getElementById('lead-select-all');

            boxes.forEach(checkbox => {
                const row = checkbox.closest('.lead-row');
                if (row) row.dataset.selected = checkbox.checked ? 'true' : 'false';
            });

            if (master) {
                master.checked = boxes.length > 0 && selected.length === boxes.length;
                master.indeterminate = selected.length > 0 && selected.length < boxes.length;
            }

            setBulkMode(selected.length > 0);
            syncToolbar();
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

        window.selectedLeadIds = selectedTableIds;
        window.clearLeadBulkSelection = function () {
            checkboxes().forEach(cb => { cb.checked = false; });
            syncBulkState();
        };
        window.cleanupLeadBulkSelection = function () {
            controller.abort();
            clearLongPress();
        };

        document.addEventListener('change', function (event) {
            const target = event.target;

            if (target.matches('#lead-select-all')) {
                setAllSelected(target.checked);
                return;
            }

            if (target.matches('#leads-table-container .lead-select-checkbox')) {
                syncBulkState();
            }
        }, { signal });

        document.addEventListener('click', function (event) {
            const target = event.target;

            if (target.closest('#lead-bulk-select-page')) {
                setAllSelected(true);
                return;
            }

            if (target.closest('#lead-bulk-clear')) {
                window.clearLeadBulkSelection();
                return;
            }

            const row = target.closest('#leads-table-container .lead-row');
            if (!row || target.closest('a, button, input, select, textarea')) return;
            if (longPressActivated) {
                longPressActivated = false;
                return;
            }
            if (Date.now() - longPressStartedAt < longPressMs + 80) return;
            if (!bulkMode) return;

            const checkbox = row.querySelector('.lead-select-checkbox');
            if (!checkbox) return;
            checkbox.checked = !checkbox.checked;
            syncBulkState();
        }, { signal });

        document.addEventListener('pointerdown', function (event) {
            const row = event.target.closest('#leads-table-container .lead-row');
            if (!row || event.target.closest('a, button, input, select, textarea')) return;

            longPressStartedAt = Date.now();
            longPressActivated = false;
            clearLongPress();
            longPressTimer = setTimeout(function () {
                const checkbox = row.querySelector('.lead-select-checkbox');
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

<div class="mt-4">{{ $leads->links() }}</div>

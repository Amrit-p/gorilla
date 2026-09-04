@php
    $viewingDeleted =
        request('list_scope') === \App\Support\CrmConstants::JOB_LIST_SCOPE_DELETED &&
        (auth()->user()?->hasRole(\App\Support\CrmRoles::OFFICE_MANAGER) ?? false);
    $canReorder = auth()->user()?->can('manage-job-records') && ! $viewingDeleted;
    // Payout mode (salary calculator drill-down) renders the full table, and
    // additionally locks the rows a mower has already been paid out for. Route
    // reordering stays off there: route_sequence is a per-day order, so dragging
    // inside a month-wide list would scramble it.
    $payoutMode = $payoutMode ?? false;
    $canReorder = $canReorder && ! $payoutMode;

    // Pagination is opt-in: only lists whose controller resolved a page size
    // through crm_per_page() pass $perPage in. Everything else keeps plain links.
    $showPagination = isset($perPage);
    $infiniteScroll = $infiniteScroll ?? false;
    $paginationConfig = [
        'paginator' => $jobs,
        'perPage' => $perPage ?? null,
        'infinite' => $infiniteScroll,
        'chunkUrl' => $chunkUrl ?? route('admin.jobs.index'),
        'params' => $chunkParams ?? [],
        // Scroll chunks must respect whatever filters are on screen. The jobs
        // list exposes them as getFilters(); lists without a filter bar have no
        // such global and the component simply skips it.
        'filtersFn' => $filtersFn ?? 'getFilters',
        'reloadFn' => $reloadFn ?? 'crmReloadJobsTable',
        'target' => $paginationTarget ?? '#jobs-table-container',
        'itemLabel' => 'job',
    ];
@endphp

<x-jobs.bulk-toolbar :viewing-deleted="$viewingDeleted" />

@if ($showPagination)
    <x-crm.table-pagination :config="$paginationConfig" placement="top" />
@else
    <div class="mb-4">{{ $jobs->links() }}</div>
@endif

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
                <th colspan="3" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide bg-yellow-100 text-yellow-800">Schedule</th>
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
    @include('admin.jobs.partials.rows')
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
                <td colspan="{{ $canReorder ? 4 : 3 }}" class="px-4 py-2.5 text-xs font-medium text-slate-400 uppercase tracking-wide">{{ $infiniteScroll ? 'Loaded total' : 'Page total' }}</td>
                <td class="whitespace-nowrap px-4 py-2.5">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <span id="jobs-est-total">{{ $estFormatted }}</span> estimated
                    </span>
                </td>
                <td colspan="6" class="px-4 py-2.5"></td>
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
        // Appended infinite-scroll chunks call this so the master checkbox and
        // bulk toolbar account for the rows that just arrived.
        window.syncJobBulkState = syncBulkState;
        window.cleanupJobBulkSelection = function () {
            controller.abort();
            clearLongPress();
            if (window.crmTablePagination) {
                window.crmTablePagination.cleanup();
            }
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
@if ($showPagination)
    <x-crm.table-pagination :config="$paginationConfig" placement="bottom" />
@else
    <div class="mt-4">{{ $jobs->links() }}</div>
@endif

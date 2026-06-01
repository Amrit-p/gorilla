@php
    $hasActiveFilters = !empty($filters['search']) || !empty($filters['list_scope']) || !empty($filters['status']) || !empty($filters['zone_id']) || !empty($filters['recurrence_id']) || !empty($filters['assignment']) || !empty($filters['payment_mode']) || !empty($filters['payment_status']) || !empty($filters['date_range_start']) || !empty($filters['date_range_end']);
    $activeCount = collect($filters)->filter(fn ($v) => $v !== '' && $v !== null)->count();
    $tableContainer = $tableContainer ?? 'jobs-table-container';
@endphp

<div class="rounded-xl border border-slate-200 bg-white shadow-sm">
    {{-- Toggle header --}}
    <div class="flex items-center">
        <button type="button" id="job-filter-toggle" class="flex flex-1 items-center justify-between px-4 py-3 text-left">
            <div class="flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-600">Filters</span>
                @if ($activeCount > 0)
                    <span class="rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-600">{{ $activeCount }}</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if ($hasActiveFilters)
                    <a href="{{ $resetUrl ?? route('admin.jobs.index') }}" class="text-[11px] text-slate-400 hover:text-slate-600" onclick="event.stopPropagation()">Reset all</a>
                @endif
                <svg id="job-filter-chevron" class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200 {{ $hasActiveFilters ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        <div class="flex flex-wrap items-center gap-2 border-l border-slate-100 px-4 py-2.5">
            <x-ui.export-dropdown
                :excelHref="$excelHref ?? route('admin.jobs.export.excel')"
                :pdfHref="$pdfHref ?? route('admin.jobs.export.pdf')"
            />
            @can('manage-job-records')
                <a href="{{ route('admin.jobs.create') }}" class="rounded-md bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-700">Create Job</a>
            @endcan
        </div>
    </div>

    {{-- Collapsible body --}}
    <form id="job-filter-form" class="{{ $hasActiveFilters ? '' : 'hidden' }} border-t border-slate-100 px-4 py-3">

        @if(isset($listScopes))
            <div class="mb-3 flex flex-wrap gap-1.5">
                @foreach ($listScopes as $scopeKey => $scopeLabel)
                    <button type="button" data-list-scope="{{ $scopeKey }}"
                        class="job-list-scope rounded-full border px-2.5 py-1 text-xs font-medium {{ ($filters['list_scope'] ?? '') === $scopeKey ? 'border-emerald-600 bg-emerald-50 text-emerald-800' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}"
                    >{{ $scopeLabel }}</button>
                @endforeach
                <button type="button" data-list-scope=""
                    class="job-list-scope rounded-full border px-2.5 py-1 text-xs font-medium {{ ($filters['list_scope'] ?? '') === '' ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}"
                >All</button>
            </div>
        @endif

        <input type="hidden" name="list_scope" id="job-list-scope" value="{{ $filters['list_scope'] ?? '' }}">
        @if (!empty($clientId))
            <input type="hidden" name="client_id" value="{{ $clientId }}">
        @endif

        <div class="grid grid-cols-4 gap-x-2.5 gap-y-3">

            {{-- Row 1 --}}
            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Search</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Customer, address, ID…"
                        class="filter w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-2 text-xs text-slate-700 placeholder-slate-400 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100"
                    >
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Zone</label>
                <select name="zone_id" class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All zones</option>
                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" @selected(($filters['zone_id'] ?? '') == $zone->id)>{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Status</label>
                <select name="status" class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">Any status</option>
                    @foreach ($workflowStatuses as $workflowStatus)
                        <option value="{{ $workflowStatus }}" @selected(($filters['status'] ?? '') === $workflowStatus)>{{ $workflowStatus }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Recurrence</label>
                <select name="recurrence_id" class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">Any recurrence</option>
                    @foreach ($recurrences as $recurrence)
                        <option value="{{ $recurrence->id }}" @selected(($filters['recurrence_id'] ?? '') == $recurrence->id)>{{ $recurrence->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Row 2 --}}
            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Assignment</label>
                <select name="assignment" class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">Any assignment</option>
                    <option value="assigned" @selected(($filters['assignment'] ?? '') === 'assigned')>Assigned</option>
                    <option value="unassigned" @selected(($filters['assignment'] ?? '') === 'unassigned')>Unassigned</option>
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Payment Mode</label>
                <select name="payment_mode" class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">Any payment mode</option>
                    @foreach ($paymentModes as $mode)
                        <option value="{{ $mode }}" @selected(($filters['payment_mode'] ?? '') === $mode)>{{ $mode }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Payment Status</label>
                <select name="payment_status" class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">Any payment status</option>
                    @foreach ($paymentStatuses as $payStatus)
                        <option value="{{ $payStatus }}" @selected(($filters['payment_status'] ?? '') === $payStatus)>{{ $payStatus }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Scheduled Date</label>
                <x-ui.daterange-picker
                    placeholder="Date range"
                    name="date_range"
                    :startDate="$filters['date_range_start'] ?? null"
                    :endDate="$filters['date_range_end'] ?? null"
                    :show-ranges="true"
                    class="filter"
                />
            </div>

        </div>

        <div class="mt-2.5 flex items-center justify-end gap-2">
            <a href="{{ $resetUrl ?? route('admin.jobs.index') }}" class="text-xs text-slate-400 hover:text-slate-600">Reset</a>
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 active:scale-95">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Apply
            </button>
        </div>

    </form>
</div>

<style>
    @keyframes jobs-spin { to { transform: rotate(360deg); } }
    #{{ $tableContainer }} { position: relative; }
    #jobs-loading-overlay {
        position: absolute; inset: 0; z-index: 20;
        background: rgba(255,255,255,0.72);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        display: flex; align-items: center; justify-content: center;
        border-radius: 0.75rem;
    }
    #jobs-loading-overlay .jobs-spinner {
        width: 28px; height: 28px;
        border: 2.5px solid #e0e7ff;
        border-top-color: #4f46e5;
        border-radius: 50%;
        animation: jobs-spin 0.7s linear infinite;
    }
</style>

<script>
    var jobsSearchTimer;

    function showJobsLoading() {
        if ($('#jobs-loading-overlay').length) return;
        $('#{{ $tableContainer }}').append(
            '<div id="jobs-loading-overlay">' +
                '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;">' +
                    '<div class="jobs-spinner"></div>' +
                    '<span style="font-size:0.7rem;font-weight:500;color:#64748b;letter-spacing:0.05em;">Loading…</span>' +
                '</div>' +
            '</div>'
        );
    }

    let jobFilterCallback = @json($filterCallback ?? null);

    function loadJobs() {
        if (jobFilterCallback && typeof window[jobFilterCallback] === 'function') {
            const data = Object.fromEntries(new FormData(document.getElementById('job-filter-form')).entries());
            window[jobFilterCallback](data);
            return;
        }
        showJobsLoading();
        $.get("{{ route('admin.jobs.index') }}", $('#job-filter-form').serialize(), function (res) {
            $('#{{ $tableContainer }}').html(res.html);
        }, 'json').fail(function () {
            $('#jobs-loading-overlay').remove();
        });
    }

    $('#job-filter-toggle').on('click', function () {
        $('#job-filter-form').toggleClass('hidden');
        $('#job-filter-chevron').toggleClass('rotate-180');
    });

    $('.job-list-scope').on('click', function () {
        const scope = $(this).data('list-scope');
        $('#job-list-scope').val(scope);
        $('.job-list-scope').removeClass('border-emerald-600 bg-emerald-50 text-emerald-800 border-slate-800 bg-slate-800 text-white');
        $(this).addClass(scope === '' ? 'border-slate-800 bg-slate-800 text-white' : 'border-emerald-600 bg-emerald-50 text-emerald-800');
        loadJobs();
    });

    {{-- Selects and datepicker: fire immediately on change --}}
    $('#job-filter-form').on('change', 'select.filter, input[readonly].filter', loadJobs);

    {{-- Search text input: debounced 400 ms --}}
    $('#job-filter-form').on('input', 'input[type="text"]:not([readonly]).filter', function () {
        clearTimeout(jobsSearchTimer);
        jobsSearchTimer = setTimeout(loadJobs, 400);
    });

    $('#job-filter-form').on('submit', function (e) {
        e.preventDefault();
        loadJobs();
    });

    $(document).on('click', '#{{ $tableContainer }} .pagination a', function (e) {
        e.preventDefault();
        loadJobs($(this).attr('href'));
    });
</script>

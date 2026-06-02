@php
    $hasActiveFilters =
        !empty($filters['search']) ||
        !empty($filters['status']) ||
        !empty($filters['zone_id']) ||
        !empty($filters['recurrence_id']) ||
        !empty($filters['assignment']) ||
        !empty($filters['payment_mode']) ||
        !empty($filters['payment_status']) ||
        !empty($filters['equipment_type_id']) ||
        !empty($filters['customer_type']) ||
        !empty($filters['service_type']) ||
        !empty($filters['date_range_start']) ||
        !empty($filters['date_range_end']);
    $activeCount = collect($filters)->filter(fn($v) => $v !== '' && $v !== null)->count();
@endphp

<div id="map-filters-panel" class="col-span-2 hidden flex min-h-0 flex-col border-l border-slate-200 bg-white">

    {{-- Header: title, export, reset, apply --}}
    <div class="flex shrink-0 items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-2.5">
        <div class="flex items-center gap-1.5">
            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
            </svg>
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-600">Filters</span>
            @if ($activeCount > 0)
                <span class="rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-600">{{ $activeCount }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <x-ui.export-dropdown :excelHref="route('admin.jobs.export.excel')" :pdfHref="route('admin.jobs.export.pdf')" />
            <a href="{{ route('admin.maps.index') }}" class="text-xs text-slate-400 hover:text-slate-600">Reset</a>
            <button type="submit" form="job-filter-form"
                class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 active:scale-95">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
                Apply
            </button>
        </div>
    </div>

    {{-- Scrollable filter form --}}
    <form id="job-filter-form" class="min-h-0 flex-1 space-y-3 overflow-y-auto px-4 py-3">

        {{-- Scope pills --}}
        <div class="flex flex-wrap gap-1.5">
            @foreach ($listScopes as $scopeKey => $scopeLabel)
                <button type="button" data-list-scope="{{ $scopeKey }}"
                    class="job-list-scope rounded-full border px-2.5 py-1 text-xs font-medium {{ ($filters['list_scope'] ?? '') === $scopeKey ? 'border-emerald-600 bg-emerald-50 text-emerald-800' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">{{ $scopeLabel }}</button>
            @endforeach
            <button type="button" data-list-scope=""
                class="job-list-scope rounded-full border px-2.5 py-1 text-xs font-medium {{ ($filters['list_scope'] ?? '') === '' ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">All</button>
        </div>
        <input type="hidden" name="list_scope" id="job-list-scope" value="{{ $filters['list_scope'] ?? '' }}">

        <div class="flex flex-col gap-1">
            <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Search</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                    </svg>
                </span>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                    placeholder="Customer, address, ID…"
                    class="filter w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-xs text-slate-700 placeholder-slate-400 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
            </div>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Zone</label>
            <select name="zone_id"
                class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">All zones</option>
                @foreach ($zones as $zone)
                    <option value="{{ $zone->id }}" @selected(($filters['zone_id'] ?? '') == $zone->id)>{{ $zone->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Status</label>
            <select name="status"
                class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">Any status</option>
                @foreach ($workflowStatuses as $workflowStatus)
                    <option value="{{ $workflowStatus }}" @selected(($filters['status'] ?? '') === $workflowStatus)>{{ $workflowStatus }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Recurrence</label>
            <select name="recurrence_id"
                class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">Any recurrence</option>
                @foreach ($recurrences as $recurrence)
                    <option value="{{ $recurrence->id }}" @selected(($filters['recurrence_id'] ?? '') == $recurrence->id)>{{ $recurrence->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Assignment</label>
            <select name="assignment"
                class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">Any assignment</option>
                <option value="assigned" @selected(($filters['assignment'] ?? '') === 'assigned')>Assigned</option>
                <option value="unassigned" @selected(($filters['assignment'] ?? '') === 'unassigned')>Unassigned</option>
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Payment Mode</label>
            <select name="payment_mode"
                class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">Any payment mode</option>
                @foreach ($paymentModes as $mode)
                    <option value="{{ $mode }}" @selected(($filters['payment_mode'] ?? '') === $mode)>{{ $mode }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Payment Status</label>
            <select name="payment_status"
                class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">Any payment status</option>
                @foreach ($paymentStatuses as $payStatus)
                    <option value="{{ $payStatus }}" @selected(($filters['payment_status'] ?? '') === $payStatus)>{{ $payStatus }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Equipment Type</label>
            <select name="equipment_type_id"
                class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">Any equipment</option>
                @foreach ($equipmentTypes as $equipmentType)
                    <option value="{{ $equipmentType['id'] }}" @selected(($filters['equipment_type_id'] ?? '') == $equipmentType['id'])>{{ $equipmentType['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Customer Type</label>
            <select name="customer_type"
                class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">Any type</option>
                @foreach ($customerTypes as $type)
                    <option value="{{ $type }}" @selected(($filters['customer_type'] ?? '') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Service Type</label>
            <select name="service_type"
                class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">Any service</option>
                @foreach ($serviceTypes as $serviceType)
                    <option value="{{ $serviceType }}" @selected(($filters['service_type'] ?? '') === $serviceType)>{{ $serviceType }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Scheduled Date</label>
            <x-ui.daterange-picker placeholder="Date range" name="date_range"
                :startDate="$filters['date_range_start'] ?? null"
                :endDate="$filters['date_range_end'] ?? null"
                :show-ranges="true" class="filter" />
        </div>

    </form>

    {{-- Bottom: routing controls + legend --}}
    <div class="shrink-0 space-y-3 border-t border-slate-200 px-4 py-3">
        <button id="refresh-map-btn"
            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Refresh Map</button>
        <div id="map-legend" class="space-y-1">
            <h4 class="text-xs font-semibold text-slate-700">Equipment Types</h4>
            <div id="map-legend-items" class="space-y-1 text-xs text-slate-600"></div>
        </div>
    </div>
</div>

<script>
    function getFilters() {
        const params = new URLSearchParams(new FormData(document.getElementById('job-filter-form')));
        return {
            ...Object.fromEntries(params.entries()),
            list_scope: $('#job-list-scope').val(),
        };
    }

    $('#map-filter-toggle-btn').on('click', function () {
        const panel  = $('#map-filters-panel');
        const mapCol = $('#jobs-map-wrap');
        const hiding = !panel.hasClass('hidden');

        panel.toggleClass('hidden', hiding);
        mapCol.toggleClass('col-span-4', !hiding).toggleClass('col-span-6', hiding);
        $('#map-filter-toggle-label').text(hiding ? 'Show Filters' : 'Hide Filters');
    });

    $('.job-list-scope').on('click', function () {
        const scope = $(this).data('list-scope');
        $('#job-list-scope').val(scope);
        $('.job-list-scope').removeClass(
            'border-emerald-600 bg-emerald-50 text-emerald-800 border-slate-800 bg-slate-800 text-white'
        );
        $(this).addClass(
            scope === '' ? 'border-slate-800 bg-slate-800 text-white' : 'border-emerald-600 bg-emerald-50 text-emerald-800'
        );
        window.reloadMapFromFilter(getFilters());
    });

    var mapSearchTimer;

    $('#job-filter-form').on('change', 'select.filter, input[readonly].filter', function () {
        window.reloadMapFromFilter(getFilters());
    });

    $('#job-filter-form').on('input', 'input[type="text"]:not([readonly]).filter', function () {
        clearTimeout(mapSearchTimer);
        mapSearchTimer = setTimeout(function () {
            window.reloadMapFromFilter(getFilters());
        }, 400);
    });

    $('#job-filter-form').on('submit', function (e) {
        e.preventDefault();
        window.reloadMapFromFilter(getFilters());
    });
</script>

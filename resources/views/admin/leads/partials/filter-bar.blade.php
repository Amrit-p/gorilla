@php
    $hasActiveFilters =
        !empty($filters['search']) ||
        !empty($filters['status']) ||
        !empty($filters['assigned_sales_user_id']) ||
        !empty($filters['zone_id']) ||
        !empty($filters['recurrence_id']);
    $activeCount = collect($filters)->filter(fn ($v) => $v !== '' && $v !== null)->count();
    $tableContainer = $tableContainer ?? 'leads-table-container';
    $filterCallback = $filterCallback ?? 'loadLeads';
    $excelHref = $excelHref ?? route('admin.leads.export.excel');
    $pdfHref = $pdfHref ?? route('admin.leads.export.pdf');
    $filterUrl = $filterUrl ?? route('admin.leads.index');
    $resetUrl = $resetUrl ?? route('admin.leads.index');
    $showHeaderActions = $showHeaderActions ?? true;
    $visibleFilters = $visibleFilters ?? null;
    $showFilter = fn (string $key): bool => is_null($visibleFilters) || in_array($key, $visibleFilters, true);
@endphp

<div class="rounded-xl border border-slate-200 bg-white shadow-sm">
    {{-- Toggle header --}}
    <div class="flex items-center">
        <button type="button" id="lead-filter-toggle" class="flex flex-1 items-center justify-between px-4 py-3 text-left">
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
                    <a href="{{ $resetUrl }}" class="text-[11px] text-slate-400 hover:text-slate-600" onclick="event.stopPropagation()">Reset all</a>
                @endif
                <svg id="lead-filter-chevron" class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200 {{ $hasActiveFilters ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        @if ($showHeaderActions)
            <div class="flex flex-wrap items-center gap-2 border-l border-slate-100 px-4 py-2.5">
                <x-ui.export-dropdown :excelHref="$excelHref" :pdfHref="$pdfHref" />
                @can('manage-leads')
                    <button id="open-import-modal" type="button" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs hover:bg-slate-50">Import</button>
                    <a href="{{ route('admin.leads.create') }}" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">Add Lead</a>
                @endcan
            </div>
        @endif
    </div>

    {{-- Collapsible body --}}
    <form id="lead-filter-form" class="{{ $hasActiveFilters ? '' : 'hidden' }} border-t border-slate-100 px-4 py-3">

        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-4">

            @if ($showFilter('search'))
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search name, email, phone…"
                        class="filter w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-2 text-xs text-slate-700 placeholder-slate-400 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100"
                    >
                </div>
            @endif

            @if ($showFilter('status'))
                <select name="status" class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            @endif

            @if ($showFilter('assigned_sales_user_id'))
                <select name="assigned_sales_user_id" class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All assignees</option>
                    @foreach ($salesUsers as $salesUser)
                        <option value="{{ $salesUser->id }}" @selected(($filters['assigned_sales_user_id'] ?? '') == $salesUser->id)>{{ $salesUser->name }}</option>
                    @endforeach
                </select>
            @endif

            @if ($showFilter('zone_id'))
                <select name="zone_id" class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All zones</option>
                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" @selected(($filters['zone_id'] ?? '') == $zone->id)>{{ $zone->name }}</option>
                    @endforeach
                </select>
            @endif

            @if ($showFilter('recurrence_id'))
                <select name="recurrence_id" class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All recurrences</option>
                    @foreach ($recurrences as $recurrence)
                        <option value="{{ $recurrence->id }}" @selected(($filters['recurrence_id'] ?? '') == $recurrence->id)>{{ $recurrence->name }}</option>
                    @endforeach
                </select>
            @endif

        </div>

        <div class="mt-2.5 flex items-center justify-end gap-2">
            <a href="{{ $resetUrl }}" class="text-xs text-slate-400 hover:text-slate-600">Reset</a>
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 active:scale-95">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Apply
            </button>
        </div>

    </form>
</div>

<script>
    var leadFilterSearchTimer;

    function getLeadFilters() {
        const params = new URLSearchParams(new FormData(document.getElementById('lead-filter-form')));
        return Object.fromEntries(params.entries());
    }

    function updateLeadExportLinks() {
        const params = new URLSearchParams(new FormData(document.getElementById('lead-filter-form')));
        const qs = params.toString();
        const excelBase = @json($excelHref);
        const pdfBase = @json($pdfHref);
        if ($('#export-excel-link').length) { $('#export-excel-link').attr('href', qs ? excelBase + '?' + qs : excelBase); }
        if ($('#export-pdf-link').length) { $('#export-pdf-link').attr('href', qs ? pdfBase + '?' + qs : pdfBase); }
    }

    function {{ $filterCallback }}(filters) {
        updateLeadExportLinks();
        $.ajax({
            url: @json($filterUrl),
            method: 'GET',
            data: filters,
            dataType: 'json',
            success: function (res) {
                $('#{{ $tableContainer }}').html(res.html);
            },
            error: function () {
                alert('Failed to load leads. Please try again.');
            }
        });
    }

    $('#lead-filter-toggle').on('click', function () {
        $('#lead-filter-form').toggleClass('hidden');
        $('#lead-filter-chevron').toggleClass('rotate-180');
    });

    {{-- Selects: fire immediately on change --}}
    $('#lead-filter-form').on('change', 'select.filter', function () {
        {{ $filterCallback }}(getLeadFilters());
    });

    {{-- Search text input: debounced 400 ms --}}
    $('#lead-filter-form').on('input', 'input[type="text"].filter', function () {
        clearTimeout(leadFilterSearchTimer);
        leadFilterSearchTimer = setTimeout(function () {
            {{ $filterCallback }}(getLeadFilters());
        }, 400);
    });

    $('#lead-filter-form').on('submit', function (e) {
        e.preventDefault();
        {{ $filterCallback }}(getLeadFilters());
    });

    $(document).on('click', '#{{ $tableContainer }} .pagination a', function (e) {
        e.preventDefault();
        {{ $filterCallback }}(getLeadFilters());
    });
</script>

@php
    $hasActiveFilters =
        !empty($filters['search']) ||
        !empty($filters['user_id']) ||
        !empty($filters['date_range_start']) ||
        !empty($filters['date_range_end']);

    $activeCount = collect($filters)->filter(fn ($v) => $v !== '' && $v !== null)->count();
    $excelHref   = route('admin.employee-bonuses.export.excel');
    $pdfHref     = route('admin.employee-bonuses.export.pdf');
    $resetUrl    = route('admin.employee-bonuses.index');
@endphp

<div class="rounded-xl border border-slate-200 bg-white shadow-sm">

    {{-- Toggle header --}}
    <div class="flex items-center">
        <button type="button" id="bonus-filter-toggle" class="flex flex-1 items-center justify-between px-4 py-3 text-left">
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
                <svg id="bonus-filter-chevron"
                    class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200 {{ $hasActiveFilters ? 'rotate-180' : '' }}"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        <div class="flex flex-wrap items-center gap-2 border-l border-slate-100 px-4 py-2.5">
            <x-ui.export-dropdown :excelHref="$excelHref" :pdfHref="$pdfHref" />
            @can('manage-employee-bonuses')
                <a href="{{ route('admin.employee-bonuses.create') }}"
                   class="rounded-md bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-700">
                    Add Bonus
                </a>
            @endcan
        </div>
    </div>

    {{-- Collapsible body --}}
    <form id="bonus-filter-form" class="{{ $hasActiveFilters ? '' : 'hidden' }} border-t border-slate-100 px-4 py-3">

        <div class="grid grid-cols-1 gap-x-2.5 gap-y-3 sm:grid-cols-2 lg:grid-cols-4">

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Employee</label>
                <select name="user_id"
                    class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All employees</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(($filters['user_id'] ?? '') == $employee->id)>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Search</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                        placeholder="Employee name…"
                        class="filter w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-2 text-xs text-slate-700 placeholder-slate-400 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>

            <div class="flex flex-col gap-1 sm:col-span-2 lg:col-span-2">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Bonus Date Range</label>
                <x-ui.daterange-picker
                    placeholder="Select date range"
                    name="date_range"
                    :startDate="$filters['date_range_start'] ?? null"
                    :endDate="$filters['date_range_end'] ?? null"
                    :show-ranges="true"
                    class="filter"
                />
            </div>

        </div>

        <div class="mt-2.5 flex items-center justify-end gap-2">
            <a href="{{ $resetUrl }}" class="text-xs text-slate-400 hover:text-slate-600">Reset</a>
            <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 active:scale-95">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Apply
            </button>
        </div>

    </form>
</div>

<style>
    #bonuses-table-container { position: relative; }

    #bonuses-loading-overlay {
        position: absolute; inset: 0; z-index: 20;
        background: rgba(255,255,255,.72);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        display: flex; align-items: center; justify-content: center;
        border-radius: .75rem;
    }
    @keyframes bonuses-spin { to { transform: rotate(360deg); } }
    #bonuses-loading-overlay .bonuses-spinner {
        width: 28px; height: 28px;
        border: 2.5px solid #e0e7ff; border-top-color: #4f46e5;
        border-radius: 50%; animation: bonuses-spin .7s linear infinite;
    }
</style>

<script>
    var bonusSearchTimer;

    function showBonusesLoading() {
        if ($('#bonuses-loading-overlay').length) return;
        $('#bonuses-table-container').append(
            '<div id="bonuses-loading-overlay">' +
            '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;">' +
            '<div class="bonuses-spinner"></div>' +
            '<span style="font-size:.7rem;font-weight:500;color:#64748b;letter-spacing:.05em;">Loading…</span>' +
            '</div></div>'
        );
    }

    function getBonusFilters() {
        return Object.fromEntries(new URLSearchParams(new FormData(document.getElementById('bonus-filter-form'))).entries());
    }

    function updateBonusExportLinks() {
        const qs = new URLSearchParams(new FormData(document.getElementById('bonus-filter-form'))).toString();
        $('#export-excel-link').attr('href', qs ? @json($excelHref) + '?' + qs : @json($excelHref));
        $('#export-pdf-link').attr('href',   qs ? @json($pdfHref)   + '?' + qs : @json($pdfHref));
    }

    function loadBonuses(url) {
        url = url || @json(route('admin.employee-bonuses.index'));
        updateBonusExportLinks();
        showBonusesLoading();
        $.ajax({
            url: url,
            method: 'GET',
            data: getBonusFilters(),
            dataType: 'json',
            success: function (res) { $('#bonuses-table-container').html(res.html); },
            error: function () {
                $('#bonuses-loading-overlay').remove();
            }
        });
    }

    $('#bonus-filter-toggle').on('click', function () {
        $('#bonus-filter-form').toggleClass('hidden');
        $('#bonus-filter-chevron').toggleClass('rotate-180');
    });

    $('#bonus-filter-form').on('change', 'select.filter, input[readonly].filter', function () {
        loadBonuses();
    });

    $('#bonus-filter-form').on('input', 'input[type="text"]:not([readonly]).filter', function () {
        clearTimeout(bonusSearchTimer);
        bonusSearchTimer = setTimeout(loadBonuses, 400);
    });

    $('#bonus-filter-form').on('submit', function (e) {
        e.preventDefault();
        loadBonuses();
    });

    $(document).on('click', '#bonuses-table-container .pagination a', function (e) {
        e.preventDefault();
        loadBonuses($(this).attr('href'));
    });

    updateBonusExportLinks();
</script>

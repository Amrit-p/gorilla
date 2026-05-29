@php
    $hasActiveFilters = !empty($filters['search']) || !empty($filters['list_scope']) || !empty($filters['status']) || !empty($filters['zone_id']) || !empty($filters['recurrence_id']);
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
                :excelHref="route('admin.jobs.export.excel')"
                :pdfHref="route('admin.jobs.export.pdf')"
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

        <div class="grid grid-cols-5 gap-2.5">

            <div class="relative col-span-2">
                <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search customer, address, ID…"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-2 text-xs text-slate-700 placeholder-slate-400 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100"
                >
            </div>

            <select name="zone_id" class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">All zones</option>
                @foreach ($zones as $zone)
                    <option value="{{ $zone->id }}" @selected(($filters['zone_id'] ?? '') == $zone->id)>{{ $zone->name }}</option>
                @endforeach
            </select>

            <select name="status" class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">Any status</option>
                @foreach ($workflowStatuses as $workflowStatus)
                    <option value="{{ $workflowStatus }}" @selected(($filters['status'] ?? '') === $workflowStatus)>{{ $workflowStatus }}</option>
                @endforeach
            </select>

            <select name="recurrence_id" class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                <option value="">Any recurrence</option>
                @foreach ($recurrences as $recurrence)
                    <option value="{{ $recurrence->id }}" @selected(($filters['recurrence_id'] ?? '') == $recurrence->id)>{{ $recurrence->name }}</option>
                @endforeach
            </select>

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

<script>
    function loadJobs() {
        $.get("{{ route('admin.jobs.index') }}", $('#job-filter-form').serialize(), function (res) {
            $('#{{ $tableContainer }}').html(res.html);
        }, 'json');
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

    $('#job-filter-form').on('submit', function (e) {
        e.preventDefault();
        loadJobs();
    });

    $(document).on('click', '#{{ $tableContainer }} .pagination a', function (e) {
        e.preventDefault();
        loadJobs($(this).attr('href'));
    });
</script>

@php
    $hasActiveFilters = !empty($filters['search']) || !empty($filters['list_scope']) || !empty($filters['status']) || !empty($filters['zone_id']);
    $activeCount = collect($filters)->filter(fn ($v) => $v !== '' && $v !== null)->count();
    $tableContainer = $tableContainer ?? 'jobs-table-container';
@endphp

<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

    {{-- Toggle header --}}
    <button type="button" id="job-filter-toggle" class="flex w-full items-center justify-between px-5 py-4 text-left">
        <div class="flex items-center gap-2">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <span class="text-sm font-semibold uppercase tracking-wide text-slate-600">Filters</span>
            @if ($activeCount > 0)
                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $activeCount }} active</span>
            @endif
        </div>
        <div class="flex items-center gap-3">
            @if ($hasActiveFilters)
                <a href="{{ $resetUrl ?? route('admin.jobs.index') }}" class="text-xs text-slate-400 transition-colors hover:text-slate-600" onclick="event.stopPropagation()">Reset all</a>
            @endif
            <svg id="job-filter-chevron" class="h-4 w-4 text-slate-400 transition-transform duration-200 {{ $hasActiveFilters ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </button>

    {{-- Collapsible body --}}
    <form id="job-filter-form" class="{{ $hasActiveFilters ? '' : 'hidden' }} border-t border-slate-100 p-5">
        @if(isset($listScopes))
            {{-- Scope pills --}}
            <div class="mb-4 flex flex-wrap gap-2">
                @foreach ($listScopes as $scopeKey => $scopeLabel)
                    <button
                        type="button"
                        data-list-scope="{{ $scopeKey }}"
                        class="job-list-scope rounded-full border px-3 py-1.5 text-sm font-medium {{ ($filters['list_scope'] ?? '') === $scopeKey ? 'border-emerald-600 bg-emerald-50 text-emerald-800' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}"
                    >{{ $scopeLabel }}</button>
                @endforeach
                <button type="button" data-list-scope="" class="job-list-scope rounded-full border px-3 py-1.5 text-sm font-medium {{ ($filters['list_scope'] ?? '') === '' ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">All</button>
            </div>
        @endif

        {{-- Search --}}
        <div class="mb-4">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search customer, address, ID…"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-sm text-slate-700 placeholder-slate-400 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100"
                >
            </div>
        </div>

        <input type="hidden" name="list_scope" id="job-list-scope" value="{{ $filters['list_scope'] ?? '' }}">
        @if (!empty($clientId))
            <input type="hidden" name="client_id" value="{{ $clientId }}">
        @endif

        {{-- Filter dropdowns --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Zone</label>
                <select name="zone_id" class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All zones</option>
                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" @selected(($filters['zone_id'] ?? '') == $zone->id)>{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Status</label>
                <select name="status" class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">Any status</option>
                    @foreach ($workflowStatuses as $workflowStatus)
                        <option value="{{ $workflowStatus }}" @selected(($filters['status'] ?? '') === $workflowStatus)>{{ $workflowStatus }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        {{-- Actions --}}
        <div class="mt-4 flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 active:scale-95">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Apply filters
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

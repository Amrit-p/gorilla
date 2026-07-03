<x-layouts.dashboard :title="'Checklist Reports'" :subtitle="'View checklist submission activity for all employees.'">
    <div class="space-y-5">

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div id="checklist-alert" class="hidden"></div>

        {{-- Filter Bar --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center p-2">
                <button type="button" id="checklist-filter-toggle" class="flex flex-1 items-center justify-between px-4 py-3 text-left">
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-600">Filters</span>
                    </div>
                    <svg id="checklist-filter-chevron" class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <x-ui.export-dropdown
                    :excelHref="route('reports.checklist.export')"
                    :pdfHref="route('reports.checklist.export-pdf')"
                    excelId="checklist-excel-btn"
                    pdfId="checklist-pdf-btn"
                />
            </div>

            <div id="checklist-filter-body" class="border-t border-slate-100 px-4 py-4">
                <form id="checklist-filter-form" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {{-- Date range --}}
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-500">Date Range</label>
                        <x-ui.daterange-picker
                            name="date_range"
                            placeholder="Select date range"
                            :startDate="$filters['start_date'] ?? ''"
                            :endDate="$filters['end_date'] ?? ''"
                            :showRanges="true"
                            class="filter"
                        />
                    </div>

                    {{-- Employee --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">Employee</label>
                        <select name="user_id"
                                class="filter w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300">
                            <option value="">All Employees</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ ($filters['user_id'] ?? '') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Checklist --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">Checklist</label>
                        <select name="checklist_id"
                                class="filter w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300">
                            <option value="">All Checklists</option>
                            @foreach ($checklists as $checklist)
                                <option value="{{ $checklist->id }}" {{ ($filters['checklist_id'] ?? '') == $checklist->id ? 'selected' : '' }}>
                                    {{ $checklist->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="sm:col-span-2 lg:col-span-4 flex items-center gap-3">
                        <button type="button" onclick="loadChecklistReport()"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                            Apply Filters
                        </button>
                        <button type="button" onclick="resetChecklistFilters()"
                                class="rounded-md border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="checklist-table-container" class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
            @include('reports.checklist.partials.table')
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Filter toggle
            $('#checklist-filter-toggle').on('click', function () {
                $('#checklist-filter-body').slideToggle(150);
                $('#checklist-filter-chevron').toggleClass('rotate-180');
            });

            loadChecklistReport();

            // Auto-apply filters on change
            $('#checklist-filter-form').on('change', 'select.filter, input[readonly].filter', function () {
                loadChecklistReport();
            });
        });

        function getChecklistFilters() {
            const params = new URLSearchParams();
            $('#checklist-filter-form').serializeArray().forEach(({name, value}) => {
                if (value) params.append(name, value);
            });
            return params;
        }

        function loadChecklistReport() {
            const params = getChecklistFilters();
            const url = '{{ route('reports.checklist.report') }}';

            $('#checklist-table-container').html('<div class="px-4 py-8 text-center text-sm text-slate-400">Loading...</div>');

            $.ajax({
                url: url + '?' + params.toString(),
                method: 'GET',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                success: function (response) {
                    $('#checklist-table-container').html(response.html);
                },
                error: function () {
                    $('#checklist-table-container').html('<div class="px-4 py-8 text-center text-sm text-rose-500">Failed to load report. Please try again.</div>');
                },
            });

            // Update export links with current filters
            const exportBase = '{{ route('reports.checklist.export') }}?' + params.toString();
            const pdfBase    = '{{ route('reports.checklist.export-pdf') }}?' + params.toString();
            $('#checklist-excel-btn').attr('href', exportBase);
            $('#checklist-pdf-btn').attr('href', pdfBase);
        }

        function resetChecklistFilters() {
            $('#checklist-filter-form')[0].reset();
            loadChecklistReport();
        }
    </script>
</x-layouts.dashboard>

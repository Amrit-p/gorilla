<x-layouts.dashboard :title="'Job Scheduling'">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Jobs</h2>
                <p class="text-sm text-slate-600">Track workflow, assign mowers, and filter by Today / Upcoming / Done / Hold.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.export-dropdown
                    :excelHref="route('admin.jobs.export.excel')"
                    :pdfHref="route('admin.jobs.export.pdf')"
                />
                @can('manage-job-records')
                    <a href="{{ route('admin.jobs.create') }}" class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Create Job</a>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @include('admin.partials.job-alert')
        @include('admin.jobs.partials.filter-bar', ['filters' => $filters])

        <div id="jobs-table-container">
            @include('admin.jobs.partials.table', ['jobs' => $jobs])
        </div>
    </div>

    @include('admin.partials.job-modals')
    @include('admin.partials.dropdown-script')
    @include('admin.partials.job-actions-script')

    <script>
        function syncJobExportLinks() {
            const params = $('#job-filter-form').serialize();
            $('#export-excel-link').attr('href', "{{ route('admin.jobs.export.excel') }}" + (params ? '?' + params : ''));
            $('#export-pdf-link').attr('href',   "{{ route('admin.jobs.export.pdf') }}"   + (params ? '?' + params : ''));
        }
        syncJobExportLinks();
        $('#job-filter-form').on('change input', syncJobExportLinks);
    </script>

</x-layouts.dashboard>

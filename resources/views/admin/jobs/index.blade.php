<x-layouts.dashboard :title="'Job Scheduling'" :subtitle="'View, filter, and manage your job schedules in one place.'">
    <div class="space-y-5">
        

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

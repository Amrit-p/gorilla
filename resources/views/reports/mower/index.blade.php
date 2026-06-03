<x-layouts.dashboard :title="'Mower Reports'">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Mower Reports</h2>
                <p class="text-sm text-slate-600">View earnings, completed jobs, and incentive details for all mowers.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}</div>
        @endif

        <div id="mower-alert" class="hidden"></div>
        @include('admin.jobs.partials.filter-bar', [
            'filters' => $filters,
            'filterCallback' => 'loadMowerReports',
            'filterUrl' => route('reports.mower.report'),
            'excelHref' => route('reports.mower.export'),
            'pdfHref' => route('reports.mower.export-pdf'),
            'tableContainer' => 'mower-table-container',
        ])

        <div id="mower-table-container" class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
            @include('reports.mower.partials.table')
        </div>
    </div>

    <x-reports.mower-jobs-modal :employees="$employees" :workflowStatuses="$workflowStatuses" />
    <x-reports.mower-bonus-modal />

    <script>
        $(document).ready(function() {
            loadMowerReports();
        });
    </script>
</x-layouts.dashboard>

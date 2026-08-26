    <div class="space-y-5">

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
            'hideListScope' => true,
            'visibleFilters' => $isMower ? ['search', 'date_range'] : null,
        ])

        <div id="mower-table-container" class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
            @include('reports.mower.partials.table', [
                'hideBonusColumn' => $hideBonusColumn,
            ])
        </div>
    </div>

    <x-reports.mower-jobs-modal :employees="$employees" :workflowStatuses="$workflowStatuses" />

    <script>
        $(document).ready(function() {
            loadMowerReports();
        });
    </script>


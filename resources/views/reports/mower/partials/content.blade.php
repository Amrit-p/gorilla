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
                'hideSalesColumn' => $hideSalesColumn,
            ])
        </div>
    </div>

    <x-reports.mower-jobs-modal :employees="$employees" :workflowStatuses="$workflowStatuses" />

    {{-- The payout and bonus totals drill down into the payouts behind them,
         using the same modal as the salary calculator. --}}
    <x-salary.payouts-modal :list-url="route('reports.mower.payouts')" />

    <script>
        (function() {
            function currentFilters() {
                const form = document.getElementById('job-filter-form');

                return form ? Object.fromEntries(new FormData(form).entries()) : {};
            }

            $(document).on('click', '.mower-payout-cell', function() {
                const userId = $(this).data('user-id');
                if (!userId) return;

                window.salaryPayoutsModal.open({
                    params: {
                        ...currentFilters(),
                        done_by_user_id: userId
                    },
                    subtitle: $(this).data('mower-name'),
                });
            });

            // Editing or deleting a payout moves the report's payout and bonus totals.
            window.addEventListener('payouts:changed', function() {
                loadMowerReports();
            });
        })();
    </script>

    <script>
        $(document).ready(function() {
            loadMowerReports();
        });
    </script>


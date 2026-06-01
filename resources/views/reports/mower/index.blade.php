<x-layouts.dashboard :title="'Mower Reports'">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Mower Reports</h2>
                <p class="text-sm text-slate-600">View earnings, completed jobs, and incentive details for all mowers.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div id="mower-alert" class="hidden"></div>

        @include('admin.jobs.partials.filter-bar', ['filters' => $filters, 'filterCallback' => 'loadMowerReports', 'excelHref' => route('reports.mower.export'), 'pdfHref' => route('reports.mower.export-pdf'), 'tableContainer' => 'mower-table-container'])

        <div id="mower-table-container" class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
            @include('reports.mower.partials.table')
        </div>
    </div>

    <x-reports.mower-jobs-modal :jobs-url="route('reports.mower.jobs')" />
    @include('admin.partials.job-modals')
    @include('admin.partials.dropdown-script')
    @include('admin.partials.job-actions-script')
    <script>
        function showMowerAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#mower-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        function loadMowerReports() {
            if(typeof window.fetchJobs === 'function') {
                // Clear job modal if open, to prevent mismatch with new data
                window.fetchJobs();
            }
            const data = Object.fromEntries(new FormData(document.getElementById('job-filter-form')).entries());
            $.ajax({
                url: "{{ route('reports.mower.report') }}",
                method: 'GET',
                headers: { Accept: 'application/json' },
                data: data,
                beforeSend: function () {
                    showJobsLoading();
                },
                success: function (res) {
                    if (res && res.html) {
                        $('#mower-table-container').html(res.html);
                    } else {
                        $('#mower-table-container tbody').html('<tr class="border-b border-slate-200"><td colspan="9" class="px-4 py-8 text-center text-slate-600">No mower reports available.</td></tr>');
                    }
                },
                error: function () {
                    $('#jobs-loading-overlay').remove();
                    const tbody = $('#mower-table-container tbody');
                    tbody.html('<tr class="border-b border-slate-200"><td colspan="9" class="px-4 py-8 text-center text-red-600">Failed to load mower reports.</td></tr>');
                    showMowerAlert('Failed to load mower reports.', true);
                }
            });
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        $(document).ready(function () {
            loadMowerReports();
        });

    </script>
</x-layouts.dashboard>

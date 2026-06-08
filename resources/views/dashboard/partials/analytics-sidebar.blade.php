@php
    $defaultStart = now()->startOfMonth()->toDateString();
    $defaultEnd   = now()->toDateString();
@endphp

<aside id="analytics-sidebar"
    class="hidden w-80 flex-shrink-0 xl:block">
    <div class="sticky top-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Analytics</h2>
                <p class="mt-0.5 text-xs text-slate-500">Reports & charts</p>
            </div>
            <button type="button" id="analytics-sidebar-close"
                class="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                title="Close">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Date range filter --}}
        <div class="border-b border-slate-100 px-4 py-3">
            <label class="mb-1.5 block text-xs font-medium text-slate-600">Filter by scheduled date</label>
            <x-ui.daterange-picker
                name="analytics_range"
                placeholder="Select date range"
                :showRanges="true"
                :startDate="$defaultStart"
                :endDate="$defaultEnd"
                onChange="window.analyticsReload"
            />
        </div>

        {{-- Loading overlay --}}
        <div id="analytics-loading" class="hidden px-4 py-8 text-center text-xs text-slate-400">
            Loading charts…
        </div>

        {{-- Charts --}}
        <div id="analytics-charts" class="divide-y divide-slate-100">

            {{-- Revenue comparison --}}
            <div class="p-4">
                <h3 class="text-xs font-semibold text-slate-700">Revenue</h3>
                <p id="analytics-revenue-meta" class="mt-0.5 text-[11px] text-slate-400">Current vs previous period</p>
                <div class="relative mt-3 h-44">
                    <canvas id="analytics-revenue-chart"></canvas>
                </div>
            </div>

            {{-- Job performance --}}
            <div class="p-4">
                <div class="flex items-baseline justify-between">
                    <h3 class="text-xs font-semibold text-slate-700">Job performance</h3>
                    <span id="analytics-jobs-total" class="text-base font-bold text-slate-800">—</span>
                </div>
                <p class="mt-0.5 text-[11px] text-slate-400">Total jobs in period</p>
                <div class="relative mt-3 h-44">
                    <canvas id="analytics-jobs-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</aside>

@push('scripts')
<script>
(function () {
    var _charts = {};
    var _loaded = false;

    function makeChart(id, labels, datasets) {
        if (_charts[id]) { _charts[id].destroy(); }
        var ctx = document.getElementById(id);
        if (!ctx) { return; }
        _charts[id] = new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 8, font: { size: 10 } },
                    },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 } } },
                },
            },
        });
    }

    function loadAnalytics(start, end) {
        $('#analytics-loading').removeClass('hidden');
        $('#analytics-charts').addClass('hidden');

        $.get('{{ route('dashboard.analytics.charts') }}', { start_date: start, end_date: end })
            .done(function (res) {
                makeChart('analytics-revenue-chart', res.revenue.labels, res.revenue.datasets);
                if (res.revenue.meta) {
                    $('#analytics-revenue-meta').text(res.revenue.meta.current_period + ' vs ' + res.revenue.meta.prev_period);
                }
                makeChart('analytics-jobs-chart', res.jobs.labels, res.jobs.datasets);
                if (res.jobs.meta) {
                    $('#analytics-jobs-total').text(res.jobs.meta.total + ' jobs');
                }
            })
            .always(function () {
                $('#analytics-loading').addClass('hidden');
                $('#analytics-charts').removeClass('hidden');
            });
    }

    window.analyticsReload = function (instance) {
        loadAnalytics(
            instance.startDate.format('YYYY-MM-DD'),
            instance.endDate.format('YYYY-MM-DD')
        );
    };

    var $sidebar = $('#analytics-sidebar');
    var storageKey = 'analyticsSidebarOpen';

    function openSidebar() {
        $sidebar.removeClass('hidden').addClass('xl:block');
        localStorage.setItem(storageKey, '1');
        if (!_loaded) {
            loadAnalytics('{{ $defaultStart }}', '{{ $defaultEnd }}');
            _loaded = true;
        }
    }

    function closeSidebar() {
        $sidebar.addClass('hidden');
        localStorage.setItem(storageKey, '0');
    }

    $('#analytics-sidebar-toggle').on('click', function () {
        if ($sidebar.hasClass('hidden')) {
            openSidebar();
        } else {
            closeSidebar();
        }
    });

    $('#analytics-sidebar-close').on('click', function () {
        closeSidebar();
    });

    // Restore persisted state; default open on first visit
    var stored = localStorage.getItem(storageKey);
    if (stored === null || stored === '1') {
        openSidebar();
    }
})();
</script>
@endpush

@php
    $defaultStart = now()->startOfMonth()->toDateString();
    $defaultEnd   = now()->toDateString();
@endphp

<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">

    {{-- Panel header: title + date range picker --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Business Analytics</h2>
            <p class="text-xs text-slate-500">Revenue & job performance</p>
        </div>
        <div class="w-64">
            <x-ui.daterange-picker
                name="analytics_tab_range"
                placeholder="Filter by scheduled date"
                :showRanges="true"
                :startDate="$defaultStart"
                :endDate="$defaultEnd"
                onChange="window.analyticsTabReload"
            />
        </div>
    </div>

    {{-- Tab buttons --}}
    <div class="border-b border-slate-200 px-4">
        <nav class="-mb-px flex gap-0">
            <button type="button" data-analytics-tab="revenue"
                class="analytics-tab-btn border-b-2 border-indigo-600 px-4 py-3 text-sm font-medium text-indigo-600 focus:outline-none">
                Revenue
            </button>
            <button type="button" data-analytics-tab="jobs"
                class="analytics-tab-btn border-b-2 border-transparent px-4 py-3 text-sm font-medium text-slate-500 hover:text-slate-700 focus:outline-none">
                Job Performance
            </button>
        </nav>
    </div>

    {{-- Loading state --}}
    <div id="analytics-tab-loading" class="hidden px-4 py-10 text-center text-sm text-slate-400">
        Loading…
    </div>

    {{-- Revenue tab --}}
    <div id="analytics-tab-revenue" class="analytics-tab-panel p-4">
        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-xs text-slate-500">Current period</p>
                <p id="analytics-rev-current" class="mt-1 text-xl font-bold text-slate-900">—</p>
            </div>
            <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-xs text-slate-500">Previous period</p>
                <p id="analytics-rev-prev" class="mt-1 text-xl font-bold text-slate-900">—</p>
            </div>
            <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-xs text-slate-500">Change</p>
                <p id="analytics-rev-change" class="mt-1 text-xl font-bold text-slate-900">—</p>
            </div>
        </div>
        <div class="relative h-64">
            <canvas id="analytics-revenue-chart"></canvas>
        </div>
        <p id="analytics-revenue-meta" class="mt-2 text-center text-[11px] text-slate-400"></p>
    </div>

    {{-- Job performance tab --}}
    <div id="analytics-tab-jobs" class="analytics-tab-panel hidden p-4">
        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-xs text-slate-500">Total jobs in period</p>
                <p id="analytics-jobs-total" class="mt-1 text-xl font-bold text-slate-900">—</p>
            </div>
            <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-xs text-slate-500">Selected range</p>
                <p id="analytics-jobs-range" class="mt-1 text-sm font-semibold text-slate-700">—</p>
            </div>
        </div>
        <div class="relative h-64">
            <canvas id="analytics-jobs-chart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var _charts = {};

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
                        labels: { boxWidth: 10, padding: 10, font: { size: 11 } },
                    },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 45 } },
                    y: { beginAtZero: true, ticks: { font: { size: 11 } } },
                },
            },
        });
    }

    function loadAnalytics(start, end) {
        $('#analytics-tab-loading').removeClass('hidden');
        $('.analytics-tab-panel').addClass('hidden');

        $.get('{{ route('dashboard.analytics.charts') }}', { start_date: start, end_date: end })
            .done(function (res) {
                // Revenue chart
                makeChart('analytics-revenue-chart', res.revenue.labels, res.revenue.datasets);

                var currentTotal = res.revenue.datasets[0]?.data?.reduce(function(a, b) { return a + b; }, 0) || 0;
                var prevTotal    = res.revenue.datasets[1]?.data?.reduce(function(a, b) { return a + b; }, 0) || 0;
                var change       = prevTotal > 0 ? (((currentTotal - prevTotal) / prevTotal) * 100).toFixed(1) : null;

                $('#analytics-rev-current').text('$' + currentTotal.toFixed(2));
                $('#analytics-rev-prev').text('$' + prevTotal.toFixed(2));

                if (change !== null) {
                    var sign = parseFloat(change) >= 0 ? '+' : '';
                    $('#analytics-rev-change')
                        .text(sign + change + '%')
                        .removeClass('text-emerald-600 text-red-600 text-slate-900')
                        .addClass(parseFloat(change) >= 0 ? 'text-emerald-600' : 'text-red-600');
                } else {
                    $('#analytics-rev-change').text('N/A').removeClass('text-emerald-600 text-red-600').addClass('text-slate-900');
                }

                if (res.revenue.meta) {
                    $('#analytics-revenue-meta').text(res.revenue.meta.current_period + ' vs ' + res.revenue.meta.prev_period);
                }

                // Jobs chart
                makeChart('analytics-jobs-chart', res.jobs.labels, res.jobs.datasets);

                if (res.jobs.meta) {
                    $('#analytics-jobs-total').text(res.jobs.meta.total + ' jobs');
                }
                $('#analytics-jobs-range').text(start + ' – ' + end);
            })
            .always(function () {
                $('#analytics-tab-loading').addClass('hidden');
                var $active = $('.analytics-tab-btn.border-indigo-600');
                var activeTab = $active.data('analytics-tab') || 'revenue';
                $('#analytics-tab-' + activeTab).removeClass('hidden');
            });
    }

    // Tab switching
    $(document).on('click', '.analytics-tab-btn', function () {
        var tab = $(this).data('analytics-tab');

        $('.analytics-tab-btn')
            .removeClass('border-indigo-600 text-indigo-600')
            .addClass('border-transparent text-slate-500');

        $(this)
            .removeClass('border-transparent text-slate-500')
            .addClass('border-indigo-600 text-indigo-600');

        $('.analytics-tab-panel').addClass('hidden');
        $('#analytics-tab-' + tab).removeClass('hidden');
    });

    // Called by the daterange picker onChange
    window.analyticsTabReload = function (instance) {
        loadAnalytics(
            instance.startDate.format('YYYY-MM-DD'),
            instance.endDate.format('YYYY-MM-DD')
        );
    };

    // Initial load
    loadAnalytics('{{ $defaultStart }}', '{{ $defaultEnd }}');
})();
</script>
@endpush

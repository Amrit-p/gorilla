@php
    $mowerPerformanceDefaultStart = now()->startOfMonth()->toDateString();
    $mowerPerformanceDefaultEnd   = now()->toDateString();
@endphp

<div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Mower performance</h3>
            <p class="mt-0.5 text-xs text-slate-500">Completed jobs and logged hours by crew member</p>
        </div>
        <div class="w-64">
            <x-ui.daterange-picker
                name="mower_performance_range"
                placeholder="Filter by date range"
                :showRanges="true"
                :startDate="$mowerPerformanceDefaultStart"
                :endDate="$mowerPerformanceDefaultEnd"
                onChange="window.mowerPerformanceReload"
            />
        </div>
    </div>

    <div id="mower-performance-table-container" class="mt-3">
        @include('dashboard.partials.mower-performance-table-rows', ['mowerPerformance' => $analytics['mower_performance'] ?? []])
    </div>
</div>

@push('scripts')
<script>
(function () {
    function loadMowerPerformance(start, end) {
        $('#mower-performance-table-container').html('<p class="px-3 py-6 text-center text-sm text-slate-400">Loading…</p>');

        $.get('{{ route('dashboard.mower-performance-table') }}', { start_date: start, end_date: end })
            .done(function (html) {
                $('#mower-performance-table-container').html(html);
            });
    }

    window.mowerPerformanceReload = function (instance) {
        loadMowerPerformance(
            instance.startDate.format('YYYY-MM-DD'),
            instance.endDate.format('YYYY-MM-DD')
        );
    };
})();
</script>
@endpush

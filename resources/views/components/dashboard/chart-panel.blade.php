@props([
    'title',
    'subtitle' => null,
    'chartId',
    'type' => 'bar',
    'labels' => [],
    'datasets' => [],
    'height' => '240px',
])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200 bg-white p-4 shadow-sm']) }}>
    <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
    @if ($subtitle)
        <p class="mt-0.5 text-xs text-slate-500">{{ $subtitle }}</p>
    @endif
    <div class="mt-3" style="height: {{ $height }}">
        <canvas
            id="{{ $chartId }}"
            data-crm-chart
            data-chart-type="{{ $type }}"
            data-chart-labels='@json($labels)'
            data-chart-datasets='@json($datasets)'
        ></canvas>
    </div>
</div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
        <script src="{{ asset('js/dashboard-charts.js') }}"></script>
    @endpush
@endonce

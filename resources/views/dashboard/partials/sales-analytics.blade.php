@php
    $cards = $analytics['cards'] ?? [];
    $charts = $analytics['charts'] ?? [];
@endphp

<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <x-dashboard.stat-card
        :label="$cards['jobs_today']['label']"
        :value="$cards['jobs_today']['value']"
        :subtitle="$cards['jobs_today']['subtitle']"
        :accent="$cards['jobs_today']['accent']"
    />
</div>

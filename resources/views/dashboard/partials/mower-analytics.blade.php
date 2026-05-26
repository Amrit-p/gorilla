@php
    $cards = $analytics['cards'] ?? [];
    $charts = $analytics['charts'] ?? [];
@endphp

<div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
    <x-dashboard.stat-card
        :label="$cards['todays_jobs']['label']"
        :value="$cards['todays_jobs']['value']"
        :subtitle="$cards['todays_jobs']['subtitle']"
        :accent="$cards['todays_jobs']['accent']"
    />
    <x-dashboard.stat-card
        :label="$cards['completed_hours']['label']"
        :value="$cards['completed_hours']['value']"
        :subtitle="$cards['completed_hours']['subtitle']"
        :accent="$cards['completed_hours']['accent']"
    />
    <x-dashboard.stat-card
        :label="$cards['pending_jobs']['label']"
        :value="$cards['pending_jobs']['value']"
        :subtitle="$cards['pending_jobs']['subtitle']"
        :accent="$cards['pending_jobs']['accent']"
    />
</div>

<x-dashboard.chart-panel
    class="max-w-xl"
    title="Hours logged this week"
    subtitle="Completed jobs only"
    chart-id="mower-weekly-hours-chart"
    type="bar"
    :labels="$charts['weekly_hours']['labels'] ?? []"
    :datasets="$charts['weekly_hours']['datasets'] ?? []"
    height="200px"
/>

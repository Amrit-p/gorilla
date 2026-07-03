@php
    $cards = $analytics['cards'] ?? [];
    $charts = $analytics['charts'] ?? [];
@endphp

<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <x-dashboard.stat-card
        :label="$cards['conversion_rate']['label']"
        :value="$cards['conversion_rate']['value']"
        :subtitle="$cards['conversion_rate']['subtitle']"
        :accent="$cards['conversion_rate']['accent']"
    />
    <x-dashboard.stat-card
        :label="$cards['new_leads']['label']"
        :value="$cards['new_leads']['value']"
        :subtitle="$cards['new_leads']['subtitle']"
        :accent="$cards['new_leads']['accent']"
    />
    <x-dashboard.stat-card
        :label="$cards['follow_up']['label']"
        :value="$cards['follow_up']['value']"
        :subtitle="$cards['follow_up']['subtitle']"
        :accent="$cards['follow_up']['accent']"
    />
    <x-dashboard.stat-card
        :label="$cards['jobs_today']['label']"
        :value="$cards['jobs_today']['value']"
        :subtitle="$cards['jobs_today']['subtitle']"
        :accent="$cards['jobs_today']['accent']"
    />
</div>

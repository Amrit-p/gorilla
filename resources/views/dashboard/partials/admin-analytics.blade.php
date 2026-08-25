@php
    $cards = $analytics['cards'] ?? [];
    $charts = $analytics['charts'] ?? [];
@endphp

<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <x-dashboard.stat-card
        :label="$cards['total_revenue']['label']"
        :value="$cards['total_revenue']['value']"
        :subtitle="$cards['total_revenue']['subtitle']"
        :accent="$cards['total_revenue']['accent']"
    />
    <x-dashboard.stat-card
        :label="$cards['pending_payments']['label']"
        :value="$cards['pending_payments']['value']"
        :subtitle="$cards['pending_payments']['subtitle']"
        :accent="$cards['pending_payments']['accent']"
    />
    <x-dashboard.stat-card
        :label="$cards['jobs_today']['label']"
        :value="$cards['jobs_today']['value']"
        :subtitle="$cards['jobs_today']['subtitle']"
        :accent="$cards['jobs_today']['accent']"
    />
    <x-dashboard.stat-card
        :label="$cards['jobs_tomorrow']['label']"
        :value="$cards['jobs_tomorrow']['value']"
        :subtitle="$cards['jobs_tomorrow']['subtitle']"
        :accent="$cards['jobs_tomorrow']['accent']"
    />
</div>

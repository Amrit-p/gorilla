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
        :label="$cards['mature_leads']['label']"
        :value="$cards['mature_leads']['value']"
        :subtitle="$cards['mature_leads']['subtitle']"
        :accent="$cards['mature_leads']['accent']"
    />
    <x-dashboard.stat-card
        :label="$cards['follow_up']['label']"
        :value="$cards['follow_up']['value']"
        :subtitle="$cards['follow_up']['subtitle']"
        :accent="$cards['follow_up']['accent']"
    />
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <x-dashboard.chart-panel
        title="Lead pipeline"
        subtitle="Current count by status"
        chart-id="sales-leads-chart"
        type="doughnut"
        :labels="$charts['leads_by_status']['labels'] ?? []"
        :datasets="$charts['leads_by_status']['datasets'] ?? []"
    />
    <x-dashboard.chart-panel
        title="Conversion activity"
        subtitle="New vs mature/won — last 14 days"
        chart-id="sales-conversion-chart"
        type="line"
        :labels="$charts['conversion_trend']['labels'] ?? []"
        :datasets="$charts['conversion_trend']['datasets'] ?? []"
    />
</div>

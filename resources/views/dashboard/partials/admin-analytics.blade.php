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
        :label="$cards['completed_jobs']['label']"
        :value="$cards['completed_jobs']['value']"
        :subtitle="$cards['completed_jobs']['subtitle']"
        :accent="$cards['completed_jobs']['accent']"
    />
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <x-dashboard.chart-panel
        title="Revenue trend"
        subtitle="Last 7 days — completed & received"
        chart-id="admin-revenue-chart"
        type="bar"
        :labels="$charts['revenue_trend']['labels'] ?? []"
        :datasets="$charts['revenue_trend']['datasets'] ?? []"
    />
    <x-dashboard.chart-panel
        title="Jobs by status"
        subtitle="Scheduled in the last 30 days"
        chart-id="admin-jobs-status-chart"
        type="doughnut"
        :labels="$charts['jobs_by_status']['labels'] ?? []"
        :datasets="$charts['jobs_by_status']['datasets'] ?? []"
    />
</div>

<div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-900">Mower performance (MTD)</h3>
    <p class="mt-0.5 text-xs text-slate-500">Completed jobs and logged hours by crew member</p>
    <div class="mt-3 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 font-semibold">Mower</th>
                    <th class="px-3 py-2 font-semibold">Completed</th>
                    <th class="px-3 py-2 font-semibold">Hours</th>
                    <th class="px-3 py-2 font-semibold">Efficiency</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($analytics['mower_performance'] ?? [] as $row)
                    <tr>
                        <td class="px-3 py-2 font-medium text-slate-800">{{ $row['name'] }}</td>
                        <td class="px-3 py-2 text-slate-700">{{ $row['completed_jobs'] }}</td>
                        <td class="px-3 py-2 text-slate-700">{{ $row['hours'] }}h</td>
                        <td class="px-3 py-2 text-slate-600">{{ $row['efficiency'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-6 text-center text-slate-500">No completed jobs this month yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

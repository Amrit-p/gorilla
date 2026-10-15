@php
    $cards = $analytics['cards'] ?? [];
    $charts = $analytics['charts'] ?? [];
@endphp

<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">{{ $cards['total_revenue']['label'] }}</div>
        <div class="grid grid-cols-2 gap-3 p-3">
            <div>
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Completed</p>
                <p class="text-xl font-bold text-slate-900">{{ $cards['total_revenue']['completed_amount'] }}</p>
            </div>
            <div>
                <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Received</p>
                <p class="text-xl font-bold text-emerald-700">{{ $cards['total_revenue']['received_amount'] }}</p>
                <p class="text-[10px] text-slate-400">incl. partial payments</p>
            </div>
        </div>
    </div>
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

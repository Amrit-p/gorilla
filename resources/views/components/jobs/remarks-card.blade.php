@props(['empty' => false])

<div id="job-remarks-card" class="rounded-2xl border border-slate-200 bg-white p-6">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-sm font-semibold text-slate-900">This visit remarks</h3>
        <button type="button" id="job-remarks-toggle-btn" class="hidden text-xs font-medium text-emerald-700 hover:underline"></button>
    </div>
    <div id="job-last-remark" class="mt-3">
        @if ($empty)
            <p class="text-sm text-slate-400">Select a customer to see remarks.</p>
        @else
            <p class="animate-pulse text-sm text-slate-400">Loading remarks…</p>
        @endif
    </div>
    <div id="job-all-remarks-list" class="mt-3 hidden space-y-3"></div>
</div>

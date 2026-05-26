@props(['statistics' => []])

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Total jobs</p>
        <p class="mt-1 text-xl font-semibold text-slate-900">{{ $statistics['total_jobs'] ?? 0 }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Completed</p>
        <p class="mt-1 text-xl font-semibold text-emerald-700">{{ $statistics['completed_jobs'] ?? 0 }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Active</p>
        <p class="mt-1 text-xl font-semibold text-amber-700">{{ $statistics['active_jobs'] ?? 0 }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Cancelled</p>
        <p class="mt-1 text-xl font-semibold text-slate-700">{{ $statistics['cancelled_jobs'] ?? 0 }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Last job</p>
        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $statistics['last_job_date'] ? \Illuminate\Support\Carbon::parse($statistics['last_job_date'])->format('M j, Y') : '—' }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Profile charges</p>
        <p class="mt-1 text-xl font-semibold text-slate-900">${{ number_format((float) ($statistics['lifetime_charges'] ?? 0), 2) }}</p>
    </div>
</div>

@php
    /** @var \App\Models\Job $job */
    $services = is_array($job->required_services) && count($job->required_services)
        ? implode(', ', $job->required_services)
        : '—';
    $canVerify = auth()->user()?->can('verify-jobs') ?? false;
    $canUpdate = auth()->user()?->can('update', $job) ?? false;
    $canDelete = auth()->user()?->can('delete', $job) ?? false;
    $assigned = $job->assignedEmployees->pluck('name')->filter()->implode(', ')
        ?: ($job->doneByUser?->name ?? '—');
    $freq = $job->recurrence?->name ?: ($job->is_recurring ? 'Recurring' : 'One-time');
    $isPaid = $job->payment_status === \App\Enums\JobOperationalPaymentStatus::RECEIVED->value;
@endphp

<div class="customer-details-shell flex min-h-[70vh] flex-col" data-job-id="{{ $job->id }}">
    {{-- Header --}}
    <div class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/80 px-8 py-5">
        <div class="flex min-w-0 items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-xl font-semibold tracking-tight text-slate-900">Customer Details</h3>
                <p class="mt-0.5 truncate text-sm text-slate-500">
                    <span class="font-medium text-slate-700">{{ $job->customerDisplayName() }}</span>
                    <span class="mx-1.5 text-slate-300">·</span>
                    Job #{{ $job->id }}
                    <span class="mx-1.5 text-slate-300">·</span>
                    {{ $services }}
                </p>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <x-jobs.status-badge :status="$job->status" />
            <button type="button" data-close-modal="customer-details-modal"
                    class="rounded-xl p-2.5 text-slate-400 transition hover:bg-white hover:text-slate-700"
                    aria-label="Close">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Body --}}
    <div class="grid flex-1 lg:grid-cols-[1.05fr_1.35fr_0.95fr]">
        {{-- Left: customer & schedule (contact once) --}}
        <div class="border-b border-slate-200 bg-white px-8 py-6 lg:border-b-0 lg:border-r">
            <p class="mb-4 text-xs font-bold uppercase tracking-[0.12em] text-slate-400">Profile &amp; schedule</p>

            <div class="space-y-5">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Address</p>
                    <p class="mt-1.5 text-[15px] font-medium leading-snug text-slate-900">{{ $job->client_address ?: '—' }}</p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Phone</p>
                        <p class="mt-1.5 text-[15px] font-medium text-slate-900">{{ $job->phone ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Email</p>
                        <p class="mt-1.5 break-all text-[15px] font-medium text-slate-900">{{ $job->email ?: '—' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Zone</p>
                        <p class="mt-1.5">
                            @if ($job->zone)
                                <span class="inline-flex rounded-lg bg-slate-900 px-3 py-1 text-xs font-semibold text-white">{{ $job->zone->name }}</span>
                            @else
                                <span class="text-[15px] text-slate-400">—</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Acc. level</p>
                        <p class="mt-1.5">
                            @if ($job->accountingLevel)
                                <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $job->accountingLevel->name }}</span>
                            @else
                                <span class="text-[15px] text-slate-400">—</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Service</p>
                            <p class="mt-1 text-sm font-semibold text-sky-600">{{ $services }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Job type</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $job->job_type ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Freq</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $freq }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Next date</p>
                            <p class="mt-1 text-sm font-semibold text-emerald-600">{{ $next_date ?: '—' }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Assigned mower</p>
                    <p class="mt-1.5 text-[15px] font-medium text-slate-900">{{ $assigned }}</p>
                </div>

                @if ($job->site_instructions)
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Site instructions</p>
                        <p class="mt-1.5 whitespace-pre-wrap text-sm leading-relaxed text-slate-600">{{ $job->site_instructions }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Center: past visits only (current job payment lives in the sidebar) --}}
        <div class="flex flex-col border-b border-slate-200 bg-white px-8 py-6 lg:border-b-0 lg:border-r">
            <div class="mb-4 flex items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-400">Past visits</p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ count($service_history) }} previous {{ count($service_history) === 1 ? 'job' : 'jobs' }}
                        <span class="text-slate-300">·</span>
                        payment for this job is on the right
                    </p>
                </div>
            </div>

            <div class="min-h-0 flex-1 space-y-3 overflow-y-auto pr-1" style="max-height: min(58vh, 560px)">
                @forelse ($service_history as $row)
                    <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3.5 transition hover:border-slate-300 hover:bg-slate-50/60">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline gap-x-3 gap-y-0.5">
                                <span class="text-[15px] font-semibold text-slate-900">{{ $row['date'] ?? '—' }}</span>
                                <span class="text-sm text-slate-500">{{ $row['mower'] }}</span>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if ($row['is_paid'])
                                <span class="text-sm font-semibold text-emerald-700">${{ number_format($row['charges'], 2) }}</span>
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Paid</span>
                            @else
                                <span class="text-sm font-semibold text-amber-800">${{ number_format($row['charges'], 2) }}</span>
                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800">Unpaid</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="flex h-40 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 px-6 text-center">
                        <p class="text-sm font-medium text-slate-500">No previous visits yet</p>
                        <p class="mt-1 text-xs text-slate-400">Manage this job’s payment in the sidebar →</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Right sidebar: manage payment for this job, then edit --}}
        <aside class="flex flex-col bg-slate-50 px-7 py-6">
            <p class="mb-4 text-xs font-bold uppercase tracking-[0.12em] text-slate-400">Payment detail</p>

            <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4">
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">This job #{{ $job->id }}</span>
                        <span class="text-lg font-bold text-slate-900">${{ number_format((float) ($job->charges ?? 0), 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm text-slate-500">Pay mode</span>
                        <span class="text-sm font-semibold text-slate-800">{{ $job->payment_mode ?: '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm text-slate-500">Status</span>
                        @if ($isPaid)
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold uppercase text-emerald-700">Received</span>
                        @else
                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold uppercase text-amber-800">{{ $job->payment_status ?: 'Pending' }}</span>
                        @endif
                    </div>
                    @if ($job->payment_pending_reason)
                        <div class="rounded-xl bg-amber-50 px-3 py-2 text-xs leading-relaxed text-amber-800">
                            {{ $job->payment_pending_reason }}
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-3">
                        @if (! $isPaid && $canVerify && ! $job->isVerified())
                            <button type="button"
                                    class="customer-details-verify inline-flex flex-1 items-center justify-center rounded-xl border border-emerald-400 bg-emerald-50 px-3 py-2.5 text-xs font-bold uppercase tracking-wide text-emerald-700 hover:bg-emerald-100"
                                    data-id="{{ $job->id }}">
                                Verify payment
                            </button>
                        @endif
                        @if ($canDelete)
                            <button type="button"
                                    class="customer-details-delete inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2.5 text-xs font-bold uppercase tracking-wide text-rose-600 hover:bg-rose-50"
                                    data-id="{{ $job->id }}"
                                    title="Delete job">
                                Delete
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl bg-slate-800 px-5 py-4 text-white">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-300">Total revenue</p>
                    <p class="mt-1 text-3xl font-bold tracking-tight">${{ number_format($total_revenue, 2) }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-600 px-5 py-4 text-white">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-100">Payment received</p>
                    <p class="mt-1 text-3xl font-bold tracking-tight">${{ number_format($payment_received, 2) }}</p>
                </div>
                <div class="rounded-2xl bg-rose-500 px-5 py-4 text-white">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-100">Balance due</p>
                    <p class="mt-1 text-3xl font-bold tracking-tight">${{ number_format($balance_due, 2) }}</p>
                </div>
            </div>

            <div class="mt-auto space-y-2.5 pt-6">
                @if ($canUpdate)
                    <a href="{{ route('admin.jobs.edit', $job) }}"
                       class="flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                        </svg>
                        Edit profile
                    </a>
                @endif
                <a href="{{ route('admin.jobs.show', $job) }}"
                   class="flex w-full items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                    Open full job page
                </a>
            </div>
        </aside>
    </div>
</div>

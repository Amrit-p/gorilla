@php
    $monthLabel = \Illuminate\Support\Carbon::create($year, $month, 1)->format('F Y');
    $paidOnPage = collect($jobs->items())->filter(fn ($job) => ($job->mower_payout_id ?? null) !== null)->count();
@endphp

{{--
    The id is what the shared jobs table partial binds its selection JS to, so
    keeping it here gives the drill-down the full toolbar, long-press bulk mode
    and select-all behaviour without duplicating any of it.
--}}
<div id="jobs-table-container" data-mower-id="{{ $mower->id }}" data-year="{{ $year }}" data-month="{{ $month }}">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-indigo-600 shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ $mower->name }} &middot; {{ $monthLabel }}</p>
                <p class="text-xs text-slate-500">
                    {{ $jobs->total() }} completed {{ \Illuminate\Support\Str::plural('job', $jobs->total()) }}
                    @if ($paidOnPage > 0)
                        &middot; {{ $paidOnPage }} already paid out on this page
                    @endif
                    &middot; select rows, then use <span class="font-medium text-emerald-700">Payout</span> in the toolbar to create or edit
                </p>
            </div>
        </div>

        <button type="button" class="salary-jobs-close inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
            Close
        </button>
    </div>

    @if ($jobs->total() === 0)
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-400">
            No completed jobs for {{ $mower->name }} in {{ $monthLabel }}.
        </div>
    @else
        @include('admin.jobs.partials.table', ['jobs' => $jobs, 'payoutMode' => true])
    @endif
</div>

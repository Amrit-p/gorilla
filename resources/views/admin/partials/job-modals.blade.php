{{-- ── Assign Mowers ─────────────────────────────────────────────────────── --}}
<x-ui.modal id="assign-job-modal" title="Assign Mowers" maxWidth="max-w-2xl">

    {{-- Tab nav --}}
    <div class="-mx-5 mb-4 flex border-b border-slate-200 px-5" role="tablist">
        <button type="button" role="tab" aria-selected="true"
            class="job-tab-btn job-tab-active mr-1 inline-flex items-center gap-1.5 border-b-2 border-emerald-500 px-3 py-2 text-sm font-medium text-emerald-600 transition-colors"
            data-modal="assign-job-modal" data-tab="form">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3.75 19.5a7.5 7.5 0 0 1 12.122-5.894"/>
            </svg>
            Assign Mowers
        </button>
        <button type="button" role="tab" aria-selected="false"
            class="job-tab-btn mr-1 inline-flex items-center gap-1.5 border-b-2 border-transparent px-3 py-2 text-sm font-medium text-slate-500 transition-colors hover:text-slate-700"
            data-modal="assign-job-modal" data-tab="contractors">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
            </svg>
            Contractors
        </button>
        <button type="button" role="tab" aria-selected="false"
            class="job-tab-btn mr-1 inline-flex items-center gap-1.5 border-b-2 border-transparent px-3 py-2 text-sm font-medium text-slate-500 transition-colors hover:text-slate-700"
            data-modal="assign-job-modal" data-tab="history">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
            </svg>
            Client History
            <span class="job-history-dot hidden h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
        </button>
    </div>

    {{-- Form tab --}}
    <div class="job-tab-panel" data-modal="assign-job-modal" data-panel="form">
        <form id="assign-job-form" class="space-y-3">
            @csrf
            <p class="bulk-job-context hidden rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700"></p>
            <x-jobs.mower-assignment :employees="$employees" idPrefix="assign" />
            <x-ui.button type="submit">Assign</x-ui.button>
        </form>
    </div>

    {{-- Contractors tab --}}
    <div class="job-tab-panel hidden" data-modal="assign-job-modal" data-panel="contractors">
        <div id="assign-contractors-panel">
            <p class="mb-3 text-xs text-slate-500">Select an active contract to link to this job. This is optional — mowers can also be assigned without a contract.</p>
            <div id="assign-contracts-loading" class="flex items-center justify-center py-8 text-slate-400">
                <svg class="h-5 w-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                Loading contracts…
            </div>
            <div id="assign-contracts-list" class="hidden space-y-2"></div>
            <p id="assign-contracts-empty" class="hidden py-6 text-center text-sm text-slate-400">No active contracts found.</p>
            <div id="assign-contracts-actions" class="hidden mt-3 flex items-center gap-2">
                <button type="button" id="assign-contract-submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-violet-700 disabled:opacity-50">
                    Link Contract
                </button>
                <button type="button" id="assign-contract-clear"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50">
                    Remove Contract
                </button>
            </div>
        </div>
    </div>

    {{-- History tab --}}
    <div class="job-tab-panel hidden" data-modal="assign-job-modal" data-panel="history">
        <div id="assign-client-history">
            @include('admin.partials.client-history-panel', ['tabbed' => true])
        </div>
    </div>

</x-ui.modal>

{{-- ── Contractor Details ────────────────────────────────────────────────── --}}
<x-ui.modal id="contractor-details-modal" title="Contractor Details">
    <div id="contractor-details-body" class="space-y-4">
        {{-- Contractor info --}}
        <div>
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Contractor</h3>
            <p id="cd-name" class="text-sm font-semibold text-slate-800"></p>
            <div class="mt-1.5 space-y-1">
                <p id="cd-phone-row" class="hidden flex items-center gap-1.5 text-sm text-slate-600">
                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                    <span id="cd-phone"></span>
                </p>
                <p id="cd-email-row" class="hidden flex items-center gap-1.5 text-sm text-slate-600">
                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                    <a id="cd-email" href="#" class="hover:text-emerald-600 hover:underline"></a>
                </p>
            </div>
        </div>
        {{-- Contract info --}}
        <div id="cd-contract-section" class="hidden">
            <div class="border-t border-slate-100 pt-3">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Linked Contract</h3>
                <p id="cd-contract-name" class="text-sm font-semibold text-slate-800"></p>
                <div class="mt-1.5 grid grid-cols-2 gap-2">
                    <div>
                        <p class="text-xs text-slate-400">Start Date</p>
                        <p id="cd-contract-start" class="text-sm text-slate-700">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">End Date</p>
                        <p id="cd-contract-end" class="text-sm text-slate-700">—</p>
                    </div>
                </div>
                <div class="mt-2">
                    <span id="cd-contract-status" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"></span>
                </div>
            </div>
        </div>
    </div>
</x-ui.modal>

{{-- ── Reschedule ─────────────────────────────────────────────────────────── --}}
<x-ui.modal id="schedule-job-modal" title="Reschedule Job">

    {{-- Tab nav --}}
    <div class="-mx-5 mb-4 flex border-b border-slate-200 px-5" role="tablist">
        <button type="button" role="tab" aria-selected="true"
            class="job-tab-btn job-tab-active mr-1 inline-flex items-center gap-1.5 border-b-2 border-emerald-500 px-3 py-2 text-sm font-medium text-emerald-600 transition-colors"
            data-modal="schedule-job-modal" data-tab="form">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
            Schedule
        </button>
        <button type="button" role="tab" aria-selected="false"
            class="job-tab-btn mr-1 inline-flex items-center gap-1.5 border-b-2 border-transparent px-3 py-2 text-sm font-medium text-slate-500 transition-colors hover:text-slate-700"
            data-modal="schedule-job-modal" data-tab="history">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
            </svg>
            Client History
            <span class="job-history-dot hidden h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
        </button>
    </div>

    {{-- Form tab --}}
    <div class="job-tab-panel" data-modal="schedule-job-modal" data-panel="form">
        <form id="schedule-job-form" class="space-y-3">
            @csrf
            <p class="bulk-job-context hidden rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700"></p>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Date <span class="text-red-500">*</span></label>
                <input type="date" name="scheduled_date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Time <span class="text-xs font-normal text-slate-400">(optional)</span></label>
                <input type="time" name="scheduled_time" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <x-ui.button type="submit">Reschedule</x-ui.button>
        </form>
    </div>

    {{-- History tab --}}
    <div class="job-tab-panel hidden" data-modal="schedule-job-modal" data-panel="history">
        <div id="schedule-client-history">
            @include('admin.partials.client-history-panel', ['tabbed' => true])
        </div>
    </div>

</x-ui.modal>

{{-- ── Status ─────────────────────────────────────────────────────────────── --}}
<x-ui.modal id="status-job-modal" title="Update Job Status">
    <form id="status-job-form" class="space-y-3">
        @csrf
        <p class="bulk-job-context hidden rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700"></p>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                @foreach ($workflowStatuses as $workflowStatus)
                    <option value="{{ $workflowStatus }}">{{ $workflowStatus }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit">Update Status</x-ui.button>
    </form>
</x-ui.modal>

{{-- ── Remarks ─────────────────────────────────────────────────────────────── --}}
<x-ui.modal id="remarks-job-modal" title="Job Remarks" maxWidth="max-w-2xl">
    <form id="remarks-job-form" class="space-y-4">
        @csrf
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Special Remarks</label>
            <textarea name="special_remarks" rows="5" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" placeholder="No special remarks."></textarea>
        </div>
        @can('manage-job-records')
            @unless(auth()->user()?->hasRole(\App\Support\CrmRoles::MOWER))
                <x-ui.button type="submit">Save Remarks</x-ui.button>
            @endunless
        @endcan
    </form>
</x-ui.modal>

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

    {{-- History tab --}}
    <div class="job-tab-panel hidden" data-modal="assign-job-modal" data-panel="history">
        <div id="assign-client-history">
            @include('admin.partials.client-history-panel', ['tabbed' => true])
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

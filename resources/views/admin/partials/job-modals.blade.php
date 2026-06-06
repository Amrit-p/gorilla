<x-ui.modal id="assign-job-modal" title="Assign Mowers">
    <form id="assign-job-form" class="space-y-3">
        @csrf
        <p class="bulk-job-context hidden rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700"></p>
        <x-jobs.mower-assignment :employees="$employees" idPrefix="assign" />
        <x-ui.button type="submit">Assign</x-ui.button>
    </form>
</x-ui.modal>

<x-ui.modal id="schedule-job-modal" title="Reschedule Job">
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
</x-ui.modal>

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

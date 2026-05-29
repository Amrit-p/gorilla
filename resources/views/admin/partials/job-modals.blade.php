<x-ui.modal id="assign-job-modal" title="Assign Mowers">
    <form id="assign-job-form" class="space-y-3">
        @csrf
        <input type="hidden" name="job_id">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Mowers</label>
            <select name="employee_ids[]" multiple class="min-h-32 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->efficiency ?? 'Average' }})</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit">Assign</x-ui.button>
    </form>
</x-ui.modal>

<x-ui.modal id="status-job-modal" title="Update Job Status">
    <form id="status-job-form" class="space-y-3">
        @csrf
        <input type="hidden" name="job_id">
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

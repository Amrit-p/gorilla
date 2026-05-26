@php
    $jobModel = $job ?? null;
    $assignedIds = $assignedIds ?? [];
@endphp

<div class="sm:col-span-2 rounded-xl border border-slate-200 bg-white p-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Mower workload</h3>
            <p class="text-xs text-slate-500">Only users with the Mower role can be assigned. Minutes shown are for the selected job date.</p>
        </div>
        <button type="button" id="job-refresh-workloads" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs hover:bg-slate-50">Refresh</button>
    </div>
    <div id="job-mower-workloads" class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
        @foreach ($mowerWorkloads ?? [] as $workload)
            <div class="rounded-lg border border-slate-200 px-3 py-2 text-sm" data-mower-id="{{ $workload['id'] }}">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-medium text-slate-800">{{ $workload['name'] }}</span>
                    <x-ui.efficiency-badge :efficiency="$workload['efficiency']" />
                </div>
                <p class="mt-1 text-xs text-slate-500">{{ $workload['assigned_minutes'] }} min • {{ $workload['job_count'] }} jobs • load {{ $workload['adjusted_load'] }}</p>
            </div>
        @endforeach
    </div>
    <div class="mt-3 flex flex-wrap gap-2">
        <button type="button" id="job-suggest-mower" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">Smart assign suggestion</button>
        <p id="job-suggestion-text" class="text-xs text-slate-600"></p>
    </div>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Primary mower (done by)</label>
    <select name="done_by_user_id" id="job-done-by" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        <option value="">Unassigned</option>
        @foreach ($employees as $employee)
            <option value="{{ $employee->id }}" @selected((string) old('done_by_user_id', $jobModel?->done_by_user_id) === (string) $employee->id)>{{ $employee->name }} ({{ $employee->efficiency ?? 'Average' }})</option>
        @endforeach
    </select>
</div>

<div class="sm:col-span-2">
    <label class="mb-1 block text-sm font-medium text-slate-700">Assigned mowers</label>
    <select name="employee_ids[]" id="job-employee-ids" multiple class="min-h-32 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        @foreach ($employees as $employee)
            <option value="{{ $employee->id }}" @selected(in_array($employee->id, $assignedIds, true))>{{ $employee->name }} — {{ $employee->efficiency ?? 'Average' }}</option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-slate-500">Hold Ctrl/Cmd to select multiple mowers.</p>
</div>

@if ($jobModel)
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">Workflow status</label>
        <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            @foreach ($workflowStatuses as $workflowStatus)
                <option value="{{ $workflowStatus }}" @selected(old('status', $jobModel->status) === $workflowStatus)>{{ $workflowStatus }}</option>
            @endforeach
        </select>
    </div>
@endif

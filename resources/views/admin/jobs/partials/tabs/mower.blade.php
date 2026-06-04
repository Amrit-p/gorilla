@php
    $jobModel    = $job ?? null;
    $assignedIds = $assignedIds ?? [];
@endphp

<x-jobs.mower-assignment
    :employees="$employees"
    :primaryMowerId="$jobModel?->done_by_user_id"
    :assignedIds="$assignedIds"
/>

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

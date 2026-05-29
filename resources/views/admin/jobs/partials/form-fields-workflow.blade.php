@php
    $jobModel = $job ?? null;
@endphp

<div class="border-t border-slate-200 pt-3">
    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Scheduling workflow</p>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Priority</label>
            <select name="priority" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                @foreach (['Low', 'Medium', 'High', 'Urgent'] as $priority)
                    <option value="{{ $priority }}" @selected(old('priority', $jobModel?->priority) === $priority)>{{ $priority }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Operational status</label>
            <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                @foreach (['Pending','Assigned','En Route','On Site','Completed','Cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $jobModel?->status) === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-3 grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Recurring</label>
            <select name="is_recurring" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="0" @selected(! old('is_recurring', $jobModel?->is_recurring))>No</option>
                <option value="1" @selected((bool) old('is_recurring', $jobModel?->is_recurring))>Yes</option>
            </select>
        </div>
        <x-ui.input label="Route sequence" name="route_sequence" type="number" :value="old('route_sequence', $jobModel?->route_sequence ?? 0)" />
    </div>

    <div class="mt-3">
        <label class="mb-1 block text-sm font-medium text-slate-700">Recurrence <span class="text-red-500">*</span></label>
        <select name="recurrence_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required>
            <option value="">Select recurrence</option>
            @foreach ($recurrences as $recurrence)
                <option value="{{ $recurrence->id }}" @selected((string) old('recurrence_id', $jobModel?->recurrence_id) === (string) $recurrence->id)>{{ $recurrence->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mt-3">
        <label class="mb-1 block text-sm font-medium text-slate-700">Internal notes</label>
        <textarea name="internal_notes" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('internal_notes', $jobModel?->internal_notes) }}</textarea>
    </div>
</div>

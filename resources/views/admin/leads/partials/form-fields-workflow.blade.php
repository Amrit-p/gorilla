@php
    $leadModel = $lead ?? null;
@endphp

<div class="border-t border-slate-200 pt-4 sm:col-span-2">
    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Workflow</p>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Assigned sales manager</label>
            <select name="assigned_sales_user_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Unassigned</option>
                @foreach ($salesUsers as $salesUser)
                    <option value="{{ $salesUser->id }}" @selected((string) old('assigned_sales_user_id', $leadModel?->assigned_sales_user_id) === (string) $salesUser->id)>
                        {{ $salesUser->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Lead status</label>
            <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $leadModel?->status) === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<form id="job-filter-form" class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4">
    <div class="flex flex-wrap gap-2">
        @foreach ($listScopes as $scopeKey => $scopeLabel)
            <button
                type="button"
                data-list-scope="{{ $scopeKey }}"
                class="job-list-scope rounded-full border px-3 py-1.5 text-sm font-medium {{ ($filters['list_scope'] ?? '') === $scopeKey ? 'border-emerald-600 bg-emerald-50 text-emerald-800' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}"
            >
                {{ $scopeLabel }}
            </button>
        @endforeach
        <button type="button" data-list-scope="" class="job-list-scope rounded-full border px-3 py-1.5 text-sm {{ ($filters['list_scope'] ?? '') === '' ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-300 text-slate-600' }}">All</button>
    </div>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
        <input type="hidden" name="list_scope" id="job-list-scope" value="{{ $filters['list_scope'] ?? '' }}">
        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search customer, address, ID..." class="rounded-md border border-slate-300 px-3 py-2 text-sm sm:col-span-2">
        <select name="status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option value="">Any workflow status</option>
            @foreach ($workflowStatuses as $workflowStatus)
                <option value="{{ $workflowStatus }}" @selected(($filters['status'] ?? '') === $workflowStatus)>{{ $workflowStatus }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50">Apply</button>
    </div>
</form>

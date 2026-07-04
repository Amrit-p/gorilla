<x-ui.modal id="lead-status-modal" title="Update Lead Status">
    <form id="lead-status-form" class="space-y-3">
        @csrf
        @method('PATCH')
        <input type="hidden" name="lead_id">
        <p class="bulk-lead-context hidden rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-600"></p>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
            <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}">{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Timeline Note (optional)</label>
            <textarea name="note" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"></textarea>
        </div>
        <x-ui.button type="submit">Update Status</x-ui.button>
    </form>
</x-ui.modal>

<x-ui.modal id="lead-detail-modal" title="Lead Timeline">
    <div id="lead-detail-content" class="space-y-3 text-sm text-slate-700"></div>
    <form id="lead-note-form" class="mt-4 space-y-2">
        @csrf
        <input type="hidden" name="lead_id">
        <textarea name="note" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" placeholder="Add timeline note"></textarea>
        <x-ui.button type="submit">Add Note</x-ui.button>
    </form>
</x-ui.modal>

<form id="checklist-form">
    @csrf
    <input type="hidden" name="record_id" value="">

    <div class="flex flex-col gap-5 sm:flex-row">

        {{-- Left column: checklist name + submit --}}
        <div class="flex shrink-0 flex-col gap-4 sm:w-44">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-700">
                    Checklist Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                    placeholder="e.g. Pre-ride Safety Checklist" />
            </div>

            <button type="submit" id="checklist-form-submit"
                class="w-full rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Save
            </button>
        </div>

        <div class="hidden border-l border-slate-200 sm:block"></div>

        {{-- Right column: points list --}}
        <div class="flex min-w-0 flex-1 flex-col gap-2">
            <div class="flex items-center justify-between">
                <label class="text-xs font-medium text-slate-700">
                    Points <span class="text-red-500">*</span>
                    <span class="ml-1 font-normal text-slate-400">(drag to reorder)</span>
                </label>
                <button type="button" id="add-point-btn"
                    class="rounded border border-dashed border-slate-400 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50">
                    + Add Point
                </button>
            </div>

            <div id="points-container"
                class="max-h-[52vh] min-h-[80px] space-y-1.5 overflow-y-auto rounded-md pr-1"></div>

            <p id="points-empty-hint" class="text-xs text-slate-400">
                No points yet. Click "+ Add Point" to start.
            </p>
        </div>

    </div>
</form>

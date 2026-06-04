@include('components.masters.form-fields', ['showColorCode' => true])

<div>
    <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Description</label>
    <textarea
        id="description"
        name="description"
        rows="3"
        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none"
    >{{ old('description', $record?->description ?? '') }}</textarea>
</div>

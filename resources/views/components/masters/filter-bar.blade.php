@props([
    'filters' => [],
])

<form id="master-filter-form" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-3">
    <input
        type="text"
        name="search"
        value="{{ $filters['search'] ?? '' }}"
        placeholder="Search by name..."
        class="rounded-md border border-slate-300 px-3 py-2 text-sm sm:col-span-2"
    >
    <select name="status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        <option value="">All statuses</option>
        <option value="1" @selected((string) ($filters['status'] ?? '') === '1')>Active</option>
        <option value="0" @selected((string) ($filters['status'] ?? '') === '0')>Inactive</option>
    </select>
    <button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 sm:col-span-3 sm:max-w-xs">
        Apply Filters
    </button>
</form>

@props([
    'filters' => [],
])

<form id="master-filter-form" class="rounded-2xl border border-slate-200 bg-white p-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input
                type="text"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="Search by name..."
                class="w-full rounded-md border border-slate-300 py-2 pl-9 pr-3 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
            >
        </div>

        <div class="sm:w-44">
            <select
                name="status"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 shadow-sm transition focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
            >
                <option value="">All statuses</option>
                <option value="1" @selected((string) ($filters['status'] ?? '') === '1')>Active</option>
                <option value="0" @selected((string) ($filters['status'] ?? '') === '0')>Inactive</option>
            </select>
        </div>

        <button
            type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-1"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Apply Filters
        </button>
    </div>
</form>

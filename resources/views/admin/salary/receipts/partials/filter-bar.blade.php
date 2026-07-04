@php
    $hasActiveFilters =
        !empty($filters['search']) ||
        !empty($filters['mower_id']) ||
        !empty($filters['period_type']) ||
        !empty($filters['date_range_start']) ||
        !empty($filters['date_range_end']);

    $activeCount = collect($filters)->filter(fn ($v) => $v !== '' && $v !== null)->count();
    $resetUrl    = route('admin.salary-receipts.index');
    $isMower     = auth()->user()?->hasRole(\App\Support\CrmRoles::MOWER);
@endphp

<div class="rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center">
        <button type="button" id="salary-receipt-filter-toggle" class="flex flex-1 items-center justify-between px-4 py-3 text-left">
            <div class="flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-600">Filters</span>
                @if ($activeCount > 0)
                    <span class="rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-600">{{ $activeCount }}</span>
                @endif
            </div>
            @if ($hasActiveFilters)
                <a href="{{ $resetUrl }}" class="text-[11px] text-slate-400 hover:text-slate-600" onclick="event.stopPropagation()">Reset all</a>
            @endif
        </button>

        @can('manage-salary-calculator')
            <div class="flex flex-wrap items-center gap-2 border-l border-slate-100 px-4 py-2.5">
                <a href="{{ route('admin.salary-calculator.index') }}"
                   class="rounded-md bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-700">
                    Calculate Salary
                </a>
            </div>
        @endcan
    </div>

    <form id="salary-receipt-filter-form" class="{{ $hasActiveFilters ? '' : 'hidden' }} border-t border-slate-100 px-4 py-3">
        <div class="grid grid-cols-1 gap-x-2.5 gap-y-3 sm:grid-cols-2 lg:grid-cols-4">
            @unless ($isMower)
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Mower</label>
                    <select name="mower_id"
                        class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                        <option value="">All mowers</option>
                        @foreach ($mowers as $mower)
                            <option value="{{ $mower->id }}" @selected(($filters['mower_id'] ?? '') == $mower->id)>{{ $mower->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Period</label>
                <select name="period_type"
                    class="filter cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All periods</option>
                    <option value="week" @selected(($filters['period_type'] ?? '') === 'week')>Week</option>
                    <option value="month" @selected(($filters['period_type'] ?? '') === 'month')>Month</option>
                </select>
            </div>

            <div class="flex flex-col gap-1 sm:col-span-2 lg:col-span-2">
                <label class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Period Date Range</label>
                <x-ui.daterange-picker
                    placeholder="Select date range"
                    name="date_range"
                    :startDate="$filters['date_range_start'] ?? null"
                    :endDate="$filters['date_range_end'] ?? null"
                    :show-ranges="true"
                    class="filter"
                />
            </div>
        </div>

        <div class="mt-2.5 flex items-center justify-end gap-2">
            <a href="{{ $resetUrl }}" class="text-xs text-slate-400 hover:text-slate-600">Reset</a>
            <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 active:scale-95">
                Apply
            </button>
        </div>
    </form>
</div>

@php
    $hasActiveFilters = !empty($filters['search'])
        || !empty($filters['zone_id'])
        || !empty($filters['customer_type'])
        || !empty($filters['parking_status'])
        || !empty($filters['job_type'])
        || !empty($filters['payment_status'])
        || ($filters['from_lead'] !== '' && $filters['from_lead'] !== null);

    $activeCount = collect($filters)->filter(fn ($v) => $v !== '' && $v !== null)->count();
@endphp

<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

    {{-- Toggle header --}}
    <div class="flex items-center">
        <button type="button" id="client-filter-toggle" class="flex flex-1 items-center justify-between px-5 py-4 text-left">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <span class="text-sm font-semibold uppercase tracking-wide text-slate-600">Filters</span>
                @if ($activeCount > 0)
                    <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $activeCount }} active</span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @if ($hasActiveFilters)
                    <a href="{{ route('admin.clients.index') }}" class="text-xs text-slate-400 transition-colors hover:text-slate-600" onclick="event.stopPropagation()">Reset all</a>
                @endif
                <svg id="client-filter-chevron" class="h-4 w-4 text-slate-400 transition-transform duration-200 {{ $hasActiveFilters ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        <div class="flex flex-wrap items-center gap-2 border-l border-slate-100 px-4 py-3">
            <x-ui.export-dropdown
                :excelHref="route('admin.clients.export.excel')"
                :pdfHref="route('admin.clients.export.pdf')"
            />
            @can('manage-customers')
                <a href="{{ route('admin.clients.create') }}" class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Add Customer</a>
            @endcan
        </div>
    </div>

    {{-- Collapsible body --}}
    <form id="client-filter-form" class="{{ $hasActiveFilters ? '' : 'hidden' }} border-t border-slate-100 p-5">

        {{-- Search --}}
        <div class="mb-4">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search by name, ID, email or phone…"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-4 py-2.5 text-sm text-slate-700 placeholder-slate-400 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100"
                >
            </div>
        </div>

        {{-- Filter dropdowns --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Zone</label>
                <select name="zone_id" class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All zones</option>
                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" @selected($filters['zone_id'] == $zone->id)>{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Customer type</label>
                <select name="customer_type" class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All types</option>
                    @foreach ($customerTypes as $customerType)
                        <option value="{{ $customerType }}" @selected($filters['customer_type'] === $customerType)>{{ $customerType }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Parking</label>
                <select name="parking_status" class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All parking</option>
                    @foreach ($parkingStatuses as $parkingStatus)
                        <option value="{{ $parkingStatus }}" @selected($filters['parking_status'] === $parkingStatus)>{{ $parkingStatus }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Job type</label>
                <select name="job_type" class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All job types</option>
                    @foreach ($jobTypes as $jobType)
                        <option value="{{ $jobType }}" @selected($filters['job_type'] === $jobType)>{{ $jobType }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Payment</label>
                <select name="payment_status" class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All statuses</option>
                    @foreach ($paymentStatuses as $paymentStatus)
                        <option value="{{ $paymentStatus }}" @selected($filters['payment_status'] === $paymentStatus)>{{ $paymentStatus }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Source</label>
                <select name="from_lead" class="cursor-pointer rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    <option value="">All sources</option>
                    <option value="1" @selected($filters['from_lead'] === '1')>From lead</option>
                    <option value="0" @selected($filters['from_lead'] === '0')>Manual only</option>
                </select>
            </div>

        </div>

        {{-- Actions --}}
        <div class="mt-4 flex items-center justify-end gap-3">
            <a href="{{ route('admin.clients.index') }}" class="text-sm text-slate-400 transition-colors hover:text-slate-600">Reset filters</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 active:scale-95">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Apply filters
            </button>
        </div>

    </form>
</div>

<script>
    $('#client-filter-toggle').on('click', function () {
        $('#client-filter-form').toggleClass('hidden');
        $('#client-filter-chevron').toggleClass('rotate-180');
    });
</script>

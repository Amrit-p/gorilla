<form id="client-filter-form" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <span class="text-sm font-semibold text-slate-600 tracking-wide uppercase">Filters</span>
        </div>
        <a href="{{ route('admin.clients.index') }}" class="text-xs text-slate-400 hover:text-slate-600 transition-colors">
            Reset all
        </a>
    </div>

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
            <select name="zone_id" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                <option value="">All zones</option>
                @foreach ($zones as $zone)
                    <option value="{{ $zone->id }}" @selected($filters['zone_id'] == $zone->id)>{{ $zone->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Customer type</label>
            <select name="customer_type" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                <option value="">All types</option>
                @foreach ($customerTypes as $customerType)
                    <option value="{{ $customerType }}" @selected($filters['customer_type'] === $customerType)>{{ $customerType }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Parking</label>
            <select name="parking_status" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                <option value="">All parking</option>
                @foreach ($parkingStatuses as $parkingStatus)
                    <option value="{{ $parkingStatus }}" @selected($filters['parking_status'] === $parkingStatus)>{{ $parkingStatus }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Job type</label>
            <select name="job_type" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                <option value="">All job types</option>
                @foreach ($jobTypes as $jobType)
                    <option value="{{ $jobType }}" @selected($filters['job_type'] === $jobType)>{{ $jobType }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Payment</label>
            <select name="payment_status" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                <option value="">All statuses</option>
                @foreach ($paymentStatuses as $paymentStatus)
                    <option value="{{ $paymentStatus }}" @selected($filters['payment_status'] === $paymentStatus)>{{ $paymentStatus }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Source</label>
            <select name="from_lead" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                <option value="">All sources</option>
                <option value="1" @selected($filters['from_lead'] === '1')>From lead</option>
                <option value="0" @selected($filters['from_lead'] === '0')>Manual only</option>
            </select>
        </div>

    </div>

    {{-- Actions --}}
    <div class="mt-4 flex justify-end">
        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 active:scale-95">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Apply filters
        </button>
    </div>

</form>

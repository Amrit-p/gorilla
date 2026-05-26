<form id="client-filter-form" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-4 lg:grid-cols-8">
    <input
        type="text"
        name="search"
        value="{{ $filters['search'] }}"
        placeholder="Search name, ID, email, phone..."
        class="rounded-md border border-slate-300 px-3 py-2 text-sm md:col-span-2 lg:col-span-2"
    >
    <select name="customer_type" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        <option value="">All customer types</option>
        @foreach ($customerTypes as $customerType)
            <option value="{{ $customerType }}" @selected($filters['customer_type'] === $customerType)>{{ $customerType }}</option>
        @endforeach
    </select>
    <select name="parking_status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        <option value="">All parking</option>
        @foreach ($parkingStatuses as $parkingStatus)
            <option value="{{ $parkingStatus }}" @selected($filters['parking_status'] === $parkingStatus)>{{ $parkingStatus }}</option>
        @endforeach
    </select>
    <select name="job_type" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        <option value="">All job types</option>
        @foreach ($jobTypes as $jobType)
            <option value="{{ $jobType }}" @selected($filters['job_type'] === $jobType)>{{ $jobType }}</option>
        @endforeach
    </select>
    <select name="payment_status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        <option value="">All payment status</option>
        @foreach ($paymentStatuses as $paymentStatus)
            <option value="{{ $paymentStatus }}" @selected($filters['payment_status'] === $paymentStatus)>{{ $paymentStatus }}</option>
        @endforeach
    </select>
    <select name="from_lead" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        <option value="">All sources</option>
        <option value="1" @selected($filters['from_lead'] === '1')>From lead</option>
        <option value="0" @selected($filters['from_lead'] === '0')>Manual only</option>
    </select>
    <button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50">Apply</button>
</form>

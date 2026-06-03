<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <h3 class="text-sm font-semibold text-slate-900">Contact &amp; billing</h3>
        <dl class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Address</dt>
                <dd class="text-right font-medium text-slate-800">{{ $client->address ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Email</dt>
                <dd class="text-right text-slate-800">{{ $client->email ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Mobile</dt>
                <dd class="text-right text-slate-800">{{ $client->phone ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Payment</dt>
                <dd class="text-right text-slate-800">{{ $client->payment_mode ?: '—' }} • {{ $client->payment_status ?: '—' }}</dd>
            </div>
             <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Accounting level</dt>
                <dd class="text-slate-800">{{ $client->accountingLevel?->name ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Zone</dt>
                <dd class="text-slate-800">{{ $client->zone?->name ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Charges</dt>
                <dd class="text-right text-slate-800">
                    @if ($client->total_charges !== null)
                        ${{ number_format((float) $client->total_charges, 2) }}
                    @else
                        —
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <h3 class="text-sm font-semibold text-slate-900">Service profile</h3>
        <dl class="mt-4 space-y-3 text-sm">
            <div>
                <dt class="text-slate-500">Service types</dt>
                <dd class="mt-1 text-slate-800">{{ is_array($client->service_types) && $client->service_types !== [] ? implode(', ', $client->service_types) : '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Weed spray</dt>
                <dd class="text-slate-800">{{ $client->weed_spray ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Recurrence</dt>
                <dd class="text-slate-800">{{ $client->recurrence?->name ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Job / customer type</dt>
                <dd class="text-slate-800">{{ $client->job_type ?: '—' }} / {{ $client->client_type ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Job level</dt>
                <dd class="text-slate-800">{{ $client->jobLevel?->name ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 lg:col-span-2">
        <h3 class="text-sm font-semibold text-slate-900">Site &amp; operations</h3>
        <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-slate-500">Customer type</dt>
                <dd class="mt-1 font-medium text-slate-800">{{ $client->customer_type ?: "Don't Know" }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-slate-500">Additional site instructions</dt>
                <dd class="mt-1 whitespace-pre-wrap text-slate-800">{{ $client->additional_site_instructions ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Special remarks</dt>
                <dd class="mt-1 whitespace-pre-wrap text-slate-800">{{ $client->special_remarks ?: '—' }}</dd>
            </div>
            @if ($client->lead)
                <div class="sm:col-span-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800">
                    Converted from lead: <strong>{{ $client->lead->client_name }}</strong> ({{ $client->lead->status }})
                </div>
            @endif
        </dl>
    </div>
</div>

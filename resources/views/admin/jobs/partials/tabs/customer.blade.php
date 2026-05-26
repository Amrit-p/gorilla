@php
    $jobModel = $job ?? null;
    $defaultEquipmentId = old(
        'equipment_type_id',
        $jobModel?->equipment_type_id
            ?? $selectedClient?->equipment_type_id
            ?? $selectedClient?->lead?->equipment_type_id
    );
    $defaultMarkerColor = collect($equipmentTypes ?? [])
        ->firstWhere('id', (int) $defaultEquipmentId)['color_code'] ?? '#64748b';
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(300px,420px)]">
    <div class="space-y-4 lg:order-1">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Customer</label>
            <select name="client_id" id="job-client-id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select customer</option>
                @foreach ($clients as $client)
                    <option
                        value="{{ $client->id }}"
                        data-address="{{ $client->address }}"
                        data-lat="{{ $client->latitude }}"
                        data-lng="{{ $client->longitude }}"
                        data-parking="{{ $client->parking_status }}"
                        data-customer-type="{{ $client->customer_type }}"
                        data-pet-warning="{{ $client->pet_warning }}"
                        data-site-instructions="{{ $client->additional_site_instructions }}"
                        data-equipment-id="{{ $client->equipment_type_id ?? $client->lead?->equipment_type_id }}"
                        data-equipment-color="{{ $client->equipmentType?->color_code ?? $client->lead?->equipmentType?->color_code ?? '#64748b' }}"
                        @selected((string) old('client_id', $jobModel?->client_id ?? $selectedClient?->id ?? '') === (string) $client->id)
                    >
                        #{{ $client->customer_unique_id }} — {{ $client->name }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Choosing a customer fills the service address and site fields on the next steps.</p>
        </div>

        <x-forms.equipment-type
            id="job-equipment-type-id"
            :equipment-types="$equipmentTypes ?? null"
            :selected="$defaultEquipmentId"
            hint="Prefilled from the customer. Change here if this job needs different equipment — the map marker updates to match."
        />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Payment mode</label>
            <select name="payment_mode" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select payment mode</option>
                @foreach ($paymentModes as $paymentMode)
                    <option value="{{ $paymentMode }}" @selected(old('payment_mode', $jobModel?->payment_mode) === $paymentMode)>{{ $paymentMode }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Payment status</label>
            <select name="payment_status" id="job-payment-status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select payment status</option>
                @foreach ($paymentStatuses as $paymentStatus)
                    <option value="{{ $paymentStatus }}" @selected(old('payment_status', $jobModel?->payment_status) === $paymentStatus)>{{ $paymentStatus }}</option>
                @endforeach
            </select>
        </div>

        <div id="job-payment-reason-wrap" class="hidden">
            <x-ui.input label="Reason if pending" name="payment_pending_reason" :value="old('payment_pending_reason', $jobModel?->payment_pending_reason)" />
        </div>
    </div>

    <div class="lg:order-2 lg:sticky lg:top-4 lg:self-start">
        <x-maps.address-picker
            prefix="job"
            address-name="client_address"
            address-label="Service address"
            address-input-id="job-client-address"
            latitude-input-id="job-latitude"
            longitude-input-id="job-longitude"
            map-preview-id="job-map-preview"
            variant="sidebar"
            map-height="min(420px, 65vh)"
            :show-coordinates="true"
            :show-current-location="true"
            :address-value="old('client_address', $jobModel?->client_address)"
            :latitude-value="old('latitude', $jobModel?->latitude)"
            :longitude-value="old('longitude', $jobModel?->longitude)"
            :marker-color="$defaultMarkerColor"
        />
    </div>
</div>

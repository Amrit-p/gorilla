@php
    $leadModel = $lead ?? null;
    $selectedEquipmentId = old('equipment_type_id', $leadModel?->equipment_type_id);
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2 sm:col-span-2">
    {{-- Column 1: intake fields --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-ui.input
                label="Contact name"
                name="client_name"
                :value="old('client_name', $leadModel?->client_name)"
            />
        </div>

        <x-forms.service-types
            name="service_types[]"
            label="Service types"
            :service-types="$serviceTypes ?? null"
            :selected="old('service_types', $leadModel?->service_types ?? [])"
            class="sm:col-span-2"
        />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Weed spray</label>
            <select name="weed_spray" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select option</option>
                @foreach ($weedSprayOptions as $weedSprayOption)
                    <option value="{{ $weedSprayOption }}" @selected(old('weed_spray', $leadModel?->weed_spray) === $weedSprayOption)>{{ $weedSprayOption }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Equipment type</label>
            <select name="equipment_type_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select equipment</option>
                @foreach ($equipmentTypes as $equipmentType)
                    <option
                        value="{{ $equipmentType['id'] }}"
                        @selected((string) $selectedEquipmentId === (string) $equipmentType['id'])
                    >
                        {{ $equipmentType['name'] }}
                    </option>
                @endforeach
            </select>
            @if (!empty($equipmentTypes))
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($equipmentTypes as $equipmentType)
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-0.5 text-xs text-slate-600">
                            <span class="inline-block h-2.5 w-2.5 rounded-full" style="background-color: {{ $equipmentType['color_code'] ?? '#64748b' }}"></span>
                            {{ $equipmentType['name'] }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Recurrence</label>
            <select name="recurrence_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required>
                <option value="">Select recurrence</option>
                @foreach ($recurrences as $recurrence)
                    <option value="{{ $recurrence->id }}" @selected((string) old('recurrence_id', $leadModel?->recurrence_id) === (string) $recurrence->id)>{{ $recurrence->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Job type</label>
            <select name="job_type" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select job type</option>
                @foreach ($jobTypes as $jobType)
                    <option value="{{ $jobType }}" @selected(old('job_type', $leadModel?->job_type) === $jobType)>{{ $jobType }}</option>
                @endforeach
            </select>
        </div>

        <x-ui.input label="Charges" name="charges" type="number" step="0.01" min="0" :value="old('charges', $leadModel?->charges)" />
        <x-ui.input label="Mobile number" name="mobile_number" :value="old('mobile_number', $leadModel?->mobile_number)" />
        <div class="sm:col-span-2">
            <x-ui.input label="Email" name="email" type="email" :value="old('email', $leadModel?->email)" />
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Payment mode</label>
            <select name="payment_mode" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select payment mode</option>
                @foreach ($paymentModes as $paymentMode)
                    <option value="{{ $paymentMode }}" @selected(old('payment_mode', $leadModel?->payment_mode) === $paymentMode)>{{ $paymentMode }}</option>
                @endforeach
            </select>
        </div>

        <x-ui.input label="Date" name="lead_date" type="date" :value="old('lead_date', $leadModel?->lead_date?->format('Y-m-d'))" />

        @isset($salesUsers)
            @unless(auth()->user()?->hasRole(\App\Support\CrmRoles::SALES_MANAGER))
                <div class="sm:col-span-2 border-t border-slate-200 pt-4">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Assigned sales manager</label>
                    <select name="assigned_sales_user_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Unassigned</option>
                        @foreach ($salesUsers as $salesUser)
                            <option value="{{ $salesUser->id }}" @selected((string) old('assigned_sales_user_id', $leadModel?->assigned_sales_user_id) === (string) $salesUser->id)>
                                {{ $salesUser->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Only users with the Sales Manager role appear here.</p>
                </div>
            @endunless
        @endisset

        @isset($zones)
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">Zone</label>
                <select name="zone_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select zone</option>
                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" @selected((string) old('zone_id', $leadModel?->zone_id) === (string) $zone->id)>
                            {{ $zone->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endisset

        <div class="sm:col-span-2" id="lead-remarks-wrap">
            <label class="mb-1 block text-sm font-medium text-slate-700">Remarks</label>
            <textarea name="remarks" id="lead-remarks" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="3">{{ old('remarks', $leadModel?->remarks) }}</textarea>
        </div>
    </div>

    {{-- Column 2: address + map (lat/lng saved via hidden inputs) --}}
    <div class="lg:sticky lg:top-4 lg:self-start">
        <x-maps.address-picker
            prefix="lead"
            address-name="address"
            address-label="Property address"
            variant="sidebar"
            map-height="min(480px, 70vh)"
            :address-value="old('address', $leadModel?->address)"
            :latitude-value="old('latitude', $leadModel?->latitude)"
            :longitude-value="old('longitude', $leadModel?->longitude)"
        />
    </div>
</div>

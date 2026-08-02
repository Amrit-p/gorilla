@php
    $jobModel = $job ?? null;
    $defaultEquipmentId = old('equipment_type_id', $jobModel?->equipment_type_id);
    $defaultMarkerColor = collect($equipmentTypes ?? [])
        ->firstWhere('id', (int) $defaultEquipmentId)['color_code'] ?? '#64748b';
    $jobTypes = $jobTypes ?? \App\Enums\LeadJobType::values();
    $weedSprayOptions = $weedSprayOptions ?? \App\Enums\LeadWeedSpray::values();
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(300px,420px)]">
    <div class="space-y-5 lg:order-1">
        <section class="space-y-3">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Contact</h3>
                <p class="text-xs text-slate-500">Name and phone are required. Search jobs later by these details.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-ui.input
                        label="Customer name"
                        name="customer_name"
                        id="job-customer-name"
                        :value="old('customer_name', $jobModel?->customer_name)"
                        required
                    />
                </div>
                <x-ui.input
                    label="Phone"
                    name="phone"
                    id="job-phone"
                    type="tel"
                    :value="old('phone', $jobModel?->phone)"
                    required
                />
                <x-ui.input
                    label="Email"
                    name="email"
                    id="job-email"
                    type="email"
                    :value="old('email', $jobModel?->email)"
                />
            </div>
        </section>

        <section class="space-y-3 border-t border-slate-100 pt-4">
            <h3 class="text-sm font-semibold text-slate-900">Schedule &amp; services</h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <x-ui.input label="Job date" name="scheduled_date" id="job-scheduled-date" type="date" :value="old('scheduled_date', optional($jobModel?->scheduled_date)->format('Y-m-d') ?? now()->toDateString())" />
                <x-ui.input label="Start time" name="scheduled_time" type="time" :value="old('scheduled_time', $jobModel?->scheduled_time ? \Illuminate\Support\Carbon::parse($jobModel->scheduled_time)->format('H:i') : '')" />
                <div class="sm:col-span-2">
                    <x-ui.input label="Estimated duration (minutes)" name="estimated_duration_minutes" id="job-estimated-duration" type="number" min="15" max="1440" :value="old('estimated_duration_minutes', $jobModel?->estimated_duration_minutes ?? 60)" />
                </div>
            </div>

            <x-forms.service-types
                name="required_services[]"
                label="Required services"
                :service-types="$serviceTypes ?? null"
                :selected="old('required_services', $jobModel?->required_services ?? [])"
            />
        </section>

        <section class="space-y-3 border-t border-slate-100 pt-4">
            <h3 class="text-sm font-semibold text-slate-900">Job setup</h3>

            <x-forms.equipment-type
                id="job-equipment-type-id"
                :equipment-types="$equipmentTypes ?? null"
                :selected="$defaultEquipmentId"
                hint="Map marker color follows the selected equipment type."
            />

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @isset($jobLevels)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Job level</label>
                        <select name="job_level_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <option value="">— None —</option>
                            @foreach ($jobLevels as $level)
                                <option value="{{ $level->id }}" @selected((string) old('job_level_id', $jobModel?->job_level_id) === (string) $level->id)>{{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset

                @isset($zones)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Zone</label>
                        <select name="zone_id" id="job-zone-id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select zone</option>
                            @foreach ($zones as $zone)
                                <option value="{{ $zone->id }}" @selected((string) old('zone_id', $jobModel?->zone_id) === (string) $zone->id)>{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset

                @isset($recurrences)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Recurrence</label>
                        <select name="recurrence_id" id="job-recurrence-id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select recurrence</option>
                            @foreach ($recurrences as $recurrence)
                                <option value="{{ $recurrence->id }}" @selected((string) old('recurrence_id', $jobModel?->recurrence_id) === (string) $recurrence->id)>{{ $recurrence->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Job type</label>
                    <select name="job_type" id="job-job-type" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select job type</option>
                        @foreach ($jobTypes as $jobType)
                            <option value="{{ $jobType }}" @selected(old('job_type', $jobModel?->job_type) === $jobType)>{{ $jobType }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Weed spray</label>
                    <select name="weed_spray" id="job-weed-spray" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select option</option>
                        @foreach ($weedSprayOptions as $weedSpray)
                            <option value="{{ $weedSpray }}" @selected(old('weed_spray', $jobModel?->weed_spray) === $weedSpray)>{{ $weedSpray }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Parking status</label>
                    <select name="parking_status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select parking status</option>
                        @foreach ($parkingStatuses as $parkingStatus)
                            <option value="{{ $parkingStatus }}" @selected(old('parking_status', $jobModel?->parking_status) === $parkingStatus)>{{ $parkingStatus }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Site type</label>
                    <select name="customer_type" id="job-customer-type" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Select type</option>
                        @foreach ($customerTypes as $customerType)
                            <option value="{{ $customerType }}" @selected(old('customer_type', $jobModel?->customer_type) === $customerType)>{{ $customerType }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="space-y-3 border-t border-slate-100 pt-4">
            <h3 class="text-sm font-semibold text-slate-900">Payment</h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
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
                <div class="sm:col-span-2">
                    <x-ui.input label="Charges ($)" name="charges" type="number" min="0" step="0.01" :value="old('charges', $jobModel?->charges)" />
                </div>
            </div>
            <div id="job-payment-reason-wrap" class="hidden">
                <x-ui.input label="Reason if pending" name="payment_pending_reason" :value="old('payment_pending_reason', $jobModel?->payment_pending_reason)" />
            </div>
        </section>

        <section class="space-y-3 border-t border-slate-100 pt-4">
            <h3 class="text-sm font-semibold text-slate-900">Site notes</h3>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Additional instructions</label>
                <textarea name="site_instructions" id="job-site-instructions" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="2">{{ old('site_instructions', $jobModel?->site_instructions) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Pet warning</label>
                <textarea name="pet_warning" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="2">{{ old('pet_warning', $jobModel?->pet_warning) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Property details</label>
                <textarea name="property_details" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('property_details', $jobModel?->property_details) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Remarks</label>
                <textarea name="special_remarks" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="2">{{ old('special_remarks', $jobModel?->special_remarks) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Internal notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('notes', $jobModel?->notes) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Attach images</label>
                <input type="file" name="images[]" multiple accept="image/*" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
        </section>
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
            map-height="min(480px, 70vh)"
            :show-coordinates="true"
            :show-current-location="true"
            :address-value="old('client_address', $jobModel?->client_address)"
            :latitude-value="old('latitude', $jobModel?->latitude)"
            :longitude-value="old('longitude', $jobModel?->longitude)"
            :marker-color="$defaultMarkerColor"
        />
    </div>
</div>

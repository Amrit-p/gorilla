@php
    $clientModel = $client ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2 sm:col-span-2">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-forms.service-types
            name="service_types[]"
            label="Service types"
            :selected="old('service_types', $clientModel?->service_types ?? [])"
            class="sm:col-span-2"
        />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Weed spray</label>
            <select name="weed_spray" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select option</option>
                @foreach ($weedSprayOptions as $weedSprayOption)
                    <option value="{{ $weedSprayOption }}" @selected(old('weed_spray', $clientModel?->weed_spray) === $weedSprayOption)>{{ $weedSprayOption }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Recurrence</label>
            <select name="recurrence_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                @foreach ($recurrenceOptions as $recurrenceOption)
                    <option value="{{ $recurrenceOption->id }}" @selected(old('recurrence_id', $clientModel?->recurrence_id) === $recurrenceOption->id)>{{ $recurrenceOption->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Job type</label>
            <select name="job_type" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select job type</option>
                @foreach ($jobTypes as $jobType)
                    <option value="{{ $jobType }}" @selected(old('job_type', $clientModel?->job_type) === $jobType)>{{ $jobType }}</option>
                @endforeach
            </select>
        </div>

        <x-forms.equipment-type
            id="client-equipment-type-id"
            :equipment-types="$equipmentTypes ?? null"
            :selected="old('equipment_type_id', $clientModel?->equipment_type_id ?? $clientModel?->lead?->equipment_type_id)"
        />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Customer type</label>
            <select name="customer_type" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select customer type</option>
                @foreach ($customerTypes as $customerType)
                    <option value="{{ $customerType }}" @selected(old('customer_type', $clientModel?->customer_type ?? "Don't Know") === $customerType)>{{ $customerType }}</option>
                @endforeach
            </select>
        </div>

        @isset($zones)
            <div class="">
                <label class="mb-1 block text-sm font-medium text-slate-700">Zone</label>
                <select name="zone_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Select zone</option>
                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" @selected((string) old('zone_id', $clientModel?->zone_id) === (string) $zone->id)>
                            {{ $zone->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endisset

        @isset($accountingLevels)
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Accounting level</label>
                <select id="accounting-level-select" name="accounting_level_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="" data-description="">Select accounting level</option>
                    @foreach ($accountingLevels as $accountingLevel)
                        <option
                            value="{{ $accountingLevel->id }}"
                            data-description="{{ $accountingLevel->description }}"
                            @selected((string) old('accounting_level_id', $clientModel?->accounting_level_id) === (string) $accountingLevel->id)
                        >{{ $accountingLevel->name }}</option>
                    @endforeach
                </select>
                <p id="accounting-level-desc" class="mt-1 min-h-[1.25rem] text-xs text-slate-500 italic"></p>
            </div>
        @endisset

        @isset($clientRatings)
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Customer rating</label>
                <select id="client-rating-select" name="client_rating_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="" data-description="">Select customer rating</option>
                    @foreach ($clientRatings as $clientRating)
                        <option
                            value="{{ $clientRating->id }}"
                            data-description="{{ $clientRating->description }}"
                            @selected((string) old('client_rating_id', $clientModel?->client_rating_id) === (string) $clientRating->id)
                        >{{ $clientRating->name }}</option>
                    @endforeach
                </select>
                <p id="client-rating-desc" class="mt-1 min-h-[1.25rem] text-xs text-slate-500 italic"></p>
            </div>
        @endisset

        @isset($jobLevels)
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Job level</label>
                <select id="job-level-select" name="job_level_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="" data-description="">Select job level</option>
                    @foreach ($jobLevels as $jobLevel)
                        <option
                            value="{{ $jobLevel->id }}"
                            data-description="{{ $jobLevel->description }}"
                            @selected((string) old('job_level_id', $clientModel?->job_level_id) === (string) $jobLevel->id)
                        >{{ $jobLevel->name }}</option>
                    @endforeach
                </select>
                <p id="job-level-desc" class="mt-1 min-h-[1.25rem] text-xs text-slate-500 italic"></p>
            </div>
        @endisset

        <x-ui.input label="Charges" name="charges" type="number" step="0.01" min="0" :value="old('charges', $clientModel?->charges)" />
        <x-ui.input label="Estimate time" name="estimated_time" :value="old('estimated_time', $clientModel?->estimated_time)" />
        <x-ui.input label="Schedule date" name="schedule_date" type="date" :value="old('schedule_date', $clientModel?->schedule_date?->format('Y-m-d'))" />
        <x-ui.input label="Mobile number" name="phone" :value="old('phone', $clientModel?->phone)" />
        <x-ui.input label="Email" name="email" type="email" :value="old('email', $clientModel?->email)" />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Payment mode</label>
            <select name="payment_mode" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select payment mode</option>
                @foreach ($paymentModes as $paymentMode)
                    <option value="{{ $paymentMode }}" @selected(old('payment_mode', $clientModel?->payment_mode) === $paymentMode)>{{ $paymentMode }}</option>
                @endforeach
            </select>
        </div>

        <input type="hidden" name="remarks_type" value="{{ old('remarks_type', $clientModel?->remarks_type) }}" />

        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-medium text-slate-700">Additional instructions</label>
            <textarea name="additional_site_instructions" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="2">{{ old('additional_site_instructions', $clientModel?->additional_site_instructions) }}</textarea>
        </div>

        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-medium text-slate-700">Remarks</label>
            <textarea name="special_remarks" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="2">{{ old('special_remarks', $clientModel?->special_remarks) }}</textarea>
        </div>

        <x-ui.file
            class="sm:col-span-2"
            label="Documents"
            name="documents"
            :multiple="true"
            accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp"
        />
    </div>

    <div class="lg:sticky lg:top-4 lg:self-start">
        <x-maps.address-picker
            prefix="customer"
            address-name="address"
            address-label="Property address"
            variant="sidebar"
            map-height="min(480px, 70vh)"
            :address-value="old('address', $clientModel?->address)"
            :latitude-value="old('latitude', $clientModel?->latitude)"
            :longitude-value="old('longitude', $clientModel?->longitude)"
        />
    </div>
</div>

@isset($accountingLevels)
<script>
    (function () {
        var select = document.getElementById('accounting-level-select');
        var desc   = document.getElementById('accounting-level-desc');
        if (!select || !desc) return;

        function syncDesc() {
            var opt = select.options[select.selectedIndex];
            desc.textContent = (opt && opt.dataset.description) ? opt.dataset.description : '';
        }

        select.addEventListener('change', syncDesc);
        syncDesc(); // populate on page load (edit form pre-selection)
    })();
</script>
@endisset

@isset($clientRatings)
<script>
    (function () {
        var select = document.getElementById('client-rating-select');
        var desc   = document.getElementById('client-rating-desc');
        if (!select || !desc) return;

        function syncDesc() {
            var opt = select.options[select.selectedIndex];
            desc.textContent = (opt && opt.dataset.description) ? opt.dataset.description : '';
        }

        select.addEventListener('change', syncDesc);
        syncDesc();
    })();
</script>
@endisset

@isset($jobLevels)
<script>
    (function () {
        var select = document.getElementById('job-level-select');
        var desc   = document.getElementById('job-level-desc');
        if (!select || !desc) return;

        function syncDesc() {
            var opt = select.options[select.selectedIndex];
            desc.textContent = (opt && opt.dataset.description) ? opt.dataset.description : '';
        }

        select.addEventListener('change', syncDesc);
        syncDesc();
    })();
</script>
@endisset

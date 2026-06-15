<x-layouts.mower :title="'Create Customer'" :show-back="true" :back-url="route('mower.index')">
    <div class="space-y-4">
        <div>
            <h2 class="text-base font-semibold text-slate-900">New Customer</h2>
            <p class="text-xs text-slate-500">Fill in the details below. A job will be created automatically.</p>
        </div>

        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('mower.clients.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf

            {{-- Services --}}
            <section class="rounded-2xl bg-white p-4 shadow-sm space-y-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Services</p>

                <x-forms.service-types
                    name="service_types[]"
                    label="Service types"
                    :selected="old('service_types', [])"
                />

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Weed spray</label>
                    <select name="weed_spray" class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                        <option value="">Select option</option>
                        @foreach ($weedSprayOptions as $option)
                            <option value="{{ $option }}" @selected(old('weed_spray') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Job type</label>
                    <select name="job_type" class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                        <option value="">Select job type</option>
                        @foreach ($jobTypes as $jobType)
                            <option value="{{ $jobType }}" @selected(old('job_type') === $jobType)>{{ $jobType }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Recurrence</label>
                    <select name="recurrence_id" class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                        @foreach ($recurrenceOptions as $recurrenceOption)
                            <option value="{{ $recurrenceOption->id }}" @selected(old('recurrence_id') == $recurrenceOption->id)>{{ $recurrenceOption->name }}</option>
                        @endforeach
                    </select>
                </div>

                <x-forms.equipment-type
                    id="client-equipment-type-id"
                    :equipment-types="$equipmentTypes ?? null"
                    :selected="old('equipment_type_id')"
                />
            </section>

            {{-- Customer details --}}
            <section class="rounded-2xl bg-white p-4 shadow-sm space-y-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Customer Details</p>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Customer type</label>
                    <select name="customer_type" class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                        <option value="">Select customer type</option>
                        @foreach ($customerTypes as $customerType)
                            <option value="{{ $customerType }}" @selected(old('customer_type', "Don't Know") === $customerType)>{{ $customerType }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Payment mode</label>
                    <select name="payment_mode" class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                        <option value="">Select payment mode</option>
                        @foreach ($paymentModes as $paymentMode)
                            <option value="{{ $paymentMode }}" @selected(old('payment_mode') === $paymentMode)>{{ $paymentMode }}</option>
                        @endforeach
                    </select>
                </div>

                @isset($zones)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Zone</label>
                        <select name="zone_id" class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                            <option value="">Select zone</option>
                            @foreach ($zones as $zone)
                                <option value="{{ $zone->id }}" @selected((string) old('zone_id') === (string) $zone->id)>{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Charges ($)</label>
                    <input type="number" name="charges" step="0.01" min="0" value="{{ old('charges') }}"
                        class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Schedule date</label>
                    <input type="date" name="schedule_date" value="{{ old('schedule_date', now()->toDateString()) }}"
                        class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Mobile number</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                        class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Additional instructions</label>
                    <textarea name="additional_site_instructions" rows="2"
                        class="w-full rounded-xl border border-slate-300 px-3 py-3 text-base">{{ old('additional_site_instructions') }}</textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Remarks</label>
                    <textarea name="special_remarks" rows="2"
                        class="w-full rounded-xl border border-slate-300 px-3 py-3 text-base">{{ old('special_remarks') }}</textarea>
                </div>
            </section>

            {{-- Documents (required) --}}
            <section class="rounded-2xl bg-white p-4 shadow-sm space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Documents <span class="text-red-500">*</span>
                </p>
                <x-ui.file
                    name="documents"
                    :multiple="true"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp"
                    hint="Tap to select files"
                />
                @error('documents')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </section>

            {{-- Property address --}}
            <section class="rounded-2xl bg-white p-4 shadow-sm space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Property Address</p>
                <x-maps.address-picker
                    prefix="customer"
                    address-name="address"
                    address-label="Property address"
                    map-height="280px"
                    :address-value="old('address')"
                    :latitude-value="old('latitude')"
                    :longitude-value="old('longitude')"
                />
            </section>

            <input type="hidden" name="remarks_type" value="{{ old('remarks_type') }}" />

            <button type="submit"
                class="mower-touch w-full rounded-2xl bg-emerald-700 px-4 py-4 text-base font-semibold text-white shadow-sm active:bg-emerald-800">
                Create Customer &amp; Job
            </button>
        </form>
    </div>
</x-layouts.mower>

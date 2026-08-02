<x-layouts.mower :title="'Create Job'" :show-back="true" :back-url="route('mower.index')">
    <div class="space-y-4">
        <div>
            <h2 class="text-base font-semibold text-slate-900">New Job</h2>
            <p class="text-xs text-slate-500">Fill in contact and service details to create a job.</p>
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

        <form method="POST" action="{{ route('mower.jobs.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf

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
                    id="job-equipment-type-id"
                    :equipment-types="$equipmentTypes ?? null"
                    :selected="old('equipment_type_id')"
                />
            </section>

            <section class="rounded-2xl bg-white p-4 shadow-sm space-y-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contact Details</p>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Customer name</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                        class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base"
                        placeholder="Optional — defaults to address">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Mobile number <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                        class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                </div>

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
                    <label class="mb-1 block text-sm font-medium text-slate-700">Site instructions</label>
                    <textarea name="additional_site_instructions" rows="2"
                        class="w-full rounded-xl border border-slate-300 px-3 py-3 text-base">{{ old('additional_site_instructions') }}</textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Remarks</label>
                    <textarea name="special_remarks" rows="2"
                        class="w-full rounded-xl border border-slate-300 px-3 py-3 text-base">{{ old('special_remarks') }}</textarea>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-4 shadow-sm space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Property Address</p>
                <x-maps.address-picker
                    prefix="job"
                    address-name="address"
                    address-label="Property address"
                    map-height="280px"
                    :address-value="old('address')"
                    :latitude-value="old('latitude')"
                    :longitude-value="old('longitude')"
                />
            </section>

            <button type="submit"
                class="mower-touch w-full rounded-2xl bg-emerald-700 px-4 py-4 text-base font-semibold text-white shadow-sm active:bg-emerald-800">
                Create Job
            </button>
        </form>
    </div>
</x-layouts.mower>

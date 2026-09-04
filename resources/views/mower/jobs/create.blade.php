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

            <section class="rounded-2xl bg-white p-4 shadow-sm space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contact</p>
                <x-maps.address-picker
                    prefix="job"
                    address-name="address"
                    address-label="Service address"
                    placeholder="Start typing an address..."
                    map-height="280px"
                    :required="true"
                    :address-value="old('address')"
                    :latitude-value="old('latitude')"
                    :longitude-value="old('longitude')"
                />
            </section>

            <section class="rounded-2xl bg-white p-4 shadow-sm space-y-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Schedule &amp; recurrence</p>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Schedule date</label>
                    <input type="date" name="schedule_date" value="{{ old('schedule_date', now()->toDateString()) }}"
                        class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Recurrence</label>
                    <select name="recurrence_id" class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                        @foreach ($recurrenceOptions as $recurrenceOption)
                            <option value="{{ $recurrenceOption->id }}" @selected(old('recurrence_id') == $recurrenceOption->id)>{{ $recurrenceOption->name }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

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
                    <label for="job-estimated-duration" class="mb-1 block text-sm font-medium text-slate-700">Estimated time <span class="text-red-500">*</span></label>
                    <select name="estimated_duration_minutes" id="job-estimated-duration" required class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                        <option value="">Select estimated time</option>
                        @foreach ($estimatedDurationOptions as $minutes)
                            <option value="{{ $minutes }}" @selected((int) old('estimated_duration_minutes') === $minutes)>{{ $minutes }} min</option>
                        @endforeach
                    </select>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-4 shadow-sm space-y-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Payment</p>

                <div>
                    <label for="job-payment-mode" class="mb-1 block text-sm font-medium text-slate-700">Payment mode <span class="text-red-500">*</span></label>
                    <select name="payment_mode" id="job-payment-mode" required class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                        <option value="">Select payment mode</option>
                        @foreach ($paymentModes as $paymentMode)
                            <option value="{{ $paymentMode }}" @selected(old('payment_mode') === $paymentMode)>{{ $paymentMode }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="job-charges" class="mb-1 block text-sm font-medium text-slate-700">
                        Amount ($)
                        <span id="job-charges-required" class="text-red-500 {{ in_array(old('payment_mode'), $chargesRequiredPaymentModes, true) ? '' : 'hidden' }}">*</span>
                    </label>
                    <input type="number" name="charges" id="job-charges" step="0.01" min="0" value="{{ old('charges') }}"
                        @required(in_array(old('payment_mode'), $chargesRequiredPaymentModes, true))
                        class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                    <p class="mt-1 text-xs text-slate-500">Required for {{ implode(' and ', $chargesRequiredPaymentModes) }} payments.</p>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-4 shadow-sm space-y-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Account &amp; customer</p>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Customer type</label>
                    <select name="customer_type" class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                        <option value="">Select customer type</option>
                        @foreach ($customerTypes as $customerType)
                            <option value="{{ $customerType }}" @selected(old('customer_type', "Don't Know") === $customerType)>{{ $customerType }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-4 shadow-sm space-y-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Site notes</p>

                <div>
                    <label for="job-status" class="mb-1 block text-sm font-medium text-slate-700">Job status <span class="text-red-500">*</span></label>
                    <select name="status" id="job-status" required class="mower-touch w-full rounded-xl border border-slate-300 px-3 py-3 text-base">
                        {{-- NOTE: 'Started' option hidden, matching mower.jobs.show. Remove the @continue below to re-enable. --}}
                        @foreach ($workflowStatuses as $workflowStatus)
                            @continue($workflowStatus === \App\Enums\JobWorkflowStatus::STARTED->value)
                            <option value="{{ $workflowStatus }}" @selected(old('status', $defaultStatus) === $workflowStatus)>{{ $workflowStatus }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="job-comments" class="mb-1 block text-sm font-medium text-slate-700">Comments <span class="text-red-500">*</span></label>
                    <textarea name="office_remarks" id="job-comments" rows="3" required
                        class="w-full rounded-xl border border-slate-300 px-3 py-3 text-base">{{ old('office_remarks') }}</textarea>
                </div>
            </section>

            <button type="submit"
                class="mower-touch w-full rounded-2xl bg-emerald-700 px-4 py-4 text-base font-semibold text-white shadow-sm active:bg-emerald-800">
                Create Job
            </button>
        </form>
    </div>

    @push('scripts')
        <script>
            (function () {
                var modesRequiringAmount = @json($chargesRequiredPaymentModes);
                var paymentMode = document.getElementById('job-payment-mode');
                var charges = document.getElementById('job-charges');
                var marker = document.getElementById('job-charges-required');

                if (!paymentMode || !charges) {
                    return;
                }

                function syncAmountRequirement() {
                    var isRequired = modesRequiringAmount.indexOf(paymentMode.value) !== -1;
                    charges.required = isRequired;

                    if (marker) {
                        marker.classList.toggle('hidden', !isRequired);
                    }
                }

                paymentMode.addEventListener('change', syncAmountRequirement);
                syncAmountRequirement();
            })();
        </script>
    @endpush
</x-layouts.mower>

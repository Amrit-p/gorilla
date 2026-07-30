@php
    $jobModel = $job ?? null;
@endphp

<div class="sm:col-span-2">
    <x-ui.input label="Service address" name="client_address" id="job-client-address" :value="old('client_address', $jobModel?->client_address)" />
</div>

<div class="grid grid-cols-2 gap-3 sm:col-span-2">
    <x-ui.input label="Latitude" name="latitude" id="job-latitude" :value="old('latitude', $jobModel?->latitude)" readonly />
    <x-ui.input label="Longitude" name="longitude" id="job-longitude" :value="old('longitude', $jobModel?->longitude)" readonly />
    <p class="col-span-2 text-xs text-slate-500">Coordinates auto-fill from the client or are geocoded after save.</p>
</div>

<div class="grid grid-cols-2 gap-3">
    <x-ui.input label="Job date" name="scheduled_date" type="date" :value="old('scheduled_date', optional($jobModel?->scheduled_date)->format('Y-m-d'))" />
    <x-ui.input label="Start time" name="scheduled_time" type="time" :value="old('scheduled_time', $jobModel?->scheduled_time ? \Illuminate\Support\Carbon::parse($jobModel->scheduled_time)->format('H:i') : '')" />
</div>

<x-ui.input label="Estimated duration (minutes)" name="estimated_duration_minutes" type="number" min="15" max="1440" :value="old('estimated_duration_minutes', $jobModel?->estimated_duration_minutes)" />

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Job Level</label>
    <select name="job_level_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        <option value="">— None —</option>
        @foreach ($jobLevels as $level)
            <option value="{{ $level->id }}" @selected((string) old('job_level_id', $jobModel?->job_level_id) === (string) $level->id)>
                {{ $level->name }}
            </option>
        @endforeach
    </select>
</div>

<x-forms.service-types
    name="required_services[]"
    label="Required services"
    :selected="old('required_services', $jobModel?->required_services ?? [])"
/>

<div class="sm:col-span-2 border-t border-slate-200 pt-3">
    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Site instructions</p>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
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
            <label class="mb-1 block text-sm font-medium text-slate-700">Customer type</label>
            <select name="customer_type" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Select customer type</option>
                @foreach ($customerTypes as $customerType)
                    <option value="{{ $customerType }}" @selected(old('customer_type', $jobModel?->customer_type) === $customerType)>{{ $customerType }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="sm:col-span-2">
    <label class="mb-1 block text-sm font-medium text-slate-700">Additional site instructions</label>
    <textarea name="site_instructions" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="2">{{ old('site_instructions', $jobModel?->site_instructions) }}</textarea>
</div>

<div class="sm:col-span-2">
    <label class="mb-1 block text-sm font-medium text-slate-700">Pet warning</label>
    <textarea name="pet_warning" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="2">{{ old('pet_warning', $jobModel?->pet_warning) }}</textarea>
</div>

<div class="sm:col-span-2">
    <label class="mb-1 block text-sm font-medium text-slate-700">Attach images</label>
    <input type="file" name="images[]" multiple accept="image/*" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Done by</label>
    <select name="done_by_user_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        <option value="">Unassigned</option>
        @foreach ($employees as $employee)
            <option value="{{ $employee->id }}" @selected((string) old('done_by_user_id', $jobModel?->done_by_user_id) === (string) $employee->id)>{{ $employee->name }}</option>
        @endforeach
    </select>
</div>

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

<div id="job-payment-reason-wrap" class="hidden sm:col-span-2">
    <x-ui.input label="Reason if pending" name="payment_pending_reason" :value="old('payment_pending_reason', $jobModel?->payment_pending_reason)" />
</div>

<div class="sm:col-span-2">
    <label class="mb-1 block text-sm font-medium text-slate-700">Special remarks</label>
    <textarea name="special_remarks" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="3">{{ old('special_remarks', $jobModel?->special_remarks) }}</textarea>
</div>

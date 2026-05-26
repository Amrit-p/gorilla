@php
    $jobModel = $job ?? null;
@endphp

<div class="grid grid-cols-2 gap-3 sm:col-span-2">
    <x-ui.input label="Job date" name="scheduled_date" id="job-scheduled-date" type="date" :value="old('scheduled_date', optional($jobModel?->scheduled_date)->format('Y-m-d') ?? now()->toDateString())" />
    <x-ui.input label="Start time" name="scheduled_time" type="time" :value="old('scheduled_time', $jobModel?->scheduled_time ? \Illuminate\Support\Carbon::parse($jobModel->scheduled_time)->format('H:i') : '')" />
</div>

<x-ui.input label="Estimated duration (minutes)" name="estimated_duration_minutes" id="job-estimated-duration" type="number" min="15" max="1440" :value="old('estimated_duration_minutes', $jobModel?->estimated_duration_minutes ?? 60)" />

<x-forms.service-types
    name="required_services[]"
    label="Required services"
    :service-types="$serviceTypes ?? null"
    :selected="old('required_services', $jobModel?->required_services ?? [])"
/>

<div class="grid grid-cols-1 gap-3 sm:col-span-2 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Parking status</label>
        <select name="parking_status" id="job-parking-status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option value="">Select parking status</option>
            @foreach ($parkingStatuses as $parkingStatus)
                <option value="{{ $parkingStatus }}" @selected(old('parking_status', $jobModel?->parking_status) === $parkingStatus)>{{ $parkingStatus }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Site customer type</label>
        <select name="customer_type" id="job-customer-type" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option value="">Select type</option>
            @foreach ($customerTypes as $customerType)
                <option value="{{ $customerType }}" @selected(old('customer_type', $jobModel?->customer_type) === $customerType)>{{ $customerType }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="sm:col-span-2">
    <label class="mb-1 block text-sm font-medium text-slate-700">Site instructions</label>
    <textarea name="site_instructions" id="job-site-instructions" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="2">{{ old('site_instructions', $jobModel?->site_instructions) }}</textarea>
</div>

<div class="sm:col-span-2">
    <label class="mb-1 block text-sm font-medium text-slate-700">Pet warning</label>
    <textarea name="pet_warning" id="job-pet-warning" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="2">{{ old('pet_warning', $jobModel?->pet_warning) }}</textarea>
</div>

<div class="sm:col-span-2">
    <label class="mb-1 block text-sm font-medium text-slate-700">Attach images</label>
    <input type="file" name="images[]" multiple accept="image/*" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
</div>

<div class="sm:col-span-2">
    <label class="mb-1 block text-sm font-medium text-slate-700">Special remarks</label>
    <textarea name="special_remarks" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" rows="2">{{ old('special_remarks', $jobModel?->special_remarks) }}</textarea>
</div>

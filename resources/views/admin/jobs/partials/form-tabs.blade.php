@php
    $jobModel = $job ?? null;
    $assignedIds = old('employee_ids', $jobModel ? $jobModel->assignedEmployees->pluck('id')->all() : []);
    $submitLabel = $submitLabel ?? ($jobModel ? 'Update Job' : 'Create Job');
@endphp

<div class="sm:col-span-2" id="job-wizard" data-submit-label="{{ $submitLabel }}">
    <nav aria-label="Job form progress">
        <ol class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <li class="job-wizard-step flex flex-1 items-start gap-3" data-step="customer" data-step-index="1">
                <span class="job-wizard-step-badge flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-emerald-600 bg-emerald-600 text-sm font-semibold text-white">1</span>
                <div class="min-w-0 pt-0.5">
                    <p class="text-sm font-semibold text-slate-900">Customer</p>
                    <p class="text-xs text-slate-500">Select customer, location &amp; payment</p>
                </div>
            </li>
            <li class="job-wizard-step flex flex-1 items-start gap-3 opacity-50" data-step="site" data-step-index="2">
                <span class="job-wizard-step-badge flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-slate-300 bg-white text-sm font-semibold text-slate-500">2</span>
                <div class="min-w-0 pt-0.5">
                    <p class="text-sm font-semibold text-slate-700">Site details</p>
                    <p class="text-xs text-slate-500">Schedule, services &amp; site notes</p>
                </div>
            </li>
            <li class="job-wizard-step flex flex-1 items-start gap-3 opacity-50" data-step="mower" data-step-index="3">
                <span class="job-wizard-step-badge flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-slate-300 bg-white text-sm font-semibold text-slate-500">3</span>
                <div class="min-w-0 pt-0.5">
                    <p class="text-sm font-semibold text-slate-700">Mower assignment</p>
                    <p class="text-xs text-slate-500">Workload &amp; crew</p>
                </div>
            </li>
        </ol>
    </nav>

    <div class="mt-6 border-t border-slate-200 pt-6">
        <div id="job-tab-customer" class="sm:col-span-2">
            @include('admin.jobs.partials.tabs.customer', ['job' => $jobModel])
        </div>

        <div id="job-tab-site" class="hidden sm:col-span-2">
            @include('admin.jobs.partials.tabs.site', ['job' => $jobModel])
        </div>

        <div id="job-tab-mower" class="hidden sm:col-span-2">
            @include('admin.jobs.partials.tabs.mower', ['job' => $jobModel, 'assignedIds' => $assignedIds])
        </div>
    </div>

    <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5">
        <div>
            <button
                type="button"
                id="job-wizard-back"
                class="hidden rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Back
            </button>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ $jobModel ? route('admin.jobs.show', $jobModel) : route('admin.jobs.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
            <button
                type="button"
                id="job-wizard-next"
                class="rounded-md bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-800"
            >
                Continue to site details
            </button>
            <x-ui.button type="submit" id="job-form-submit" class="hidden">{{ $submitLabel }}</x-ui.button>
        </div>
    </div>
</div>

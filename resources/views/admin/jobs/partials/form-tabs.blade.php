@php
    $jobModel = $job ?? null;
    $submitLabel = $submitLabel ?? ($jobModel ? 'Update Job' : 'Create Job');
@endphp

{{-- Single-step form for both create and edit. Mowers are assigned separately,
     from the job list/detail (the "Assign" action), not from this form. --}}
<div class="sm:col-span-2">
    <div id="job-tab-details">
        @include('admin.jobs.partials.tabs.details', ['job' => $jobModel])
    </div>

    <div class="mt-8 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-5">
        <a href="{{ $jobModel ? route('admin.jobs.show', $jobModel) : route('admin.jobs.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        <x-ui.button type="submit" id="job-form-submit">{{ $submitLabel }}</x-ui.button>
    </div>
</div>

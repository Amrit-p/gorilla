<x-layouts.dashboard :title="'Job Scheduling'">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Jobs</h2>
                <p class="text-sm text-slate-600">Track workflow, assign mowers, and filter by Today / Upcoming / Done / Hold.</p>
            </div>
            @can('manage-job-records')
                <a href="{{ route('admin.jobs.create') }}" class="rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Create Job</a>
            @endcan
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @include('admin.partials.job-alert')
        @include('admin.jobs.partials.filter-bar', ['filters' => $filters])

        <div id="jobs-table-container">
            @include('admin.jobs.partials.table', ['jobs' => $jobs])
        </div>
    </div>

    @include('admin.partials.job-modals')
    @include('admin.partials.dropdown-script')
    @include('admin.partials.job-actions-script')

</x-layouts.dashboard>

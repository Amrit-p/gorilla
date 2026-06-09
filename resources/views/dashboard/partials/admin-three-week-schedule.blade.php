<div>
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-900">3-Week Schedule</h2>
            <p class="text-xs text-slate-500">Click any day count to view and manage jobs inline</p>
        </div>
        @can('view-jobs')
            <a href="{{ route('admin.jobs.index') }}" class="text-xs font-medium text-emerald-700 hover:text-emerald-800">
                View all jobs →
            </a>
        @endcan
    </div>

    <x-dashboard.three-week-calendar
        :weeks="$threeWeekSchedule"
        :daily-jobs-table-url="route('dashboard.daily-jobs-table')"
        :zones="\App\Models\Zone::orderBy('name')->get(['id','name'])"
        :workers="\App\Models\User::role(\App\Support\CrmRoles::MOWER)->where('is_active', true)->orderBy('name')->get(['id','name'])"
    />

    {{-- Job action modals — same ones used on the jobs index page --}}
    @include('admin.partials.job-modals')

    {{-- Shared dropdown utility --}}
    @include('admin.partials.dropdown-script')

    {{-- Job action handlers; filterCallback points to the panel reload fn defined in the component --}}
    @include('admin.partials.job-actions-script', ['filterCallback' => 'reloadDayPanelTable'])
</div>

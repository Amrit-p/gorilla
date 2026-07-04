@php
    $threeWeekStart = now()->startOfWeek(\Illuminate\Support\Carbon::MONDAY)->toDateString();
    $threeWeekEnd   = now()->startOfWeek(\Illuminate\Support\Carbon::MONDAY)->addWeeks(3)->subDay()->toDateString();

    // Lookups for the day panel's job filter bar.
    $dayPanelRecurrences     = \App\Models\Recurrence::query()->orderBy('name')->get(['id', 'name']);
    $dayPanelPaymentModes    = \App\Enums\JobOperationalPaymentMode::values();
    $dayPanelPaymentStatuses = \App\Enums\JobOperationalPaymentStatus::values();
    $dayPanelEquipmentTypes  = \App\Support\EquipmentTypes::selectOptions();
    $dayPanelJobLevels       = \App\Models\JobLevel::query()->active()->ordered()->get(['id', 'name', 'color_code']);
    $dayPanelCustomerTypes   = \App\Enums\JobCustomerType::values();
    $dayPanelServiceTypes    = \App\Support\ServiceTypes::all();
@endphp
<div>
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-900">3-Week Schedule</h2>
            <p class="text-xs text-slate-500">Click any day count to view and manage jobs inline</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button"
                    id="three-week-prev-btn"
                    onclick="crmShiftWeek(-1)"
                    class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-100">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Previous
            </button>
            <button type="button"
                    id="three-week-next-btn"
                    onclick="crmShiftWeek(1)"
                    class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-100">
                Next
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            @can('view-jobs')
                <a href="{{ route('admin.jobs.index', ['date_range' => ['start' => $threeWeekStart, 'end' => $threeWeekEnd]]) }}"
                   class="text-xs font-medium text-emerald-700 hover:text-emerald-800">
                    View all jobs →
                </a>
            @endcan
        </div>
    </div>

    <x-dashboard.three-week-calendar
        :weeks="$threeWeekSchedule"
        :daily-jobs-table-url="route('dashboard.daily-jobs-table')"
        :zones="\App\Models\Zone::orderBy('name')->get(['id','name'])"
        :workers="\App\Models\User::role(\App\Support\CrmRoles::MOWER)->where('is_active', true)->orderBy('name')->get(['id','name'])"
        :workflow-statuses="$workflowStatuses ?? []"
        :recurrences="$dayPanelRecurrences"
        :payment-modes="$dayPanelPaymentModes"
        :payment-statuses="$dayPanelPaymentStatuses"
        :equipment-types="$dayPanelEquipmentTypes"
        :job-levels="$dayPanelJobLevels"
        :customer-types="$dayPanelCustomerTypes"
        :service-types="$dayPanelServiceTypes"
    />

    {{-- Job action modals — same ones used on the jobs index page --}}
    @include('admin.partials.job-modals')

    {{-- Shared dropdown utility --}}
    @include('admin.partials.dropdown-script')

    {{-- Job selection registry powering #job-bulk-toolbar (count, visibility, selected ids) --}}
    <script src="{{ asset('js/job-bulk-toolbar.js') }}?v={{ @filemtime(public_path('js/job-bulk-toolbar.js')) ?: 1 }}"></script>

    {{-- Job action handlers; filterCallback points to the panel reload fn defined in the component --}}
    @include('admin.partials.job-actions-script', ['filterCallback' => 'reloadDayPanelTable'])
</div>

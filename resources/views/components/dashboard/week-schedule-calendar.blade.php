@props([
    'weeks'               => [],
    'calendarFilters'     => [],
    'weekOffset'          => 0,
    'openDay'             => null,
    'dailyJobsTableUrl'   => '',
    'dailyLeadsTableUrl'  => '',
    'zones'               => collect(),
    'workers'             => collect(),
    'workflowStatuses'    => [],
    'recurrences'         => collect(),
    'paymentModes'        => [],
    'paymentStatuses'     => [],
    'equipmentTypes'      => [],
    'jobLevels'           => collect(),
    'customerTypes'       => [],
    'serviceTypes'        => [],
    'leadStatuses'        => [],
    'leadSalesUsers'      => collect(),
])

{{-- ── Filters ─────────────────────────────────────────────────────────
     Reuses the shared jobs filter bar. It is given its own `formId` + JS hook
     names so it can coexist with the day panel's filter bar lower down this
     same page. `loadCalendarGrid` is overridden in the script block below to
     reload the grid instead of a jobs table. Initial values come from the
     dashboard's query string (see `syncUrlState` below). ─── --}}
<div class="mb-4">
    @include('admin.jobs.partials.filter-bar', [
        'filters' => array_merge([
            'search' => '',
            'list_scope' => '',
            'status' => '',
            'worker_id' => '',
            'recurrence_id' => '',
            'assignment' => '',
            'payment_mode' => '',
            'payment_status' => '',
            'equipment_type_id' => '',
            'job_level_id' => '',
            'customer_type' => '',
            'service_type' => '',
            'date_range_start' => '',
            'date_range_end' => '',
        ], array_filter($calendarFilters, fn ($value) => $value !== null)),
        'workers' => $workers,
        'workflowStatuses' => $workflowStatuses,
        'recurrences' => $recurrences,
        'paymentModes' => $paymentModes,
        'paymentStatuses' => $paymentStatuses,
        'equipmentTypes' => $equipmentTypes,
        'jobLevels' => $jobLevels,
        'customerTypes' => $customerTypes,
        'serviceTypes' => $serviceTypes,
        'hideListScope' => true,
        'showHeaderActions' => false,
        'formId' => 'cal-job-filter-form',
        'filterCallback' => 'loadCalendarGrid',
        'filtersFn' => 'getCalendarFilters',
        'reloadFn' => 'crmReloadCalendarGrid',
        'tableContainer' => 'week-schedule-grid',
        'filterUrl' => route('dashboard.week-schedule-grid'),
        'resetUrl' => route('dashboard.index'),
    ])
</div>

<div id="week-schedule-grid-wrap" class="relative">
    {{-- Grid loading overlay --}}
    <div id="week-schedule-grid-loader"
         class="absolute inset-0 z-10 hidden items-center justify-center rounded-xl bg-white/70 backdrop-blur-[1px]">
        <svg class="h-6 w-6 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
        </svg>
    </div>

    @include('dashboard.partials.week-schedule-grid', ['weeks' => $weeks])
</div>

{{-- ── Day Jobs Slide-over Panel ─────────────────────────────── --}}
{{-- NOTE: intentionally uses `right` animation, NOT `transform`, so that the
     job-actions dropdown (position:fixed) is not trapped by a CSS stacking context. --}}
<div id="crm-day-panel"
     class="space-y-5 fixed inset-y-0 right-0 z-50 flex w-full max-w-[90vw] flex-col bg-white shadow-2xl"
     style="right: -100%; transition: right 0.28s cubic-bezier(0.4,0,0.2,1);">

    {{-- Panel header --}}
    <div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4">
        <div>
            <h3 id="crm-day-panel-title" class="text-base font-semibold text-slate-900">Jobs</h3>
            <p id="crm-day-panel-subtitle" class="mt-0.5 text-xs text-slate-500"></p>
        </div>
        <div class="flex items-center gap-2">
            <span id="crm-day-panel-link-wrap" class="hidden">
                <a id="crm-day-panel-full-link" href="#"
                   class="text-xs font-medium text-emerald-700 hover:text-emerald-800">
                    View in jobs page →
                </a>
            </span>
            <button type="button" onclick="crmCloseDayPanel()"
                class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-200 hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Job alert (needed by job-actions-script) --}}
    <div id="job-alert" class="hidden mx-4 mt-3 rounded-md border px-3 py-2 text-sm"></div>

    {{-- Panel body --}}
    <div class="flex-1 overflow-auto p-2 space-y-4" style="scrollbar-gutter: stable">
        {{-- NOTE: the job table is loaded via AJAX into this container --}}

        {{-- Job filters, scoped to this day's job table only --}}
        @include('admin.jobs.partials.filter-bar', [
            'filters' => [
                'search' => '',
                'list_scope' => '',
                'status' => '',
                'recurrence_id' => '',
                'assignment' => '',
                'payment_mode' => '',
                'payment_status' => '',
                'equipment_type_id' => '',
                'job_level_id' => '',
                'customer_type' => '',
                'service_type' => '',
                'date_range_start' => '',
                'date_range_end' => '',
            ],
            'workflowStatuses' => $workflowStatuses,
            'recurrences' => $recurrences,
            'paymentModes' => $paymentModes,
            'paymentStatuses' => $paymentStatuses,
            'equipmentTypes' => $equipmentTypes,
            'jobLevels' => $jobLevels,
            'customerTypes' => $customerTypes,
            'serviceTypes' => $serviceTypes,
            'hideListScope' => true,
            'showHeaderActions' => false,
            'tableContainer' => 'crm-day-content',
            'filterCallback' => 'loadDayPanelJobs',
            'filterUrl' => $dailyJobsTableUrl,
            'resetUrl' => '#',
        ])

        {{-- Loader --}}
        <div id="crm-day-loader" class="hidden items-center justify-center py-16">
            <svg class="h-6 w-6 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
        </div>

        {{-- Error --}}
        <div id="crm-day-error" class="hidden items-center justify-center py-16">
            <p class="text-sm text-red-500">Failed to load jobs. Please try again.</p>
        </div>

        {{-- Server-rendered job table is injected here --}}
        <div id="crm-day-content" class="hidden"></div>

    </div>
</div>

{{-- Backdrop --}}
<div id="crm-day-backdrop"
     class="fixed inset-0 z-40 hidden bg-black/25 backdrop-blur-[1px]"
     onclick="crmCloseDayPanel()"></div>

{{-- ── Day Leads Slide-over Panel ────────────────────────────── --}}
<div id="crm-lead-panel"
     class="space-y-5 fixed inset-y-0 right-0 z-50 flex w-full max-w-[90vw] flex-col bg-white shadow-2xl"
     style="right: -100%; transition: right 0.28s cubic-bezier(0.4,0,0.2,1);">

    {{-- Panel header --}}
    <div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4">
        <div>
            <h3 id="crm-lead-panel-title" class="text-base font-semibold text-slate-900">Leads</h3>
            <p id="crm-lead-panel-subtitle" class="mt-0.5 text-xs text-slate-500"></p>
        </div>
        <button type="button" onclick="crmCloseLeadPanel()"
            class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-200 hover:text-slate-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Lead alert (needed by lead-actions-script) --}}
    <div id="lead-alert" class="hidden mx-4 mt-3 rounded-md border px-3 py-2 text-sm"></div>

    {{-- Panel body --}}
    <div class="flex-1 overflow-auto p-2 space-y-4" style="scrollbar-gutter: stable">
        {{-- NOTE: the lead table is loaded via AJAX into this container --}}

        {{-- Lead filters, scoped to this day's lead table only --}}
        @include('admin.leads.partials.filter-bar', [
            'filters' => [
                'search' => '',
                'status' => '',
                'assigned_sales_user_id' => '',
                'zone_id' => '',
                'recurrence_id' => '',
            ],
            'statuses' => $leadStatuses,
            'salesUsers' => $leadSalesUsers,
            'zones' => $zones,
            'recurrences' => $recurrences,
            'showHeaderActions' => false,
            'tableContainer' => 'crm-lead-content',
            'filterCallback' => 'loadDayPanelLeads',
            'filterUrl' => $dailyLeadsTableUrl,
            'resetUrl' => '#',
        ])

        {{-- Loader --}}
        <div id="crm-lead-loader" class="hidden items-center justify-center py-16">
            <svg class="h-6 w-6 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
        </div>

        {{-- Error --}}
        <div id="crm-lead-error" class="hidden items-center justify-center py-16">
            <p class="text-sm text-red-500">Failed to load leads. Please try again.</p>
        </div>

        {{-- Server-rendered lead table is injected here --}}
        <div id="crm-lead-content" class="hidden"></div>

    </div>
</div>

{{-- Backdrop --}}
<div id="crm-lead-backdrop"
     class="fixed inset-0 z-40 hidden bg-black/25 backdrop-blur-[1px]"
     onclick="crmCloseLeadPanel()"></div>

@push('scripts')
<script>
(function () {
    var _tableUrl     = @json($dailyJobsTableUrl);
    var _leadTableUrl  = @json($dailyLeadsTableUrl);
    var _gridUrl      = @json(route('dashboard.week-schedule-grid'));
    var _currentDate  = null;
    var _currentLeadDate = null;
    var _weekOffset   = @json((int) $weekOffset);

    /* ── filter state ────────────────────────────────────────────────
       Reads the shared jobs filter bar rendered above the grid (form id
       `cal-job-filter-form`). Empty fields are dropped so they don't trip the
       grid endpoint's `nullable|integer` validation. ─── */
    function getCalFilters() {
        var out  = {};
        var form = document.getElementById('cal-job-filter-form');
        if (form) {
            new URLSearchParams(new FormData(form)).forEach(function (value, key) {
                if (value !== '') { out[key] = value; }
            });
        }
        return out;
    }

    function buildTableUrl(base, filters) {
        return base + '?' + $.param(filters);
    }

    /* ── mirror the calendar's state (filters, week, open day) into the page
       URL, so returning to it — e.g. after editing a job — lands on the same
       view. The dashboard reads these params back on load. ─── */
    function syncUrlState() {
        var params = getCalFilters();
        if (_weekOffset !== 0) { params.week_offset = _weekOffset; }
        if (_currentDate) { params.day = _currentDate; }

        var qs = $.param(params);
        history.replaceState(history.state, '', window.location.pathname + (qs ? '?' + qs : '') + window.location.hash);
    }

    /* ── merges the calendar's filter bar with the day panel's own filter bar
       and the currently open date. The panel's own fields win when set; the
       calendar's filters only fill in what the panel leaves blank. Empty fields
       are dropped so they don't trip the backend's `nullable|integer`
       validation on filters like zone_id. ─── */
    function getDayPanelFilters() {
        var filters = {};
        var panelForm = document.getElementById('job-filter-form');
        if (panelForm) {
            new URLSearchParams(new FormData(panelForm)).forEach(function (value, key) {
                if (value !== '') { filters[key] = value; }
            });
        }

        var cal = getCalFilters();
        Object.keys(cal).forEach(function (key) {
            if (! filters[key]) { filters[key] = cal[key]; }
        });

        if (_currentDate) { filters.date = _currentDate; }

        return filters;
    }

    /* ── merges the quick filters (zone/search) with the lead panel's own
       filter bar (status/assignee/zone/recurrence/search) and the currently
       open date. The panel's own fields win when set; quick filters only
       fill in what's left blank. ─── */
    function getLeadPanelFilters() {
        var filters = {};
        var panelForm = document.getElementById('lead-filter-form');
        if (panelForm) {
            new URLSearchParams(new FormData(panelForm)).forEach(function (value, key) {
                if (value !== '') { filters[key] = value; }
            });
        }

        var cal = getCalFilters();
        if (!filters.zone_id && cal.zone_id) { filters.zone_id = cal.zone_id; }
        if (!filters.search && cal.search)   { filters.search  = cal.search; }

        if (_currentLeadDate) { filters.date = _currentLeadDate; }

        return filters;
    }

    /* ── sync the panel's own date-range filter to the clicked day ──── */
    function syncDayPanelDateRange() {
        var $input = $('#job-filter-form input[readonly].filter');
        if (!$input.length || !_currentDate) { return; }

        var picker = $input.data('daterangepicker');
        if (picker) {
            var m = moment(_currentDate, 'YYYY-MM-DD');
            picker.setStartDate(m);
            picker.setEndDate(m);
        }

        $input.val(_currentDate + ' – ' + _currentDate);
        $input.siblings('button').removeClass('hidden');
        $('#job-filter-form input[name="date_range[start]"]').val(_currentDate);
        $('#job-filter-form input[name="date_range[end]"]').val(_currentDate);
    }

    /* ── helpers ─────────────────────────────────────── */
    function show(id) {
        var el = document.getElementById(id);
        if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
    }
    function hide(id) {
        var el = document.getElementById(id);
        if (el) { el.classList.remove('flex'); el.classList.add('hidden'); }
    }

    /* ── core loader ─────────────────────────────────── */
    function loadTable(url) {
        hide('crm-day-content');
        hide('crm-day-error');
        show('crm-day-loader');

        $.ajax({
            url: url,
            method: 'GET',
            headers: { Accept: 'text/html, */*' },
            success: function (html) {
                hide('crm-day-loader');

                var $content = $('#crm-day-content');
                $content.html(html);
                show('crm-day-content');

                /* Count visible job rows to update subtitle */
                var count = $content.find('.job-row').length;
                var dayFilters = getDayPanelFilters();
                var hasActive  = Object.keys(dayFilters).some(function (key) { return key !== 'date'; });
                var suffix     = hasActive ? ' (filtered)' : '';
                document.getElementById('crm-day-panel-subtitle').textContent =
                    count + ' ' + (count === 1 ? 'job' : 'jobs') + ' scheduled' + suffix;

                /* NOTE: do NOT re-call crmDropdown here — it already uses document-level
                   event delegation registered once by job-actions-script, so calling it
                   again would register duplicate handlers that break the menus. */
            },
            error: function () {
                hide('crm-day-loader');
                show('crm-day-error');
                document.getElementById('crm-day-panel-subtitle').textContent = '';
            }
        });
    }

    /* ── lead table loader ───────────────────────────── */
    function loadLeadTable(url) {
        hide('crm-lead-content');
        hide('crm-lead-error');
        show('crm-lead-loader');

        $.ajax({
            url: url,
            method: 'GET',
            headers: { Accept: 'text/html, */*' },
            success: function (html) {
                hide('crm-lead-loader');

                var $content = $('#crm-lead-content');
                $content.html(html);
                show('crm-lead-content');

                var count = $content.find('.lead-row').length;
                document.getElementById('crm-lead-panel-subtitle').textContent =
                    count + ' ' + (count === 1 ? 'lead' : 'leads') + ' scheduled';
            },
            error: function () {
                hide('crm-lead-loader');
                show('crm-lead-error');
                document.getElementById('crm-lead-panel-subtitle').textContent = '';
            }
        });
    }

    /* ── refresh the calendar grid without a page reload ─────────── */
    function reloadCalendarGrid(callback) {
        var params = getCalFilters();
        params.week_offset = _weekOffset;
        var url = _gridUrl + '?' + $.param(params);

        show('week-schedule-grid-loader');

        $.ajax({
            url: url,
            method: 'GET',
            headers: { Accept: 'text/html, */*' },
            success: function (html) {
                $('#week-schedule-grid').replaceWith(html);
                hide('week-schedule-grid-loader');
                if (typeof callback === 'function') { callback(); }
            },
            error: function () {
                hide('week-schedule-grid-loader');
                if (typeof callback === 'function') { callback(); }
            }
        });
    }

    /* ── week navigation ─────────────────────────────── */
    window.crmShiftWeek = function (delta) {
        _weekOffset += delta;
        syncUrlState();

        var prevBtn = document.getElementById('week-schedule-prev-btn');
        var nextBtn = document.getElementById('week-schedule-next-btn');
        if (prevBtn) { prevBtn.disabled = true; prevBtn.classList.add('opacity-50', 'cursor-not-allowed'); }
        if (nextBtn) { nextBtn.disabled = true; nextBtn.classList.add('opacity-50', 'cursor-not-allowed'); }

        reloadCalendarGrid(function () {
            if (prevBtn) { prevBtn.disabled = false; prevBtn.classList.remove('opacity-50', 'cursor-not-allowed'); }
            if (nextBtn) { nextBtn.disabled = false; nextBtn.classList.remove('opacity-50', 'cursor-not-allowed'); }
        });
    };

    /* ── reload hook used by job-actions-script after each action ─ */
    window.reloadDayPanelTable = function () {
        if (_currentDate) {
            loadTable(buildTableUrl(_tableUrl, getDayPanelFilters()));
            reloadCalendarGrid();
        }
    };

    /* ── day panel's own filter bar (status, recurrence, assignment, etc.) ─ */
    window.loadDayPanelJobs = function () {
        if (_currentDate) {
            loadTable(buildTableUrl(_tableUrl, getDayPanelFilters()));
        }
    };

    /* ── open ────────────────────────────────────────── */
    function openDayPanel(date, label) {
        _currentDate = date;
        syncUrlState();

        document.getElementById('crm-day-panel-title').textContent   = label;
        document.getElementById('crm-day-panel-subtitle').textContent = 'Loading…';

        syncDayPanelDateRange();

        /* Update "view in jobs page" link */
        @can('view-jobs')
        var $link = $('#crm-day-panel-link-wrap');
        $('#crm-day-panel-full-link').attr(
            'href',
            '{{ route('admin.jobs.index') }}?scheduled_date=' + encodeURIComponent(_currentDate)
        );
        $link.removeClass('hidden');
        @endcan

        document.getElementById('crm-day-panel').style.right = '0';
        document.getElementById('crm-day-backdrop').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        loadTable(buildTableUrl(_tableUrl, getDayPanelFilters()));
    }

    window.crmOpenDayPanel = function (btn) {
        openDayPanel(btn.dataset.date, btn.dataset.label);
    };

    /* ── reopen the day panel restored from the URL (`?day=YYYY-MM-DD`).
       The label matches the grid buttons' "Monday, 05 Sep" format; the day may
       no longer have a button if its jobs were moved elsewhere. ─── */
    var _initialDay = @json($openDay);
    if (_initialDay) {
        $(function () {
            openDayPanel(_initialDay, moment(_initialDay, 'YYYY-MM-DD').format('dddd, DD MMM'));
        });
    }

    /* ── close ───────────────────────────────────────── */
    window.crmCloseDayPanel = function () {
        var wasOpen = _currentDate !== null;
        document.getElementById('crm-day-panel').style.right = '-100%';
        document.getElementById('crm-day-backdrop').classList.add('hidden');
        document.body.style.overflow = '';
        _currentDate = null;
        if (wasOpen) { syncUrlState(); }

        if (typeof window.clearJobBulkSelection === 'function') {
            window.clearJobBulkSelection();
        }
        var toolbar = document.getElementById('job-bulk-toolbar');
        if (toolbar) { toolbar.classList.add('hidden'); }
    };

    /* ── reload hook used by lead-actions-script after each action ─ */
    window.reloadDayPanelLeads = function () {
        if (_currentLeadDate) {
            loadLeadTable(buildTableUrl(_leadTableUrl, getLeadPanelFilters()));
            reloadCalendarGrid();
        }
    };

    /* ── lead panel's own filter bar (status, assignee, zone, recurrence, etc.) ─ */
    window.loadDayPanelLeads = function () {
        if (_currentLeadDate) {
            loadLeadTable(buildTableUrl(_leadTableUrl, getLeadPanelFilters()));
        }
    };

    /* ── open ────────────────────────────────────────── */
    window.crmOpenLeadPanel = function (btn) {
        _currentLeadDate = btn.dataset.date;
        var label        = btn.dataset.label;

        document.getElementById('crm-lead-panel-title').textContent    = label;
        document.getElementById('crm-lead-panel-subtitle').textContent = 'Loading…';

        document.getElementById('crm-lead-panel').style.right = '0';
        document.getElementById('crm-lead-backdrop').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        loadLeadTable(buildTableUrl(_leadTableUrl, getLeadPanelFilters()));
    };

    /* ── close ───────────────────────────────────────── */
    window.crmCloseLeadPanel = function () {
        document.getElementById('crm-lead-panel').style.right = '-100%';
        document.getElementById('crm-lead-backdrop').classList.add('hidden');
        document.body.style.overflow = '';
        _currentLeadDate = null;

        if (typeof window.clearLeadBulkSelection === 'function') {
            window.clearLeadBulkSelection();
        }
        var toolbar = document.getElementById('lead-bulk-toolbar');
        if (toolbar) { toolbar.classList.add('hidden'); }
    };

    /* ── filter controls ─────────────────────────────────────────────
       The shared filter bar (form id `cal-job-filter-form`) fires this on every
       change/submit. We override its default table-reload behaviour to instead
       refresh the calendar grid and any open slide-over panel. ─── */
    window.loadCalendarGrid = function () {
        syncUrlState();
        reloadCalendarGrid();
        if (_currentDate) {
            loadTable(buildTableUrl(_tableUrl, getDayPanelFilters()));
        }
        if (_currentLeadDate) {
            loadLeadTable(buildTableUrl(_leadTableUrl, getLeadPanelFilters()));
        }
    };

    /* ── intercept pagination clicks inside the panel ── */
    $(document).on('click', '#crm-day-content .pagination a', function (e) {
        e.preventDefault();
        /* Preserve filters when paging */
        var pageUrl = $(this).attr('href');
        var qs      = $.param(getDayPanelFilters());
        pageUrl += (pageUrl.includes('?') ? '&' : '?') + qs;
        loadTable(pageUrl);
    });

    $(document).on('click', '#crm-lead-content .pagination a', function (e) {
        e.preventDefault();
        var pageUrl = $(this).attr('href');
        var qs      = $.param(getLeadPanelFilters());
        pageUrl += (pageUrl.includes('?') ? '&' : '?') + qs;
        loadLeadTable(pageUrl);
    });

    /* ── Escape closes the panels ────────────────────── */
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            window.crmCloseDayPanel();
            window.crmCloseLeadPanel();
        }
    });
})();
</script>
@endpush

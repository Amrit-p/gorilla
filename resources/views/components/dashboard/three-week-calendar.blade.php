@props([
    'weeks'               => [],
    'dailyJobsTableUrl'   => '',
])

<div class="space-y-4">
    @forelse ($weeks as $week)
        @php
            $weekTotal = array_sum(array_column($week['days'], 'total'));
        @endphp
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            {{-- Week header --}}
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-3">
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-semibold text-slate-800">{{ $week['label'] }}</h3>
                    <span class="text-xs text-slate-400">{{ $week['start_date'] }} – {{ $week['end_date'] }}</span>
                </div>
                @if ($weekTotal > 0)
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                        {{ $weekTotal }} {{ $weekTotal === 1 ? 'job' : 'jobs' }}
                    </span>
                @else
                    <span class="text-xs text-slate-400">No jobs this week</span>
                @endif
            </div>

            {{-- Day columns --}}
            <div class="overflow-x-auto">
                <div class="grid min-w-[700px] grid-cols-7 divide-x divide-slate-100">
                    @foreach ($week['days'] as $day)
                        @php
                            $isToday = $day['is_today'];
                            $isPast  = $day['is_past'];
                            $hasJobs = $day['total'] > 0;
                        @endphp
                        <div class="flex min-h-[140px] flex-col {{ $isToday ? 'bg-emerald-50' : ($isPast ? 'bg-slate-50/50' : 'bg-white') }}">

                            {{-- Day header --}}
                            <div class="border-b {{ $isToday ? 'border-emerald-200' : 'border-slate-100' }} px-3 py-2">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-[11px] font-bold uppercase tracking-wide {{ $isToday ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ substr($day['day_name'], 0, 3) }}
                                    </span>
                                    @if ($isToday)
                                        <span class="rounded bg-emerald-600 px-1 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white">Today</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-[11px] {{ $isToday ? 'font-semibold text-emerald-600' : 'text-slate-400' }}">
                                    {{ $day['date_label'] }}
                                </p>
                            </div>

                            {{-- Day body --}}
                            <div class="flex flex-1 flex-col p-2.5">
                                @if ($hasJobs)
                                    <button
                                        type="button"
                                        class="group mb-2 flex items-baseline gap-1 text-left"
                                        data-date="{{ $day['date'] }}"
                                        data-label="{{ $day['day_name'] }}, {{ $day['date_label'] }}"
                                        onclick="crmOpenDayPanel(this)"
                                    >
                                        <span class="text-xl font-extrabold leading-none {{ $isToday ? 'text-emerald-700' : 'text-slate-800' }} transition-colors group-hover:text-emerald-600">
                                            {{ $day['total'] }}
                                        </span>
                                        <span class="text-[10px] {{ $isToday ? 'text-emerald-500' : 'text-slate-400' }} transition-colors group-hover:text-emerald-500">
                                            {{ $day['total'] === 1 ? 'job' : 'jobs' }}
                                        </span>
                                    </button>

                                    @if ($day['zone_count'] > 0)
                                        <div class="space-y-1">
                                            @foreach ($day['zones'] as $zoneName => $zoneCount)
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="truncate text-[10px] text-slate-500" title="{{ $zoneName }}">{{ $zoneName }}</span>
                                                    <span class="flex-shrink-0 rounded bg-slate-100 px-1 text-[10px] font-semibold text-slate-600">{{ $zoneCount }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <div class="flex flex-1 items-center justify-center">
                                        <span class="text-xs text-slate-300">—</span>
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-slate-200 bg-white px-6 py-10 text-center shadow-sm">
            <p class="text-sm text-slate-500">No schedule data available.</p>
        </div>
    @endforelse
</div>

{{-- ── Day Jobs Slide-over Panel ─────────────────────────────── --}}
{{-- NOTE: intentionally uses `right` animation, NOT `transform`, so that the
     job-actions dropdown (position:fixed) is not trapped by a CSS stacking context. --}}
<div id="crm-day-panel"
     class="fixed inset-y-0 right-0 z-50 flex w-full max-w-[90vw] flex-col bg-white shadow-2xl"
     style="right: -100%; transition: right 0.28s cubic-bezier(0.4,0,0.2,1);">

    {{-- Panel header --}}
    <div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4">
        <div>
            <h3 id="crm-day-panel-title" class="text-base font-semibold text-slate-900">Jobs</h3>
            <p id="crm-day-panel-subtitle" class="mt-0.5 text-xs text-slate-500"></p>
        </div>
        <div class="flex items-center gap-2">
            <span id="crm-day-panel-link-wrap" class="hidden">
                <a id="crm-day-panel-full-link" href="#" target="_blank"
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
    <div class="flex-1 overflow-auto">

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

@push('scripts')
<script>
(function () {
    var _tableUrl   = @json($dailyJobsTableUrl);
    var _currentDate = null;

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
                document.getElementById('crm-day-panel-subtitle').textContent =
                    count + ' ' + (count === 1 ? 'job' : 'jobs') + ' scheduled';

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

    /* ── reload hook used by job-actions-script after each action ─ */
    window.reloadDayPanelTable = function () {
        if (_currentDate) {
            loadTable(_tableUrl + '?date=' + encodeURIComponent(_currentDate));
        }
    };

    /* ── open ────────────────────────────────────────── */
    window.crmOpenDayPanel = function (btn) {
        _currentDate = btn.dataset.date;
        var label    = btn.dataset.label;

        document.getElementById('crm-day-panel-title').textContent   = label;
        document.getElementById('crm-day-panel-subtitle').textContent = 'Loading…';

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

        loadTable(_tableUrl + '?date=' + encodeURIComponent(_currentDate));
    };

    /* ── close ───────────────────────────────────────── */
    window.crmCloseDayPanel = function () {
        document.getElementById('crm-day-panel').style.right = '-100%';
        document.getElementById('crm-day-backdrop').classList.add('hidden');
        document.body.style.overflow = '';
        _currentDate = null;
    };

    /* ── intercept pagination clicks inside the panel ── */
    $(document).on('click', '#crm-day-content .pagination a', function (e) {
        e.preventDefault();
        loadTable($(this).attr('href'));
    });

    /* ── Escape closes the panel ─────────────────────── */
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') { window.crmCloseDayPanel(); }
    });
})();
</script>
@endpush

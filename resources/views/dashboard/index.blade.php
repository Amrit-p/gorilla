@php
$dashboardType = $analytics['type'] ?? 'admin';
@endphp

<x-layouts.dashboard :title="'Dashboard'" subtitle="Analytics and quick access to your CRM modules">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600">
                Welcome back, <span class="font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                • {{ now()->format('d M Y') }}
                @if ($dashboardType === 'admin')
                <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Admin</span>
                @elseif ($dashboardType === 'sales')
                <span class="ml-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800">Sales</span>
                @else
                <span class="ml-1 rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-800">Mower</span>
                @endif
            </p>
            <div class="flex items-center gap-2">
                @if ($dashboardType === 'mower')
                <a href="{{ route('mower.index') }}" class="rounded-md bg-emerald-700 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-800">Open field dashboard</a>
                @endif
                <button id="open-preferences-modal" type="button" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                    Preferences
                </button>
            </div>
        </div>

        <div id="dashboard-alert" class="hidden"></div>

        @if (in_array($dashboardType, ['admin', 'sales']))
        @include('dashboard.partials.admin-analytics', ['analytics' => $analytics])
        @else
        @include('dashboard.partials.mower-analytics', ['analytics' => $analytics])
        @endif
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm xl:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Today's schedule</h2>
                        <p class="text-xs text-slate-500">{{ now()->format('l, d M Y') }}</p>
                    </div>
                    @can('view-jobs')
                    <a href="{{ route('admin.jobs.index', ['date_range' => ['start' => now()->toDateString(), 'end' => now()->toDateString()]]) }}"
                        class="text-xs font-medium text-emerald-700 hover:text-emerald-800">View all jobs →</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Customer</th>
                                <th class="px-4 py-3 font-semibold">Address</th>
                                <th class="px-4 py-3 font-semibold">Time</th>
                                <th class="px-4 py-3 font-semibold">Payment</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($todaysJobs as $job)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    @can('view-jobs')
                                    <a href="{{ route('admin.jobs.show', $job) }}" class="hover:text-emerald-700">{{ $job->client?->name ?? '—' }}</a>
                                    @else
                                    {{ $job->client?->name ?? '—' }}
                                    @endcan
                                </td>
                                <td class="max-w-[200px] truncate px-4 py-3 text-slate-600" title="{{ $job->client_address }}">{{ $job->client_address ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    @if ($job->scheduled_time)
                                    {{ \Illuminate\Support\Carbon::parse($job->scheduled_time)->format('g:i A') }}
                                    @else
                                    —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $job->payment_status ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-jobs.status-badge :status="$job->status" />
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">No jobs scheduled for today.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($dashboardType === 'admin')
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="text-base font-semibold text-slate-900">Lead pipeline</h2>
                <p class="mt-1 text-xs text-slate-500">Counts by current status</p>
                <ul class="mt-4 space-y-2">
                    @forelse ($leadsByStatus as $status => $count)
                    <li class="flex items-center justify-between rounded-md border border-slate-100 px-3 py-2 text-sm">
                        <span class="text-slate-700">{{ $status }}</span>
                        <span class="font-semibold text-slate-900">{{ $count }}</span>
                    </li>
                    @empty
                    <li class="text-sm text-slate-500">No leads yet.</li>
                    @endforelse
                </ul>
            </div>
            @endif
        </div>

        @if (in_array($dashboardType, ['admin', 'sales']))
        @include('dashboard.partials.admin-three-week-schedule', ['threeWeekSchedule' => $threeWeekSchedule])
        @endif

        @include('dashboard.partials.quick-actions')

        @if ($dashboardType === 'admin')
        @include('dashboard.partials.mower-performance-table', ['analytics' => $analytics])
        @endif

        @if ($dashboardType === 'admin' || $dashboardType === 'sales')
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <div class="xl:col-span-2">
                @include('dashboard.partials.analytics-tabs')
            </div>
            <x-dashboard.chart-panel
                title="Jobs by status"
                subtitle="Scheduled in the last 30 days"
                chart-id="dashboard-jobs-status-chart"
                type="doughnut"
                :labels="$analytics['charts']['jobs_by_status']['labels'] ?? []"
                :datasets="$analytics['charts']['jobs_by_status']['datasets'] ?? []" />
        </div>
        @endif

    </div>

    @if (in_array($dashboardType, ['admin', 'sales']))
    <button id="open-hold-jobs-panel" type="button"
        class="fixed right-0 top-1/2 z-30 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-l-lg bg-amber-500 text-white shadow-lg transition-colors hover:bg-amber-600"
        title="Hold Jobs">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </button>

    {{-- Hold Jobs slide-over panel --}}
    <div id="dashboard-hold-jobs-panel"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col bg-white shadow-2xl"
        style="right: -100%; transition: right 0.28s cubic-bezier(0.4,0,0.2,1);">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Hold Jobs</h3>
                <p id="dashboard-hold-jobs-subtitle" class="mt-0.5 text-xs text-slate-500"></p>
            </div>
            <button type="button" id="close-hold-jobs-panel" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-200 hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-auto p-4" style="scrollbar-gutter: stable">
            @can('assign-jobs')
            <p class="mb-2 text-[11px] text-slate-400">Hold a job card to select it, then reschedule multiple jobs at once.</p>
            <div id="hold-jobs-bulk-bar" class="mb-3 hidden items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2">
                <p class="text-xs font-medium text-emerald-800"><span id="hold-jobs-bulk-count">0</span> selected</p>
                <div class="flex items-center gap-2">
                    <button type="button" id="hold-jobs-bulk-schedule" class="rounded-md bg-emerald-700 px-2.5 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-emerald-800">
                        Reschedule
                    </button>
                    <button type="button" id="hold-jobs-bulk-clear" class="rounded-md border border-emerald-300 bg-white px-2.5 py-1.5 text-xs font-medium text-emerald-700 transition-colors hover:bg-emerald-100">
                        Clear
                    </button>
                </div>
            </div>
            @endcan
            <div id="dashboard-hold-jobs-loader" class="hidden items-center justify-center py-16">
                <svg class="h-6 w-6 animate-spin text-amber-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
            </div>
            <div id="dashboard-hold-jobs-error" class="hidden items-center justify-center py-16">
                <p class="text-sm text-red-500">Failed to load hold jobs. Please try again.</p>
            </div>
            <div id="dashboard-hold-jobs-content"></div>
        </div>
    </div>
    <div id="dashboard-hold-jobs-backdrop" class="fixed inset-0 z-40 hidden bg-black/25 backdrop-blur-[1px]"></div>
    @endif

    <x-ui.modal id="dashboard-preferences-modal" title="Dashboard Preferences">
        <form id="dashboard-preferences-form" action="{{ route('dashboard.preferences.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Theme</label>
                <select name="theme" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="light" @selected($preferences->theme === 'light')>Light</option>
                    <option value="dark" @selected($preferences->theme === 'dark')>Dark</option>
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="compact_cards" value="1" @checked($preferences->compact_cards)>
                Compact statistic cards
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="show_activity_timeline" value="1" @checked($preferences->show_activity_timeline)>
                Show activity timeline panel
            </label>
            <x-ui.button type="submit">Save Preferences</x-ui.button>
        </form>
    </x-ui.modal>

    @push('scripts')
    <script>
        function showDashboardAlert(message, isError = false) {
            const baseClass = isError ?
                'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700' :
                'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#dashboard-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        $('#open-preferences-modal').on('click', function() {
            $('#dashboard-preferences-modal').removeClass('hidden').addClass('flex');
        });

        $('[data-close-modal="dashboard-preferences-modal"]').on('click', function() {
            $('#dashboard-preferences-modal').addClass('hidden').removeClass('flex');
        });

        $('#dashboard-preferences-form').on('submit', function(event) {
            event.preventDefault();
            const $form = $(this);
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: {
                    _token: $form.find('input[name="_token"]').val(),
                    _method: 'PATCH',
                    theme: $form.find('select[name="theme"]').val(),
                    compact_cards: $form.find('input[name="compact_cards"]').is(':checked') ? 1 : 0,
                    show_activity_timeline: $form.find('input[name="show_activity_timeline"]').is(':checked') ? 1 : 0,
                },
                headers: {
                    Accept: 'application/json'
                },
                success: function(response) {
                    showDashboardAlert(response.message || 'Dashboard preferences saved.');
                    $('#dashboard-preferences-modal').addClass('hidden').removeClass('flex');
                    setTimeout(() => window.location.reload(), 400);
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    showDashboardAlert(Object.values(errors)[0]?.[0] || 'Unable to save preferences.', true);
                },
            });
        });

        if (typeof window.crmInitDashboardCharts === 'function') {
            window.crmInitDashboardCharts();
        }

        @if(in_array($dashboardType, ['admin', 'sales']))
        var holdJobsUrl = @json(route('dashboard.hold-jobs'));

        function loadHoldJobs() {
            $('#dashboard-hold-jobs-content').addClass('hidden').empty();
            $('#dashboard-hold-jobs-error').removeClass('flex').addClass('hidden');
            $('#dashboard-hold-jobs-loader').removeClass('hidden').addClass('flex');

            $.ajax({
                url: holdJobsUrl,
                method: 'GET',
                headers: {
                    Accept: 'text/html, */*'
                },
                success: function(html) {
                    $('#dashboard-hold-jobs-loader').removeClass('flex').addClass('hidden');
                    var $content = $('#dashboard-hold-jobs-content');
                    $content.html(html).removeClass('hidden');
                    var count = $content.find('.hold-job-card').length;
                    $('#dashboard-hold-jobs-subtitle').text(count + ' ' + (count === 1 ? 'job' : 'jobs') + ' on hold');
                },
                error: function() {
                    $('#dashboard-hold-jobs-loader').removeClass('flex').addClass('hidden');
                    $('#dashboard-hold-jobs-error').removeClass('hidden').addClass('flex');
                    $('#dashboard-hold-jobs-subtitle').text('');
                },
            });
        }

        function openHoldJobsPanel() {
            $('#dashboard-hold-jobs-panel').css('right', '0');
            $('#dashboard-hold-jobs-backdrop').removeClass('hidden');
            $('body').css('overflow', 'hidden');
            loadHoldJobs();
        }

        function closeHoldJobsPanel() {
            $('#dashboard-hold-jobs-panel').css('right', '-100%');
            $('#dashboard-hold-jobs-backdrop').addClass('hidden');
            $('body').css('overflow', '');
        }

        $('#open-hold-jobs-panel').on('click', openHoldJobsPanel);
        $('#close-hold-jobs-panel').on('click', closeHoldJobsPanel);
        $('#dashboard-hold-jobs-backdrop').on('click', closeHoldJobsPanel);
        $(document).on('keydown', function(event) {
            if (event.key === 'Escape') {
                closeHoldJobsPanel();
            }
        });

        window.reloadHoldJobsPanel = function() {
            if ($('#dashboard-hold-jobs-panel').css('right') === '0px') {
                loadHoldJobs();
            }
        };

        @can('assign-jobs')
        // ── Long-press-to-select + bulk reschedule ──────────────────────
        var holdJobsBulkMode = false;
        var holdJobsLongPressTimer = null;
        var holdJobsLongPressActivated = false;
        var HOLD_JOBS_LONG_PRESS_MS = 520;

        function holdJobsSelectedIds() {
            if (!window.crmJobSelection) {
                return [];
            }
            return window.crmJobSelection.getIds().filter(function(id) {
                return document.querySelector('#dashboard-hold-jobs-content .hold-job-card[data-job-id="' + id + '"]');
            });
        }

        function syncHoldJobsBulkBar() {
            var ids = holdJobsSelectedIds();
            holdJobsBulkMode = ids.length > 0;
            $('#hold-jobs-bulk-count').text(ids.length);
            $('#hold-jobs-bulk-bar').toggleClass('hidden', ids.length === 0).toggleClass('flex', ids.length > 0);

            $('#dashboard-hold-jobs-content .hold-job-card').each(function() {
                var id = Number(this.dataset.jobId);
                var selected = window.crmJobSelection && window.crmJobSelection.has(id);
                $(this).toggleClass('ring-2 ring-emerald-500 bg-emerald-50/60', !!selected);
            });
        }

        window.addEventListener('jobs:selection-changed', syncHoldJobsBulkBar);

        $(document).on('pointerdown', '#dashboard-hold-jobs-content .hold-job-card', function(event) {
            if ($(event.target).closest('a, button, input, select, textarea').length) {
                return;
            }
            var card = this;
            holdJobsLongPressActivated = false;
            clearTimeout(holdJobsLongPressTimer);
            holdJobsLongPressTimer = setTimeout(function() {
                if (window.crmJobSelection) {
                    window.crmJobSelection.toggle(Number(card.dataset.jobId));
                }
                holdJobsLongPressActivated = true;
                syncHoldJobsBulkBar();
            }, HOLD_JOBS_LONG_PRESS_MS);
        });

        $(document).on('pointerup pointercancel pointerleave', function() {
            clearTimeout(holdJobsLongPressTimer);
        });

        $(document).on('click', '#dashboard-hold-jobs-content .hold-job-card', function(event) {
            if ($(event.target).closest('a, button, input, select, textarea').length) {
                return;
            }

            if (holdJobsLongPressActivated) {
                holdJobsLongPressActivated = false;
                return;
            }

            var id = Number(this.dataset.jobId);
            if (holdJobsBulkMode) {
                if (window.crmJobSelection) {
                    window.crmJobSelection.toggle(id);
                }
                syncHoldJobsBulkBar();
                return;
            }

            var href = this.dataset.href;
            if (href) {
                window.location.href = href;
            }
        });

        $('#hold-jobs-bulk-clear').on('click', function() {
            if (window.crmJobSelection) {
                window.crmJobSelection.clear();
            }
        });

        $('#hold-jobs-bulk-schedule').on('click', function() {
            var ids = holdJobsSelectedIds();
            if (!ids.length) {
                return;
            }
            $('#schedule-job-form')[0].reset();
            markModalBulkContext($('#schedule-job-form'), ids, 'Reschedule');
            $('#schedule-job-form-error').addClass('hidden').text('');
            $('#schedule-job-modal').data({
                'client-id': '',
                'job-id': 0,
                'history-loaded': false
            });
            resetModalTabs('schedule-job-modal');
            $('#schedule-job-modal .job-tab-btn[data-tab="history"]').addClass('hidden');
            openModal('schedule-job-modal');
        });
        @endcan
        @endif
    </script>
    @endpush
</x-layouts.dashboard>
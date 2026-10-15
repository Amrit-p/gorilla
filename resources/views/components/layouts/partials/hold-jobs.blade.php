{{--
    Global "Hold Jobs" slide-over panel. Rendered once by the dashboard layout so the
    panel (and its trigger button in the navbar) is available on every admin/sales page.

    The card actions (open customer details, reschedule, bulk reschedule) reuse the shared
    job modals + action handlers. Those are included here too so the panel is fully
    functional even on pages that don't otherwise load them — @once (inside those partials)
    keeps them from rendering twice on pages that do.
--}}
@can('view-jobs')
    @include('admin.partials.job-modals')

    {{-- Job selection registry powering bulk reschedule (window.crmJobSelection). --}}
    <script src="{{ asset('js/job-bulk-toolbar.js') }}?v={{ @filemtime(public_path('js/job-bulk-toolbar.js')) ?: 1 }}"></script>

    {{-- Shared job-action handlers (reschedule, customer details, etc.). The host page's
         own include — when present — wins the @once race and supplies the real callback. --}}
    @include('admin.partials.job-actions-script', ['filterCallback' => 'crmHoldJobsNoopFilter'])

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
        <div class="shrink-0 border-b border-slate-200 bg-white px-4 py-3">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                    </svg>
                </span>
                <input type="text" id="hold-jobs-search" autocomplete="off"
                    placeholder="Search name, phone, address, date, mower, status..."
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-8 text-xs text-slate-700 placeholder-slate-400 outline-none focus:border-amber-400 focus:bg-white focus:ring-2 focus:ring-amber-100">
                <button type="button" id="hold-jobs-search-clear" class="absolute inset-y-0 right-2 hidden items-center text-slate-400 transition-colors hover:text-slate-600">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
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

    @push('scripts')
    <script>
        // No-op refresh callback used when this page has no job table of its own. The panel
        // itself is reloaded separately via window.reloadHoldJobsPanel after any job action.
        window.crmHoldJobsNoopFilter = window.crmHoldJobsNoopFilter || function () {};

        var holdJobsUrl = @json(route('dashboard.hold-jobs'));

        var holdJobsSearchTimer = null;
        var holdJobsRequest = null;
        var HOLD_JOBS_SEARCH_DEBOUNCE_MS = 300;

        // `silent` keeps the current cards on screen (dimmed) while the search request runs.
        function loadHoldJobs(options) {
            var settings = options || {};
            var search = $.trim($('#hold-jobs-search').val() || '');

            if (holdJobsRequest) {
                holdJobsRequest.abort();
                holdJobsRequest = null;
            }

            $('#dashboard-hold-jobs-error').removeClass('flex').addClass('hidden');

            if (settings.silent) {
                $('#dashboard-hold-jobs-content').addClass('opacity-50');
            } else {
                $('#dashboard-hold-jobs-content').addClass('hidden').empty();
                $('#dashboard-hold-jobs-loader').removeClass('hidden').addClass('flex');
            }

            holdJobsRequest = $.ajax({
                url: holdJobsUrl,
                method: 'GET',
                data: {
                    search: search
                },
                headers: {
                    Accept: 'text/html, */*'
                },
                success: function(html) {
                    var $content = $('#dashboard-hold-jobs-content');
                    $content.html(html).removeClass('hidden opacity-50');
                    var count = $content.find('.hold-job-card').length;
                    $('#dashboard-hold-jobs-subtitle').text(
                        count + ' ' + (count === 1 ? 'job' : 'jobs') + (search ? ' matched' : ' on hold')
                    );
                    if (typeof syncHoldJobsBulkBar === 'function') {
                        syncHoldJobsBulkBar();
                    }
                },
                error: function(xhr, status) {
                    if (status === 'abort') {
                        return;
                    }
                    $('#dashboard-hold-jobs-content').removeClass('opacity-50');
                    $('#dashboard-hold-jobs-error').removeClass('hidden').addClass('flex');
                    $('#dashboard-hold-jobs-subtitle').text('');
                },
                complete: function() {
                    $('#dashboard-hold-jobs-loader').removeClass('flex').addClass('hidden');
                },
            });
        }

        $('#hold-jobs-search').on('input', function() {
            var hasValue = $.trim(this.value || '') !== '';
            $('#hold-jobs-search-clear').toggleClass('hidden', !hasValue).toggleClass('flex', hasValue);

            clearTimeout(holdJobsSearchTimer);
            holdJobsSearchTimer = setTimeout(function() {
                loadHoldJobs({
                    silent: true
                });
            }, HOLD_JOBS_SEARCH_DEBOUNCE_MS);
        });

        $('#hold-jobs-search').on('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                clearTimeout(holdJobsSearchTimer);
                loadHoldJobs({
                    silent: true
                });
            }
        });

        $('#hold-jobs-search-clear').on('click', function() {
            $('#hold-jobs-search').val('').trigger('input').trigger('focus');
        });

        function openHoldJobsPanel() {
            $('#dashboard-hold-jobs-panel').css('right', '0');
            $('#dashboard-hold-jobs-backdrop').removeClass('hidden');
            $('body').css('overflow', 'hidden');
            clearTimeout(holdJobsSearchTimer);
            $('#hold-jobs-search').val('');
            $('#hold-jobs-search-clear').removeClass('flex').addClass('hidden');
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
            if (event.key !== 'Escape') {
                return;
            }

            if ($(event.target).is('#hold-jobs-search') && $.trim($(event.target).val() || '') !== '') {
                $('#hold-jobs-search').val('').trigger('input');
                return;
            }

            closeHoldJobsPanel();
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

            if (typeof window.openCustomerDetailsModal === 'function') {
                window.openCustomerDetailsModal(id);
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
    </script>
    @endpush
@endcan

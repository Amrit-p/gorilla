<x-layouts.dashboard :title="'Salary Calculator'">
    <x-ui.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Salary Calculator'],
    ]" />

    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Salary Calculator</h2>
                <p class="mt-0.5 text-sm text-slate-500">Month-by-month sales, bonus, payout and hours. Open a month to pay out its completed jobs.</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-end gap-4">
                <div class="flex min-w-[14rem] flex-1 flex-col gap-1.5">
                    <label for="salary-mower-select" class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Mower</label>
                    <select id="salary-mower-select"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition-colors focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                        <option value="">Select a mower</option>
                        @foreach ($mowers as $mower)
                            <option value="{{ $mower->id }}" data-mower-name="{{ $mower->name }}">{{ $mower->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex w-40 flex-col gap-1.5">
                    <label for="salary-year-select" class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Year</label>
                    <select id="salary-year-select"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition-colors focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                        @for ($year = $currentYear; $year >= $currentYear - 5; $year--)
                            <option value="{{ $year }}" @selected($year === $currentYear)>{{ $year }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        <div id="salary-months-table-container">
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-16 text-center">
                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                </svg>
                <p class="mt-3 text-sm font-medium text-slate-600">Select a mower</p>
                <p class="mt-0.5 text-xs text-slate-400">Their monthly breakdown will appear here.</p>
            </div>
        </div>
    </div>

    {{-- Payouts-for-a-month modal, opened from the "Payouts" count column. Lists
         each payout with its creator and creation time, and edits/deletes it in
         place. Shared with the mower report. --}}
    <x-salary.payouts-modal :list-url="route('admin.salary-calculator.month-payouts')" />

    {{-- The drill-down embeds the full jobs table, so it needs the same alert
         target, modals and dropdown behaviour the jobs list page wires up. --}}
    @include('admin.partials.job-alert')
    @include('admin.partials.job-modals')
    @include('admin.partials.dropdown-script')

    {{-- Job selection registry powering #job-bulk-toolbar --}}
    <script src="{{ asset('js/job-bulk-toolbar.js') }}?v={{ @filemtime(public_path('js/job-bulk-toolbar.js')) ?: 1 }}"></script>
    @include('admin.partials.job-actions-script', ['filterCallback' => 'reloadSalaryJobsTable'])

    <style>
        #salary-months-table-container { position: relative; min-height: 4rem; }

        #salary-months-loading-overlay {
            position: absolute; inset: 0; z-index: 20;
            background: rgba(255,255,255,.72);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            display: flex; align-items: center; justify-content: center;
            border-radius: 1rem;
        }
        @keyframes salary-months-spin { to { transform: rotate(360deg); } }
        #salary-months-loading-overlay .salary-months-spinner {
            width: 28px; height: 28px;
            border: 2.5px solid #e0e7ff; border-top-color: #4f46e5;
            border-radius: 50%; animation: salary-months-spin .7s linear infinite;
        }
        .salary-month-row.is-open .salary-month-chevron { transform: rotate(90deg); }
        .salary-month-row.is-open { background-color: rgb(238 242 255 / .8); }
    </style>

    <script>
        (function () {
            const monthsUrl = @json(route('admin.salary-calculator.months'));
            const jobsUrl = @json(route('admin.salary-calculator.jobs'));

            // The month whose sub-table is currently expanded, so pagination and
            // post-action reloads know what to re-fetch.
            let openMonth = null;

            function showMonthsLoading() {
                if ($('#salary-months-loading-overlay').length) return;
                $('#salary-months-table-container').append(
                    '<div id="salary-months-loading-overlay">' +
                    '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;">' +
                    '<div class="salary-months-spinner"></div>' +
                    '<span style="font-size:.7rem;font-weight:500;color:#64748b;letter-spacing:.05em;">Loading…</span>' +
                    '</div></div>'
                );
            }

            function collapseAll() {
                if (typeof window.cleanupJobBulkSelection === 'function') {
                    window.cleanupJobBulkSelection();
                }
                if (window.crmJobSelection) { window.crmJobSelection.clear(); }

                $('.salary-jobs-row').addClass('hidden').find('.salary-jobs-slot').empty();
                $('.salary-month-row').removeClass('is-open');
                openMonth = null;
            }

            function loadMonths() {
                const mowerId = $('#salary-mower-select').val();
                const year = $('#salary-year-select').val();

                collapseAll();

                if (!mowerId) {
                    $('#salary-months-table-container').html(
                        '<div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-16 text-center text-sm text-slate-400">Select a mower to view their monthly breakdown.</div>'
                    );
                    return;
                }

                showMonthsLoading();

                $.ajax({
                    url: monthsUrl,
                    method: 'GET',
                    data: { mower_id: mowerId, year: year },
                    success: function (res) {
                        $('#salary-months-table-container').html(res.html);
                    },
                    error: function () {
                        $('#salary-months-loading-overlay').remove();
                    }
                });
            }

            function slotFor(month) {
                return $('.salary-jobs-row[data-month="' + month + '"]');
            }

            function loadJobs(mowerId, year, month, page) {
                const $row = slotFor(month);
                const $slot = $row.find('.salary-jobs-slot');

                openMonth = { mowerId: mowerId, year: year, month: month, page: page || 1 };

                $row.removeClass('hidden');
                $('.salary-month-row').removeClass('is-open');
                $('.salary-month-row[data-month="' + month + '"]').addClass('is-open');
                $slot.html('<p class="py-6 text-center text-sm text-slate-400">Loading jobs…</p>');

                $.ajax({
                    url: jobsUrl,
                    method: 'GET',
                    data: { mower_id: mowerId, year: year, month: month, page: openMonth.page },
                    success: function (res) {
                        $slot.html(res.html);
                    },
                    error: function () {
                        $slot.html('<p class="py-6 text-center text-sm text-red-600">Unable to load jobs for this month.</p>');
                    }
                });
            }

            // Called by job-actions-script after any bulk job action, and after a payout.
            window.reloadSalaryJobsTable = function () {
                if (!openMonth) { return; }
                loadJobs(openMonth.mowerId, openMonth.year, openMonth.month, openMonth.page);
            };

            $(document).on('change', '#salary-mower-select, #salary-year-select', loadMonths);

            $(document).on('click', '.salary-month-cell', function () {
                const btn = $(this);
                const month = Number(btn.data('month'));

                // Clicking the open month closes it.
                if (openMonth && openMonth.month === month) {
                    collapseAll();
                    return;
                }

                collapseAll();
                loadJobs(btn.data('mower-id'), btn.data('year'), month);
            });

            $(document).on('click', '.salary-jobs-close', collapseAll);

            // Keep the shared paginator inside the expanded row instead of navigating.
            $(document).on('click', '.salary-jobs-slot nav a, .salary-jobs-slot .pagination a', function (e) {
                e.preventDefault();
                if (!openMonth) return;

                const href = $(this).attr('href');
                if (!href) return;

                const page = new URL(href, window.location.origin).searchParams.get('page');
                loadJobs(openMonth.mowerId, openMonth.year, openMonth.month, page || 1);
            });

            window.addEventListener('jobs:payout-recorded', function () {
                const reopen = openMonth;
                loadMonths();
                if (reopen) {
                    // Re-open the same month once the refreshed table is in the DOM.
                    setTimeout(function () {
                        loadJobs(reopen.mowerId, reopen.year, reopen.month, reopen.page);
                    }, 350);
                }
            });

            // The payouts modal edits and deletes payouts, which changes the
            // totals and counts this table shows.
            window.addEventListener('payouts:changed', function () {
                loadMonths();
            });
        })();
    </script>
</x-layouts.dashboard>

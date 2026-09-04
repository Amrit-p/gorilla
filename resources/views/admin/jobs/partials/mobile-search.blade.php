<div class="lg:hidden">
    <button type="button" id="mobile-job-search-trigger"
        class="flex w-full items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-left shadow-sm transition-transform active:scale-[0.99]">
        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
        </svg>
        <span class="text-sm text-slate-400">Search jobs by name, phone, address…</span>
    </button>
</div>

<div id="mobile-job-search-modal" class="fixed inset-0 z-[900] hidden flex-col bg-white lg:hidden">
    {{-- Header --}}
    <div class="flex items-center gap-2 border-b border-slate-100 px-3 py-2.5">
        <div class="relative flex-1">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                </svg>
            </span>
            <input type="text" id="mobile-job-search-input" autocomplete="off" inputmode="search"
                placeholder="Name, phone, email, address…"
                class="w-full rounded-full border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-9 text-sm text-slate-700 placeholder-slate-400 outline-none focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100">
            <button type="button" id="mobile-job-search-clear"
                class="absolute inset-y-0 right-2 hidden items-center px-1 text-slate-400 hover:text-slate-600" aria-label="Clear search">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <button type="button" id="mobile-job-search-close" class="shrink-0 px-2 py-1.5 text-sm font-medium text-emerald-700">Cancel</button>
    </div>

    {{-- Results --}}
    <div id="mobile-job-search-results" class="flex-1 overflow-y-auto overscroll-contain bg-slate-50 px-3 py-3"></div>

    @can('create', \App\Models\Job::class)
        <a href="{{ route('admin.jobs.create') }}"
           class="absolute bottom-5 right-5 z-10 inline-flex items-center gap-1.5 rounded-full bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg transition-transform active:scale-95">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            New Job
        </a>
    @endcan
</div>

<script>
    (function () {
        var $trigger = $('#mobile-job-search-trigger');
        var $modal = $('#mobile-job-search-modal');
        var $input = $('#mobile-job-search-input');
        var $clear = $('#mobile-job-search-clear');
        var $results = $('#mobile-job-search-results');
        var searchTimer;
        var lastQuery = null;
        var searchUrl = @json(route('admin.jobs.index'));

        function showLoading() {
            $results.html(
                '<div class="flex items-center justify-center py-16"><div class="jobs-spinner"></div></div>'
            );
        }

        function fetchResults(term) {
            lastQuery = term;
            showLoading();
            $.ajax({
                url: searchUrl,
                method: 'GET',
                data: { search: term, cards_only: 1 },
                dataType: 'json',
                success: function (res) {
                    $results.html(res.html);
                },
                error: function () {
                    $results.html(
                        '<div class="px-4 py-16 text-center text-sm text-rose-500">Failed to load jobs. Please try again.</div>'
                    );
                }
            });
        }

        function openSearch() {
            $modal.removeClass('hidden').addClass('flex');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { $input.trigger('focus'); }, 50);
            if (lastQuery === null) fetchResults('');
        }

        function closeSearch() {
            $modal.addClass('hidden').removeClass('flex');
            document.body.style.overflow = '';
        }

        $trigger.on('click', openSearch);
        $('#mobile-job-search-close').on('click', closeSearch);

        $input.on('input', function () {
            var val = $(this).val();
            $clear.toggleClass('hidden', !val).toggleClass('flex', !!val);
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { fetchResults(val.trim()); }, 350);
        });

        $clear.on('click', function () {
            $input.val('').trigger('focus');
            $clear.addClass('hidden').removeClass('flex');
            clearTimeout(searchTimer);
            fetchResults('');
        });

        // Every job action (delete / reschedule / etc.) calls the page's
        // refreshJobsTable() when it succeeds. Wrap it — once every inline
        // script on the page has run — so a deleted/rescheduled job doesn't
        // linger as a stale card here too. Deferred via $(fn) rather than
        // read at include-time because job-actions-script.blade.php may be
        // included after this partial, depending on the page.
        $(function () {
            var originalRefresh = window.refreshJobsTable;
            if (typeof originalRefresh !== 'function') return;
            window.refreshJobsTable = function () {
                originalRefresh.apply(this, arguments);
                if ($modal.hasClass('flex') && lastQuery !== null) {
                    fetchResults(lastQuery);
                }
            };
        });
    })();
</script>

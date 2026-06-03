<div id="mower-bonus-modal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="mower-bonus-modal-backdrop"></div>
    <div
        class="absolute inset-4 sm:inset-8 md:inset-12 flex flex-col rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/10 overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 shrink-0">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400" id="mower-bonus-modal-subtitle"></p>
                <h3 class="text-base font-semibold text-slate-900">Bonuses</h3>
            </div>
            <div class="flex items-center gap-2">
                @can('manage-employee-bonuses')
                <a id="mower-bonus-add-btn"
                    href="#"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Bonus
                </a>
                @endcan
                <x-ui.export-dropdown excelHref="#"
                    pdfHref="#"
                    excelId="bonus-modal-export-excel"
                    pdfId="bonus-modal-export-pdf"
                    wrapperId="bonus-modal-export-wrap"
                    btnId="bonus-modal-export-btn"
                    menuId="bonus-modal-export-menu" />
                <button type="button" id="mower-bonus-modal-close"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none"
                    aria-label="Close">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        {{-- Body --}}
        <div id="mower-bonus-modal-body" class="flex-1 overflow-auto p-5"></div>
    </div>
</div>

<script>
    (function () {
        const BONUSES_URL    = @json(route('admin.employee-bonuses.index'));
        const EXCEL_BASE_URL = @json(route('admin.employee-bonuses.export.excel'));
        const PDF_BASE_URL   = @json(route('admin.employee-bonuses.export.pdf'));
        const CREATE_URL     = @json(route('admin.employee-bonuses.create'));

        const current = { user_id: null, mowerName: '' };

        function loadingHtml() {
            return '<div class="flex items-center justify-center py-16 text-slate-400">' +
                '<svg class="mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">' +
                '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>' +
                '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>' +
                '</svg>Loading bonuses…</div>';
        }

        function setBody(html) {
            $('#mower-bonus-modal-body').html(html);
        }

        function getFilters() {
            const form = document.getElementById('job-filter-form');
            const dateStart = form ? $(form).find('[name="date_range[start]"]').val() : '';
            const dateEnd   = form ? $(form).find('[name="date_range[end]"]').val()   : '';
            return {
                user_id:              current.user_id,
                'date_range[start]':  dateStart || '',
                'date_range[end]':    dateEnd   || '',
            };
        }

        function buildExportUrl(base) {
            const params = getFilters();
            Object.keys(params).forEach(function (k) {
                if (params[k] === null || params[k] === undefined || params[k] === '') delete params[k];
            });
            const qs = new URLSearchParams(params).toString();
            return base + (qs ? '?' + qs : '');
        }

        function fetchBonuses(url) {
            $.ajax({
                url:     url || BONUSES_URL,
                method:  'GET',
                headers: { Accept: 'application/json' },
                data:    url ? {} : getFilters(),
                success: function (res) {
                    setBody(res && res.html
                        ? res.html
                        : '<p class="py-8 text-center text-slate-500">No bonuses found.</p>');
                },
                error: function () {
                    setBody('<p class="py-8 text-center text-red-600">Failed to load bonuses.</p>');
                },
            });
        }

        function openModal() {
            $('#mower-bonus-modal-subtitle').text(current.mowerName);
            $('#bonus-modal-export-excel').attr('href', buildExportUrl(EXCEL_BASE_URL));
            $('#bonus-modal-export-pdf').attr('href',   buildExportUrl(PDF_BASE_URL));
            $('#mower-bonus-add-btn').attr('href', CREATE_URL + '?user_id=' + current.user_id);
            setBody(loadingHtml());
            $('#mower-bonus-modal').removeClass('hidden');
            $('body').addClass('overflow-hidden');
            fetchBonuses();
        }

        function closeModal() {
            $('#mower-bonus-modal').addClass('hidden');
            $('body').removeClass('overflow-hidden');
        }

        // Bonus cell click
        $(document).on('click', '.mower-bonus-cell', function () {
            const userId = $(this).data('user-id');
            if (!userId) return;
            current.user_id    = userId;
            current.mowerName  = $(this).data('mower-name');
            openModal();
        });

        // Close triggers
        $('#mower-bonus-modal-close, #mower-bonus-modal-backdrop').on('click', closeModal);
        $(document).on('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

        // In-modal pagination
        $(document).on('click', '#mower-bonus-modal-body a[href]', function (e) {
            const href = $(this).attr('href');
            if (!href || !href.includes('page=')) return;
            e.preventDefault();
            setBody(loadingHtml());
            $.ajax({
                url:     href,
                method:  'GET',
                headers: { Accept: 'application/json' },
                success: function (res) { if (res && res.html) setBody(res.html); },
                error:   function () {
                    setBody('<p class="py-8 text-center text-red-600">Failed to load page.</p>');
                },
            });
        });
    })();
</script>

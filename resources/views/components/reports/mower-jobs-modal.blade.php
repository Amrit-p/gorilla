@props(['employees', 'workflowStatuses'])

<div id="mower-jobs-modal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="mower-jobs-modal-backdrop"></div>
    <div
        class="absolute inset-4 sm:inset-8 md:inset-12 flex flex-col rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/10 overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 shrink-0">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400" id="mower-jobs-modal-subtitle"></p>
                <h3 class="text-base font-semibold text-slate-900" id="mower-jobs-modal-title">Jobs</h3>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.export-dropdown excelHref="{{ route('admin.jobs.export.excel') }}"
                    pdfHref="{{ route('admin.jobs.export.pdf') }}" excelId="mower-modal-export-excel"
                    pdfId="mower-modal-export-pdf" wrapperId="mower-modal-export-wrap" btnId="mower-modal-export-btn"
                    menuId="mower-modal-export-menu" />
                <button type="button" id="mower-jobs-modal-close"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none"
                    aria-label="Close">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        {{-- Body --}}
        <div id="mower-jobs-modal-body" class="flex-1 overflow-auto p-5"></div>
    </div>
</div>

@include('admin.partials.job-modals')
@include('admin.partials.dropdown-script')
@include('admin.partials.job-actions-script', [
    'filterCallback' => 'fetchJobs',
])
<script>
    const {fetchJobs} = (function() {
        const JOBS_URL = @json(route('reports.mower.jobs'));
        const EXCEL_BASE_URL = @json(route('admin.jobs.export.excel'));
        const PDF_BASE_URL = @json(route('admin.jobs.export.pdf'));
        const current = {
            done_by_user_id: null,
            mowerName: '',
            label: '',
            status: '',
            payment_status: '',
        };

        function loadingHtml() {
            return '<div class="flex items-center justify-center py-16 text-slate-400">' +
                '<svg class="mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">' +
                '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>' +
                '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>' +
                '</svg>Loading jobs…</div>';
        }

        function setBody(html) {
            $('#mower-jobs-modal-body').html(html);
        }

        function getFormFilters() {
            const form = document.getElementById('job-filter-form');
            if (!form) return {};
            const fd = new FormData(form);
            const obj = Object.fromEntries(fd.entries());

            return {
                ...obj,
                ...current,
            };
        }

        function fetchJobs() {
            $.ajax({
                url: JOBS_URL,
                method: 'GET',
                headers: {
                    Accept: 'application/json'
                },
                data: getFormFilters(),
                success: function(res) {
                    setBody(res && res.html ? res.html :
                        '<p class="py-8 text-center text-slate-500">No jobs found.</p>');
                },
                error: function() {
                    setBody('<p class="py-8 text-center text-red-600">Failed to load jobs.</p>');
                },
            });
        }


        function buildExportUrl(base) {
            const params = getFormFilters();
            // Remove blank/null values so the URL stays clean
            Object.keys(params).forEach(function(k) {
                if (params[k] === null || params[k] === undefined || params[k] === '') delete params[k];
            });
            const qs = new URLSearchParams(params).toString();
            return base + (qs ? '?' + qs : '');
        }

        function openModal() {
            $('#mower-jobs-modal-subtitle').text(current.mowerName);
            $('#mower-jobs-modal-title').text(current.label);
            $('#mower-modal-export-excel').attr('href', buildExportUrl(EXCEL_BASE_URL));
            $('#mower-modal-export-pdf').attr('href', buildExportUrl(PDF_BASE_URL));
            setBody(loadingHtml());
            $('#mower-jobs-modal').removeClass('hidden');
            $('body').addClass('overflow-hidden');
            fetchJobs();
        }

        function closeModal() {
            $('#mower-jobs-modal').addClass('hidden');
            $('body').removeClass('overflow-hidden');
        }

        // Stat cell click
        $(document).on('click', '.mower-stat-cell', function() {
            const userId = $(this).data('user-id');
            const mowerName = $(this).data('mower-name');
            const label = $(this).data('label');
            const status = $(this).data('status');
            const payment_status = $(this).data('payment-status');
            if (!userId) return;

            current.done_by_user_id = userId;
            current.mowerName = mowerName;
            current.label = label;
            current.status = status;
            current.payment_status = payment_status;
            
            openModal();
        });

        // Close triggers
        $('#mower-jobs-modal-close, #mower-jobs-modal-backdrop').on('click', closeModal);
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });

        // In-modal pagination (paginator carries all query params via withQueryString)
        $(document).on('click', '#mower-jobs-modal-body a[href]', function(e) {
            const href = $(this).attr('href');
            if (!href || !href.includes('page=')) return;
            e.preventDefault();
            setBody(loadingHtml());
            $.ajax({
                url: href,
                method: 'GET',
                headers: {
                    Accept: 'application/json'
                },
                success: function(res) {
                    if (res && res.html) setBody(res.html);
                },
                error: function() {
                    setBody(
                        '<p class="py-8 text-center text-red-600">Failed to load page.</p>');
                },
            });
        });

        return {
            fetchJobs,
        }
    })();
</script>

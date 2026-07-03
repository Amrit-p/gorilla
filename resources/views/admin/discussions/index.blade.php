<x-layouts.dashboard title="Discussions">
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Discussions</h2>
                <p class="text-sm text-slate-600">Internal business discussions — admin only.</p>
            </div>
            <a href="{{ route('admin.discussions.create') }}"
               class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                New discussion
            </a>
        </div>

        {{-- Flash --}}
        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <form id="discussion-filter-form" class="flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-white p-4">
            <input id="discussion-search" name="search" type="text" placeholder="Search by title…"
                   value="{{ $filters['search'] ?? '' }}"
                   class="filter w-full rounded-md border border-slate-300 px-3 py-2 text-sm sm:w-64">
            <select id="discussion-category-filter" name="category"
                    class="filter rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All categories</option>
                <option value="worker" @selected(($filters['category'] ?? '') === 'worker')>Worker</option>
                <option value="budget" @selected(($filters['category'] ?? '') === 'budget')>Budget</option>
                <option value="expansion" @selected(($filters['category'] ?? '') === 'expansion')>Expansion</option>
                <option value="crm_update" @selected(($filters['category'] ?? '') === 'crm_update')>CRM Update</option>
                <option value="general" @selected(($filters['category'] ?? '') === 'general')>General</option>
            </select>
        </form>

        {{-- List --}}
        <div id="discussions-list-container" class="relative rounded-2xl border border-slate-200 bg-white">
            @include('admin.discussions.partials.list')
        </div>

    </div>

    <style>
        #discussions-loading-overlay {
            position: absolute;
            inset: 0;
            z-index: 20;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
        }
        #discussions-loading-overlay .discussions-spinner {
            width: 28px;
            height: 28px;
            border: 2.5px solid #e0e7ff;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: discussions-spin 0.7s linear infinite;
        }
        @keyframes discussions-spin {
            to { transform: rotate(360deg); }
        }
    </style>

    {{-- Delete confirmation modal --}}
    <div id="delete-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
            <h3 class="text-base font-semibold text-slate-900">Delete discussion?</h3>
            <p class="mt-1 text-sm text-slate-600">This will permanently remove the discussion and all its files.</p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" id="delete-modal-cancel"
                        class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <form id="delete-modal-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Delete modal
        $(document).on('click', '.delete-discussion-btn', function () {
            $('#delete-modal-form').attr('action', $(this).data('action'));
            $('#delete-modal').removeClass('hidden').addClass('flex');
        });

        $('#delete-modal-cancel').on('click', function () {
            $('#delete-modal').removeClass('flex').addClass('hidden');
        });

        $('#delete-modal').on('click', function (e) {
            if ($(e.target).is('#delete-modal')) {
                $(this).removeClass('flex').addClass('hidden');
            }
        });

        // Server-side search + category filter
        function showDiscussionsLoading() {
            if ($('#discussions-loading-overlay').length) return;
            $('#discussions-list-container').append(
                '<div id="discussions-loading-overlay">' +
                '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;">' +
                '<div class="discussions-spinner"></div>' +
                '<span style="font-size:0.7rem;font-weight:500;color:#64748b;letter-spacing:0.05em;">Loading…</span>' +
                '</div>' +
                '</div>'
            );
        }

        function fetchDiscussions() {
            showDiscussionsLoading();
            $.get('{{ route('admin.discussions.index') }}', $('#discussion-filter-form').serialize(), function (res) {
                $('#discussions-list-container').html(res.html);
            }).fail(function () {
                $('#discussions-loading-overlay').remove();
            });
        }

        let discussionSearchTimer;
        $('#discussion-filter-form').on('input', 'input[type="text"].filter', function () {
            clearTimeout(discussionSearchTimer);
            discussionSearchTimer = setTimeout(fetchDiscussions, 400);
        });
        $('#discussion-filter-form').on('change', 'select.filter', fetchDiscussions);
        $('#discussion-filter-form').on('submit', function (e) { e.preventDefault(); fetchDiscussions(); });
    </script>
    @endpush
</x-layouts.dashboard>

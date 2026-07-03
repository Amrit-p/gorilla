<x-layouts.dashboard title="Follow-ups">
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Follow-ups</h2>
                <p class="text-sm text-slate-600">Track and manage follow-up actions.</p>
            </div>
            <a href="{{ route('admin.followups.create') }}"
               class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                New follow-up
            </a>
        </div>

        {{-- Flash --}}
        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <form id="followup-filter-form" method="GET" action="{{ route('admin.followups.index') }}"
              class="flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-white p-4">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Search outcome…"
                   class="filter w-full rounded-md border border-slate-300 px-3 py-2 text-sm sm:w-64">
            <select name="status" class="filter rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach ($options['statuses'] as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            <select name="followable_type" class="filter rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All types</option>
                @foreach ($options['followableTypes'] as $type)
                    <option value="{{ $type }}" @selected(($filters['followable_type'] ?? '') === $type)>
                        {{ class_basename($type) }}
                    </option>
                @endforeach
            </select>
            <button type="submit"
                    class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Filter
            </button>
            @if (array_filter($filters))
                <a href="{{ route('admin.followups.index') }}"
                   class="rounded-md px-3 py-2 text-sm text-slate-500 hover:text-slate-700">
                    Clear
                </a>
            @endif
        </form>

        {{-- List --}}
        <div id="followups-list-container" class="relative rounded-2xl border border-slate-200 bg-white">
            @include('admin.followups.partials.list')
        </div>

    </div>

    <style>
        #followups-loading-overlay {
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
        #followups-loading-overlay .followups-spinner {
            width: 28px;
            height: 28px;
            border: 2.5px solid #e0e7ff;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: followups-spin 0.7s linear infinite;
        }
        @keyframes followups-spin {
            to { transform: rotate(360deg); }
        }
    </style>

    {{-- Delete confirmation modal --}}
    <div id="delete-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
            <h3 class="text-base font-semibold text-slate-900">Delete follow-up?</h3>
            <p class="mt-1 text-sm text-slate-600">This will permanently remove the follow-up record.</p>
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
        $(document).on('click', '.delete-followup-btn', function () {
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

        // Auto-apply filters
        function showFollowupsLoading() {
            if ($('#followups-loading-overlay').length) return;
            $('#followups-list-container').append(
                '<div id="followups-loading-overlay">' +
                '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;">' +
                '<div class="followups-spinner"></div>' +
                '<span style="font-size:0.7rem;font-weight:500;color:#64748b;letter-spacing:0.05em;">Loading…</span>' +
                '</div>' +
                '</div>'
            );
        }

        function fetchFollowups(url) {
            showFollowupsLoading();
            $.get(url || '{{ route('admin.followups.index') }}', $('#followup-filter-form').serialize(), function (res) {
                $('#followups-list-container').html(res.html);
            }).fail(function () {
                $('#followups-loading-overlay').remove();
            });
        }

        let followupSearchTimer;
        $('#followup-filter-form').on('input', 'input[type="text"].filter', function () {
            clearTimeout(followupSearchTimer);
            followupSearchTimer = setTimeout(function () { fetchFollowups(); }, 400);
        });
        $('#followup-filter-form').on('change', 'select.filter', function () { fetchFollowups(); });
        $('#followup-filter-form').on('submit', function (e) { e.preventDefault(); fetchFollowups(); });

        $(document).on('click', '#followups-list-container .pagination a', function (e) {
            e.preventDefault();
            fetchFollowups($(this).attr('href'));
        });
    </script>
    @endpush
</x-layouts.dashboard>

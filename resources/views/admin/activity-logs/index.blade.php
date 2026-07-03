<x-layouts.dashboard :title="'Activity Logs'">
    <div class="space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Audit Timeline</h2>
            <p class="text-sm text-slate-600">Track actor, action, context, and timestamp across all modules.</p>
        </div>

        <form id="activity-filter-form" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-3 xl:grid-cols-5">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search action/desc/ip..." class="filter rounded-md border border-slate-300 px-3 py-2 text-sm">
            <select name="action" class="filter rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                @endforeach
            </select>
            <select name="user_id" class="filter rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All actors</option>
                @foreach ($actors as $actor)
                    <option value="{{ $actor->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $actor->id)>{{ $actor->name }} ({{ $actor->email }})</option>
                @endforeach
            </select>
            <x-ui.daterange-picker
                name="date_range"
                placeholder="Select date range"
                :startDate="$filters['date_from'] ?? ''"
                :endDate="$filters['date_to'] ?? ''"
                :showRanges="true"
                class="filter"
            />
            <button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm">Apply</button>
        </form>

        <div id="activity-table-container" class="relative">
            @include('admin.activity-logs.partials.table', ['logs' => $logs])
        </div>
    </div>

    <style>
        #activity-loading-overlay {
            position: absolute;
            inset: 0;
            z-index: 20;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }
        #activity-loading-overlay .activity-spinner {
            width: 28px;
            height: 28px;
            border: 2.5px solid #e0e7ff;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: activity-spin 0.7s linear infinite;
        }
        @keyframes activity-spin {
            to { transform: rotate(360deg); }
        }
    </style>

    <script>
        function showActivityLoading() {
            if ($('#activity-loading-overlay').length) return;
            $('#activity-table-container').append(
                '<div id="activity-loading-overlay">' +
                '<div style="display:flex;flex-direction:column;align-items:center;gap:10px;">' +
                '<div class="activity-spinner"></div>' +
                '<span style="font-size:0.7rem;font-weight:500;color:#64748b;letter-spacing:0.05em;">Loading…</span>' +
                '</div>' +
                '</div>'
            );
        }

        function refreshActivityLogs(url = "{{ route('admin.activity-logs.index') }}") {
            showActivityLoading();
            $.get(url, $('#activity-filter-form').serialize(), function (res) {
                $('#activity-table-container').html(res.html);
            }).fail(function () {
                $('#activity-loading-overlay').remove();
            });
        }

        $('#activity-filter-form').on('submit', function (e) {
            e.preventDefault();
            refreshActivityLogs();
        });

        $('#activity-filter-form').on('change', 'select.filter, input[readonly].filter', function () {
            refreshActivityLogs();
        });

        let activitySearchTimer;
        $('#activity-filter-form').on('input', 'input[type="text"].filter', function () {
            clearTimeout(activitySearchTimer);
            activitySearchTimer = setTimeout(function () {
                refreshActivityLogs();
            }, 400);
        });

        $(document).on('click', '#activity-table-container .pagination a', function (e) {
            e.preventDefault();
            refreshActivityLogs($(this).attr('href'));
        });
    </script>
</x-layouts.dashboard>

<x-layouts.dashboard :title="'Activity Logs'">
    <div class="space-y-5">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Audit Timeline</h2>
            <p class="text-sm text-slate-600">Track actor, action, context, and timestamp across all modules.</p>
        </div>

        <form id="activity-filter-form" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-3 xl:grid-cols-6">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search action/desc/ip..." class="rounded-md border border-slate-300 px-3 py-2 text-sm">
            <select name="action" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                @endforeach
            </select>
            <select name="user_id" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">All actors</option>
                @foreach ($actors as $actor)
                    <option value="{{ $actor->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $actor->id)>{{ $actor->name }} ({{ $actor->email }})</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
            <button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm">Apply</button>
        </form>

        <div id="activity-table-container">
            @include('admin.activity-logs.partials.table', ['logs' => $logs])
        </div>
    </div>

    <script>
        function refreshActivityLogs(url = "{{ route('admin.activity-logs.index') }}") {
            $.get(url, $('#activity-filter-form').serialize(), function (res) {
                $('#activity-table-container').html(res.html);
            });
        }

        $('#activity-filter-form').on('submit', function (e) {
            e.preventDefault();
            refreshActivityLogs();
        });

        $(document).on('click', '#activity-table-container .pagination a', function (e) {
            e.preventDefault();
            refreshActivityLogs($(this).attr('href'));
        });
    </script>
</x-layouts.dashboard>

<x-layouts.dashboard :title="'Notifications'">
    <div class="space-y-4">
        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-3">
            <p class="text-sm text-slate-700">Unread notifications: <span class="font-semibold">{{ $unreadCount }}</span></p>
            <button id="mark-all-read" class="rounded-md border border-slate-300 px-3 py-1 text-xs">Mark all as read</button>
        </div>

        <div id="notification-alert" class="hidden"></div>

        <div class="rounded-lg border border-slate-200 bg-white">
            @forelse ($notifications as $notification)
                @php
                    $jobLink = !empty($notification->data['job_id'])
                        ? route('admin.jobs.show', $notification->data['job_id'])
                        : null;
                @endphp
                <div
                    data-notification-id="{{ $notification->id }}"
                    data-unread="{{ is_null($notification->read_at) ? '1' : '0' }}"
                    class="notification-row border-b border-slate-100 p-3 last:border-b-0">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800">{{ $notification->data['message'] ?? 'Notification' }}</p>
                            <div class="mt-1 flex items-center gap-3">
                                <p class="text-xs text-slate-500">{{ $notification->created_at?->diffForHumans() }}</p>
                                @if ($jobLink)
                                    <a href="{{ $jobLink }}" class="text-xs font-medium text-blue-600 hover:underline">View Job &rarr;</a>
                                @endif
                            </div>
                        </div>
                        <span class="notification-badge">
                            @if (is_null($notification->read_at))
                                <x-ui.badge type="warning">Unread</x-ui.badge>
                            @else
                                <x-ui.badge>Read</x-ui.badge>
                            @endif
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-4 text-sm text-slate-500">No notifications yet.</div>
            @endforelse
        </div>

        <div>{{ $notifications->links() }}</div>
    </div>

    <script>
        function showNotificationAlert(message, isError = false) {
            const baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#notification-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }

        $('#mark-all-read').on('click', function () {
            $.ajax({
                url: "{{ route('notifications.read') }}",
                method: 'POST',
                data: { _token: "{{ csrf_token() }}" },
                headers: { 'Accept': 'application/json' },
                success: function (res) {
                    showNotificationAlert(res.message || 'Done');
                    setTimeout(() => window.location.reload(), 300);
                },
                error: function () {
                    showNotificationAlert('Failed to mark notifications as read.', true);
                }
            });
        });

        (function () {
            let pendingIds = [];
            let flushTimer = null;

            function flushPending() {
                if (pendingIds.length === 0) {
                    return;
                }

                const ids = pendingIds;
                pendingIds = [];

                $.ajax({
                    url: "{{ route('notifications.read') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", notification_ids: ids },
                    headers: { 'Accept': 'application/json' }
                });
            }

            function scheduleFlush() {
                clearTimeout(flushTimer);
                flushTimer = setTimeout(flushPending, 800);
            }

            if (!('IntersectionObserver' in window)) {
                return;
            }

            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    const row = entry.target;
                    if (row.dataset.unread !== '1') {
                        observer.unobserve(row);
                        return;
                    }

                    row.dataset.unread = '0';
                    row.querySelector('.notification-badge').innerHTML = '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-slate-100 text-slate-700">Read</span>';
                    pendingIds.push(row.dataset.notificationId);
                    scheduleFlush();
                    observer.unobserve(row);
                });
            }, { threshold: 0.6 });

            document.querySelectorAll('.notification-row').forEach(function (row) {
                observer.observe(row);
            });

            window.addEventListener('beforeunload', flushPending);
        })();
    </script>
</x-layouts.dashboard>

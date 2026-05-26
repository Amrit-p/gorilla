<x-layouts.dashboard :title="'Notifications'">
    <div class="space-y-4">
        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-3">
            <p class="text-sm text-slate-700">Unread notifications: <span class="font-semibold">{{ $unreadCount }}</span></p>
            <button id="mark-all-read" class="rounded-md border border-slate-300 px-3 py-1 text-xs">Mark all as read</button>
        </div>

        <div id="notification-alert" class="hidden"></div>

        <div class="rounded-lg border border-slate-200 bg-white">
            @forelse ($notifications as $notification)
                <div class="border-b border-slate-100 p-3 last:border-b-0">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $notification->data['message'] ?? 'Notification' }}</p>
                            <p class="text-xs text-slate-500">{{ $notification->created_at?->diffForHumans() }}</p>
                        </div>
                        @if (is_null($notification->read_at))
                            <x-ui.badge type="warning">Unread</x-ui.badge>
                        @else
                            <x-ui.badge>Read</x-ui.badge>
                        @endif
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
    </script>
</x-layouts.dashboard>

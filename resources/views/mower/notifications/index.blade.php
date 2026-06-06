<x-layouts.mower :title="'Notifications'" :showBack="true" :backUrl="route('mower.index')">

    {{-- Header summary bar --}}
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-base font-semibold text-slate-800">Notifications</h1>
            @if ($unreadCount > 0)
                <p class="text-xs text-slate-500">{{ $unreadCount }} unread</p>
            @else
                <p class="text-xs text-slate-500">All caught up</p>
            @endif
        </div>
        @if ($unreadCount > 0)
            <button id="mark-all-read"
                class="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 active:bg-emerald-100">
                Mark all read
            </button>
        @endif
    </div>

    {{-- Flash alert --}}
    <div id="notif-alert" class="mb-3 hidden rounded-lg px-3 py-2 text-sm"></div>

    @if ($notifications->isEmpty())
        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-16 text-center">
            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
                <svg class="h-7 w-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">No notifications yet</p>
            <p class="mt-1 text-xs text-slate-400">You'll see job assignments here</p>
        </div>
    @else
        {{-- Date-grouped notification list --}}
        <div class="space-y-5">
            @foreach ($grouped as $dateString => $items)
                @php
                    $date = \Carbon\Carbon::parse($dateString);
                    if ($date->isToday()) {
                        $label = 'Today';
                    } elseif ($date->isYesterday()) {
                        $label = 'Yesterday';
                    } else {
                        $label = $date->format('d F, Y');
                    }
                @endphp

                <div>
                    {{-- Date label --}}
                    <div class="mb-2 flex items-center gap-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</span>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>

                    {{-- Notifications for this date --}}
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @foreach ($items as $i => $notification)
                            @php
                                $isUnread = is_null($notification->read_at);
                                $isLast = $loop->last;

                                $link = null;
                                if (!empty($notification->data['job_id'])) {
                                    $link = route('mower.jobs.show', $notification->data['job_id']);
                                }
                            @endphp

                            <div
                                data-notification-id="{{ $notification->id }}"
                                data-link="{{ $link }}"
                                onclick="handleNotificationTap(this)"
                                class="notification-item relative flex cursor-pointer items-start gap-3 px-4 py-3.5 transition-colors active:bg-slate-50 {{ $isLast ? '' : 'border-b border-slate-100' }} {{ $isUnread ? 'bg-emerald-50/60' : 'bg-white' }}">

                                {{-- Unread indicator --}}
                                <div class="mt-1.5 shrink-0">
                                    @if ($isUnread)
                                        <span class="block h-2 w-2 rounded-full bg-emerald-500"></span>
                                    @else
                                        <span class="block h-2 w-2 rounded-full bg-slate-200"></span>
                                    @endif
                                </div>

                                {{-- Content --}}
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm leading-snug {{ $isUnread ? 'font-semibold text-slate-800' : 'font-normal text-slate-600' }}">
                                        {{ $notification->data['message'] ?? 'Notification' }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-400">
                                        {{ $notification->created_at->format('g:i A') }}
                                    </p>
                                </div>

                                {{-- Chevron if there's a link --}}
                                @if ($link)
                                    <div class="mt-1 shrink-0 text-slate-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($notifications->hasPages())
            <div class="mt-6 flex items-center justify-between gap-2">
                @if ($notifications->onFirstPage())
                    <span class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs text-slate-300">Previous</span>
                @else
                    <a href="{{ $notifications->previousPageUrl() }}"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-medium text-slate-600 active:bg-slate-50">
                        Previous
                    </a>
                @endif

                <span class="text-xs text-slate-400">Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</span>

                @if ($notifications->hasMorePages())
                    <a href="{{ $notifications->nextPageUrl() }}"
                        class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-xs font-medium text-emerald-700 active:bg-emerald-100">
                        Next
                    </a>
                @else
                    <span class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs text-slate-300">Next</span>
                @endif
            </div>
        @endif
    @endif

    @push('scripts')
    <script>
        function showAlert(msg, isError) {
            var el = document.getElementById('notif-alert');
            el.className = isError
                ? 'mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            el.textContent = msg;
            el.classList.remove('hidden');
        }

        function handleNotificationTap(el) {
            var id = el.dataset.notificationId;
            var link = el.dataset.link;

            // Fire-and-forget mark as read, then navigate
            $.ajax({
                url: '{{ route("notifications.read") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', notification_ids: [id] },
                headers: { 'Accept': 'application/json' }
            });

            if (link && link !== 'null' && link !== '') {
                window.location.href = link;
            }
        }

        var markAllBtn = document.getElementById('mark-all-read');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function () {
                markAllBtn.disabled = true;
                markAllBtn.textContent = 'Marking…';
                $.ajax({
                    url: '{{ route("notifications.read") }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    headers: { 'Accept': 'application/json' },
                    success: function (res) {
                        showAlert(res.message || 'All marked as read.');
                        setTimeout(function () { window.location.reload(); }, 400);
                    },
                    error: function () {
                        showAlert('Could not mark notifications as read.', true);
                        markAllBtn.disabled = false;
                        markAllBtn.textContent = 'Mark all read';
                    }
                });
            });
        }
    </script>
    @endpush

</x-layouts.mower>

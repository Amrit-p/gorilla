@php
    $dashboardType = $analytics['type'] ?? 'admin';
@endphp

<x-layouts.dashboard :title="'Dashboard'" subtitle="Analytics and quick access to your CRM modules">
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600">
                Welcome back, <span class="font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                • {{ now()->format('d M Y') }}
                @if ($dashboardType === 'admin')
                    <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Admin</span>
                @elseif ($dashboardType === 'sales')
                    <span class="ml-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800">Sales</span>
                @else
                    <span class="ml-1 rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-800">Mower</span>
                @endif
            </p>
            <div class="flex items-center gap-2">
                @if ($dashboardType === 'mower')
                    <a href="{{ route('mower.index') }}" class="rounded-md bg-emerald-700 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-800">Open field dashboard</a>
                @endif
                <button id="open-preferences-modal" type="button" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                    Preferences
                </button>
            </div>
        </div>

        <div id="dashboard-alert" class="hidden"></div>

        @include('dashboard.partials.quick-actions')

        @if ($dashboardType === 'admin')
            @include('dashboard.partials.admin-analytics', ['analytics' => $analytics])
            @include('dashboard.partials.analytics-tabs')
            @include('dashboard.partials.admin-three-week-schedule', ['threeWeekSchedule' => $threeWeekSchedule])
        @elseif ($dashboardType === 'sales')
            @include('dashboard.partials.sales-analytics', ['analytics' => $analytics])
        @else
            @include('dashboard.partials.mower-analytics', ['analytics' => $analytics])
        @endif

        @if ($dashboardType !== 'sales')
            <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
                <div class="rounded-lg border border-slate-200 bg-white shadow-sm xl:col-span-2">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Today's schedule</h2>
                            <p class="text-xs text-slate-500">{{ now()->format('l, d M Y') }}</p>
                        </div>
                        @can('view-jobs')
                            <a href="{{ route('admin.jobs.index') }}" class="text-xs font-medium text-emerald-700 hover:text-emerald-800">View all jobs →</a>
                        @endcan
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Customer</th>
                                    <th class="px-4 py-3 font-semibold">Address</th>
                                    <th class="px-4 py-3 font-semibold">Time</th>
                                    <th class="px-4 py-3 font-semibold">Payment</th>
                                    <th class="px-4 py-3 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($todaysJobs as $job)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-medium text-slate-800">
                                            @can('view-jobs')
                                                <a href="{{ route('admin.jobs.show', $job) }}" class="hover:text-emerald-700">{{ $job->client?->name ?? '—' }}</a>
                                            @else
                                                {{ $job->client?->name ?? '—' }}
                                            @endcan
                                        </td>
                                        <td class="max-w-[200px] truncate px-4 py-3 text-slate-600" title="{{ $job->client_address }}">{{ $job->client_address ?: '—' }}</td>
                                        <td class="px-4 py-3 text-slate-600">
                                            @if ($job->scheduled_time)
                                                {{ \Illuminate\Support\Carbon::parse($job->scheduled_time)->format('g:i A') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ $job->payment_status ?: '—' }}</td>
                                        <td class="px-4 py-3">
                                            <x-jobs.status-badge :status="$job->status" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">No jobs scheduled for today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($dashboardType === 'admin')
                    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <h2 class="text-base font-semibold text-slate-900">Lead pipeline</h2>
                        <p class="mt-1 text-xs text-slate-500">Counts by current status</p>
                        <ul class="mt-4 space-y-2">
                            @forelse ($leadsByStatus as $status => $count)
                                <li class="flex items-center justify-between rounded-md border border-slate-100 px-3 py-2 text-sm">
                                    <span class="text-slate-700">{{ $status }}</span>
                                    <span class="font-semibold text-slate-900">{{ $count }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500">No leads yet.</li>
                            @endforelse
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        @if ($preferences->show_activity_timeline && $dashboardType === 'admin')
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-base font-semibold text-slate-900">Recent activity</h2>
                <div class="space-y-2">
                    @forelse ($activityLogs as $activity)
                        <div class="rounded-md border border-slate-200 p-2">
                            <p class="text-sm font-medium text-slate-800">{{ $activity->action }}</p>
                            @if ($activity->description)
                                <p class="mt-0.5 text-xs text-slate-600">{{ $activity->description }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-500">{{ $activity->user?->name ?? 'System' }} • {{ $activity->created_at?->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No recent activity yet.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    <x-ui.modal id="dashboard-preferences-modal" title="Dashboard Preferences">
        <form id="dashboard-preferences-form" action="{{ route('dashboard.preferences.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Theme</label>
                <select name="theme" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="light" @selected($preferences->theme === 'light')>Light</option>
                    <option value="dark" @selected($preferences->theme === 'dark')>Dark</option>
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="compact_cards" value="1" @checked($preferences->compact_cards)>
                Compact statistic cards
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="show_activity_timeline" value="1" @checked($preferences->show_activity_timeline)>
                Show activity timeline panel
            </label>
            <x-ui.button type="submit">Save Preferences</x-ui.button>
        </form>
    </x-ui.modal>

    @push('scripts')
        <script>
            function showDashboardAlert(message, isError = false) {
                const baseClass = isError
                    ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                    : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                $('#dashboard-alert').removeClass('hidden').attr('class', baseClass).text(message);
            }

            $('#open-preferences-modal').on('click', function () {
                $('#dashboard-preferences-modal').removeClass('hidden').addClass('flex');
            });

            $('[data-close-modal="dashboard-preferences-modal"]').on('click', function () {
                $('#dashboard-preferences-modal').addClass('hidden').removeClass('flex');
            });

            $('#dashboard-preferences-form').on('submit', function (event) {
                event.preventDefault();
                const $form = $(this);
                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: {
                        _token: $form.find('input[name="_token"]').val(),
                        _method: 'PATCH',
                        theme: $form.find('select[name="theme"]').val(),
                        compact_cards: $form.find('input[name="compact_cards"]').is(':checked') ? 1 : 0,
                        show_activity_timeline: $form.find('input[name="show_activity_timeline"]').is(':checked') ? 1 : 0,
                    },
                    headers: { Accept: 'application/json' },
                    success: function (response) {
                        showDashboardAlert(response.message || 'Dashboard preferences saved.');
                        $('#dashboard-preferences-modal').addClass('hidden').removeClass('flex');
                        setTimeout(() => window.location.reload(), 400);
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        showDashboardAlert(Object.values(errors)[0]?.[0] || 'Unable to save preferences.', true);
                    },
                });
            });

            if (typeof window.crmInitDashboardCharts === 'function') {
                window.crmInitDashboardCharts();
            }
        </script>
    @endpush
</x-layouts.dashboard>

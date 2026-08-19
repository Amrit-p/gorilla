<div id="three-week-grid" class="space-y-4">
    @forelse ($weeks as $week)
        @php
            $weekTotal = (int) ($week['total_jobs'] ?? array_sum(array_column($week['days'], 'total')));
            $weekPending = (int) ($week['pending_jobs'] ?? array_sum(array_column($week['days'], 'pending')));
            $weekPendingPayment = (int) ($week['pending_payment_jobs'] ?? array_sum(array_column($week['days'], 'pending_payment')));
            $weekLeadTotal = (int) ($week['lead_total'] ?? array_sum(array_column($week['days'], 'lead_total')));
            $weekFollowUpTotal = (int) ($week['follow_up_total'] ?? array_sum(array_column($week['days'], 'follow_up_total')));
        @endphp
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            {{-- Week header --}}
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/80 px-5 py-3">
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-semibold text-slate-800">{{ $week['label'] }}</h3>
                    <span class="text-xs text-slate-400">{{ $week['start_date'] }} – {{ $week['end_date'] }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($weekTotal > 0)
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                            Jobs {{ $weekTotal }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Pending Jobs {{ $weekPending }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                            Unpaid {{ $weekPendingPayment }}
                        </span>
                    @else
                        <span class="text-xs text-slate-400">No jobs this week</span>
                    @endif
                    @if ($weekLeadTotal > 0)
                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-800">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                            Leads {{ $weekLeadTotal }}
                        </span>
                    @endif
                    @if ($weekFollowUpTotal > 0)
                        <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-800">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
                            Follow-ups {{ $weekFollowUpTotal }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Day columns --}}
            <div class="overflow-x-auto">
                <div class="grid min-w-[820px] grid-cols-7 divide-x divide-slate-100">
                    @foreach ($week['days'] as $day)
                        @php
                            $isToday = $day['is_today'];
                            $isPast  = $day['is_past'];
                            $hasJobs = $day['total'] > 0;
                            $hasLeads = $day['lead_total'] > 0;
                            $hasFollowUps = ($day['follow_up_total'] ?? 0) > 0;
                            $hasAny = $hasJobs || $hasLeads || $hasFollowUps;
                        @endphp
                        <div class="flex min-h-[220px] flex-col {{ $isToday ? 'bg-emerald-50/80' : ($isPast ? 'bg-slate-50/40' : 'bg-white') }}">

                            {{-- Day header --}}
                            <div class="border-b {{ $isToday ? 'border-emerald-200' : 'border-slate-100' }} px-3 py-2.5">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-[11px] font-bold uppercase tracking-wide {{ $isToday ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ substr($day['day_name'], 0, 3) }}
                                    </span>
                                    @if ($isToday)
                                        <span class="rounded-md bg-emerald-600 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white">Today</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-[11px] {{ $isToday ? 'font-semibold text-emerald-600' : 'text-slate-400' }}">
                                    {{ $day['date_label'] }}
                                </p>
                            </div>

                            {{-- Day body --}}
                            <div class="flex flex-1 flex-col gap-2.5 p-3">
                                @if ($hasJobs)
                                    <button
                                        type="button"
                                        class="group space-y-2 rounded-xl text-left transition"
                                        data-date="{{ $day['date'] }}"
                                        data-label="{{ $day['day_name'] }}, {{ $day['date_label'] }}"
                                        onclick="crmOpenDayPanel(this)"
                                    >
                                        {{-- Jobs --}}
                                        <div class="flex items-center gap-2 rounded-lg px-1 py-1.5 group-hover:bg-emerald-50/80">
                                            <svg class="h-4 w-4 shrink-0 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                                            <span class="flex-1 text-xs font-semibold text-slate-600">Jobs</span>
                                            <span class="text-base font-bold tabular-nums text-slate-900">{{ $day['total'] }}</span>
                                        </div>

                                        @if (($day['pending'] ?? 0) > 0 || ($day['pending_payment'] ?? 0) > 0)
                                            <div class="flex flex-wrap gap-1.5 pl-0.5">
                                                @if (($day['pending'] ?? 0) > 0)
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-800" title="Pending Jobs">
                                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                        Pending Jobs {{ $day['pending'] }}
                                                    </span>
                                                @endif
                                                @if (($day['pending_payment'] ?? 0) > 0)
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-rose-700" title="Unpaid">
                                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                                                        Unpaid {{ $day['pending_payment'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </button>

                                    @if ($day['zone_count'] > 0)
                                        <div class="space-y-1.5">
                                            @foreach ($day['zones'] as $zoneName => $zoneCount)
                                                <div class="flex items-center gap-2 px-1 py-1">
                                                    <svg class="h-3.5 w-3.5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                                    <span class="min-w-0 flex-1 truncate text-[11px] font-semibold text-emerald-800" title="{{ $zoneName }}">{{ $zoneName }}</span>
                                                    <span class="shrink-0 text-xs font-bold tabular-nums text-emerald-700">{{ $zoneCount }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif

                                @if ($hasLeads)
                                    <button
                                        type="button"
                                        class="group flex w-full items-center gap-2 rounded-lg px-1 py-1.5 text-left transition hover:bg-indigo-50"
                                        data-date="{{ $day['date'] }}"
                                        data-label="{{ $day['day_name'] }}, {{ $day['date_label'] }}"
                                        onclick="crmOpenLeadPanel(this)"
                                    >
                                        <svg class="h-4 w-4 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                        <span class="flex-1 text-xs font-semibold text-indigo-700">Leads</span>
                                        <span class="text-base font-bold tabular-nums text-indigo-900">{{ $day['lead_total'] }}</span>
                                    </button>
                                @endif

                                @if ($hasFollowUps)
                                    <div class="space-y-1.5">
                                        @can(App\Support\CrmPermissions::MANAGE_FOLLOWUPS)
                                            <a
                                                href="{{ route('admin.followups.index', ['next_followup_from' => $day['date'], 'next_followup_to' => $day['date'], 'status' => 'pending']) }}"
                                                class="group flex items-center gap-2 rounded-lg px-1 py-1.5 transition hover:bg-orange-50"
                                            >
                                                <svg class="h-4 w-4 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
                                                <span class="flex-1 text-xs font-semibold text-orange-800">Follow-ups</span>
                                                <span class="text-base font-bold tabular-nums text-orange-900">{{ $day['follow_up_total'] }}</span>
                                            </a>
                                        @else
                                            <div class="flex items-center gap-2 px-1 py-1.5">
                                                <svg class="h-4 w-4 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
                                                <span class="flex-1 text-xs font-semibold text-orange-800">Follow-ups</span>
                                                <span class="text-base font-bold tabular-nums text-orange-900">{{ $day['follow_up_total'] }}</span>
                                            </div>
                                        @endcan

                                        @foreach ($day['follow_up_types'] ?? [] as $followUpType)
                                            @can(App\Support\CrmPermissions::MANAGE_FOLLOWUPS)
                                                <a
                                                    href="{{ route('admin.followups.index', ['next_followup_from' => $day['date'], 'next_followup_to' => $day['date'], 'status' => 'pending', 'followable_type' => $followUpType['type']]) }}"
                                                    class="flex items-center gap-2 rounded-lg px-1 py-1 transition hover:bg-orange-50"
                                                >
                                                    <svg class="h-3.5 w-3.5 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h10.5a.75.75 0 0 1 .75.75v15.75a.75.75 0 0 1-1.28.53L12 15.75l-4.72 4.53A.75.75 0 0 1 6 20.25V4.5a.75.75 0 0 1 .75-.75Z"/></svg>
                                                    <span class="min-w-0 flex-1 truncate text-[11px] font-semibold text-orange-800" title="{{ $followUpType['label'] }}">{{ $followUpType['label'] }}</span>
                                                    <span class="shrink-0 text-xs font-bold tabular-nums text-orange-700">{{ $followUpType['count'] }}</span>
                                                </a>
                                            @else
                                                <div class="flex items-center gap-2 px-1 py-1">
                                                    <svg class="h-3.5 w-3.5 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h10.5a.75.75 0 0 1 .75.75v15.75a.75.75 0 0 1-1.28.53L12 15.75l-4.72 4.53A.75.75 0 0 1 6 20.25V4.5a.75.75 0 0 1 .75-.75Z"/></svg>
                                                    <span class="min-w-0 flex-1 truncate text-[11px] font-semibold text-orange-800" title="{{ $followUpType['label'] }}">{{ $followUpType['label'] }}</span>
                                                    <span class="shrink-0 text-xs font-bold tabular-nums text-orange-700">{{ $followUpType['count'] }}</span>
                                                </div>
                                            @endcan
                                        @endforeach
                                    </div>
                                @endif

                                @unless ($hasAny)
                                    <div class="flex flex-1 items-center justify-center">
                                        <span class="text-xs text-slate-300">—</span>
                                    </div>
                                @endunless
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-slate-200 bg-white px-6 py-10 text-center shadow-sm">
            <p class="text-sm text-slate-500">No schedule data available.</p>
        </div>
    @endforelse
</div>

<div id="three-week-grid" class="space-y-4">
    @forelse ($weeks as $week)
        @php
            $weekTotal = array_sum(array_column($week['days'], 'total'));
        @endphp
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            {{-- Week header --}}
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-3">
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-semibold text-slate-800">{{ $week['label'] }}</h3>
                    <span class="text-xs text-slate-400">{{ $week['start_date'] }} – {{ $week['end_date'] }}</span>
                </div>
                @if ($weekTotal > 0)
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                        {{ $weekTotal }} {{ $weekTotal === 1 ? 'job' : 'jobs' }}
                    </span>
                @else
                    <span class="text-xs text-slate-400">No jobs this week</span>
                @endif
            </div>

            {{-- Day columns --}}
            <div class="overflow-x-auto">
                <div class="grid min-w-[700px] grid-cols-7 divide-x divide-slate-100">
                    @foreach ($week['days'] as $day)
                        @php
                            $isToday = $day['is_today'];
                            $isPast  = $day['is_past'];
                            $hasJobs = $day['total'] > 0;
                        @endphp
                        <div class="flex min-h-[140px] flex-col {{ $isToday ? 'bg-emerald-50' : ($isPast ? 'bg-slate-50/50' : 'bg-white') }}">

                            {{-- Day header --}}
                            <div class="border-b {{ $isToday ? 'border-emerald-200' : 'border-slate-100' }} px-3 py-2">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-[11px] font-bold uppercase tracking-wide {{ $isToday ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ substr($day['day_name'], 0, 3) }}
                                    </span>
                                    @if ($isToday)
                                        <span class="rounded bg-emerald-600 px-1 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white">Today</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-[11px] {{ $isToday ? 'font-semibold text-emerald-600' : 'text-slate-400' }}">
                                    {{ $day['date_label'] }}
                                </p>
                            </div>

                            {{-- Day body --}}
                            <div class="flex flex-1 flex-col p-2.5">
                                @if ($hasJobs)
                                    <button
                                        type="button"
                                        class="group mb-2 flex items-baseline gap-1 text-left"
                                        data-date="{{ $day['date'] }}"
                                        data-label="{{ $day['day_name'] }}, {{ $day['date_label'] }}"
                                        onclick="crmOpenDayPanel(this)"
                                    >
                                        <span class="text-xl font-extrabold leading-none {{ $isToday ? 'text-emerald-700' : 'text-slate-800' }} transition-colors group-hover:text-emerald-600">
                                            {{ $day['total'] }}
                                        </span>
                                        <span class="text-[10px] {{ $isToday ? 'text-emerald-500' : 'text-slate-400' }} transition-colors group-hover:text-emerald-500">
                                            {{ $day['total'] === 1 ? 'job' : 'jobs' }}
                                        </span>
                                    </button>

                                    @if ($day['zone_count'] > 0)
                                        <div class="space-y-1">
                                            @foreach ($day['zones'] as $zoneName => $zoneCount)
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="truncate text-[10px] text-slate-500" title="{{ $zoneName }}">{{ $zoneName }}</span>
                                                    <span class="flex-shrink-0 rounded bg-slate-100 px-1 text-[10px] font-semibold text-slate-600">{{ $zoneCount }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <div class="flex flex-1 items-center justify-center">
                                        <span class="text-xs text-slate-300">—</span>
                                    </div>
                                @endif
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

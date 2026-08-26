<x-layouts.mower :title="'My Jobs'">
    <div class="space-y-4">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @php $cards = $analytics['cards'] ?? []; @endphp
        <div class="grid grid-cols-3 gap-2">
            <div class="rounded-xl bg-white p-3 text-center shadow-sm">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Completed</p>
                <p id="mower-analytics-completed" class="mt-1 text-xl font-bold text-emerald-800">{{ $cards['range_jobs']['value'] ?? '0' }}</p>
            </div>
            <div class="rounded-xl bg-white p-3 text-center shadow-sm">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Hours</p>
                <p id="mower-analytics-hours" class="mt-1 text-xl font-bold text-sky-800">{{ $cards['completed_hours']['value'] ?? '0h' }}</p>
            </div>
            <div class="rounded-xl bg-white p-3 text-center shadow-sm">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Total Upcoming</p>
                <p id="mower-analytics-upcoming" class="mt-1 text-xl font-bold text-amber-800">{{ $cards['upcoming_jobs']['value'] ?? '0' }}</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Assigned to you</p>
            <x-ui.daterange-picker
                id="mower-schedule-date"
                :startDate="$scheduleStart"
                :endDate="$scheduleEnd"
                placeholder="{{ __('Schedule date') }}"
                style="--drp-primary:#059669; --drp-primary-700:#047857; --drp-primary-50:#ecfdf5; --drp-primary-100:#d1fae8; --drp-font-color:#064e3b; --drp-border:#d1fae8; --drp-footer-bg:#ecfdf5; --drp-hover-bg:#ecfdf5; --drp-muted:#065f46; --drp-muted-2:#6ee7b7;"
            />
        </div>

        <div id="mower-alert" class="hidden rounded-xl px-4 py-3 text-sm" style="position:fixed;top:1rem;left:50%;transform:translateX(-50%);z-index:9999;min-width:280px;max-width:90vw;"></div>

        <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4">
            @foreach ($listScopes as $key => $label)
                @if ($key === \App\Support\CrmConstants::MOWER_SCOPE_PENDING)
                    @continue
                @endif
                <button
                    type="button"
                    data-scope="{{ $key }}"
                    class="mower-scope mower-touch shrink-0 whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium {{ $scope === $key ? 'bg-emerald-700 text-white' : 'bg-white text-slate-600 shadow-sm' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4">
            @can('view-jobs')
                <a
                    href="{{ route('mower.map') }}"
                    class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full bg-sky-700 px-3 py-2 text-xs font-semibold text-white shadow-sm active:bg-sky-800"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Map View
                </a>
            @endcan
            @can('view-mower-report')
                <a
                    href="{{ route('reports.mower.index') }}"
                    class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full bg-indigo-700 px-3 py-2 text-xs font-semibold text-white shadow-sm active:bg-indigo-800"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V9m4 8V5m4 12v-6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Report
                </a>
            @endcan
            <a
                id="mower-export-pdf"
                href="{{ route('mower.export-pdf', ['scope' => $scope, 'date_range[start]' => $scheduleStart, 'date_range[end]' => $scheduleEnd]) }}"
                class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full bg-slate-800 px-3 py-2 text-xs font-semibold text-white shadow-sm active:bg-slate-900"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                Export PDF
            </a>
        </div>

        <div id="mower-job-list">
            @include('mower.partials.job-list', ['jobs' => $jobs, 'scope' => $scope])
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/mower-dashboard.js') }}?v={{ filemtime(public_path('js/mower-dashboard.js')) }}"></script>
        <script>
            window.mowerRoutes = {
                index: @json(route('mower.index')),
                exportPdf: @json(route('mower.export-pdf')),
            };
            window.mowerInitialDate = @json($scheduleStart);
            window.mowerInitialEndDate = @json($scheduleEnd);
            window.mowerInitialScope = @json($scope);
        </script>
    @endpush
</x-layouts.mower>

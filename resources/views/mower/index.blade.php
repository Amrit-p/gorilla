<x-layouts.mower :title="'My Jobs'">
    <div class="space-y-4">
        @php $cards = $analytics['cards'] ?? []; @endphp
        <div class="grid grid-cols-3 gap-2">
            <div class="rounded-xl bg-white p-3 text-center shadow-sm">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Completed</p>
                <p id="mower-analytics-completed" class="mt-1 text-xl font-bold text-emerald-800">{{ $cards['todays_jobs']['value'] ?? '0' }}</p>
            </div>
            <div class="rounded-xl bg-white p-3 text-center shadow-sm">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Hours</p>
                <p id="mower-analytics-hours" class="mt-1 text-xl font-bold text-sky-800">{{ $cards['completed_hours']['value'] ?? '0h' }}</p>
            </div>
            <div class="rounded-xl bg-white p-3 text-center shadow-sm">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Total Pending</p>
                <p id="mower-analytics-pending" class="mt-1 text-xl font-bold text-amber-800">{{ $cards['pending_jobs']['value'] ?? '0' }}</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Assigned to you</p>
            <input
                type="date"
                id="mower-schedule-date"
                value="{{ $scheduleDate }}"
                class="mt-1 w-full rounded-lg border-0 bg-transparent p-0 text-lg font-semibold text-slate-900 focus:ring-0"
            >
        </div>

        <div id="mower-alert" class="hidden rounded-xl px-4 py-3 text-sm" style="position:fixed;top:1rem;left:50%;transform:translateX(-50%);z-index:9999;min-width:280px;max-width:90vw;"></div>

        <div class="grid grid-cols-3 gap-2">
            @foreach ($listScopes as $key => $label)
                <button
                    type="button"
                    data-scope="{{ $key }}"
                    class="mower-scope mower-touch rounded-full px-2 py-2 text-sm font-medium text-center {{ $scope === $key ? 'bg-emerald-700 text-white' : 'bg-white text-slate-600 shadow-sm' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="flex justify-end">
            <a
                id="mower-export-pdf"
                href="{{ route('mower.export-pdf', ['scope' => $scope, 'schedule_date' => $scheduleDate]) }}"
                target="_blank"
                class="inline-flex items-center gap-1.5 rounded-full bg-slate-800 px-4 py-2 text-xs font-semibold text-white shadow-sm active:bg-slate-900"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
            window.mowerInitialDate = @json($scheduleDate);
        </script>
    @endpush
</x-layouts.mower>

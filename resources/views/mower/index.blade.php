<x-layouts.mower :title="'My Jobs'">
    <div class="space-y-4">
        @php $cards = $analytics['cards'] ?? []; @endphp
        <div class="grid grid-cols-3 gap-2">
            <div class="rounded-xl bg-white p-3 text-center shadow-sm">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Today</p>
                <p class="mt-1 text-xl font-bold text-emerald-800">{{ $cards['todays_jobs']['value'] ?? '0' }}</p>
            </div>
            <div class="rounded-xl bg-white p-3 text-center shadow-sm">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Hours</p>
                <p class="mt-1 text-xl font-bold text-sky-800">{{ $cards['completed_hours']['value'] ?? '0h' }}</p>
            </div>
            <div class="rounded-xl bg-white p-3 text-center shadow-sm">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Pending</p>
                <p class="mt-1 text-xl font-bold text-amber-800">{{ $cards['pending_jobs']['value'] ?? '0' }}</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Assigned to you</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ now()->format('l, M j') }}</p>
        </div>

        <div id="mower-alert" class="hidden rounded-xl px-4 py-3 text-sm"></div>

        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach (['today' => 'Today', 'upcoming' => 'Upcoming', 'completed' => 'Done', 'hold' => 'Hold'] as $key => $label)
                <button
                    type="button"
                    data-scope="{{ $key }}"
                    class="mower-scope mower-touch shrink-0 rounded-full px-4 py-2 text-sm font-medium {{ $scope === $key ? 'bg-emerald-700 text-white' : 'bg-white text-slate-600 shadow-sm' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div id="mower-job-list">
            @include('mower.partials.job-list', ['jobs' => $jobs, 'scope' => $scope])
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/mower-dashboard.js') }}"></script>
        <script>
            window.mowerRoutes = { index: @json(route('mower.index')) };
        </script>
    @endpush
</x-layouts.mower>

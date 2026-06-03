<x-layouts.dashboard :title="'Map & Routing'">

    <div class="grid grid-cols-6 overflow-hidden rounded-xl border border-slate-200" style="height: calc(100vh - 7rem);">

        {{-- Left: Full map --}}
        <div class="relative col-span-6" id="jobs-map-wrap">
            <div id="jobs-map" class="h-full w-full bg-slate-100"></div>

            {{-- Alert overlay --}}
            <div id="map-alert" class="absolute bottom-3 left-1/2 z-[1000] hidden -translate-x-1/2"></div>

            {{-- Filter toggle button --}}
            <button id="map-filter-toggle-btn"
                class="absolute right-3 top-3 z-[1000] flex items-center gap-1.5 rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 shadow-sm hover:bg-slate-50">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
                <span id="map-filter-toggle-label">Show Filters</span>
            </button>
        </div>

        {{-- Right: Filters panel (extracted partial) --}}
        @include('admin.maps.partials.filter-panel')

    </div>

    <script src="{{ asset('js/map-job-popup.js') }}?v={{ @filemtime(public_path('js/map-job-popup.js')) ?: 1 }}"></script>
    <script>
        window.crmJobsMapConfig = @json(array_merge($mapConfig, [
            'canManageJobs' => auth()->user()->can('manage-job-records'),
        ]));
        window.crmJobsMapConfig.highlightJob = new URLSearchParams(window.location.search).get('highlight_job');

        function showMapAlert(message, isError) {
            var baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#map-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }
    </script>

    @if ($mapConfig['provider'] === 'google')
        @include('components.scripts.google-maps')
        <script src="{{ asset('js/google-jobs-map.js') }}?v={{ @filemtime(public_path('js/google-jobs-map.js')) ?: 1 }}"></script>
        <script>window.crmInitJobsMap(window.crmJobsMapConfig);</script>
    @else
        <link rel="stylesheet" href="{{ asset('js/leaflet/leaflet.css') }}">
        <link rel="stylesheet" href="{{ asset('js/leaflet/MarkerCluster.css') }}">
        <link rel="stylesheet" href="{{ asset('js/leaflet/MarkerCluster.Default.css') }}">
        <script src="{{ asset('js/leaflet/leaflet.js') }}"></script>
        <script src="{{ asset('js/leaflet/leaflet.markercluster.js') }}"></script>
        <script src="{{ asset('js/leaflet-jobs-map.js') }}?v={{ @filemtime(public_path('js/leaflet-jobs-map.js')) ?: 1 }}"></script>
        <script>window.crmInitJobsMap(window.crmJobsMapConfig);</script>
    @endif

    <script>
        (function () {
            var currentFilters = {};
            window.reloadMapFromFilter = function (filters) {
                currentFilters = filters || {};
                window.crmMapLoad(currentFilters);
            };
            window.refreshMapInPlace = function () {
                window.crmMapLoad(currentFilters, true);
            };
            $('#refresh-map-btn').on('click', function () {
                window.crmMapLoad(currentFilters);
            });
        })();
        window.crmMapLoad(getFilters());
    </script>

    @include('admin.partials.job-modals')
    @include('admin.partials.job-actions-script', ['filterCallback' => 'refreshMapInPlace'])

</x-layouts.dashboard>

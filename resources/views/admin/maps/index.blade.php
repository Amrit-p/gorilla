<x-layouts.dashboard :title="'Map & Routing'">
    <div class="space-y-4">

        {{-- Filter bar (drives map reload via filterCallback) --}}
        @include('admin.jobs.partials.filter-bar', [
            'filters'       => $filters,
            'filterCallback' => 'reloadMapFromFilter',
            'resetUrl'       => route('admin.maps.index'),
            'tableContainer' => 'jobs-map-wrap',
        ])

        <div id="map-alert" class="hidden"></div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">
            <div class="xl:col-span-3" id="jobs-map-wrap">
                <div id="jobs-map" class="h-[520px] w-full rounded border border-slate-200 bg-slate-100"></div>
            </div>

            <div class="space-y-3 rounded border border-slate-200 bg-white p-3">
                <h3 class="text-sm font-semibold text-slate-900">Routing Controls</h3>
                <p class="text-xs text-slate-500">Click markers to select jobs. Marker colour reflects equipment type. Use the filters above to narrow results.</p>

                <button id="refresh-map-btn" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">Refresh Map</button>

                {{-- Equipment-type colour legend (populated by JS) --}}
                <div id="map-legend" class="space-y-1">
                    <h4 class="text-xs font-semibold text-slate-700">Equipment Types</h4>
                    <div id="map-legend-items" class="space-y-1 text-xs text-slate-600"></div>
                </div>
            </div>
        </div>
    </div>

   

    <script src="{{ asset('js/map-job-popup.js') }}?v={{ @filemtime(public_path('js/map-job-popup.js')) ?: 1 }}"></script>
    <script>
        window.crmJobsMapConfig = @json(array_merge($mapConfig, [
            'canManageJobs' => auth()->user()->can('manage-job-records'),
        ]));

        function showMapAlert(message, isError) {
            var baseClass = isError
                ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
            $('#map-alert').removeClass('hidden').attr('class', baseClass).text(message);
        }
    </script>

    @if ($mapConfig['provider'] === 'google')
        {{-- Google Maps (loader + impl; async API load handled inside the JS) --}}
        @include('components.scripts.google-maps')
        <script src="{{ asset('js/google-jobs-map.js') }}?v={{ @filemtime(public_path('js/google-jobs-map.js')) ?: 1 }}"></script>
        <script>window.crmInitJobsMap(window.crmJobsMapConfig);</script>
    @else
        {{-- OpenStreetMap / Leaflet (vendored) --}}
        <link rel="stylesheet" href="{{ asset('js/leaflet/leaflet.css') }}">
        <link rel="stylesheet" href="{{ asset('js/leaflet/MarkerCluster.css') }}">
        <link rel="stylesheet" href="{{ asset('js/leaflet/MarkerCluster.Default.css') }}">
        <script src="{{ asset('js/leaflet/leaflet.js') }}"></script>
        <script src="{{ asset('js/leaflet/leaflet.markercluster.js') }}"></script>
        <script src="{{ asset('js/leaflet-jobs-map.js') }}?v={{ @filemtime(public_path('js/leaflet-jobs-map.js')) ?: 1 }}"></script>
        <script>window.crmInitJobsMap(window.crmJobsMapConfig);</script>
    @endif

    {{-- Common map callbacks — provider-agnostic, delegates to window.crmMapLoad --}}
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
        window.crmMapLoad(getFilters())
    </script>

    {{-- Job action modals --}}
    @include('admin.partials.job-modals')
    {{-- Job action scripts (assign/status/delete modals + delegation) --}}
    @include('admin.partials.job-actions-script', ['filterCallback' => 'refreshMapInPlace'])

</x-layouts.dashboard>

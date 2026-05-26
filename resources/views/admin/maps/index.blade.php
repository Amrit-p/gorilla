<x-layouts.dashboard :title="'Map & Routing'">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600">
                Visualize jobs on the map, cluster points, optimize route sequence, and assign nearest employee.
                @if ($mapConfig['provider'] === 'google')
                    <span class="text-emerald-700">Google Maps</span>
                @else
                    <span class="text-slate-500">OpenStreetMap fallback</span>
                    @unless ($mapConfig['google']['enabled'] ?? false)
                        — add a Google Maps API key in Website Settings to enable Google.
                    @endunless
                @endif
            </p>
            <div class="flex items-center gap-2">
                <input id="map-date-filter" type="date" value="{{ $initialDate }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <button id="refresh-map-btn" class="rounded-md border border-slate-300 px-3 py-2 text-sm">Refresh</button>
            </div>
        </div>

        <div id="map-alert" class="hidden"></div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">
            <div class="xl:col-span-3">
                <div id="jobs-map" class="h-[520px] w-full rounded border border-slate-200 bg-slate-100"></div>
            </div>
            <div class="space-y-3 rounded border border-slate-200 bg-white p-3">
                <h3 class="text-sm font-semibold text-slate-900">Routing Controls</h3>
                <p class="text-xs text-slate-500">Click markers to select jobs. Marker color reflects equipment type. Open job details in a new tab from the popup.</p>
                <button id="optimize-route-btn" class="w-full rounded-md bg-slate-900 px-3 py-2 text-sm text-white">Optimize Route Sequence</button>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-700">Assign nearest employee to Job ID</label>
                    <div class="flex gap-2">
                        <input id="nearest-job-id" type="number" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" placeholder="Job ID">
                        <button id="assign-nearest-btn" class="rounded-md border border-slate-300 px-3 py-2 text-sm">Assign</button>
                    </div>
                </div>

                <div>
                    <h4 class="mb-1 text-xs font-semibold text-slate-700">Selected Job IDs</h4>
                    <p id="selected-job-ids" class="text-xs text-slate-600">None</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.crmJobsMapConfig = @json($mapConfig);
    </script>

    @if ($mapConfig['provider'] === 'google')
        @include('components.scripts.google-maps')
        <script src="{{ asset('js/map-job-popup.js') }}?v={{ @filemtime(public_path('js/map-job-popup.js')) ?: 1 }}"></script>
        <script src="{{ asset('js/google-jobs-map.js') }}?v={{ @filemtime(public_path('js/google-jobs-map.js')) ?: 1 }}"></script>
        <script>
            window.crmLoadGoogleMaps(window.crmJobsMapConfig.google, function () {
                if (typeof window.crmInitGoogleJobsMap === 'function') {
                    window.crmInitGoogleJobsMap(window.crmJobsMapConfig);
                }
            });
        </script>
    @else
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
        <script src="{{ asset('js/map-job-popup.js') }}?v={{ @filemtime(public_path('js/map-job-popup.js')) ?: 1 }}"></script>
        <script>
            (function () {
                let map;
                let markerLayer;
                let routeLine;
                let selectedJobIds = new Set();

                function showMapAlert(message, isError) {
                    const baseClass = isError
                        ? 'rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700'
                        : 'rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
                    $('#map-alert').removeClass('hidden').attr('class', baseClass).text(message);
                }

                function updateSelectedIdsLabel() {
                    const ids = Array.from(selectedJobIds).sort((a, b) => a - b);
                    $('#selected-job-ids').text(ids.length ? ids.join(', ') : 'None');
                }

                function coloredDivIcon(color) {
                    return L.divIcon({
                        className: '',
                        html: '<span style="display:block;width:14px;height:14px;border-radius:50%;background:' + (color || '#64748b') + ';border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.35)"></span>',
                        iconSize: [14, 14],
                        iconAnchor: [7, 7],
                    });
                }

                function initMap() {
                    const center = window.crmJobsMapConfig.defaultCenter;
                    map = L.map('jobs-map').setView([center.lat, center.lng], window.crmJobsMapConfig.defaultZoom || 10);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 18,
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(map);
                    markerLayer = L.markerClusterGroup();
                    map.addLayer(markerLayer);
                }

                function drawRouteLine(points) {
                    if (routeLine) map.removeLayer(routeLine);
                    if (points.length < 2) return;
                    routeLine = L.polyline(points, { color: '#1d4ed8', weight: 3, opacity: 0.8 }).addTo(map);
                }

                function loadJobsOnMap() {
                    $.get(window.crmJobsMapConfig.jobsUrl, { scheduled_date: $('#map-date-filter').val() }, function (response) {
                        markerLayer.clearLayers();
                        selectedJobIds.clear();
                        updateSelectedIdsLabel();

                        const jobs = response.jobs || [];
                        const bounds = [];
                        const routePoints = [];

                        jobs.forEach(function (job) {
                            const marker = L.marker([job.lat, job.lng], { icon: coloredDivIcon(job.equipment_color) });
                            marker.bindPopup(
                                typeof window.crmBuildMapJobPopup === 'function'
                                    ? window.crmBuildMapJobPopup(job)
                                    : '<strong>Job #' + job.id + '</strong>'
                            );
                            marker.on('click', function () {
                                if (selectedJobIds.has(job.id)) selectedJobIds.delete(job.id);
                                else selectedJobIds.add(job.id);
                                updateSelectedIdsLabel();
                            });
                            markerLayer.addLayer(marker);
                            bounds.push([job.lat, job.lng]);
                            routePoints.push([job.lat, job.lng]);
                        });

                        drawRouteLine(routePoints);
                        if (bounds.length) map.fitBounds(bounds, { padding: [30, 30] });
                        showMapAlert('Loaded ' + jobs.length + ' geocoded jobs on map.');
                    }).fail(function () {
                        showMapAlert('Unable to load map jobs.', true);
                    });
                }

                $('#refresh-map-btn').on('click', loadJobsOnMap);
                $('#optimize-route-btn').on('click', function () {
                    const ids = Array.from(selectedJobIds);
                    if (ids.length < 2) {
                        showMapAlert('Select at least 2 jobs from map markers to optimize route.', true);
                        return;
                    }
                    $.ajax({
                        url: window.crmJobsMapConfig.optimizeUrl,
                        method: 'POST',
                        data: { _token: window.crmJobsMapConfig.csrfToken, job_ids: ids },
                        headers: { Accept: 'application/json' },
                        success: function (res) {
                            showMapAlert(res.message || 'Route optimized.');
                            loadJobsOnMap();
                        },
                        error: function (xhr) {
                            showMapAlert(Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || 'Route optimization failed.', true);
                        },
                    });
                });
                $('#assign-nearest-btn').on('click', function () {
                    const jobId = $('#nearest-job-id').val();
                    if (!jobId) {
                        showMapAlert('Enter a job ID first.', true);
                        return;
                    }
                    $.ajax({
                        url: window.crmJobsMapConfig.assignNearestUrl,
                        method: 'POST',
                        data: { _token: window.crmJobsMapConfig.csrfToken, job_id: jobId },
                        headers: { Accept: 'application/json' },
                        success: function (res) {
                            showMapAlert(res.message || 'Nearest employee assigned.');
                            loadJobsOnMap();
                        },
                        error: function (xhr) {
                            showMapAlert(xhr.responseJSON?.message || 'Nearest assignment failed.', true);
                        },
                    });
                });

                initMap();
                loadJobsOnMap();
            })();
        </script>
    @endif
</x-layouts.dashboard>

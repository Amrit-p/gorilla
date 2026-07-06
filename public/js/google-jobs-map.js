/**
 * Google Maps implementation for the admin jobs routing map.
 * Exposes window.crmInitJobsMap and window.crmMapLoad — same surface as leaflet-jobs-map.js.
 * Handles async API loading internally.
 */
window.crmInitGoogleJobsMap = window.crmInitJobsMap = function (mapConfig) {
    /* Stub crmMapLoad immediately so calls made before Google Maps finishes loading
       are captured and replayed once the real implementation is ready. */
    var _pendingParams;
    window.crmMapLoad = function (params) {
        _pendingParams = params || {};
    };

    window.crmLoadGoogleMaps(mapConfig.google, function () {
        'use strict';

        var SELECTED_COLOR      = '#000000';
        var POPUP_CLOSE_DELAY   = 200;

        function svgMarkerHtml(color) {
            var fill = color || '#64748b';
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:32px;height:40px;display:block"><path d="m205.542 407.089 43.658 55.429a8.355 8.355 0 0 0 1.535 1.56 8.52 8.52 0 0 0 11.965-1.438l43.76-55.551c24.239 4.361 44.86 11.749 59.461 21.081 13.589 8.689 21.568 18.79 21.568 29.209 0 13.77-13.749 26.859-35.979 36.891-24.289 10.948-58.04 17.73-95.51 17.73s-71.224-6.782-95.513-17.73c-22.226-10.032-35.978-23.121-35.978-36.891 0-10.419 7.984-20.52 21.573-29.209 14.6-9.332 35.217-16.72 59.459-21.081zm50.458-313.069a83.414 83.414 0 1 1 -58.982 24.43 83.112 83.112 0 0 1 58.982-24.43zm46.919 36.5a66.35 66.35 0 1 0 19.432 46.909 66.142 66.142 0 0 0 -19.431-46.909zm92.912 27.439a133.045 133.045 0 0 0 -75.08-115.821 8.513 8.513 0 0 0 -7.531 15.27 115.967 115.967 0 0 1 65.61 101.151 8.506 8.506 0 1 0 17-.6zm27.148 4.86c0-41.149-14.558-81.2-46.479-113.129a170.952 170.952 0 0 0 -241 0c-31.921 31.93-46.486 71.98-46.486 113.129 0 44.42 16.873 90.191 47.155 128.639l119.831 152.142 119.83-152.142c30.281-38.448 47.15-84.219 47.15-128.639z" fill="' + fill + '" fill-rule="evenodd"/></svg>';
        }

        function createSvgMarkerContent(color) {
            var el = document.createElement('div');
            el.style.width = '32px';
            el.style.height = '40px';
            el.innerHTML = svgMarkerHtml(color);
            return el;
        }

        function officeMarkerHtml() {
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:34px;height:34px;display:block;filter:drop-shadow(0 1px 2px rgba(0,0,0,.4));">'
                + '<circle cx="12" cy="12" r="11" fill="#4f46e5" stroke="#ffffff" stroke-width="2"/>'
                + '<path d="M12 5.5 5 11h2v6h4v-4h2v4h4v-6h2z" fill="#ffffff"/>'
                + '</svg>';
        }

        function isJobSelected(jobId) {
            return !!(window.crmJobSelection && window.crmJobSelection.has(jobId));
        }

        var map;
        var infoWindow;
        var markers = [];
        var markersById = {};
        var infoCloseTimer;
        var activeJobId;
        var directionsService;
        var directionsRenderer;
        var routeVisible = false;
        var lastJobs = [];
        var MAX_ROUTE_STOPS = 25; // Directions API caps origin + destination + waypoints at 25 total.
        var officeMarkerRef;
        var OFFICE_COLLISION_EPSILON = 0.0003; // ~30m — job pins this close to the office get it nudged aside so both stay visible.


        function cancelInfoClose() { clearTimeout(infoCloseTimer); }
        function scheduleInfoClose() { infoCloseTimer = setTimeout(function () { infoWindow.close(); activeJobId = null; }, POPUP_CLOSE_DELAY); }

        function openInfoWindowFor(job, marker) {
            var html = typeof window.crmBuildMapJobPopup === 'function'
                ? window.crmBuildMapJobPopup(job)
                : '<strong>Job #' + job.id + '</strong>';

            infoWindow.setContent(html);
            infoWindow.open({ map: map, anchor: marker });
            activeJobId = job.id;
        }


        function clearMarkers() {
            markers.forEach(function (m) { m.map = null; });
            markers = [];
            markersById = {};
        }


        function setMarkerBadge(jobId, number) {
            var entry = markersById[jobId];
            if (!entry) { return; }

            var badge = entry.content.querySelector('.map-route-badge');
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'map-route-badge';
                badge.style.cssText = 'position:absolute;top:1px;left:0;width:32px;text-align:center;pointer-events:none;';
                entry.content.style.position = 'relative';
                entry.content.appendChild(badge);
            }
            badge.innerHTML = '<span style="display:inline-flex;align-items:center;justify-content:center;'
                + 'width:15px;height:15px;border-radius:50%;background:#fff;color:#000;font-size:10px;'
                + 'font-weight:700;line-height:1;box-shadow:0 0 0 1px rgba(0,0,0,.35);">' + number + '</span>';
        }


        function clearMarkerBadges() {
            Object.keys(markersById).forEach(function (id) {
                var badge = markersById[id].content.querySelector('.map-route-badge');
                if (badge) { badge.remove(); }
            });
        }


        function hideRoute() {
            directionsRenderer.setMap(null);
            routeVisible = false;
            clearMarkerBadges();
            var label = document.getElementById('map-route-toggle-label');
            if (label) { label.textContent = 'Show Route'; }
        }


        function drawRoute() {
            if (lastJobs.length < 1) {
                showMapAlert('Need at least 1 geocoded job to draw a route.', true);
                return;
            }

            var stops = lastJobs.slice();
            if (stops.length > MAX_ROUTE_STOPS) {
                showMapAlert('Route limited to the first ' + MAX_ROUTE_STOPS + ' jobs (Google Directions API limit).', true);
                stops = stops.slice(0, MAX_ROUTE_STOPS);
            }

            var origin = mapConfig.defaultCenter;
            var destination = stops[stops.length - 1];
            var waypoints = stops.slice(0, -1).map(function (job) {
                return { location: { lat: job.lat, lng: job.lng }, stopover: true };
            });

            directionsService.route({
                origin: { lat: origin.lat, lng: origin.lng },
                destination: { lat: destination.lat, lng: destination.lng },
                waypoints: waypoints,
                optimizeWaypoints: true,
                travelMode: google.maps.TravelMode.DRIVING,
            }, function (result, status) {
                if (status !== 'OK') {
                    showMapAlert('Unable to compute route: ' + status, true);
                    return;
                }

                directionsRenderer.setDirections(result);
                directionsRenderer.setMap(map);
                routeVisible = true;
                var label = document.getElementById('map-route-toggle-label');
                if (label) { label.textContent = 'Hide Route'; }

                /* waypoint_order indexes into the `waypoints` array (stops minus the fixed
                   destination) — reassemble the actual optimized visiting order from it. */
                clearMarkerBadges();
                var visitOrder = result.routes[0].waypoint_order.map(function (idx) { return stops[idx]; });
                visitOrder.push(destination);
                visitOrder.forEach(function (job, i) {
                    setMarkerBadge(job.id, i + 1);
                });

                var totals = result.routes[0].legs.reduce(function (acc, leg) {
                    acc.meters  += leg.distance.value;
                    acc.seconds += leg.duration.value;
                    return acc;
                }, { meters: 0, seconds: 0 });

                showMapAlert(
                    'Route: ' + (totals.meters / 1000).toFixed(1) + ' km, ~' +
                    Math.round(totals.seconds / 60) + ' min across ' + stops.length + ' stops.'
                );
            });
        }


        window.crmToggleRoute = function () {
            if (routeVisible) {
                hideRoute();
            } else {
                drawRoute();
            }
        };


        function initMap() {
            var center = mapConfig.defaultCenter;
            map = new google.maps.Map(document.getElementById('jobs-map'), {
                center: { lat: center.lat, lng: center.lng },
                zoom: mapConfig.defaultZoom || 10,
                mapId: window.crmGoogleMapId(mapConfig),
                mapTypeControl: false,
                fullscreenControl: false,
                streetViewControl: false,
                clickableIcons: false,
            });
            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                suppressMarkers: true,
                preserveViewport: true,
                polylineOptions: { strokeColor: '#000000', strokeWeight: 4, strokeOpacity: 0.85 },
            });

            infoWindow = new google.maps.InfoWindow({ maxWidth: 320 });
            infoWindow.addListener('closeclick', function () { activeJobId = null; });
            infoWindow.addListener('domready', function () {
                var iw = document.querySelector('.gm-style-iw');
                if (!iw) { return; }
                var bubble = iw.closest('.gm-style-iw-a') || iw;
                bubble.addEventListener('mouseenter', cancelInfoClose);
                bubble.addEventListener('mouseleave', scheduleInfoClose);
            });
            window.crmHideMapPois(map);

            window.crmEnsureGoogleMarkerLibrary().then(function (lib) {
                var content = document.createElement('div');
                content.style.width = '34px';
                content.style.height = '34px';
                content.innerHTML = officeMarkerHtml();

                var officeMarker = new lib.AdvancedMarkerElement({
                    map: map,
                    position: { lat: center.lat, lng: center.lng },
                    title: 'Office',
                    content: content,
                    zIndex: 999,
                });
                officeMarkerRef = officeMarker;

                content.addEventListener('click', function () {
                    infoWindow.setContent('<strong>Office</strong>');
                    infoWindow.open({ map: map, anchor: officeMarker });
                });
            });

            window.addEventListener('jobs:selection-changed', function (e) {
                var ids = new Set((e.detail && e.detail.ids) || []);
                Object.keys(markersById).forEach(function (id) {
                    var entry = markersById[id];
                    entry.content.innerHTML = svgMarkerHtml(ids.has(Number(id)) ? SELECTED_COLOR : entry.color);
                });
            });
        }


        window.crmMapLoad = function (params, preserveView) {
            $.get(mapConfig.jobsUrl, params || {}, function (response) {
                clearMarkers();
                hideRoute();

                var jobs            = response.jobs || [];
                var bounds          = new google.maps.LatLngBounds();
                var highlightData   = null;
                lastJobs = jobs;

                if (officeMarkerRef) {
                    var office = mapConfig.defaultCenter;
                    var overlapsOffice = jobs.some(function (job) {
                        return Math.abs(job.lat - office.lat) < OFFICE_COLLISION_EPSILON
                            && Math.abs(job.lng - office.lng) < OFFICE_COLLISION_EPSILON;
                    });
                    /* Nudge the real position (not just CSS) so the marker's hit-test area
                       moves with it — a CSS-only offset left the click/hover target stuck
                       over the job pin's true spot even though the icon visually moved. */
                    officeMarkerRef.position = overlapsOffice
                        ? { lat: office.lat + 0.00015, lng: office.lng - 0.00025 }
                        : { lat: office.lat, lng: office.lng };
                }

                window.crmEnsureGoogleMarkerLibrary().then(function (lib) {
                    jobs.forEach(function (job) {
                        var pos       = { lat: job.lat, lng: job.lng };
                        var baseColor = job.equipment_color || '#64748b';
                        var content   = createSvgMarkerContent(isJobSelected(job.id) ? SELECTED_COLOR : baseColor);

                        var marker = new lib.AdvancedMarkerElement({
                            map: map,
                            position: pos,
                            title: 'Job #' + job.id,
                            content: content,
                        });

                        /* Hover to preview (desktop); tap to toggle (touch/mobile — no hover events fire there). */
                        content.addEventListener('mouseenter', function () {
                            cancelInfoClose();
                            openInfoWindowFor(job, marker);
                        });
                        content.addEventListener('mouseleave', scheduleInfoClose);
                        content.addEventListener('click', function (e) {
                            e.stopPropagation();
                            cancelInfoClose();
                            if (activeJobId === job.id) {
                                infoWindow.close();
                                activeJobId = null;
                            } else {
                                openInfoWindowFor(job, marker);
                            }
                        });

                        markers.push(marker);
                        markersById[job.id] = { marker: marker, content: content, color: baseColor };
                        bounds.extend(pos);

                        if (mapConfig.highlightJob && job.id == mapConfig.highlightJob) {
                            highlightData = { marker: marker, job: job };
                        }
                    });

                    if (highlightData) {
                        mapConfig.highlightJob = null;
                        map.setCenter({ lat: highlightData.job.lat, lng: highlightData.job.lng });
                        map.setZoom(15);
                        openInfoWindowFor(highlightData.job, highlightData.marker);
                    } else if (!preserveView && jobs.length > 0) {
                        map.fitBounds(bounds, 40);
                    }

                    showMapAlert('Loaded ' + jobs.length + ' geocoded job' + (jobs.length !== 1 ? 's' : '') + '.');
                });
            }).fail(function () {
                showMapAlert('Unable to load map jobs.', true);
            });
        };


        initMap();

        /* Replay any call that arrived before Google Maps was ready */
        if (_pendingParams !== undefined) {
            window.crmMapLoad(_pendingParams);
        }
    });
};

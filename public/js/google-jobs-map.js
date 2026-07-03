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

        function isJobSelected(jobId) {
            return !!(window.crmJobSelection && window.crmJobSelection.has(jobId));
        }

        var map;
        var infoWindow;
        var markers = [];
        var markersById = {};
        var infoCloseTimer;


        function cancelInfoClose() { clearTimeout(infoCloseTimer); }
        function scheduleInfoClose() { infoCloseTimer = setTimeout(function () { infoWindow.close(); }, POPUP_CLOSE_DELAY); }


        function clearMarkers() {
            markers.forEach(function (m) { m.map = null; });
            markers = [];
            markersById = {};
        }


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
            infoWindow = new google.maps.InfoWindow({ maxWidth: 320 });
            infoWindow.addListener('domready', function () {
                var iw = document.querySelector('.gm-style-iw');
                if (!iw) { return; }
                var bubble = iw.closest('.gm-style-iw-a') || iw;
                bubble.addEventListener('mouseenter', cancelInfoClose);
                bubble.addEventListener('mouseleave', scheduleInfoClose);
            });
            window.crmHideMapPois(map);

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

                var jobs            = response.jobs || [];
                var bounds          = new google.maps.LatLngBounds();
                var routePoints     = [];
                var highlightData   = null;

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

                        /* Hover to open/close instead of click. */
                        content.addEventListener('mouseenter', function () {
                            cancelInfoClose();
                            var html = typeof window.crmBuildMapJobPopup === 'function'
                                ? window.crmBuildMapJobPopup(job)
                                : '<strong>Job #' + job.id + '</strong>';

                            infoWindow.setContent(html);
                            infoWindow.open({ map: map, anchor: marker });
                        });
                        content.addEventListener('mouseleave', scheduleInfoClose);

                        markers.push(marker);
                        markersById[job.id] = { marker: marker, content: content, color: baseColor };
                        bounds.extend(pos);

                        if (mapConfig.highlightJob && job.id == mapConfig.highlightJob) {
                            highlightData = { marker: marker, job: job };
                        }

                        if (job.route_sequence) {
                            routePoints[job.route_sequence - 1] = pos;
                        } else {
                            routePoints.push(pos);
                        }
                    });

                    if (highlightData) {
                        mapConfig.highlightJob = null;
                        map.setCenter({ lat: highlightData.job.lat, lng: highlightData.job.lng });
                        map.setZoom(15);
                        var html = typeof window.crmBuildMapJobPopup === 'function'
                            ? window.crmBuildMapJobPopup(highlightData.job)
                            : '<strong>Job #' + highlightData.job.id + '</strong>';
                        infoWindow.setContent(html);
                        infoWindow.open({ map: map, anchor: highlightData.marker });
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

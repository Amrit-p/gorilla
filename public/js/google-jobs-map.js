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

        var map;
        var infoWindow;
        var markers = [];


        function clearMarkers() {
            markers.forEach(function (m) { m.map = null; });
            markers = [];
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
            });
            infoWindow = new google.maps.InfoWindow({ maxWidth: 320 });
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
                        var pos     = { lat: job.lat, lng: job.lng };
                        var content = window.crmCreateCircleMarkerContent(job.equipment_color || '#64748b', 14);

                        var marker = new lib.AdvancedMarkerElement({
                            map: map,
                            position: pos,
                            title: 'Job #' + job.id,
                            content: content,
                        });

                        marker.addListener('click', function () {
                            var html = typeof window.crmBuildMapJobPopup === 'function'
                                ? window.crmBuildMapJobPopup(job)
                                : '<strong>Job #' + job.id + '</strong>';

                            infoWindow.setContent(html);
                            infoWindow.open({ map: map, anchor: marker });
                        });

                        markers.push(marker);
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

/**
 * Leaflet / OpenStreetMap implementation for the admin jobs routing map.
 * Exposes window.crmInitJobsMap and window.crmMapLoad — same surface as google-jobs-map.js.
 */
window.crmInitLeafletJobsMap = window.crmInitJobsMap = function (mapConfig) {
    'use strict';

    var map, markerLayer;


    function svgMarkerIcon(color) {
        var fill = color || '#64748b';
        var html = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:32px;height:40px;display:block"><path d="m205.542 407.089 43.658 55.429a8.355 8.355 0 0 0 1.535 1.56 8.52 8.52 0 0 0 11.965-1.438l43.76-55.551c24.239 4.361 44.86 11.749 59.461 21.081 13.589 8.689 21.568 18.79 21.568 29.209 0 13.77-13.749 26.859-35.979 36.891-24.289 10.948-58.04 17.73-95.51 17.73s-71.224-6.782-95.513-17.73c-22.226-10.032-35.978-23.121-35.978-36.891 0-10.419 7.984-20.52 21.573-29.209 14.6-9.332 35.217-16.72 59.459-21.081zm50.458-313.069a83.414 83.414 0 1 1 -58.982 24.43 83.112 83.112 0 0 1 58.982-24.43zm46.919 36.5a66.35 66.35 0 1 0 19.432 46.909 66.142 66.142 0 0 0 -19.431-46.909zm92.912 27.439a133.045 133.045 0 0 0 -75.08-115.821 8.513 8.513 0 0 0 -7.531 15.27 115.967 115.967 0 0 1 65.61 101.151 8.506 8.506 0 1 0 17-.6zm27.148 4.86c0-41.149-14.558-81.2-46.479-113.129a170.952 170.952 0 0 0 -241 0c-31.921 31.93-46.486 71.98-46.486 113.129 0 44.42 16.873 90.191 47.155 128.639l119.831 152.142 119.83-152.142c30.281-38.448 47.15-84.219 47.15-128.639z" fill="' + fill + '" fill-rule="evenodd"/></svg>';
        return L.divIcon({
            className: '',
            html: html,
            iconSize:    [32, 40],
            iconAnchor:  [16, 40],
            popupAnchor: [0, -40],
        });
    }

    function buildLegend(jobs) {
        var seen = {};
        jobs.forEach(function (job) {
            if (job.equipment_name && !seen[job.equipment_name]) {
                seen[job.equipment_name] = job.equipment_color || '#64748b';
            }
        });
        var html = '';
        Object.keys(seen).forEach(function (name) {
            html += '<div style="display:flex;align-items:center;gap:6px;">' +
                '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + seen[name] + ';flex-shrink:0;"></span>' +
                '<span>' + name + '</span></div>';
        });
        document.getElementById('map-legend-items').innerHTML = html || '<span class="text-slate-400">None</span>';
    }


    function initMap() {
        var center = mapConfig.defaultCenter;
        map = L.map('jobs-map').setView([center.lat, center.lng], mapConfig.defaultZoom || 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);
        markerLayer = L.markerClusterGroup();
        map.addLayer(markerLayer);
    }


    window.crmMapLoad = function (params, preserveView) {
        $.get(mapConfig.jobsUrl, params || {}, function (response) {
            markerLayer.clearLayers();

            var jobs            = response.jobs || [];
            var bounds          = [];
            var highlightMarker = null;

            jobs.forEach(function (job) {
                var marker = L.marker([job.lat, job.lng], { icon: svgMarkerIcon(job.equipment_color) });
                marker.bindPopup(
                    typeof window.crmBuildMapJobPopup === 'function'
                        ? window.crmBuildMapJobPopup(job)
                        : '<strong>Job #' + job.id + '</strong>',
                    { maxWidth: 320 }
                );
                markerLayer.addLayer(marker);
                bounds.push([job.lat, job.lng]);
                if (mapConfig.highlightJob && job.id == mapConfig.highlightJob) {
                    highlightMarker = marker;
                }
            });

            buildLegend(jobs);
            if (highlightMarker) {
                mapConfig.highlightJob = null;
                markerLayer.zoomToShowLayer(highlightMarker, function () {
                    highlightMarker.openPopup();
                });
            } else if (bounds.length && !preserveView) {
                map.fitBounds(bounds, { padding: [30, 30] });
            }
            showMapAlert('Loaded ' + jobs.length + ' geocoded job' + (jobs.length !== 1 ? 's' : '') + '.');
        }).fail(function () {
            showMapAlert('Unable to load map jobs.', true);
        });
    };


    initMap();
};

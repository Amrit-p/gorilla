/**
 * Leaflet / OpenStreetMap implementation for the admin jobs routing map.
 * Exposes window.crmInitJobsMap and window.crmMapLoad — same surface as google-jobs-map.js.
 */
window.crmInitLeafletJobsMap = window.crmInitJobsMap = function (mapConfig) {
    'use strict';

    var map, markerLayer;


    function coloredDivIcon(color) {
        return L.divIcon({
            className: '',
            html: '<span style="display:block;width:14px;height:14px;border-radius:50%;background:' + (color || '#64748b') + ';border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.35)"></span>',
            iconSize:   [14, 14],
            iconAnchor: [7, 7],
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

            var jobs   = response.jobs || [];
            var bounds = [];

            jobs.forEach(function (job) {
                var marker = L.marker([job.lat, job.lng], { icon: coloredDivIcon(job.equipment_color) });
                marker.bindPopup(
                    typeof window.crmBuildMapJobPopup === 'function'
                        ? window.crmBuildMapJobPopup(job)
                        : '<strong>Job #' + job.id + '</strong>',
                    { maxWidth: 320 }
                );
                markerLayer.addLayer(marker);
                bounds.push([job.lat, job.lng]);
            });

            buildLegend(jobs);
            if (bounds.length && !preserveView) {
                map.fitBounds(bounds, { padding: [30, 30] });
            }
            showMapAlert('Loaded ' + jobs.length + ' geocoded job' + (jobs.length !== 1 ? 's' : '') + '.');
        }).fail(function () {
            showMapAlert('Unable to load map jobs.', true);
        });
    };


    initMap();
    window.crmMapLoad({});
};

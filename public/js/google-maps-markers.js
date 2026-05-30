/**
 * Shared Google Maps Advanced Marker helpers (replaces deprecated google.maps.Marker).
 */
(function (window) {
    'use strict';

    let markerLibraryPromise = null;

    function loadMarkerLibrary() {
        if (!window.google || !google.maps) {
            return Promise.reject(new Error('Google Maps API is not loaded'));
        }

        if (!markerLibraryPromise) {
            markerLibraryPromise = google.maps.importLibrary('marker');
        }

        return markerLibraryPromise;
    }

    window.crmGoogleMapId = function (config) {
        if (config && config.mapId) {
            return config.mapId;
        }

        if (config && config.google && config.google.mapId) {
            return config.google.mapId;
        }

        return 'DEMO_MAP_ID';
    };

    window.crmEnsureGoogleMarkerLibrary = function () {
        return loadMarkerLibrary();
    };

    window.crmCreateCircleMarkerContent = function (color, size) {
        const diameter = size || 14;
        const el = document.createElement('div');
        el.style.width = diameter + 'px';
        el.style.height = diameter + 'px';
        el.style.borderRadius = '50%';
        el.style.background = color || '#64748b';
        el.style.border = '2px solid #fff';
        el.style.boxShadow = '0 1px 3px rgba(0,0,0,.35)';
        el.style.boxSizing = 'border-box';
        return el;
    };

    window.crmReadAdvancedMarkerLatLng = function (marker) {
        const pos = marker && marker.position;
        if (!pos) {
            return null;
        }

        const lat = typeof pos.lat === 'function' ? pos.lat() : pos.lat;
        const lng = typeof pos.lng === 'function' ? pos.lng() : pos.lng;

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return null;
        }

        return { lat: lat, lng: lng };
    };

    window.crmCreateAdvancedMarker = function (options) {
        return loadMarkerLibrary().then(function (lib) {
            const AdvancedMarkerElement = lib.AdvancedMarkerElement;
            const size = options.draggable ? 18 : 14;
            const marker = new AdvancedMarkerElement({
                map: options.map,
                position: options.position,
                title: options.title || '',
                content: window.crmCreateCircleMarkerContent(options.color, size),
                gmpDraggable: !!options.draggable,
            });

            marker._crmMarkerColor = options.color || '#64748b';

            if (options.draggable && typeof options.onDragEnd === 'function') {
                marker.addListener('dragend', function () {
                    const coords = window.crmReadAdvancedMarkerLatLng(marker);
                    if (coords) {
                        options.onDragEnd(coords.lat, coords.lng);
                    }
                });
            }

            if (typeof options.onClick === 'function') {
                marker.addListener('click', options.onClick);
            }

            return marker;
        });
    };

    window.crmSetAdvancedMarkerColor = function (marker, color) {
        if (!marker) {
            return;
        }

        const size = marker.gmpDraggable ? 18 : 14;
        marker.content = window.crmCreateCircleMarkerContent(color, size);
        marker._crmMarkerColor = color || '#64748b';
    };

    window.crmSetAdvancedMarkerPosition = function (marker, position) {
        if (marker) {
            marker.position = position;
        }
    };

    window.crmRemoveAdvancedMarker = function (marker) {
        if (marker) {
            marker.map = null;
        }
    };
})(window);

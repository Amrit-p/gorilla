/**
 * Lazy-load Google Maps JS API once per page (shared callback queue).
 */
(function (window) {
    'use strict';

    const LOADER = {
        loading: false,
        loaded: false,
        callbacks: [],
    };

    function runCallbacks() {
        LOADER.loaded = true;
        LOADER.loading = false;
        const queue = LOADER.callbacks.splice(0, LOADER.callbacks.length);
        queue.forEach(function (cb) {
            try {
                cb();
            } catch (error) {
                console.error('Google Maps callback failed', error);
            }
        });
    }

    window.crmLoadGoogleMaps = function (config, callback) {
        if (typeof callback === 'function') {
            if (window.google && window.google.maps) {
                callback();
                return;
            }
            LOADER.callbacks.push(callback);
        }

        if (!config || !config.enabled || !config.apiKey) {
            runCallbacks();
            return;
        }

        if (window.google && window.google.maps) {
            runCallbacks();
            return;
        }

        if (LOADER.loading) {
            return;
        }

        LOADER.loading = true;

        const libraries = (config.libraries || ['places']).join(',');
        const previousCallback = window.__crmGoogleMapsInit;

        window.__crmGoogleMapsInit = function () {
            if (typeof previousCallback === 'function') {
                previousCallback();
            }
            runCallbacks();
        };

        const script = document.createElement('script');
        script.async = true;
        script.defer = true;
        script.src =
            'https://maps.googleapis.com/maps/api/js?key=' +
            encodeURIComponent(config.apiKey) +
            '&libraries=' +
            encodeURIComponent(libraries) +
            '&loading=async' +
            '&callback=__crmGoogleMapsInit';
        script.onerror = function () {
            LOADER.loading = false;
            console.error('Failed to load Google Maps JavaScript API.');
            runCallbacks();
        };
        document.head.appendChild(script);
    };
})(window);

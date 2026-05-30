/**
 * Reusable Google Places autocomplete + draggable marker map preview.
 */
(function ($, window) {
    'use strict';

    const instances = {};

    function parseCoord(value, fallback) {
        const parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function getInstance(prefix) {
        return instances[prefix] || null;
    }

    function renderFallback($preview, message) {
        $preview.html(
            '<div class="flex h-full items-center justify-center p-4 text-center text-sm text-slate-500">' +
                message +
                '</div>'
        );
    }

    function buildInstance(config) {
        const prefix = config.prefix;
        const $address = $('#' + config.addressInputId);
        const $lat = $('#' + config.latitudeInputId);
        const $lng = $('#' + config.longitudeInputId);
        const $preview = $('#' + config.mapPreviewId);
        const $hint = $('#' + config.hintId);

        const instance = {
            prefix: prefix,
            config: config,
            map: null,
            marker: null,
            markerColor: config.markerColor || '#64748b',
            placeAutocomplete: null,
            geocoder: null,
            setAddressValue: function (text) {
                const value = text || '';
                $address.val(value);
                if (instance.placeAutocomplete) {
                    instance.placeAutocomplete.value = value;
                }
            },
            readAddressFromWidget: function () {
                if (instance.placeAutocomplete && instance.placeAutocomplete.value) {
                    return String(instance.placeAutocomplete.value);
                }

                return $address.val();
            },
            bindFormSync: function () {
                const form = $address.closest('form')[0];
                if (!form || form.dataset.crmAddressPickerBound === prefix) {
                    return;
                }

                form.dataset.crmAddressPickerBound = prefix;
                form.addEventListener('submit', function () {
                    if (instance.placeAutocomplete) {
                        $address.val(instance.readAddressFromWidget());
                    }
                });
            },
            updateCoordinates: function (lat, lng) {
                $lat.val(lat.toFixed(7));
                $lng.val(lng.toFixed(7));
            },
            setHint: function (message) {
                $hint.text(message);
            },
            reverseGeocode: function (lat, lng) {
                if (!config.mapsEnabled || !window.google || !google.maps) {
                    return;
                }

                if (!instance.geocoder) {
                    instance.geocoder = new google.maps.Geocoder();
                }

                instance.setHint('Looking up address…');

                instance.geocoder.geocode({ location: { lat: lat, lng: lng } }, function (results, status) {
                    if (status === 'OK' && results && results[0]) {
                        instance.setAddressValue(results[0].formatted_address);
                        instance.setHint('Location pinned. Drag the marker to update the address.');
                        return;
                    }

                    instance.setHint('Coordinates saved. Could not resolve a street address for this pin.');
                });
            },
            setMarkerColor: function (color) {
                instance.markerColor = color || '#64748b';
                if (instance.marker && typeof window.crmSetAdvancedMarkerColor === 'function') {
                    window.crmSetAdvancedMarkerColor(instance.marker, instance.markerColor);
                }
            },
            placeMarker: function (lat, lng) {
                if (!instance.map || !window.google) {
                    return;
                }

                const position = { lat: lat, lng: lng };

                if (!instance.marker) {
                    window
                        .crmCreateAdvancedMarker({
                            map: instance.map,
                            position: position,
                            color: instance.markerColor,
                            draggable: true,
                            onDragEnd: function (dragLat, dragLng) {
                                instance.handleLocationUpdate(dragLat, dragLng, true);
                            },
                        })
                        .then(function (marker) {
                            instance.marker = marker;
                        })
                        .catch(function (error) {
                            console.error('Advanced marker failed to initialize', error);
                        });
                } else {
                    window.crmSetAdvancedMarkerPosition(instance.marker, position);
                    window.crmSetAdvancedMarkerColor(instance.marker, instance.markerColor);
                }

                instance.map.setCenter(position);
                instance.map.setZoom(15);
            },
            handleLocationUpdate: function (lat, lng, geocode) {
                instance.updateCoordinates(lat, lng);
                instance.placeMarker(lat, lng);
                if (geocode) {
                    instance.reverseGeocode(lat, lng);
                }
            },
            initMap: function () {
                if (!$preview.length) {
                    return;
                }

                const defaultLat = parseCoord($preview.data('default-lat'), 43.6532);
                const defaultLng = parseCoord($preview.data('default-lng'), -79.3832);
                const lat = parseCoord($lat.val(), defaultLat);
                const lng = parseCoord($lng.val(), defaultLng);

                if (!config.mapsEnabled || !window.google || !window.google.maps) {
                    renderFallback(
                        $preview,
                        'Map preview unavailable. Configure a Google Maps API key in Website Settings.'
                    );
                    return;
                }

                $preview.empty();
                instance.map = new google.maps.Map($preview[0], {
                    center: { lat: lat, lng: lng },
                    zoom: $lat.val() ? 15 : 10,
                    mapId: window.crmGoogleMapId(config.loader),
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: false,
                });

                if ($lat.val() && $lng.val()) {
                    instance.placeMarker(lat, lng);
                }

                instance.map.addListener('click', function (event) {
                    if (!event.latLng) {
                        return;
                    }
                    instance.handleLocationUpdate(event.latLng.lat(), event.latLng.lng(), true);
                });
            },
            initAutocomplete: async function () {
                const input = document.getElementById(config.addressInputId);
                if (!input || !config.mapsEnabled || !window.google || !google.maps) {
                    return;
                }

                const wrapper = input.closest('div');
                if (!wrapper) {
                    return;
                }

                try {
                    const { PlaceAutocompleteElement } = await google.maps.importLibrary('places');
                    const placeAutocomplete = new PlaceAutocompleteElement({});
                    const placeholder =
                        input.getAttribute('placeholder') || config.placeholder || 'Start typing an address...';

                    placeAutocomplete.classList.add('crm-place-autocomplete');
                    placeAutocomplete.placeholder = placeholder;

                    input.type = 'hidden';
                    input.tabIndex = -1;
                    input.setAttribute('aria-hidden', 'true');

                    wrapper.appendChild(placeAutocomplete);
                    instance.placeAutocomplete = placeAutocomplete;

                    if ($address.val()) {
                        placeAutocomplete.value = $address.val();
                    }

                    placeAutocomplete.addEventListener('input', function () {
                        $address.val(instance.readAddressFromWidget());
                    });

                    placeAutocomplete.addEventListener('gmp-select', async function (event) {
                        const placePrediction = event.placePrediction;
                        if (!placePrediction) {
                            instance.setHint('Could not resolve that address. Try another suggestion or set the marker manually.');
                            return;
                        }

                        const place = placePrediction.toPlace();
                        await place.fetchFields({
                            fields: ['displayName', 'formattedAddress', 'location'],
                        });

                        if (!place.location) {
                            instance.setHint('Could not resolve that address. Try another suggestion or set the marker manually.');
                            return;
                        }

                        const formatted =
                            place.formattedAddress || place.displayName || instance.readAddressFromWidget();
                        instance.setAddressValue(formatted);

                        const placeLat = place.location.lat();
                        const placeLng = place.location.lng();
                        instance.handleLocationUpdate(placeLat, placeLng, false);
                        instance.setHint('Location pinned. Drag the marker to update the address.');
                    });

                    instance.bindFormSync();
                } catch (error) {
                    console.error('Place autocomplete failed to initialize', error);
                    input.type = 'text';
                    input.removeAttribute('aria-hidden');
                    input.tabIndex = 0;
                }
            },
            useCurrentLocation: function () {
                if (!navigator.geolocation) {
                    instance.setHint('Geolocation is not supported in this browser.');
                    return;
                }

                instance.setHint('Getting your current location…');

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        instance.handleLocationUpdate(
                            position.coords.latitude,
                            position.coords.longitude,
                            true
                        );
                    },
                    function (error) {
                        const message =
                            error.code === error.PERMISSION_DENIED
                                ? 'Location permission denied. Allow location access in your browser.'
                                : 'Could not detect your location. Try again or set the pin manually.';
                        instance.setHint(message);
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
                );
            },
            refreshFromInputs: function () {
                const lat = parseCoord($lat.val(), parseCoord($preview.data('default-lat'), 43.6532));
                const lng = parseCoord($lng.val(), parseCoord($preview.data('default-lng'), -79.3832));
                if ($lat.val() && $lng.val() && instance.map) {
                    instance.placeMarker(lat, lng);
                }
            },
        };

        instances[prefix] = instance;
        return instance;
    }

    function initPicker(config) {
        const instance = buildInstance(config);

        if (!config.mapsEnabled) {
            instance.initMap();
            return;
        }

        window.crmLoadGoogleMaps(config.loader, function () {
            window.crmEnsureGoogleMarkerLibrary()
                .then(function () {
                    instance.initMap();
                    void instance.initAutocomplete();
                })
                .catch(function (error) {
                    console.error('Google marker library failed to load', error);
                    instance.initMap();
                    void instance.initAutocomplete();
                });
        });
    }

    window.crmFlushAddressPickerQueue = function () {
        const queue = window.crmAddressPickerQueue || [];
        window.crmAddressPickerQueue = [];
        queue.forEach(initPicker);
    };

    window.crmRefreshAddressPicker = function (prefix) {
        const instance = getInstance(prefix);
        if (instance) {
            instance.refreshFromInputs();
        }
    };

    window.crmSetAddressPickerMarkerColor = function (prefix, color) {
        const instance = getInstance(prefix);
        if (instance) {
            instance.setMarkerColor(color);
        }
    };

    $(document).on('click', '.crm-use-current-location', function () {
        const prefix = $(this).data('picker-prefix');
        const instance = getInstance(prefix);
        if (instance) {
            instance.useCurrentLocation();
        }
    });
})(window.jQuery, window);

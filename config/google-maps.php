<?php

return [
    'default_center' => [
        'lat' => (float) env('GOOGLE_MAPS_DEFAULT_LAT', -36.8485),
        'lng' => (float) env('GOOGLE_MAPS_DEFAULT_LNG', 174.7633),
    ],

    'default_zoom' => 10,
    'detail_zoom' => 15,

    /** Google Maps JS API libraries to load (Places for autocomplete). */
    'libraries' => ['places'],
];

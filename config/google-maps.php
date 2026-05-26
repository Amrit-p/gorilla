<?php

return [
    'default_center' => [
        'lat' => (float) env('GOOGLE_MAPS_DEFAULT_LAT', 43.6532),
        'lng' => (float) env('GOOGLE_MAPS_DEFAULT_LNG', -79.3832),
    ],

    'default_zoom' => 10,
    'detail_zoom' => 15,

    /** Google Maps JS API libraries to load (Places for autocomplete). */
    'libraries' => ['places'],
];

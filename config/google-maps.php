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

    /**
     * Map ID from Google Cloud Console (required for vector maps + POI hiding via featureLayer).
     * Set GOOGLE_MAPS_MAP_ID in .env and enable vector rendering + POI feature layer in Cloud Console.
     */
    'map_id' => env('GOOGLE_MAPS_MAP_ID', ''),
];

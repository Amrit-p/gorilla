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
     * Limits Places autocomplete suggestions to these CLDR region codes.
     * Comma-separated, max 15 codes; set to an empty string to search worldwide.
     */
    'region_codes' => env('GOOGLE_MAPS_REGION_CODES', 'nz'),

    /**
     * Map ID from Google Cloud Console (required for vector maps + POI hiding via featureLayer).
     * Set GOOGLE_MAPS_MAP_ID in .env and enable vector rendering + POI feature layer in Cloud Console.
     */
    'map_id' => env('GOOGLE_MAPS_MAP_ID', ''),
];

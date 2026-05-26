<?php

return [
    'disk' => 'public',

    /** Max upload size per file in kilobytes (10 MB). */
    'max_upload_kb' => 10240,

    /** Max files per single upload request. */
    'max_files_per_request' => 6,

    /** Max stored images per job per kind (before / after). */
    'max_per_kind' => 50,

    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ],

    'full' => [
        'max_width' => 1920,
        'max_height' => 1920,
        'jpeg_quality' => 75,
    ],

    'thumb' => [
        'max_width' => 320,
        'max_height' => 320,
        'jpeg_quality' => 70,
    ],
];

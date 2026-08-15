<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vite Configuration
    |--------------------------------------------------------------------------
    |
    | Laravel will look for your manifest file to load the assets generated
    | by Vite. By default, the manifest file and assets live inside the
    | "public/build" directory, but you may change that here if needed.
    |
    */

    'manifest_path' => public_path('build/manifest.json'),

    'build_path' => public_path('build'),

    // Force production to always use built assets even if a stale Vite "hot" file exists.
    // This prevents the app from trying to load the local dev server URL (e.g., http://[::1]:5173).
    'hot_file' => strcasecmp(env('APP_ENV', 'production'), 'production') === 0
        ? storage_path('framework/vite.hot')
        : public_path('hot'),

    'dev_server' => [        'url' => env('VITE_DEV_SERVER_URL', 'http://localhost:5173'),
        'enabled' => env('VITE_DEV_SERVER', false),
    ],
];

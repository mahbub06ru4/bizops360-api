<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The API is a stateless bearer-token API (Sanctum tokens, not cookies), so
    | credentials are never shared cross-origin. CORS only matters here for a
    | browser-based client (the Flutter web build, /docs/api). Restrict with
    | CORS_ALLOWED_ORIGINS in production; leave it empty locally to allow any
    | origin during development.
    |
    */

    'paths' => ['api/*', 'docs/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')))) ?: ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];

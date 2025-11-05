<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    ['api/*', 'sanctum/csrf-cookie', 'razorpay/webhook'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'Access-Control-allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000', 'http://localhost:4000')),
    'allowed_headers' => env('CORS_ALLOWED_HEADERS', '*') === '*' ? ['*'] : explode(',', env('CORS_ALLOWED_HEADERS', 'Content-Type,Authorization,X-Requested-With,Origin,Accept')),
    'exposed_headers' => env('CORS_EXPOSED_HEADERS', '') === '' ? [] : explode(',', env('CORS_EXPOSED_HEADERS', '')),
    'max_age' => 0,
    'supports_credentials' => true, // true if you use cookie-based auth/CSRF

];

<?php

return [
    // Apply to API endpoints and the Sanctum handshake
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Exact origins used by the dev app (include scheme + port)
    'allowed_origins' => [
        'https://app.zeroday.test:5173',
        'https://app.zeroday.test:5174', // only if you actually use :5174
        'http://localhost:5173',         // optional if you serve app via http localhost
        'http://localhost:5174',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    // Allow cookies/credentialed requests
    'supports_credentials' => true,
];

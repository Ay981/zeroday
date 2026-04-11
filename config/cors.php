<?php

return [
    // Apply to all API endpoints and the Sanctum handshake
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'], // Allow GET, POST, PUT, DELETE

    'allowed_origins' => ['http://localhost:5174'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Allow all headers (like Authorization)

    'exposed_headers' => [],

    'max_age' => 86400,

    // Set to true to allow cross-site auth/cookies
    'supports_credentials' => true,
];

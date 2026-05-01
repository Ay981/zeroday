<?php

return [
    // Apply to API endpoints and the Sanctum handshake
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Exact origins used by the dev app (include scheme + port)
  'allowed_origins' => [
    'https://app.zeroday.test:5173',
    'http://localhost:5173',
    'https://zeroday.aymenabdulkerim.dev', // REMOVED THE TRAILING SLASH
],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    // Allow cookies/credentialed requests
    'supports_credentials' => true,
];

<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://miproyecto.local', 'http://127.0.0.1', 'http://localhost', 'http://localhost:5173', 'http://localhost:5174', 'http://10.10.10.192', 'http://sistemasalas.com', 'http://www.sistemasalas.com'],
    'supports_credentials' => true,
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
];

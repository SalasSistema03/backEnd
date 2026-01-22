<?php

return [
    'database' => [
        'driver' => env('DB_TURNOS_DRIVER', 'mysql'),
        'url' => env('DB_TURNOS_URL'),
        'host' => env('DB_TURNOS_HOST', '127.0.0.1'),
        'port' => env('DB_TURNOS_PORT', '3306'),
        'database' => env('DB_TURNOS_DATABASE', 'turnos'),
        'username' => env('DB_TURNOS_USERNAME', 'root'),
        'password' => env('DB_TURNOS_PASSWORD', ''),
        'unix_socket' => env('DB_TURNOS_SOCKET', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        ]) : [],
    ],
];

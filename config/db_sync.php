<?php

// config for Teguh02/FilamentDbSync
return [
    'exclude_tables' => [
        'migrations',
        'password_resets',
    ],
    'sync_host' => env('SYNC_HOST', 'http://laravel-lain.com'),
    'sync_route' => env('SYNC_ROUTE', '/filament-db-sync'),
    'auth_token' => env('SYNC_TOKEN', 'default_token'),
];
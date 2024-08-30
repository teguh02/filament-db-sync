<?php

// config for Teguh02/FilamentDbSync
return [
    'exclude_tables' => [
        'migrations',
        'password_resets',
        'filament_db_sync_table',
        'password_reset_tokens',
        'failed_jobs',
        'jobs',
        'sessions',
        'cache',
        'cache_locks',
        'job_batches',

        // Sqlite default tables
        'sqlite_sequence',

        // Add your tables here
    ],
    
    'sync_host' => env('SYNC_HOST', 'http://laravel-lain.com'),
    'sync_route' => env('SYNC_ROUTE', '/filament-db-sync'),
    'auth_token' => env('SYNC_TOKEN', 'default_token'),
];
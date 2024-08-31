<?php

// config for Teguh02/FilamentDbSync
return [

    /**
     * The host where the data will be synced
     */
    'sync_host' => env('SYNC_HOST', 'http://localhost'),

    /**
     * The token to be used for authentication
     */
    'auth_token' => env('SYNC_TOKEN', 'default_token'),
];

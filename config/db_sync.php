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

    /**
     * Models configuration
     */
    'models' => [
        /**
         * If set to true, the package will
         * automatically scan all models in the app/Models directory
         */
        'auto_scan' => env('AUTO_SCAN_MODELS', true),

        /**
         * If auto_scan is set to true,
         * this configuration will be used to exclude models
         * which will not be synced
         */
        'excluded' => [
            // App\Models\User::class,
        ],

        /**
         * When auto_scan is set to false,
         * this configuration will be used to include models
         */
        'included' => [
            // App\Models\User::class,
        ],

        /**
         * The column to be used as the key
         * when syncing data
         */
        'column_as_key' => [
            // class => column
            App\Models\User::class => 'email',

            // or you can use the table name
            // table_name => column
            // 'users' => 'email',
        ],
    ],

    /**
     * Sync configuration
     */
    'sync' => [
        /**
         * The action to be taken when there is duplicate data
         *
         * Available options:
         * - update : update the existing data
         * - duplicate : create a new data with the same data
         */
        'duplicate_data_action' => env('DUPLICATE_DATA_ACTION', 'update'),
    ],
];

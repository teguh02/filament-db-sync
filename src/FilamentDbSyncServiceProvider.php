<?php

namespace Teguh02\FilamentDbSync;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
class FilamentDbSyncServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // publish vendor command
        // php artisan vendor:publish --provider="Teguh02\FilamentDbSync\FilamentDbSyncServiceProvider" --tag="config"
        // php artisan vendor:publish --provider="Teguh02\FilamentDbSync\FilamentDbSyncServiceProvider" --tag="migrations"
        // or
        // php artisan vendor:publish --provider="Teguh02\FilamentDbSync\FilamentDbSyncServiceProvider"

        //Register Config file
        $this->mergeConfigFrom(
            __DIR__.'/../config/db_sync.php',
            'db_sync'
        );

        // publish the db_sync config file
        $this->publishes([
            __DIR__.'/../config/db_sync.php' => config_path('db_sync.php'),
        ], 'config');
        
        // publish the create_db_sync_table.php migration file to create the sync_logs table
        $this->publishes([
            __DIR__.'/../database/migrations/create_db_sync_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_db_sync_table.php'),
        ], 'migrations');

        // register the db_sync api routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        if (empty(env('SYNC_TOKEN'))) {
            // append SYNC_TOKEN env variable to .env file
            // and random string for the auth token
            file_put_contents(
                base_path('.env'),
                PHP_EOL.'SYNC_TOKEN='.Str::random(60),
                FILE_APPEND
            );
        }

        if (empty(env('SYNC_HOST'))) {
            // append SYNC_HOST env variable to .env file
            // and set blank for the sync host
            file_put_contents(
                base_path('.env'),
                PHP_EOL.'SYNC_HOST=http://your_sync_host_direction',
                FILE_APPEND
            );
        }
    }

    public function boot(): void
    {
        //you boot methods here
    }
}

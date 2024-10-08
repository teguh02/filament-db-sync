<?php

namespace Teguh02\FilamentDbSync\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Teguh02\FilamentDbSync\FilamentDbSync;
use Teguh02\FilamentDbSync\Models\DbSync;
use Teguh02\FilamentDbSync\Services\ModelsServices;

class SyncTableFromServerJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public $timeout = 0; // maximum number of seconds the job is allowed to run

    public $tries = 3; // maximum number of attempts

    public $maxExceptions = 3; // maximum number of exceptions to handle

    public $backoff = 3; // number of seconds to wait before retrying the job

    protected $plugin_ids;

    protected $models;

    protected $model_definition;

    protected $models_datas;

    protected $sync_host;

    protected $sync_config;

    protected $sync_token;

    // Used for getting data from the sync host according to the table name
    protected $api_get_data;

    public function __construct($models)
    {
        $this->plugin_ids = (new FilamentDbSync)->getId();

        $this->models = $models::class;

        // Definition : {"class":"App\\Models\\Items","table_name":"items","schema":[{"name":"name","type":"string"},{"name":"description","type":"string"},{"name":"price","type":"integer"},{"name":"stock","type":"integer"},{"name":"expired_at","type":"date"}]}
        // Datas : [{"id":9,"name":"Dr. Barney Simonis DVM","email":"krippin@hotmail.com","email_verified_at":null,"created_at":"2024-08-30T17:49:09.000000Z","updated_at":"2024-08-30T17:49:09.000000Z"},{"id":10,"name":"Lizzie Aufderhar","email":"hirthe.stanley@hill.info","email_verified_at":null,"created_at":"2024-08-30T17:49:09.000000Z","updated_at":"2024-08-30T17:49:09.000000Z"},{"id":11,"name":"Anastasia Davis","email":"bradley.doyle@schiller.net","email_verified_at":null,"created_at":"2024-08-30T17:49:09.000000Z","updated_at":"2024-08-30T17:49:09.000000Z"},{"id":6,"name":"Hyman Graham","email":"weldon02@yahoo.com","email_verified_at":null,"created_at":"2024-08-30T17:49:08.000000Z","updated_at":"2024-08-30T17:49:08.000000Z"},{"id":7,"name":"Thurman Douglas","email":"hallie.cremin@mayer.biz","email_verified_at":null,"created_at":"2024-08-30T17:49:08.000000Z","updated_at":"2024-08-30T17:49:08.000000Z"},{"id":8,"name":"Mrs. Margaret Lang","email":"watsica.cassandre@ortiz.com","email_verified_at":null,"created_at":"2024-08-30T17:49:08.000000Z","updated_at":"2024-08-30T17:49:08.000000Z"},{"id":2,"name":"Novella Hudson","email":"uhackett@emard.com","email_verified_at":null,"created_at":"2024-08-30T17:49:07.000000Z","updated_at":"2024-08-30T17:49:07.000000Z"},{"id":3,"name":"Karley Schmitt","email":"buster59@gmail.com","email_verified_at":null,"created_at":"2024-08-30T17:49:07.000000Z","updated_at":"2024-08-30T17:49:07.000000Z"},{"id":4,"name":"Christop Johnston II","email":"johann02@mante.com","email_verified_at":null,"created_at":"2024-08-30T17:49:07.000000Z","updated_at":"2024-08-30T17:49:07.000000Z"},{"id":5,"name":"Delores O'Hara","email":"vbernier@gmail.com","email_verified_at":null,"created_at":"2024-08-30T17:49:07.000000Z","updated_at":"2024-08-30T17:49:07.000000Z"},{"id":1,"name":"Admin","email":"admin@gmail.com","email_verified_at":null,"created_at":"2024-08-30T14:11:52.000000Z","updated_at":"2024-08-30T14:11:52.000000Z"}]
        $this->model_definition = ModelsServices::modelsTableSchemaDefinition($this->models);
        $this->models_datas = ModelsServices::getDatas($this->models);

        $this->sync_host = Config::get('db_sync.sync_host');
        $this->sync_token = Config::get('db_sync.auth_token');
        $this->sync_config = Config::get('db_sync.sync');

        $this->api_get_data = '/' . str_replace(config('app.url'), '', Route::getRoutes()->getByName('api.filament-db-get-data')->uri());
    }

    public function handle()
    {
        // Get table name
        $table_name = (new $this->models)->getTable();

        // Get the data from the sync host
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->sync_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($this->sync_host . $this->api_get_data, [
            'table_name' => $table_name,
        ])->json();

        if (isset($response['status']) && $response['status'] == 'Data retrieved') {
            if (isset($response['data'])) {
                // Save the data to the database
                ModelsServices::storeDataToDatabase(
                    $response['primary_key'],
                    $this->model_definition,
                    $response['data'],
                    $this->plugin_ids,
                    $this->sync_config
                );
            }
        }

        Log::info('[' . $this->plugin_ids . '] Models received: ' . $this->models);
        Log::info('[' . $this->plugin_ids . '] Model definition received: ' . json_encode($this->model_definition));
        Log::info('[' . $this->plugin_ids . '] Models datas received: ' . json_encode($this->models_datas));

        // Store the success job to the database
        DbSync::create([
            'model' => $this->models,
            'model_id' => null,
            'action' => 'pull',
            'data' => json_encode($this->models_datas),
            'status' => 'success',
            'failed_at' => null,
            'failed_reason' => null,
        ]);
    }

    public function failed($exception)
    {
        // Store the failed job to the database
        DbSync::create([
            'model' => $this->models,
            'model_id' => null,
            'action' => 'push',
            'data' => json_encode($this->models_datas),
            'status' => 'failed',
            'failed_at' => now(),
            'failed_reason' => $exception->getMessage() . ' ' . $exception->getTraceAsString(),
        ]);

        // Log the error
        Log::error('[' . $this->plugin_ids . '] ' . $exception->getMessage() . ' ' . $exception->getTraceAsString());
    }
}

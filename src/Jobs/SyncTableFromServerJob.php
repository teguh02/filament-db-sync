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
        ]) -> json();

        if (isset($response['status']) && $response['status'] == 'Data retrieved') {
            if (isset($response['data'])) {
                ModelsServices::storeDataToDatabase($this->model_definition, $response['data'], $this->plugin_ids, $this->sync_config);
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
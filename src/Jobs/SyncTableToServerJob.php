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

class SyncTableToServerJob implements ShouldQueue
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

    protected $sync_token;

    // Used for receiving data from the sync host and saving it to the database
    protected $api_receive;

    public function __construct($models)
    {
        $this->plugin_ids = (new FilamentDbSync)->getId();

        $this->models = $models::class;
        $this->model_definition = ModelsServices::modelsTableSchemaDefinition($this->models);
        $this->models_datas = ModelsServices::getDatas($this->models);

        $this->sync_host = Config::get('db_sync.sync_host');
        $this->sync_token = Config::get('db_sync.auth_token');

        $this->api_receive = '/' . str_replace(config('app.url'), '', Route::getRoutes()->getByName('api.filament-db-receive')->uri());
    }

    public function handle()
    {
        try {
            // Send the data to the sync host
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->sync_token,
                'Content-Type' => 'application/json',
            ])->post($this->sync_host . $this->api_receive, [
                'model' => $this->models,
                'model_definition' => $this->model_definition,
                'models_datas' => $this->models_datas,
            ]);

            // Store the success job to the database
            DbSync::create([
                'model' => $this->models,
                'model_id' => null,
                'action' => 'push',
                'data' => json_encode($this->models_datas),
                'status' => 'success',
                'failed_at' => null,
                'failed_reason' => null,
            ]);

            // Store to logs
            Log::info('[' . $this->plugin_ids . '] Models : ' . $this->models);
            Log::info('[' . $this->plugin_ids . '] Model Definition : ' . json_encode($this->model_definition));
            Log::info('[' . $this->plugin_ids . '] Models Datas : ' . json_encode($this->models_datas));
            Log::info('[' . $this->plugin_ids . '] Sync Host : ' . $this->sync_host);
            Log::info('[' . $this->plugin_ids . '] Sync Token : ' . $this->sync_token);
            Log::info('[' . $this->plugin_ids . '] API Receive : ' . $this->api_receive);
            Log::info('[' . $this->plugin_ids . '] Response : ' . $response->body());

        } catch (\Throwable $th) {
            // Store the failed job to the database
            DbSync::create([
                'model' => $this->models,
                'model_id' => null,
                'action' => 'push',
                'data' => json_encode($this->models_datas),
                'status' => 'failed',
                'failed_at' => now(),
                'failed_reason' => $th->getMessage() . ' ' . $th->getTraceAsString(),
            ]);

            // Log the error
            Log::error('[' . $this->plugin_ids . '] ' . $th->getMessage() . ' ' . $th->getTraceAsString());
        }
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

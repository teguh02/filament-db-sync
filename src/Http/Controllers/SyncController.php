<?php

namespace Teguh02\FilamentDbSync\Http\Controllers;

use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Queue;
use Teguh02\FilamentDbSync\Jobs\SyncTableToServerJob;
use Teguh02\FilamentDbSync\Services\ModelsServices;
use Illuminate\Support\Facades\Log;
use Teguh02\FilamentDbSync\FilamentDbSync;
use Illuminate\Support\Facades\Config;
class SyncController extends Controller
{
    protected $plugin_ids;
    protected $sync_config;

    function __construct()
    {
        $this->plugin_ids = (new FilamentDbSync) -> getId();   
        $this->sync_config = Config::get('db_sync.sync');
    }

    public function sync()
    {
        foreach (ModelsServices::getModelsWantToBeSynced() as $models) {
            Queue::push(new SyncTableToServerJob($models));
        }

        Notification::make()
            ->title('Sync started')
            ->success()
            ->send();

        return response()->json(['status' => 'Sync started']);
    }

    public function receive(Request $request)
    {
        $authToken = $request->header('Authorization');
        if ($authToken !== 'Bearer ' . config('db_sync.auth_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $model = $request->input('model');
        $model_definition = $request->input('model_definition');
        $models_datas = $request->input('models_datas');

        // Store the data to the logs
        Log::info('[' . $this->plugin_ids . '] Models received: ' . $model);
        Log::info('[' . $this->plugin_ids . '] Model definition received: ' . json_encode($model_definition));
        Log::info('[' . $this->plugin_ids . '] Models datas received: ' . json_encode($models_datas));

        // Create table schema
        ModelsServices::createTableSchema($model_definition, $this->plugin_ids);

        // Save the data to the database
        ModelsServices::storeDataToDatabase( $model_definition, $models_datas, $this->plugin_ids, $this->sync_config);

        // Return the response
        return response()->json(['status' => 'Data received']);
    }

    public function getData(Request $request)
    {
        $authToken = $request->header('Authorization');
        if ($authToken !== 'Bearer ' . config('db_sync.auth_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

    }
}

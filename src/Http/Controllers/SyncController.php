<?php

namespace Teguh02\FilamentDbSync\Http\Controllers;

use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Queue;
use Teguh02\FilamentDbSync\Jobs\SyncTableToServerJob;
use Teguh02\FilamentDbSync\Services\ModelsServices;

class SyncController extends Controller
{
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

    }

    public function getData(Request $request)
    {
        $authToken = $request->header('Authorization');
        if ($authToken !== 'Bearer ' . config('db_sync.auth_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        
    }
}

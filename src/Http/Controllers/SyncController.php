<?php

namespace Teguh02\FilamentDbSync\Http\Controllers;

use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Teguh02\FilamentDbSync\Jobs\SyncTableJob;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Controller;
use Teguh02\FilamentDbSync\Models\DbSync;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    public function sync()
    {

        $tablesToSync = array_diff($tables, $excludeTables);

        foreach ($tablesToSync as $table) {
            Queue::push(new SyncTableJob($table));
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
}

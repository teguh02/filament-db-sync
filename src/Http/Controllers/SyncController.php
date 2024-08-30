<?php

namespace Teguh02\FilamentDbSync\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Teguh02\FilamentDbSync\Jobs\SyncTableJob;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Controller;

class SyncController extends Controller
{
    public function sync()
    {
        $excludeTables = config('db_sync.exclude_tables');
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        $tablesToSync = array_diff($tables, $excludeTables);

        foreach ($tablesToSync as $table) {
            Queue::push(new SyncTableJob($table));
        }

        Notification::make()
            ->title('Sinkronisasi dimulai')
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

        $table = $request->input('table');
        $csvData = base64_decode($request->input('data'));

        $lines = explode("\n", $csvData);
        $header = str_getcsv(array_shift($lines));
        $data = array_map('str_getcsv', $lines);

        DB::beginTransaction();
        try {
            if (!Schema::hasTable($table)) {
                Schema::create($table, function ($tableSchema) use ($header) {
                    foreach ($header as $column) {
                        $tableSchema->string($column)->nullable();
                    }
                });
            }

            foreach ($data as $row) {
                DB::table($table)->insert(array_combine($header, $row));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'Data received and processed']);
    }
}

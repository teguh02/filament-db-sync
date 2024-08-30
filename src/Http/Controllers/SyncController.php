<?php

namespace Teguh02\FilamentDbSync\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Teguh02\FilamentDbSync\Jobs\SyncTableJob;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Controller;
use Teguh02\FilamentDbSync\Models\DbSync;

class SyncController extends Controller
{
    public function sync()
    {
        $excludeTables = config('db_sync.exclude_tables');
        $all_tables = [];

        switch (config('database.connections.' . config('database.default') . '.driver')) {
            case 'mysql':
            case 'mariadb':
                default:
                $all_tables = DB::select('SHOW TABLES');
                break;
                
            case 'pgsql':
                $all_tables = DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'");
                break;

            case 'sqlite':
                $all_tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
                break;

            case 'sqlsrv':
                $all_tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
                break;
        }

        $tables = collect($all_tables)->map(function ($table) {
            return reset($table);
        }) -> toArray();

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

        $table = $request->input('table');
        $csvData = base64_decode($request->input('data'));

        $lines = explode("\n", $csvData);
        $header = str_getcsv(array_shift($lines));
        $data = array_map('str_getcsv', $lines);

        DB::beginTransaction();
        try {
            // If table does not exist, create it
            if (!Schema::hasTable($table)) {
                Schema::create($table, function ($tableSchema) use ($header) {
                    foreach ($header as $column) {
                        $tableSchema->string($column)->nullable();
                    }
                });
            } 

            // Get all columns in the table
            $columns = Schema::getColumnListing($table);

            // Insert data according to the columns in the table
            foreach ($data as $row) {
                $rowData = [];
                foreach ($columns as $column) {
                    $rowData[$column] = $row[array_search($column, $header)] ?? 0;
                }

                // Remove empty values
                $rowData = array_filter($rowData, function ($value) {
                    return !in_array($value, ['NULL', 'null', '', 0, null]);
                });

                DB::table($table)->insert($rowData);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            DbSync::create([
                'model' => 'table',
                'model_id' => $table,
                'action' => 'pull',
                'data' => $csvData,
                'status' => 'failed',
                'failed_at' => now(),
                'failed_reason' => $e->getMessage() . ' ' . $e->getTraceAsString(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'Data received and processed']);
    }
}

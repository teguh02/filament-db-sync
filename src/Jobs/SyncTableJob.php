<?php

namespace Teguh02\FilamentDbSync\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Teguh02\FilamentDbSync\Models\DbSync;
class SyncTableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $timeout = 0; // maximum number of seconds the job is allowed to run
    public $tries = 3; // maximum number of attempts
    public $maxExceptions = 3; // maximum number of exceptions to handle
    public $backoff = 3; // number of seconds to wait before retrying the job

    protected $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function handle()
    {
        // Get all data from the table
        $data = DB::table($this->table)
                        // ->whereNull('deleted_at') // Uncomment this line if you want to sync only non-deleted data
                        ->get()
                        ->toArray();

        // Get table schema
        // and the data type
        $schema = DB::select("SHOW COLUMNS FROM $this->table");

        dd($schema);

        try {
            // Send data to the sync host
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('db_sync.auth_token'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(config('db_sync.sync_host') . config('db_sync.sync_route'), [
                'table' => $this->table,
                'data' => json_encode($data),
                'primaryKey' => config('db_sync.manual_primary_key.' . $this->table, config('db_sync.default_primary_key')),
            ]);

            if ($response->failed()) {
                throw new \Exception($response->body());
            } else {
                DbSync::create([
                    'model' => 'table',
                    'model_id' => $this->table,
                    'action' => 'push',
                    'data' => json_encode($data),
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }
        } catch (\Throwable $th) {
            DbSync::create([
                'model' => 'table',
                'model_id' => $this->table,
                'action' => 'push',
                'data' => json_encode($data),
                'status' => 'failed',
                'failed_at' => now(),
                'failed_reason' => $th->getMessage() . ' ' . $th->getTraceAsString(),
            ]);

            return false;
        }
    }

    public function failed($exception)
    {
        DbSync::create([
            'model' => 'table',
            'model_id' => $this->table,
            'action' => 'push',
            'data' => json_encode([]),
            'status' => 'failed',
            'failed_at' => now(),
            'failed_reason' => $exception->getMessage() . ' ' . $exception->getTraceAsString(),
        ]);
    }
}

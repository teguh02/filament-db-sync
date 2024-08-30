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
        $data = DB::table($this->table)->get()->toArray();
        $header = array_keys((array) $data[0]);

        $csvData = implode(',', $header) . "\n";

        try {
            foreach ($data as $row) {
                $csvData .= implode(',', array_map([$this, 'escapeCSVValue'], (array) $row)) . "\n";
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('db_sync.auth_token'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(config('db_sync.sync_host') . config('db_sync.sync_route'), [
                'table' => $this->table,
                'data' => base64_encode($csvData),
            ]);

            if ($response->failed()) {
                throw new \Exception($response->body());
            } else {
                DbSync::create([
                    'model' => 'table',
                    'model_id' => $this->table,
                    'action' => 'push',
                    'data' => $csvData,
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }
        } catch (\Throwable $th) {
            DbSync::create([
                'model' => 'table',
                'model_id' => $this->table,
                'action' => 'push',
                'data' => $csvData,
                'status' => 'failed',
                'failed_at' => now(),
                'failed_reason' => $th->getMessage() . ' ' . $th->getTraceAsString(),
            ]);

            return false;
        }
    }

    protected function escapeCSVValue($value)
    {
        return str_replace(["'", "\n", "\r"], ["''", "\\n", "\\r"], $value);
    }

    public function failed($exception)
    {
        DbSync::create([
            'model' => 'table',
            'model_id' => $this->table,
            'action' => 'push',
            'data' => '',
            'status' => 'failed',
            'failed_at' => now(),
            'failed_reason' => $exception->getMessage() . ' ' . $exception->getTraceAsString(),
        ]);
    }
}

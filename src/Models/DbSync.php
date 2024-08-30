<?php

namespace Teguh02\FilamentDbSync\Models;

class DbSync extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'filament_db_sync_table';

    protected $fillable = [
        'model',
        'model_id',
        'action',
        'data',
        'status',
        'completed_at',
        'failed_at',
        'failed_reason',
    ];
}
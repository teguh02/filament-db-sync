<?php

use Illuminate\Support\Facades\Route;
use Teguh02\FilamentDbSync\Http\Controllers\SyncController;

// Used for receiving data from the sync host and saving it to the database
Route::post('/api/filament-db-receive', [SyncController::class, 'receive']) 
    ->name('api.filament-db-receive');

// Used for getting data from the sync host according to the table name
Route::post('/api/filament-db-get-data', [SyncController::class, 'getData']) 
    ->name('api.filament-db-get-data');
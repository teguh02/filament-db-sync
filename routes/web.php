<?php

use Illuminate\Support\Facades\Route;
use Teguh02\FilamentDbSync\Http\Controllers\SyncController;

Route::post('/filament-db-sync', [SyncController::class, 'receive']) // This is the route that will receive the data from the client
    ->name('filament.sync.receive');
<?php

use Illuminate\Support\Facades\Route;
use Teguh02\FilamentDbSync\Http\Controllers\SyncController;

Route::post('/api/filament-db-sync', [SyncController::class, 'receive']) 
    ->name('api.filament-db-sync');
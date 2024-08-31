<?php

namespace Teguh02\FilamentDbSync;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Teguh02\FilamentDbSync\Resources\SyncResource;

class FilamentDbSync implements Plugin
{
    public function getId(): string
    {
        return 'filament-db-sync';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            SyncResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static;
    }
}

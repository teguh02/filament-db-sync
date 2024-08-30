<?php

namespace Teguh02\FilamentDbSync\Resources\SyncResource\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Teguh02\FilamentDbSync\Http\Controllers\SyncController;
use Teguh02\FilamentDbSync\Resources\SyncResource;

class IndexDatabaseSync extends Page
{
    protected static string $resource = SyncResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static string $view = 'filament.pages.database-sync-page';

    public function syncDatabase()
    {
        try {
            app(SyncController::class)->sync();

            Notification::make()
                ->title('Sinkronisasi berhasil dimulai')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Sinkronisasi gagal')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }
}

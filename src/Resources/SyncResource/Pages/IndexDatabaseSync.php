<?php

namespace Teguh02\FilamentDbSync\Resources\SyncResource\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Teguh02\FilamentDbSync\Http\Controllers\SyncController;
use Teguh02\FilamentDbSync\Resources\SyncResource;

class IndexDatabaseSync extends ListRecords
{
    protected static string $resource = SyncResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncDatabase')
                ->label('Sync Database')
                ->icon('heroicon-o-arrow-path')
                ->action('syncDatabase'),
        ];
    }

    public function syncDatabase()
    {
        try {
            app(SyncController::class)->sync();

            Notification::make()
                ->title('Database synchronized')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Database sync failed')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }
}

<?php

namespace Teguh02\FilamentDbSync\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Teguh02\FilamentDbSync\Resources\SyncResource\Pages\IndexDatabaseSync;

class SyncResource extends Resource
{
    // protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    public function getTitle(): string
    {
        return 'Sync Database';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => IndexDatabaseSync::route('/'),
        ];
    }
}

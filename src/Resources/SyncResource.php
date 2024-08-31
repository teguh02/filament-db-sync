<?php

namespace Teguh02\FilamentDbSync\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Teguh02\FilamentDbSync\Models\DbSync;
use Filament\Forms\Components\Section;
use Teguh02\FilamentDbSync\Resources\SyncResource\Pages\IndexDatabaseSync;

class SyncResource extends Resource
{
    protected static ?string $model = DbSync::class;

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Database Sync';    

    protected static ?string $modelLabel = 'Database Sync';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Database Sync')
                    ->schema([
                        Forms\Components\TextInput::make('model')
                            ->label('Model'),
                            
                        Forms\Components\TextInput::make('action')
                            ->label('Action'),
                            
                        Forms\Components\TextInput::make('status')
                            ->label('Status'),
                            
                        Forms\Components\DatePicker::make('completed_at')
                            ->label('Completed At'),
                            
                        Forms\Components\DatePicker::make('failed_at')
                            ->label('Failed At'),
                            
                        Forms\Components\Textarea::make('failed_reason')
                            ->rows(4)
                            ->label('Failed Reason'),
                            
                        Forms\Components\Textarea::make('data')
                            ->rows(4)
                            ->label('Data'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('model')
                    ->label('Model'),

                TextColumn::make('data_total')
                    ->label('Data Total')
                    ->default(fn ($record) => count(json_decode($record->data, true))),

                TextColumn::make('action')
                    ->badge()
                    ->color(fn ($record) => match ($record->action) {
                        'push' => 'success',
                        'pull' => 'info',
                        default => 'neutral',
                    })
                    ->label('Action'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'neutral',
                    })
                    ->label('Status'),

                TextColumn::make('created_at')
                    ->label('Timestamp'),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
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

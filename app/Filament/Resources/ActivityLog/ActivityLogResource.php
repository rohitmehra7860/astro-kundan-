<?php

namespace App\Filament\Resources\ActivityLog;

use App\Filament\Resources\ActivityLog\Pages\ListActivities;
use App\Filament\Resources\ActivityLog\Pages\ViewActivity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Activity Logs';

    protected static ?string $modelLabel = 'Activity';

    protected static ?string $pluralModelLabel = 'Activity Logs';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('log_name')
                    ->label('Log Name')
                    ->badge()
                    ->color('info'),
                TextEntry::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                TextEntry::make('subject_type')
                    ->label('Subject Type'),
                TextEntry::make('subject_id')
                    ->label('Subject ID'),
                TextEntry::make('causer.name')
                    ->label('Caused By')
                    ->default('System'),
                TextEntry::make('created_at')
                    ->label('Date')
                    ->dateTime(),
                KeyValueEntry::make('properties')
                    ->label('Properties')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('log_name')
                    ->label('Log')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '—')
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('By')
                    ->default('System')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),
                SelectFilter::make('log_name')
                    ->label('Log Name')
                    ->options(fn () => Activity::distinct()->pluck('log_name', 'log_name')->toArray()),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'view' => ViewActivity::route('/{record}'),
        ];
    }
}

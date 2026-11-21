<?php

namespace App\Filament\Resources\Redirects\RedirectRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RedirectRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('S.No.')
                    ->rowIndex(),
                TextColumn::make('old_path')
                    ->label('Old Path')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('new_path')
                    ->label('New Path')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status_code')
                    ->label('Type')
                    ->badge()
                    ->color(fn($state) => match((int)$state) {
                        301 => 'success',
                        302 => 'warning',
                        default => 'gray',
                    })
                    ->state(fn($record) => match((int)$record->status_code) {
                        301 => '301 Permanent',
                        302 => '302 Temporary',
                        default => $record->status_code,
                    })
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger')
                    ->state(fn($record) => $record->is_active ? 'Active' : 'Inactive')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status_code')
                    ->label('Redirect Type')
                    ->options([
                        301 => '301 Permanent',
                        302 => '302 Temporary',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    protected static \UnitEnum|string|null $navigationGroup = 'Security';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique('users', 'email', ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('S.No.')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('success')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->state(fn($record) => $record->email_verified_at ? 'Verified' : 'Unverified')
                    ->badge()
                    ->color(fn($state) => $state === 'Verified' ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn($record) => !$record->email_verified_at)
                    ->requiresConfirmation()
                    ->modalHeading('Manually Verify Email')
                    ->modalDescription('This will mark the email as verified and allow the user to log in.')
                    ->action(function ($record) {
                        $record->update(['email_verified_at' => now()]);
                        Notification::make()
                            ->title('User email verified successfully.')
                            ->success()
                            ->send();
                    }),
                Action::make('unverify')
                    ->label('Unverify')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => (bool) $record->email_verified_at && auth()->id() !== $record->id)
                    ->requiresConfirmation()
                    ->modalHeading('Unverify User Email')
                    ->modalDescription('This will mark the email as unverified and prevent the user from logging in.')
                    ->action(function ($record) {
                        $record->update(['email_verified_at' => null]);
                        Notification::make()
                            ->title('User email unverified successfully.')
                            ->success()
                            ->send();
                    }),
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

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}

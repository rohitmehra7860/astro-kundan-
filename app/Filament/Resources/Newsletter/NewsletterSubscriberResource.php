<?php

namespace App\Filament\Resources\Newsletter;

use App\Models\NewsletterSubscriber;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section as InfolistSection;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';
    protected static \UnitEnum|string|null $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Newsletter Subscribers';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->columnSpanFull(),

            TextInput::make('name')
                ->maxLength(255)
                ->placeholder('Subscriber name (optional)'),

            Select::make('status')
                ->options([
                    'active'       => 'Active',
                    'unsubscribed' => 'Unsubscribed',
                ])
                ->default('active')
                ->required(),

            DateTimePicker::make('subscribed_at')
                ->label('Subscribed At')
                ->default(now()),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            InfolistSection::make('Subscriber Details')
                ->schema([
                    TextEntry::make('email')
                        ->copyable()
                        ->icon('heroicon-o-envelope'),
                    TextEntry::make('name')
                        ->placeholder('—'),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn($state) => match($state) {
                            'active'       => 'success',
                            'unsubscribed' => 'danger',
                            default        => 'gray',
                        }),
                    TextEntry::make('subscribed_at')
                        ->dateTime('d M Y, H:i'),
                    TextEntry::make('created_at')
                        ->label('Added')
                        ->dateTime('d M Y, H:i'),
                    TextEntry::make('updated_at')
                        ->label('Last Updated')
                        ->since(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'active'       => 'success',
                        'unsubscribed' => 'danger',
                        default        => 'gray',
                    }),
                TextColumn::make('subscribed_at')
                    ->label('Subscribed')
                    ->dateTime('d M Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Added')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active'       => 'Active',
                        'unsubscribed' => 'Unsubscribed',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                Action::make('unsubscribe')
                    ->label('Unsubscribe')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update(['status' => 'unsubscribed'])),
                \Filament\Actions\DeleteAction::make(),
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
            'index'  => Pages\ListNewsletterSubscribers::route('/'),
            'create' => Pages\CreateNewsletterSubscriber::route('/create'),
            'view'   => Pages\ViewNewsletterSubscriber::route('/{record}'),
            'edit'   => Pages\EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }
}

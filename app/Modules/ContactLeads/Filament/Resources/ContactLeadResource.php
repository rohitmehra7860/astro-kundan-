<?php

namespace App\Modules\ContactLeads\Filament\Resources;

use App\Modules\ContactLeads\Models\ContactLead;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactLeadResource extends Resource
{
    protected static ?string $model = ContactLead::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static \UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Contact Leads';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Contact Lead';

    protected static ?string $pluralModelLabel = 'Contact Leads';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255),

            TextInput::make('phone')
                ->tel()
                ->maxLength(20)
                ->placeholder('+1 555-123-4567'),

            TextInput::make('subject')
                ->maxLength(255)
                ->placeholder('Subject of inquiry'),

            Textarea::make('message')
                ->required()
                ->rows(5)
                ->columnSpanFull(),

            Select::make('status')
                ->options([
                    'new'      => 'New',
                    'read'     => 'Read',
                    'replied'  => 'Replied',
                    'archived' => 'Archived',
                ])
                ->default('new')
                ->required(),

            TextInput::make('source')
                ->placeholder('e.g. contact_form, landing_page')
                ->helperText('Where this lead came from'),

            Textarea::make('notes')
                ->label('Internal Notes')
                ->rows(3)
                ->placeholder('Admin notes about this lead...')
                ->columnSpanFull(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            InfolistSection::make('Lead Details')
                ->schema([
                    TextEntry::make('name')
                        ->icon('heroicon-o-user'),
                    TextEntry::make('email')
                        ->copyable()
                        ->icon('heroicon-o-envelope'),
                    TextEntry::make('phone')
                        ->icon('heroicon-o-phone')
                        ->placeholder('—'),
                    TextEntry::make('subject')
                        ->placeholder('No subject'),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn($state) => match ($state) {
                            'new'      => 'info',
                            'read'     => 'warning',
                            'replied'  => 'success',
                            'archived' => 'gray',
                            default    => 'gray',
                        }),
                    TextEntry::make('source')
                        ->placeholder('—'),
                    TextEntry::make('ip_address')
                        ->label('IP Address')
                        ->placeholder('—'),
                    TextEntry::make('created_at')
                        ->label('Received')
                        ->dateTime('d M Y, H:i'),
                    TextEntry::make('replied_at')
                        ->label('Replied At')
                        ->dateTime('d M Y, H:i')
                        ->placeholder('Not yet replied'),
                ])
                ->columns(2),
            InfolistSection::make('Message')
                ->schema([
                    TextEntry::make('message')
                        ->label('')
                        ->markdown()
                        ->columnSpanFull(),
                ]),
            InfolistSection::make('Internal Notes')
                ->schema([
                    TextEntry::make('notes')
                        ->label('')
                        ->placeholder('No notes')
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),
                TextColumn::make('subject')
                    ->limit(30)
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'new'      => 'info',
                        'read'     => 'warning',
                        'replied'  => 'success',
                        'archived' => 'gray',
                        default    => 'gray',
                    }),
                TextColumn::make('source')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'new'      => 'New',
                        'read'     => 'Read',
                        'replied'  => 'Replied',
                        'archived' => 'Archived',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                Action::make('mark_read')
                    ->label('Mark Read')
                    ->icon('heroicon-m-eye')
                    ->color('warning')
                    ->visible(fn($record) => $record->status === 'new')
                    ->action(fn($record) => $record->markAsRead()),
                Action::make('mark_replied')
                    ->label('Replied')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn($record) => in_array($record->status, ['new', 'read']))
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->markAsReplied()),
                Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-m-archive-box')
                    ->color('gray')
                    ->visible(fn($record) => $record->status !== 'archived')
                    ->action(fn($record) => $record->update(['status' => 'archived'])),
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
            'index'  => Pages\ListContactLeads::route('/'),
            'create' => Pages\CreateContactLead::route('/create'),
            'view'   => Pages\ViewContactLead::route('/{record}'),
            'edit'   => Pages\EditContactLead::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }
}

<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\BrokenLinkResource\Pages\ListBrokenLinks;
use App\Models\BrokenLink;
use App\Models\RedirectRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class BrokenLinkResource extends Resource
{
    protected static ?string $model = BrokenLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLinkSlash;

    protected static ?string $navigationLabel = 'Broken Links';

    protected static ?string $modelLabel = 'Broken Link';

    protected static ?string $pluralModelLabel = 'Broken Links';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('path')
                    ->label('Broken Path')
                    ->disabled()
                    ->columnSpanFull(),
                TextInput::make('hits_count')
                    ->label('Hits Count')
                    ->disabled(),
                TextInput::make('ip_address')
                    ->label('IP Address')
                    ->disabled(),
                TextInput::make('last_seen_at')
                    ->label('Last Seen')
                    ->formatStateUsing(fn ($state) => $state ? \Illuminate\Support\Carbon::parse($state)->format('M d, Y h:i A') . ' (' . \Illuminate\Support\Carbon::parse($state)->diffForHumans() . ')' : '')
                    ->disabled(),
                Textarea::make('referrer')
                    ->label('Referrer')
                    ->disabled()
                    ->columnSpanFull(),
                Textarea::make('user_agent')
                    ->label('User Agent')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('S.No.')
                    ->rowIndex(),
                TextColumn::make('path')
                    ->label('Broken Path')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('hits_count')
                    ->label('Hits')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                TextColumn::make('referrer')
                    ->label('Referrer')
                    ->searchable()
                    ->placeholder('Direct / No Referrer')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->referrer),
                TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->user_agent)
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_seen_at')
                    ->label('Last Seen')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('hits_count', 'desc')
            ->actions([
                ViewAction::make(),
                Action::make('create_redirect')
                    ->label('Redirect & Fix')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('success')
                    ->form([
                        TextInput::make('new_path')
                            ->label('Redirect Destination (New Path)')
                            ->required()
                            ->maxLength(2048)
                            ->placeholder('/new-url-slug or https://example.com/external-page')
                            ->helperText('The target destination. Relative paths are automatically normalized.')
                            ->columnSpanFull(),
                        Select::make('status_code')
                            ->label('Redirect Type')
                            ->options([
                                301 => '301 - Permanent Redirect (Best for SEO)',
                                302 => '302 - Temporary Redirect',
                            ])
                            ->default(301)
                            ->required()
                            ->selectablePlaceholder(false),
                    ])
                    ->action(function (BrokenLink $record, array $data): void {
                        $normalizedOld = RedirectRule::normalizePath($record->path, true);
                        
                        $exists = RedirectRule::where('old_path', $normalizedOld)->exists();
                        if ($exists) {
                            Notification::make()
                                ->title('A redirect rule for this path already exists.')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            DB::transaction(function () use ($record, $data) {
                                RedirectRule::create([
                                    'old_path' => $record->path,
                                    'new_path' => $data['new_path'],
                                    'status_code' => $data['status_code'],
                                    'is_active' => true,
                                ]);

                                $record->delete();
                            });

                            Notification::make()
                                ->title('Redirect rule created and broken link resolved.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error resolving broken link: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
            'index' => ListBrokenLinks::route('/'),
        ];
    }
}

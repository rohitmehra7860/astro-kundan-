<?php

namespace App\Filament\Resources\Comments;

use App\Models\PostComment;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section as InfolistSection;

class CommentResource extends Resource
{
    protected static ?string $model = PostComment::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static \UnitEnum|string|null $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Comments';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('post_id')
                ->label('Post')
                ->relationship('post', 'title')
                ->searchable()
                ->preload()
                ->required()
                ->columnSpanFull(),

            Select::make('parent_id')
                ->label('Reply To (Parent Comment)')
                ->relationship('parent', 'content')
                ->getOptionLabelFromRecordUsing(fn ($record) => str($record->content)->limit(60) . " — {$record->name}")
                ->searchable()
                ->preload()
                ->placeholder('None (top-level comment)')
                ->columnSpanFull(),

            TextInput::make('name')
                ->label('Author Name')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Author Email')
                ->email()
                ->required()
                ->maxLength(255),

            Textarea::make('content')
                ->label('Comment')
                ->required()
                ->rows(4)
                ->columnSpanFull(),

            Select::make('status')
                ->options([
                    'pending'  => 'Pending',
                    'approved' => 'Approved',
                    'spam'     => 'Spam',
                ])
                ->default('pending')
                ->required(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            InfolistSection::make('Comment Details')
                ->schema([
                    TextEntry::make('post.title')
                        ->label('Post'),
                    TextEntry::make('parent.content')
                        ->label('Reply To')
                        ->limit(80)
                        ->placeholder('Top-level comment'),
                    TextEntry::make('name')
                        ->label('Author'),
                    TextEntry::make('email')
                        ->label('Email')
                        ->copyable()
                        ->icon('heroicon-o-envelope'),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn($state) => match($state) {
                            'approved' => 'success',
                            'pending'  => 'warning',
                            'spam'     => 'danger',
                            default    => 'gray',
                        }),
                    TextEntry::make('created_at')
                        ->label('Date')
                        ->dateTime('d M Y, H:i'),
                ])
                ->columns(2),
            InfolistSection::make('Comment Content')
                ->schema([
                    TextEntry::make('content')
                        ->label('')
                        ->markdown()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post.title')
                    ->label('Post')
                    ->limit(40)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),
                TextColumn::make('content')
                    ->label('Comment')
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'spam'     => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('parent_id')
                    ->label('Type')
                    ->formatStateUsing(fn($state) => $state ? 'Reply' : 'Comment')
                    ->badge()
                    ->color(fn($state) => $state ? 'info' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'spam'     => 'Spam',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'approved')
                    ->action(fn($record) => $record->update(['status' => 'approved'])),
                Action::make('spam')
                    ->label('Spam')
                    ->icon('heroicon-m-no-symbol')
                    ->color('danger')
                    ->visible(fn($record) => $record->status !== 'spam')
                    ->action(fn($record) => $record->update(['status' => 'spam'])),
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
            'index'  => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'view'   => Pages\ViewComment::route('/{record}'),
            'edit'   => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\CreateMediaUpload;
use App\Filament\Resources\Media\Pages\EditMediaUpload;
use App\Filament\Resources\Media\Pages\ListMediaUploads;
use App\Filament\Resources\Media\Pages\ViewMediaUpload;
use App\Models\MediaUpload;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

use Filament\Tables\Columns\Layout\Stack;

class MediaUploadResource extends Resource
{
    protected static ?string $model = MediaUpload::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Media Library';

    protected static ?string $modelLabel = 'Media File';

    protected static ?string $pluralModelLabel = 'Media Library';

    protected static \UnitEnum|string|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->maxLength(255)
                    ->helperText('Optional. If left blank, the original filenames will be used as titles.'),
                TextInput::make('alt_text')
                    ->maxLength(255),
                
                // Multiple file upload for Create Page
                \Filament\Forms\Components\FileUpload::make('temp_files')
                    ->label('Upload Images')
                    ->multiple()
                    ->reorderable()
                    ->disk('public')
                    ->directory('temp-uploads')
                    ->image()
                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->columnSpanFull(),

                // Single file upload/editor for Edit & View Pages
                SpatieMediaLibraryFileUpload::make('gallery')
                    ->label('Image File')
                    ->collection('gallery')
                    ->image()
                    ->disk('public')
                    ->imageEditor()
                    ->required(fn ($livewire) => !($livewire instanceof \Filament\Resources\Pages\CreateRecord))
                    ->visible(fn ($livewire) => !($livewire instanceof \Filament\Resources\Pages\CreateRecord))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    SpatieMediaLibraryImageColumn::make('gallery')
                        ->collection('gallery')
                        ->conversion('thumb')
                        ->square()
                        ->size(150)
                        ->extraImgAttributes([
                            'style' => 'object-fit: cover; border-radius: 8px; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); margin: 0 auto;',
                            'class' => 'hover:scale-105 hover:shadow-lg',
                        ])
                        ->alignCenter(),
                    TextColumn::make('title')
                        ->searchable()
                        ->sortable()
                        ->weight('bold')
                        ->alignCenter()
                        ->size('sm')
                        ->description(fn ($record) => $record->alt_text, position: 'below')
                        ->extraAttributes([
                            'style' => 'margin-top: 8px; display: block; text-align: center;',
                        ]),
                ])->extraAttributes([
                    'class' => 'media-gallery-card',
                ]),
            ])
            ->contentGrid([
                'sm' => 2,
                'md' => 3,
                'lg' => 4,
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
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
            'index' => ListMediaUploads::route('/'),
            'create' => CreateMediaUpload::route('/create'),
            'view' => ViewMediaUpload::route('/{record}'),
            'edit' => EditMediaUpload::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateBlogCategory;
use App\Filament\Resources\Categories\Pages\EditBlogCategory;
use App\Filament\Resources\Categories\Pages\ListBlogCategories;
use App\Filament\Resources\Categories\Pages\ViewBlogCategory;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Filament\Shared\SeoForm;

class ServiceCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static ?string $navigationLabel = 'Service Categories';

    protected static ?string $pluralModelLabel = 'Service Categories';

    protected static ?string $modelLabel = 'Service Category';

    protected static ?string $recordTitleAttribute = 'name';

    protected static \UnitEnum|string|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'service');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => 
                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                    ),
                TextInput::make('slug')
                    ->required()
                    ->unique('categories', 'slug', ignoreRecord: true)
                    ->maxLength(255),
                ...\App\Filament\Shared\ImagePicker::make('featured_image_id', 'Category Image'),
                RichEditor::make('description')
                    ->columnSpanFull(),
                SeoForm::make(false)
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
                ImageColumn::make('featured_image_preview')
                    ->label('Image')
                    ->getStateUsing(fn ($record) => $record->featuredImage?->getFirstMediaUrl('gallery', 'thumb') ?: null)
                    ->square()
                    ->size(40),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceCategories::route('/'),
            'create' => Pages\CreateServiceCategory::route('/create'),
            'view' => Pages\ViewServiceCategory::route('/{record}'),
            'edit' => Pages\EditServiceCategory::route('/{record}/edit'),
        ];
    }
}

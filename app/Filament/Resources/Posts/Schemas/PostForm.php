<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        fn(string $operation, $state, callable $set) =>
                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                    ),
                TextInput::make('slug')
                    ->required()
                    ->unique('posts', 'slug', ignoreRecord: true)
                    ->maxLength(255),
                Select::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'published' => 'Published',
                    ])
                    ->default('draft')
                    ->selectablePlaceholder(false)
                    ->required(),
                Toggle::make('is_featured')
                    ->label('Featured Post')
                    ->helperText('Show this post in the featured section')
                    ->onIcon('heroicon-m-star')
                    ->offIcon('heroicon-m-star')
                    ->onColor('warning')
                    ->default(false),
                Toggle::make('is_pinned')
                    ->label('Pinned Post')
                    ->helperText('Pin to top of blog listing')
                    ->onIcon('heroicon-m-arrow-up-circle')
                    ->offIcon('heroicon-m-arrow-up-circle')
                    ->onColor('info')
                    ->default(false),

                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name', fn($query) => $query->where('type', 'blog'))
                    ->preload()
                    ->searchable(),
                ...\App\Filament\Shared\ImagePicker::make('featured_image_id', 'Featured Image'),
                Select::make('author_id')
                    ->relationship('author', 'name')
                    ->default(fn() => auth()->id())
                    ->preload()
                    ->searchable()
                    ->required()
                    ->columnSpan(1),
                Select::make('tags')
                    ->label('Tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(50),
                    ])
                    ->columnSpan(1),
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                \App\Filament\Shared\SeoForm::make()
                    ->columnSpanFull(),
            ]);
    }
}

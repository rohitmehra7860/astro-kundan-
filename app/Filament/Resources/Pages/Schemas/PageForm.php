<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use App\Filament\Shared\SeoForm;

class PageForm
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
                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => 
                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                    ),
                TextInput::make('slug')
                    ->required()
                    ->unique('pages', 'slug', ignoreRecord: true)
                    ->maxLength(255)
                    ->readOnly(fn ($record) => $record && $record->is_system)
                    ->helperText(fn ($record) => $record && $record->is_system
                        ? 'This is a system page slug and cannot be modified.' 
                        : null
                    ),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])
                    ->default('draft')
                    ->selectablePlaceholder(false)
                    ->required(),
                ...\App\Filament\Shared\ImagePicker::make('featured_image_id', 'Featured Image'),
                RichEditor::make('content')
                    ->columnSpanFull(),
                \Filament\Forms\Components\Builder::make('dynamic_data')
                    ->label('Dynamic Page Sections')
                    ->blocks([
                        \Filament\Forms\Components\Builder\Block::make('hero')
                            ->label('Hero Banner')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                TextInput::make('heading')->required(),
                                TextInput::make('subheading'),
                                ...\App\Filament\Shared\ImagePicker::make('image_id', 'Banner Image'),
                                TextInput::make('button_text'),
                                TextInput::make('button_link'),
                            ])
                            ->columns(2),

                        \Filament\Forms\Components\Builder\Block::make('text_image')
                            ->label('Text & Image Section')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextInput::make('title')->required(),
                                Select::make('image_position')
                                    ->options([
                                        'left' => 'Left Side',
                                        'right' => 'Right Side',
                                    ])
                                    ->default('left')
                                    ->required(),
                                ...\App\Filament\Shared\ImagePicker::make('image_id', 'Section Image'),
                                RichEditor::make('content')
                                    ->columnSpanFull()
                                    ->required(),
                            ])
                            ->columns(2),

                        \Filament\Forms\Components\Builder\Block::make('features')
                            ->label('Features Grid')
                            ->icon('heroicon-o-squares-plus')
                            ->schema([
                                TextInput::make('title')->required(),
                                TextInput::make('subtitle'),
                                \Filament\Forms\Components\Repeater::make('items')
                                    ->schema([
                                        TextInput::make('title')->required(),
                                        \Filament\Forms\Components\Textarea::make('description')->rows(2)->required(),
                                        TextInput::make('icon')->placeholder('e.g. star, shield, bolt'),
                                    ])
                                    ->columns(3)
                                    ->grid(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        \Filament\Forms\Components\Builder\Block::make('cta')
                            ->label('Call To Action (CTA)')
                            ->icon('heroicon-o-megaphone')
                            ->schema([
                                TextInput::make('title')->required(),
                                TextInput::make('subtitle'),
                                TextInput::make('button_text'),
                                TextInput::make('button_link'),
                            ])
                            ->columns(2),

                        \Filament\Forms\Components\Builder\Block::make('faq')
                            ->label('FAQ Accordion')
                            ->icon('heroicon-o-question-mark-circle')
                            ->schema([
                                TextInput::make('section_title')->default('Frequently Asked Questions'),
                                \Filament\Forms\Components\Repeater::make('faqs')
                                    ->schema([
                                        TextInput::make('question')->required(),
                                        \Filament\Forms\Components\Textarea::make('answer')->rows(2)->required(),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->addActionLabel('Add New Section')
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
                SeoForm::make()
                    ->columnSpanFull(),
            ]);
    }
}

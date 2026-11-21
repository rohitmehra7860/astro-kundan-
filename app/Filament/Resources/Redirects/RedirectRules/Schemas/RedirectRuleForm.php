<?php

namespace App\Filament\Resources\Redirects\RedirectRules\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use App\Models\RedirectRule;

class RedirectRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Redirect Rule Details')
                    ->description('Set up a 301 or 302 redirect from an old path to a new path.')
                    ->schema([
                        TextInput::make('old_path')
                            ->required()
                            ->unique('redirect_rules', 'old_path', ignoreRecord: true)
                            ->maxLength(2048)
                            ->placeholder('/old-url-slug')
                            ->helperText('The path to intercept. Relative paths are automatically normalized (e.g. /old-slug).')
                            ->rules([
                                fn (callable $get) => function (string $attribute, $value, callable $fail) use ($get) {
                                    $normalizedOld = RedirectRule::normalizePath($value);
                                    $normalizedNew = RedirectRule::normalizePath($get('new_path') ?? '');
                                    if ($normalizedOld === $normalizedNew) {
                                        $fail('The old path cannot redirect to the same new path.');
                                    }
                                },
                            ])
                            ->columnSpanFull(),

                        TextInput::make('new_path')
                            ->required()
                            ->maxLength(2048)
                            ->placeholder('/new-url-slug or https://example.com/external-page')
                            ->helperText('The target destination. Can be a relative path or a full URL.')
                            ->rules([
                                fn (callable $get) => function (string $attribute, $value, callable $fail) use ($get) {
                                    $normalizedNew = RedirectRule::normalizePath($value);
                                    $normalizedOld = RedirectRule::normalizePath($get('old_path') ?? '');
                                    if ($normalizedOld === $normalizedNew) {
                                        $fail('The new path cannot be the same as the old path.');
                                    }
                                },
                            ])
                            ->columnSpanFull(),

                        Select::make('status_code')
                            ->label('Redirect Type')
                            ->options([
                                301 => '301 - Permanent Redirect (Best for SEO)',
                                302 => '302 - Temporary Redirect',
                            ])
                            ->default(301)
                            ->selectablePlaceholder(false)
                            ->required()
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label('Is Active')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}

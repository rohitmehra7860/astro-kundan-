<?php

namespace App\Filament\Resources\Redirects\RedirectRules;

use App\Filament\Resources\Redirects\RedirectRules\Pages\CreateRedirectRule;
use App\Filament\Resources\Redirects\RedirectRules\Pages\EditRedirectRule;
use App\Filament\Resources\Redirects\RedirectRules\Pages\ListRedirectRules;
use App\Filament\Resources\Redirects\RedirectRules\Schemas\RedirectRuleForm;
use App\Filament\Resources\Redirects\RedirectRules\Tables\RedirectRulesTable;
use App\Models\RedirectRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RedirectRuleResource extends Resource
{
    protected static ?string $model = RedirectRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'URL Redirects';

    protected static ?string $modelLabel = 'Redirect Rule';

    protected static ?string $pluralModelLabel = 'Redirect Rules';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return RedirectRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RedirectRulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRedirectRules::route('/'),
            'create' => CreateRedirectRule::route('/create'),
            'edit' => EditRedirectRule::route('/{record}/edit'),
        ];
    }
}

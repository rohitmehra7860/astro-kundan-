<?php

namespace App\Filament\Resources\Redirects\RedirectRules\Pages;

use App\Filament\Resources\Redirects\RedirectRules\RedirectRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRedirectRule extends EditRecord
{
    protected static string $resource = RedirectRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getMaxContentWidth(): string
    {
        return 'full';
    }
}

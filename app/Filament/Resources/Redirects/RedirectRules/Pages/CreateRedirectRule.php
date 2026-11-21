<?php

namespace App\Filament\Resources\Redirects\RedirectRules\Pages;

use App\Filament\Resources\Redirects\RedirectRules\RedirectRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRedirectRule extends CreateRecord
{
    protected static string $resource = RedirectRuleResource::class;

    public function getMaxContentWidth(): string
    {
        return 'full';
    }
}

<?php

namespace App\Modules\ContactLeads\Filament\Resources\Pages;

use App\Modules\ContactLeads\Filament\Resources\ContactLeadResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditContactLead extends EditRecord
{
    protected static string $resource = ContactLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

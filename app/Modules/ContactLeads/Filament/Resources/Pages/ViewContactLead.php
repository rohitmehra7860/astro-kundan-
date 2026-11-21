<?php

namespace App\Modules\ContactLeads\Filament\Resources\Pages;

use App\Modules\ContactLeads\Filament\Resources\ContactLeadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContactLead extends ViewRecord
{
    protected static string $resource = ContactLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

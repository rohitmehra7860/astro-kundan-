<?php

namespace App\Modules\ContactLeads\Filament\Resources\Pages;

use App\Modules\ContactLeads\Filament\Resources\ContactLeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContactLeads extends ListRecords
{
    protected static string $resource = ContactLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

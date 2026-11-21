<?php

namespace App\Modules\ContactLeads\Filament\Resources\Pages;

use App\Modules\ContactLeads\Filament\Resources\ContactLeadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContactLead extends CreateRecord
{
    protected static string $resource = ContactLeadResource::class;
}

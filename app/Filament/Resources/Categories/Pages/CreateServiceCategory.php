<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\ServiceCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceCategory extends CreateRecord
{
    protected static string $resource = ServiceCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'service';
        return $data;
    }
}

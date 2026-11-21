<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\ServiceCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceCategories extends ListRecords
{
    protected static string $resource = ServiceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\ServiceCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceCategory extends ViewRecord
{
    protected static string $resource = ServiceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}

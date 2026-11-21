<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\ServiceCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceCategory extends EditRecord
{
    protected static string $resource = ServiceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type'] = 'service';
        return $data;
    }
}

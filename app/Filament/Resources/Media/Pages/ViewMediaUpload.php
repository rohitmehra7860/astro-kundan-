<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaUploadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMediaUpload extends ViewRecord
{
    protected static string $resource = MediaUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

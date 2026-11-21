<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaUploadResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMediaUpload extends EditRecord
{
    protected static string $resource = MediaUploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

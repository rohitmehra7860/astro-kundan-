<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaUploadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMediaUploads extends ListRecords
{
    protected static string $resource = MediaUploadResource::class;

    protected string $view = 'filament.pages.list-media';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

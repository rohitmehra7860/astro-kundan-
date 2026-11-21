<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\BlogCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBlogCategory extends ViewRecord
{
    protected static string $resource = BlogCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

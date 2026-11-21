<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\BlogCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBlogCategories extends ListRecords
{
    protected static string $resource = BlogCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

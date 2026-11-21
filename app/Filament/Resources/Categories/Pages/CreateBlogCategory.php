<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\BlogCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogCategory extends CreateRecord
{
    protected static string $resource = BlogCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'blog';
        return $data;
    }
}

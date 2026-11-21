<?php

namespace App\Filament\Resources\Redirects\BrokenLinkResource\Pages;

use App\Filament\Resources\Redirects\BrokenLinkResource;
use Filament\Resources\Pages\ListRecords;

class ListBrokenLinks extends ListRecords
{
    protected static string $resource = BrokenLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getMaxContentWidth(): string
    {
        return 'full';
    }
}

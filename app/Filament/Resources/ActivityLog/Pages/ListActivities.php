<?php

namespace App\Filament\Resources\ActivityLog\Pages;

use App\Filament\Resources\ActivityLog\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;
}

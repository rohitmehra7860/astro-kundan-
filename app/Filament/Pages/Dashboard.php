<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return collect(parent::getWidgets())
            ->filter(function ($widget) {
                $widgetClass = is_string($widget) ? $widget : get_class($widget);
                return !str_starts_with($widgetClass, 'App\\Modules\\');
            })
            ->toArray();
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\MediaUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestMediaWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Media Uploads';

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MediaUpload::query()->latest()->limit(5)
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery')
                    ->label('Preview')
                    ->collection('gallery')
                    ->conversion('thumb')
                    ->height(48),
                TextColumn::make('title')
                    ->label('Title')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('alt_text')
                    ->label('Alt Text')
                    ->color('gray')
                    ->limit(40),
                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->since()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}

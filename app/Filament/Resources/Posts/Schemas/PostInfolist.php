<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Schemas\Schema;

class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->weight('bold')
                    ->size('lg'),
                TextEntry::make('slug'),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    }),
                TextEntry::make('featured_image_preview')
                    ->label('Featured Image')
                    ->getStateUsing(fn ($record) => $record->featuredImage?->getFirstMediaUrl('gallery', 'thumb') ?: null)
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) {
                            return new \Illuminate\Support\HtmlString("<span class='text-sm text-gray-500 italic'>No image selected</span>");
                        }
                        $title = e($record->featuredImage?->title ?? 'Untitled');
                        return new \Illuminate\Support\HtmlString("
                            <div class='relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm' style='max-width: 320px; border-radius: 12px; overflow: hidden; border: 1px solid rgba(156, 163, 175, 0.2); box-shadow: 0 1px 3px rgba(0,0,0,0.05);'>
                                <div style='width: 100%; height: 160px; overflow: hidden; background-color: #f3f4f6; border-bottom: 1px solid rgba(156, 163, 175, 0.15);'>
                                    <img src='{$state}' style='width: 100%; height: 100%; object-fit: cover;' />
                                </div>
                                <div style='padding: 12px;'>
                                    <p style='font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>{$title}</p>
                                    <p style='font-size: 11px; color: #6b7280; margin: 4px 0 0 0;'>Media ID: {$record->featured_image_id}</p>
                                </div>
                            </div>
                        ");
                    }),
                TextEntry::make('author.name')
                    ->label('Author'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('content')
                    ->html()
                    ->columnSpanFull(),
            ]);
    }
}

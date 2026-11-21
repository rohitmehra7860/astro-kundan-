<?php

namespace App\Filament\Shared;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Actions\Action;
use App\Models\MediaUpload;
use Illuminate\Support\HtmlString;

class ImagePicker
{
    public static function make(string $fieldName, string $label = 'Image', $columnSpan = 1): array
    {
        $pickerName = $fieldName . '_picker';

        return [
            Hidden::make($fieldName)
                ->reactive(),
            
            Placeholder::make($pickerName)
                ->label($label)
                ->content(function ($get) use ($fieldName) {
                    $id = $get($fieldName);
                    if ($id) {
                        $media = MediaUpload::find($id);
                        $url = $media?->getFirstMediaUrl('gallery', 'thumb');
                        if ($url) {
                            return new HtmlString("
                                <div class='relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm' style='max-width: 320px; border-radius: 12px; overflow: hidden; border: 1px solid rgba(156, 163, 175, 0.2); box-shadow: 0 1px 3px rgba(0,0,0,0.05);'>
                                    <div style='width: 100%; height: 160px; overflow: hidden; background-color: #f3f4f6; border-bottom: 1px solid rgba(156, 163, 175, 0.15);'>
                                        <img src='{$url}' style='width: 100%; height: 100%; object-fit: cover;' />
                                    </div>
                                    <div style='padding: 12px;'>
                                        <p style='font-size: 14px; font-weight: 600; color: #1f2937; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>{$media->title}</p>
                                        <p style='font-size: 11px; color: #6b7280; margin: 4px 0 0 0;'>Media ID: {$media->id}</p>
                                    </div>
                                </div>
                            ");
                        }
                    }
                    return new HtmlString("<span class='text-sm text-gray-500 italic'>No image selected</span>");
                })
                ->hintActions([
                    Action::make('selectImageFromGallery')
                        ->label('Choose Image')
                        ->icon('heroicon-o-photo')
                        ->modalSubmitAction(false)
                        ->modalHeading(fn ($component) => $component->isDisabled() ? 'View Mode Only' : 'Select ' . $label)
                        ->modalWidth(fn ($component) => $component->isDisabled() ? 'md' : '5xl')
                        ->modalContent(function ($component) use ($label, $pickerName, $fieldName) {
                            if ($component->isDisabled()) {
                                return new HtmlString("
                                    <div class='p-6 text-center' style='padding: 24px; text-align: center;'>
                                        <div style='color: #f59e0b; margin-bottom: 8px;'>
                                            <svg style='width: 48px; height: 48px; margin: 0 auto;' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' />
                                            </svg>
                                        </div>
                                        <p style='font-weight: 600; margin: 8px 0 4px 0; font-size: 16px;'>You are currently viewing this record</p>
                                        <p style='color: #6b7280; font-size: 14px; margin: 0;'>Please edit the record to select or change the image.</p>
                                    </div>
                                ");
                            }
                            return view('filament.components.media-picker-modal', [
                                'statePath' => str_replace($pickerName, $fieldName, $component->getStatePath())
                            ]);
                        }),
                    Action::make('clearImage')
                        ->label('Remove')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($component, $set) use ($fieldName) {
                            if ($component->isDisabled()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Cannot remove image')
                                    ->body('Please edit the record first to make changes.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            $set($fieldName, null);
                        })
                        ->visible(fn ($get) => !empty($get($fieldName))),
                ])
                ->columnSpan($columnSpan),
        ];
    }
}

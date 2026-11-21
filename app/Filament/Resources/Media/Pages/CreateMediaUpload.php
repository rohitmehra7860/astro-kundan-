<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaUploadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaUpload extends CreateRecord
{
    protected static string $resource = MediaUploadResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $files = $data['temp_files'] ?? [];
        
        if (empty($files)) {
            $dateStr = now()->format('Y-m-d');
            $record = static::getModel()::create([
                'title' => !empty($data['title']) ? $data['title'] : "Media - {$dateStr}",
                'alt_text' => $data['alt_text'] ?? '',
            ]);
            return $record;
        }

        $firstRecord = null;
        foreach ($files as $index => $file) {
            $filename = basename($file);
            $dateStr = now()->format('Y-m-d');
            $title = !empty($data['title']) 
                ? ($index > 0 ? $data['title'] . ' - ' . ($index + 1) : $data['title']) 
                : ($index > 0 ? "Media - {$dateStr} - " . ($index + 1) : "Media - {$dateStr}");

            $record = static::getModel()::create([
                'title' => $title,
                'alt_text' => $data['alt_text'] ?? '',
            ]);

            $absolutePath = \Illuminate\Support\Facades\Storage::disk('public')->path($file);
            if (file_exists($absolutePath)) {
                $record->addMedia($absolutePath)->toMediaCollection('gallery');
            }

            if ($index === 0) {
                $firstRecord = $record;
            }
        }

        return $firstRecord;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

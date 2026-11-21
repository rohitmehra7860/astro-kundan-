<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MediaUpload;
use Illuminate\Support\Facades\DB;

class MediaUploadController extends Controller
{
    public function uploadAjax(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:10240', // Max 10MB
        ]);

        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            return DB::transaction(function () use ($request) {
                $file = $request->file('file');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $title = ucwords(str_replace(['-', '_'], ' ', $originalName));
                
                if (empty($title)) {
                    $title = 'Media - ' . now()->format('Y-m-d');
                }

                $record = MediaUpload::create([
                    'title' => $title,
                    'alt_text' => $title,
                ]);

                $record->addMedia($file)
                    ->toMediaCollection('gallery');

                $imgUrl = $record->getFirstMediaUrl('gallery', 'thumb');

                return response()->json([
                    'success' => true,
                    'id' => $record->id,
                    'title' => $record->title,
                    'url' => $imgUrl,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

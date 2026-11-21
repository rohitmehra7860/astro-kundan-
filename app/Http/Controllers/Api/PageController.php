<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Traits\HasSeoFallbacks;
use App\Traits\HasImageUrls;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    use HasSeoFallbacks, HasImageUrls;

    /**
     * Display a listing of the published pages.
     */
    public function index()
    {
        try {
            $pages = Page::where('status', 'published')
                ->with(['featuredImage', 'seoMetadata'])
                ->get()
                ->map(function ($page) {
                    return [
                        'id' => $page->id,
                        'title' => $page->title,
                        'slug' => $page->slug,
                        'featured_image_url' => $this->getMediaUrl($page->featuredImage),
                        'featured_image' => $this->resolveMedia($page->featuredImage),
                        'dynamic_data' => $page->dynamic_data,
                        'seo' => $this->getSeoFields($page),
                        'created_at' => $page->created_at,
                        'updated_at' => $page->updated_at,
                    ];
                });

            return response()->json([
                'pages' => $pages,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PageController@index: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while fetching pages.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Display the specified published page.
     */
    public function show($slug)
    {
        try {
            $page = Page::where('slug', $slug)
                ->where('status', 'published')
                ->with(['featuredImage', 'seoMetadata.ogImage'])
                ->first();

            if (!$page) {
                return response()->json([
                    'message' => 'Page not found or is currently in draft.',
                ], 404);
            }

            return response()->json([
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'content' => $page->content,
                    'featured_image_url' => $this->getMediaUrl($page->featuredImage),
                    'featured_image' => $this->resolveMedia($page->featuredImage),
                    'dynamic_data' => $page->dynamic_data,
                    'seo' => $this->getSeoFields($page),
                    'created_at' => $page->created_at,
                    'updated_at' => $page->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Error in PageController@show for slug '{$slug}': " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while loading the page.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }
}

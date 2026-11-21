<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Category;
use App\Traits\HasSeoFallbacks;
use App\Traits\HasImageUrls;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceApiController extends Controller
{
    use HasSeoFallbacks, HasImageUrls;

    /**
     * Display a listing of active published services.
     */
    public function index(Request $request)
    {
        try {
            $query = Service::where('status', 'published')
                ->with(['author', 'category.featuredImage', 'featuredImage', 'tags', 'seoMetadata']);

            if ($request->has('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->query('category'))->where('type', 'service');
                });
            }

            if ($request->has('tag')) {
                $query->whereHas('tags', function ($q) use ($request) {
                    $q->where('slug', $request->query('tag'));
                });
            }

            if ($request->boolean('featured')) {
                $query->where('is_featured', true);
            }

            $services = $query->orderByDesc('is_pinned')->latest()->paginate(15);

            $services->getCollection()->transform(function ($service) {
                $service->seo = $this->getSeoFields($service);
                if ($service->category) {
                    $service->category->seo = $this->getSeoFields($service->category);
                    $catFeaturedImage = $service->category->featuredImage;
                    $service->category->unsetRelation('featuredImage');
                    $service->category->featured_image_url = $this->getMediaUrl($catFeaturedImage);
                    $service->category->thumbnail_url = $this->getMediaUrl($catFeaturedImage, 'gallery', 'thumb');
                    $service->category->featured_image = $this->resolveMedia($catFeaturedImage);
                }
                $featuredImage = $service->featuredImage;
                $service->unsetRelation('featuredImage');
                $service->featured_image_url = $this->getMediaUrl($featuredImage);
                $service->thumbnail_url = $this->getMediaUrl($featuredImage, 'gallery', 'thumb');
                $service->featured_image = $this->resolveMedia($featuredImage);
                return $service;
            });

            return response()->json([
                'services' => $services
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ServiceApiController@index: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while fetching services.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Display the details of a single published service.
     */
    public function show($slug)
    {
        try {
            $service = Service::where('slug', $slug)
                ->where('status', 'published')
                ->with(['author', 'category.featuredImage', 'featuredImage', 'tags', 'seoMetadata.ogImage'])
                ->first();

            if (!$service) {
                return response()->json([
                    'message' => 'Service not found or is currently in draft.',
                ], 404);
            }

            $service->seo = $this->getSeoFields($service);
            if ($service->category) {
                $service->category->seo = $this->getSeoFields($service->category);
                $catFeaturedImage = $service->category->featuredImage;
                $service->category->unsetRelation('featuredImage');
                $service->category->featured_image_url = $this->getMediaUrl($catFeaturedImage);
                $service->category->thumbnail_url = $this->getMediaUrl($catFeaturedImage, 'gallery', 'thumb');
                $service->category->featured_image = $this->resolveMedia($catFeaturedImage);
            }
            $featuredImage = $service->featuredImage;
            $service->unsetRelation('featuredImage');
            $service->featured_image_url = $this->getMediaUrl($featuredImage);
            $service->thumbnail_url = $this->getMediaUrl($featuredImage, 'gallery', 'thumb');
            $service->featured_image = $this->resolveMedia($featuredImage);

            return response()->json([
                'service' => $service
            ]);
        } catch (\Exception $e) {
            Log::error("Error in ServiceApiController@show for slug '{$slug}': " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while loading the service details.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Display a listing of active published service categories.
     */
    public function categories()
    {
        try {
            $categories = Category::where('type', 'service')
                ->with(['featuredImage', 'seoMetadata'])
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'featured_image_url' => $this->getMediaUrl($category->featuredImage),
                        'featured_image' => $this->resolveMedia($category->featuredImage),
                        'seo' => $this->getSeoFields($category),
                        'created_at' => $category->created_at,
                        'updated_at' => $category->updated_at,
                    ];
                });

            return response()->json([
                'categories' => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ServiceApiController@categories: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while fetching service categories.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Traits\HasSeoFallbacks;
use App\Traits\HasImageUrls;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    use HasSeoFallbacks, HasImageUrls;

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Post::class);

            $query = Post::with(['author', 'category.featuredImage', 'category.seoMetadata.ogImage', 'featuredImage', 'tags', 'seoMetadata.ogImage']);

            if ($request->has('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->query('category'));
                });
            }

            if ($request->has('tag')) {
                $query->whereHas('tags', function ($q) use ($request) {
                    $q->where('slug', $request->query('tag'));
                });
            }

            // Featured filter
            if ($request->boolean('featured')) {
                $query->featured();
            }

            // Pinned posts always first, then latest
            if ($request->boolean('pinned')) {
                $query->pinned();
            }

            $posts = $query->orderByDesc('is_pinned')->latest()->paginate(15);

            $posts->getCollection()->transform(function ($post) {
                $post->seo = $this->getSeoFields($post);
                if ($post->category) {
                    $post->category->seo = $this->getSeoFields($post->category);
                    $catFeaturedImage = $post->category->featuredImage;
                    $post->category->unsetRelation('featuredImage');
                    $post->category->featured_image_url = $this->getMediaUrl($catFeaturedImage);
                    $post->category->thumbnail_url = $this->getMediaUrl($catFeaturedImage, 'gallery', 'thumb');
                    $post->category->featured_image = $this->resolveMedia($catFeaturedImage);
                }
                $featuredImage = $post->featuredImage;
                $post->unsetRelation('featuredImage');
                $post->featured_image_url = $this->getMediaUrl($featuredImage);
                $post->thumbnail_url = $this->getMediaUrl($featuredImage, 'gallery', 'thumb');
                $post->featured_image = $this->resolveMedia($featuredImage);
                return $post;
            });

            return response()->json([
                'posts' => $posts
            ]);
        } catch (\Exception $e) {
            Log::error('Error in BlogController@index: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while fetching posts.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('create', Post::class);

            $validated = $request->validate([
                'title'             => 'required|string|max:255',
                'slug'              => 'required|string|max:255|unique:posts,slug',
                'content'           => 'required|string',
                'status'            => 'nullable|string|in:draft,published',
                'featured_image_id' => 'nullable|integer|exists:media_uploads,id',
                'category_id'       => 'nullable|integer|exists:categories,id',
                'tags'              => 'nullable|array',
                'tags.*'            => 'integer|exists:tags,id',
            ]);

            $post = Post::create(array_merge($validated, [
                'author_id' => auth()->id(),
            ]));

            if ($request->hasFile('featured_image')) {
                $mediaUpload = \App\Models\MediaUpload::create([
                    'title' => pathinfo($request->file('featured_image')->getClientOriginalName(), PATHINFO_FILENAME),
                ]);
                $mediaUpload->addMediaFromRequest('featured_image')
                    ->toMediaCollection('gallery');
                $post->update(['featured_image_id' => $mediaUpload->id]);
            }

            if ($request->has('tags')) {
                $post->tags()->sync($request->input('tags'));
            }

            $post->load(['featuredImage', 'tags', 'seoMetadata.ogImage', 'category.featuredImage', 'category.seoMetadata.ogImage']);
            $post->seo = $this->getSeoFields($post);
            if ($post->category) {
                $post->category->seo = $this->getSeoFields($post->category);
                $catFeaturedImage = $post->category->featuredImage;
                $post->category->unsetRelation('featuredImage');
                $post->category->featured_image_url = $this->getMediaUrl($catFeaturedImage);
                $post->category->thumbnail_url = $this->getMediaUrl($catFeaturedImage, 'gallery', 'thumb');
                $post->category->featured_image = $this->resolveMedia($catFeaturedImage);
            }
            $featuredImage = $post->featuredImage;
            $post->unsetRelation('featuredImage');
            $post->featured_image_url = $this->getMediaUrl($featuredImage);
            $post->thumbnail_url = $this->getMediaUrl($featuredImage, 'gallery', 'thumb');
            $post->featured_image = $this->resolveMedia($featuredImage);

            return response()->json([
                'message' => 'Post created successfully',
                'post'    => $post
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in BlogController@store: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while creating the post.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    public function show(Post $post)
    {
        try {
            $this->authorize('view', $post);

            $post->load(['author', 'category.featuredImage', 'category.seoMetadata.ogImage', 'featuredImage', 'tags', 'seoMetadata.ogImage']);
            $post->seo = $this->getSeoFields($post);
            if ($post->category) {
                $post->category->seo = $this->getSeoFields($post->category);
                $catFeaturedImage = $post->category->featuredImage;
                $post->category->unsetRelation('featuredImage');
                $post->category->featured_image_url = $this->getMediaUrl($catFeaturedImage);
                $post->category->thumbnail_url = $this->getMediaUrl($catFeaturedImage, 'gallery', 'thumb');
                $post->category->featured_image = $this->resolveMedia($catFeaturedImage);
            }
            $featuredImage = $post->featuredImage;
            $post->unsetRelation('featuredImage');
            $post->featured_image_url = $this->getMediaUrl($featuredImage);
            $post->thumbnail_url = $this->getMediaUrl($featuredImage, 'gallery', 'thumb');
            $post->featured_image = $this->resolveMedia($featuredImage);

            return response()->json([
                'post' => $post
            ]);
        } catch (\Exception $e) {
            Log::error('Error in BlogController@show: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while loading the post.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    public function update(Request $request, Post $post)
    {
        try {
            $this->authorize('update', $post);

            $validated = $request->validate([
                'title'             => 'sometimes|required|string|max:255',
                'slug'              => 'sometimes|required|string|max:255|unique:posts,slug,' . $post->id,
                'content'           => 'sometimes|required|string',
                'status'            => 'sometimes|required|string|in:draft,published',
                'featured_image_id' => 'nullable|integer|exists:media_uploads,id',
                'category_id'       => 'sometimes|nullable|integer|exists:categories,id',
                'tags'              => 'nullable|array',
                'tags.*'            => 'integer|exists:tags,id',
            ]);

            $post->update($validated);

            if ($request->hasFile('featured_image')) {
                $mediaUpload = \App\Models\MediaUpload::create([
                    'title' => pathinfo($request->file('featured_image')->getClientOriginalName(), PATHINFO_FILENAME),
                ]);
                $mediaUpload->addMediaFromRequest('featured_image')
                    ->toMediaCollection('gallery');
                $post->update(['featured_image_id' => $mediaUpload->id]);
            }

            if ($request->has('tags')) {
                $post->tags()->sync($request->input('tags'));
            }

            $post->load(['featuredImage', 'tags', 'seoMetadata.ogImage', 'category.featuredImage', 'category.seoMetadata.ogImage']);
            $post->seo = $this->getSeoFields($post);
            if ($post->category) {
                $post->category->seo = $this->getSeoFields($post->category);
                $catFeaturedImage = $post->category->featuredImage;
                $post->category->unsetRelation('featuredImage');
                $post->category->featured_image_url = $this->getMediaUrl($catFeaturedImage);
                $post->category->thumbnail_url = $this->getMediaUrl($catFeaturedImage, 'gallery', 'thumb');
                $post->category->featured_image = $this->resolveMedia($catFeaturedImage);
            }
            $featuredImage = $post->featuredImage;
            $post->unsetRelation('featuredImage');
            $post->featured_image_url = $this->getMediaUrl($featuredImage);
            $post->thumbnail_url = $this->getMediaUrl($featuredImage, 'gallery', 'thumb');
            $post->featured_image = $this->resolveMedia($featuredImage);

            return response()->json([
                'message' => 'Post updated successfully',
                'post'    => $post
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in BlogController@update: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while updating the post.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    public function destroy(Post $post)
    {
        try {
            $this->authorize('delete', $post);

            $post->delete();

            return response()->json([
                'message' => 'Post deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in BlogController@destroy: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while deleting the post.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Return up to 4 related posts (same category first, then same tags).
     * Excludes the current post.
     */
    public function related(Post $post)
    {
        try {
            $this->authorize('view', $post);

            $tagIds = $post->tags->pluck('id');

            $related = Post::with(['featuredImage', 'category', 'tags'])
                ->where('id', '!=', $post->id)
                ->where('status', 'published')
                ->where(function ($q) use ($post, $tagIds) {
                    $q->where('category_id', $post->category_id)
                      ->orWhereHas('tags', fn($q2) => $q2->whereIn('tags.id', $tagIds));
                })
                ->latest()
                ->limit(4)
                ->get()
                ->map(function ($p) {
                    $p->seo = $this->getSeoFields($p);
                    $featuredImage = $p->featuredImage;
                    $p->unsetRelation('featuredImage');
                    $p->featured_image_url = $this->getMediaUrl($featuredImage);
                    $p->thumbnail_url      = $this->getMediaUrl($featuredImage, 'gallery', 'thumb');
                    $p->featured_image     = $this->resolveMedia($featuredImage);
                    return $p;
                });

            return response()->json(['related_posts' => $related]);
        } catch (\Exception $e) {
            Log::error('Error in BlogController@related: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Could not load related posts.'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Traits\HasSeoFallbacks;
use App\Traits\HasImageUrls;
use Illuminate\Http\Request;

class WebPageController extends Controller
{
    use HasSeoFallbacks, HasImageUrls;

    /**
     * Render the website homepage.
     */
    public function home()
    {
        // Try to fetch page with slug 'home', fallback to first published page, or a mock object
        $page = Page::where('slug', 'home')
            ->where('status', 'published')
            ->with(['featuredImage', 'seoMetadata'])
            ->first();

        if (!$page) {
            $page = Page::where('status', 'published')
                ->with(['featuredImage', 'seoMetadata'])
                ->first();
        }

        if (!$page) {
            // Create a temporary mock object if database is empty
            $page = new Page([
                'title' => 'Welcome Home',
                'slug' => 'home',
                'content' => 'Welcome to our premium corporate website dashboard backend.',
                'status' => 'published',
            ]);
        }

        $seo = $this->getSeoFields($page);

        return view('front.home', [
            'page' => $page,
            'seo' => $seo,
            'featured_image_url' => $this->getMediaUrl($page->featuredImage),
        ]);
    }

    /**
     * Render a dynamic published subpage.
     */
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->with(['featuredImage', 'seoMetadata.ogImage'])
            ->first();

        if (!$page) {
            abort(404);
        }

        $seo = $this->getSeoFields($page);

        // Prepare view data
        $data = [
            'page' => $page,
            'seo' => $seo,
            'featured_image_url' => $this->getMediaUrl($page->featuredImage),
        ];

        // Dynamically append relevant data for system pages
        if ($slug === 'blog') {
            $data['posts'] = \App\Models\Post::where('status', 'published')
                ->with(['featuredImage', 'category'])
                ->latest()
                ->get();
            $data['categories'] = \App\Models\Category::where('type', 'blog')->get();
        } elseif ($slug === 'blog-category') {
            $data['categories'] = \App\Models\Category::where('type', 'blog')
                ->with(['featuredImage'])
                ->get();
        } elseif ($slug === 'service-category') {
            $data['categories'] = \App\Models\Category::where('type', 'service')
                ->with(['featuredImage'])
                ->get();
        } elseif ($slug === 'services') {
            $data['services'] = \App\Models\Service::where('status', 'published')
                ->with(['featuredImage', 'category'])
                ->get();
        }

        // Dynamically choose view: front.{$slug} if it exists, fallback to front.show
        $viewName = view()->exists("front.{$slug}") ? "front.{$slug}" : "front.show";

        return view($viewName, $data);
    }

    /**
     * Render a dynamic published post.
     */
    public function post($slug)
    {
        $post = \App\Models\Post::where('slug', $slug)
            ->where('status', 'published')
            ->with(['featuredImage', 'seoMetadata.ogImage'])
            ->firstOrFail();

        $seo = $this->getSeoFields($post);

        return view('front.post', [
            'page' => $post,
            'seo' => $seo,
            'featured_image_url' => $this->getMediaUrl($post->featuredImage),
        ]);
    }

    /**
     * Render a dynamic category.
     */
    public function category($slug)
    {
        $category = \App\Models\Category::where('slug', $slug)
            ->with(['featuredImage', 'seoMetadata.ogImage'])
            ->firstOrFail();

        $seo = $this->getSeoFields($category);

        $data = [
            'page' => $category,
            'seo' => $seo,
            'featured_image_url' => $this->getMediaUrl($category->featuredImage),
        ];

        // Retrieve items in this category based on type
        if ($category->type === 'service') {
            $data['services'] = \App\Models\Service::where('category_id', $category->id)
                ->where('status', 'published')
                ->with(['featuredImage'])
                ->get();
        } else {
            $data['posts'] = \App\Models\Post::where('category_id', $category->id)
                ->where('status', 'published')
                ->with(['featuredImage', 'author'])
                ->latest()
                ->get();
        }

        return view('front.category', $data);
    }


    /**
     * Render single service detail page.
     */
    public function service($slug)
    {
        $service = \App\Models\Service::where('slug', $slug)
            ->where('status', 'published')
            ->with(['featuredImage', 'category', 'seoMetadata'])
            ->firstOrFail();

        $seo = $this->getSeoFields($service);

        return view('front.service', [
            'page' => $service,
            'seo' => $seo,
            'featured_image_url' => $this->getMediaUrl($service->featuredImage),
        ]);
    }
}

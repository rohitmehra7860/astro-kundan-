<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
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
        $this->setFallbackSocialImage($seo, $this->getEditorialImageUrl($page));
        $siteRoot = rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/';
        $seo['canonical_url'] = $siteRoot;
        if (!empty($seo['schema_json_ld'])) {
            $seo['schema_json_ld']['@id'] = $siteRoot . '#webpage';
            $seo['schema_json_ld']['url'] = $siteRoot;
        }
        $seo['breadcrumb_json_ld'] = null;
        $faqs = $this->getVisibleFaqs($page, $seo);
        $seo['faq_json_ld'] = $this->getFaqJsonLd($faqs);

        return view('front.home', [
            'page' => $page,
            'seo' => $seo,
            'featured_image_url' => $this->getMediaUrl($page->featuredImage),
            'services' => Service::where('status', 'published')->where('is_featured', true)->with('featuredImage')
                ->orderByRaw("FIELD(slug, 'love-relationship-guidance', 'marriage-compatibility', 'kundli-matching', 'negative-energy-guidance')")
                ->take(4)->get(),
            'posts' => Post::where('status', 'published')->with(['featuredImage', 'category'])->latest()->take(3)->get(),
            'faqs' => $faqs,
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
        $this->setFallbackSocialImage($seo, $this->getEditorialImageUrl($page));
        $faqs = $this->getVisibleFaqs($page, $seo);
        $seo['faq_json_ld'] = $this->getFaqJsonLd($faqs);

        // Prepare view data
        $data = [
            'page' => $page,
            'seo' => $seo,
            'featured_image_url' => $this->getMediaUrl($page->featuredImage),
            'faqs' => $faqs,
        ];

        // Dynamically append relevant data for system pages
        if ($slug === 'blog') {
            $data['posts'] = Post::where('status', 'published')
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
            $data['services'] = Service::where('status', 'published')
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
        $this->setFallbackSocialImage($seo, $this->getEditorialImageUrl($post));
        $faqs = $this->getVisibleFaqs($post, $seo);
        $seo['faq_json_ld'] = $this->getFaqJsonLd($faqs);

        return view('front.post', [
            'page' => $post,
            'seo' => $seo,
            'featured_image_url' => $this->getMediaUrl($post->featuredImage),
            'faqs' => $faqs,
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
        $faqs = $this->getVisibleFaqs($category, $seo);
        $seo['faq_json_ld'] = $this->getFaqJsonLd($faqs);

        $data = [
            'page' => $category,
            'seo' => $seo,
            'featured_image_url' => $this->getMediaUrl($category->featuredImage),
            'faqs' => $faqs,
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
        $this->setFallbackSocialImage($seo, $this->getEditorialImageUrl($service));
        $faqs = $this->getVisibleFaqs($service, $seo);
        $seo['faq_json_ld'] = $this->getFaqJsonLd($faqs);

        return view('front.service', [
            'page' => $service,
            'seo' => $seo,
            'featured_image_url' => $this->getMediaUrl($service->featuredImage),
            'faqs' => $faqs,
        ]);
    }

    private function getVisibleFaqs($model, array $seo): array
    {
        $faqs = $model->seoMetadata?->faqs ?? [];
        if ($model instanceof Page) {
            foreach (($model->dynamic_data ?? []) as $block) {
                if (($block['type'] ?? null) === 'faq' && is_array($block['data']['faqs'] ?? null)) {
                    $faqs = array_merge($faqs, $block['data']['faqs']);
                }
            }
        }

        return collect($faqs)->filter(fn ($faq) => !empty($faq['question']) && !empty($faq['answer']))
            ->unique(fn ($faq) => mb_strtolower(trim($faq['question'])))
            ->values()->all();
    }

    private function getEditorialImageUrl($model): ?string
    {
        $image = null;
        if ($model instanceof Page && $model->slug === 'home') {
            $image = 'images/astrologer-hero.jpg';
        } elseif ($model instanceof Post) {
            $image = [
                'what-to-expect-astrology-consultation' => 'images/blog-assets/consultation-expectations.jpg',
                'kundli-matching-compatibility-conversation' => 'images/blog-assets/kundli-compatibility.jpg',
                'thoughtful-relationship-choices' => 'images/blog-assets/relationship-reflection.jpg',
            ][$model->slug] ?? null;
        } elseif ($model instanceof Service) {
            $image = [
                'love-relationship-guidance' => 'images/services/love-relationship-guidance.jpg',
                'marriage-compatibility' => 'images/services/marriage-compatibility.jpg',
                'kundli-matching' => 'images/services/kundli-matching.jpg',
                'personal-horoscope-consultation' => 'images/services/kundli-matching.jpg',
                'vashikaran-guidance' => 'images/services/love-relationship-guidance.jpg',
                'negative-energy-guidance' => 'images/services/negative-energy-guidance.jpg',
            ][$model->slug] ?? null;
        }

        return $image ? asset($image) : null;
    }

    private function setFallbackSocialImage(array &$seo, ?string $imageUrl): void
    {
        if (empty($seo['og_image_url']) && $imageUrl) {
            $seo['og_image_url'] = $imageUrl;
            $seo['twitter_image'] = $imageUrl;
            if (!empty($seo['schema_json_ld'])) {
                $seo['schema_json_ld']['image'] = $imageUrl;
            }
        }
    }
}

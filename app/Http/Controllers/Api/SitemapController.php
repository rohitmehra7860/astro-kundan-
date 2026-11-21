<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate the dynamic XML sitemap with image support.
     */
    public function generate(): Response
    {
        $frontendUrl = env('FRONTEND_URL', config('app.url'));

        // Create XML with image sitemap namespace
        $xml = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' .
            'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"></urlset>'
        );

        // 1. Published Pages
        $pages = Page::where('status', 'published')->with('featuredImage')->get();
        foreach ($pages as $page) {
            $images = [];
            if ($page->featuredImage) {
                $images[] = [
                    'url'   => $page->featuredImage->getFirstMediaUrl('gallery'),
                    'title' => $page->featuredImage->title ?? $page->title,
                ];
            }
            $this->addUrl($xml, $this->getUrl($page, $frontendUrl), $page->updated_at, 'weekly', '0.8', $images);
        }

        // 2. Published Posts
        $posts = Post::where('status', 'published')->with('featuredImage')->get();
        foreach ($posts as $post) {
            $images = [];
            if ($post->featuredImage) {
                $images[] = [
                    'url'   => $post->featuredImage->getFirstMediaUrl('gallery'),
                    'title' => $post->featuredImage->title ?? $post->title,
                ];
            }
            $this->addUrl($xml, $this->getUrl($post, $frontendUrl), $post->updated_at, 'weekly', '0.7', $images);
        }

        // 3. Categories (Blog & Service)
        $categories = Category::with('featuredImage')->get();
        foreach ($categories as $category) {
            $images = [];
            if ($category->featuredImage) {
                $images[] = [
                    'url'   => $category->featuredImage->getFirstMediaUrl('gallery'),
                    'title' => $category->featuredImage->title ?? $category->name,
                ];
            }
            $this->addUrl($xml, $this->getUrl($category, $frontendUrl), $category->updated_at, 'weekly', '0.6', $images);
        }

        // 4. Published Services
        $services = Service::where('status', 'published')->with('featuredImage')->get();
        foreach ($services as $service) {
            $images = [];
            if ($service->featuredImage) {
                $images[] = [
                    'url'   => $service->featuredImage->getFirstMediaUrl('gallery'),
                    'title' => $service->featuredImage->title ?? $service->title,
                ];
            }
            $this->addUrl($xml, $this->getUrl($service, $frontendUrl), $service->updated_at, 'weekly', '0.8', $images);
        }

        return response($xml->asXML(), 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Format target URL based on model type.
     */
    private function getUrl($model, string $frontendUrl): string
    {
        $slug = $model->slug ?? '';
        $path = '';

        if ($model instanceof Post) {
            $path = '/blog/' . $slug;
        } elseif ($model instanceof Page) {
            $path = '/' . $slug;
        } elseif ($model instanceof Category) {
            $path = $model->type === 'service' ? '/services/category/' . $slug : '/blog/category/' . $slug;
        } elseif ($model instanceof Service) {
            $path = '/services/' . $slug;
        }

        return rtrim($frontendUrl, '/') . $path;
    }

    /**
     * Add a <url> element (with optional image children) to the XML tree.
     *
     * @param  array  $images  Each entry: ['url' => string, 'title' => string]
     */
    private function addUrl(
        \SimpleXMLElement $xml,
        string $loc,
        $lastmod,
        string $changefreq,
        string $priority,
        array $images = []
    ): void {
        $url = $xml->addChild('url');
        $url->addChild('loc', htmlspecialchars($loc));

        if ($lastmod) {
            $url->addChild('lastmod', $lastmod->tz('UTC')->toIso8601String());
        } else {
            $url->addChild('lastmod', now()->tz('UTC')->toIso8601String());
        }

        $url->addChild('changefreq', $changefreq);
        $url->addChild('priority', $priority);

        // Add image entries using the Google image sitemap extension
        foreach ($images as $image) {
            if (empty($image['url'])) {
                continue;
            }
            $imageNode = $url->addChild('image:image', '', 'http://www.google.com/schemas/sitemap-image/1.1');
            $imageNode->addChild('image:loc', htmlspecialchars($image['url']), 'http://www.google.com/schemas/sitemap-image/1.1');
            if (!empty($image['title'])) {
                $imageNode->addChild('image:title', htmlspecialchars($image['title']), 'http://www.google.com/schemas/sitemap-image/1.1');
            }
        }
    }
}

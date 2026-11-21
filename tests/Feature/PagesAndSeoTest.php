<?php

namespace Tests\Feature;

use App\Models\MediaUpload;
use App\Models\Page;
use App\Models\Post;
use App\Models\SeoMetadata;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PagesAndSeoTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->assignRole('admin');
    }

    public function test_can_list_published_pages_via_api()
    {
        $page = Page::create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'content' => 'We protect your data.',
            'status' => 'published',
        ]);

        $seo = SeoMetadata::create([
            'seoable_type' => Page::class,
            'seoable_id' => $page->id,
            'meta_title' => 'Custom Privacy Title',
            'meta_description' => 'Privacy description',
            'meta_keywords' => 'privacy, policy, data',
            'robots_instructions' => 'index, follow',
        ]);

        $response = $this->getJson('/api/pages');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'pages')
            ->assertJsonPath('pages.0.slug', 'privacy-policy')
            ->assertJsonPath('pages.0.seo.meta_title', 'Custom Privacy Title');
    }

    public function test_can_show_single_published_page_with_seo_and_og_image()
    {
        Storage::fake('public');

        $mediaUpload = MediaUpload::create([
            'title' => 'OG Image Share Banner',
        ]);
        $file = UploadedFile::fake()->image('share.jpg');
        $mediaUpload->addMedia($file)->toMediaCollection('gallery');

        $page = Page::create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => 'About our company.',
            'status' => 'published',
        ]);

        $seo = SeoMetadata::create([
            'seoable_type' => Page::class,
            'seoable_id' => $page->id,
            'meta_title' => 'About Us SEO',
            'meta_description' => 'About us SEO description',
            'og_title' => 'About Us Social Title',
            'og_description' => 'About us Social Description',
            'og_image_id' => $mediaUpload->id,
            'canonical_url' => 'https://example.com/about-us',
        ]);

        $response = $this->getJson('/api/pages/about-us');

        $response->assertStatus(200)
            ->assertJsonPath('page.title', 'About Us')
            ->assertJsonPath('page.seo.meta_title', 'About Us SEO')
            ->assertJsonPath('page.seo.og_title', 'About Us Social Title')
            ->assertJsonPath('page.seo.canonical_url', 'https://example.com/about-us');

        $this->assertNotEmpty($response->json('page.seo.og_image_url'));
    }

    public function test_cannot_view_draft_pages()
    {
        Page::create([
            'title' => 'Internal Working Draft',
            'slug' => 'internal-draft',
            'content' => 'Draft content.',
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/pages/internal-draft');

        $response->assertStatus(404);
    }

    public function test_posts_return_seo_metadata_via_api()
    {
        $post = Post::create([
            'title' => 'SEO Optimized Post',
            'slug' => 'seo-optimized-post',
            'content' => 'Post body content.',
            'status' => 'published',
            'author_id' => $this->adminUser->id,
        ]);

        SeoMetadata::create([
            'seoable_type' => Post::class,
            'seoable_id' => $post->id,
            'meta_title' => 'Post Meta Title',
            'meta_description' => 'Post Meta Description',
            'robots_instructions' => 'noindex, follow',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonPath('posts.data.0.seo.meta_title', 'Post Meta Title')
            ->assertJsonPath('posts.data.0.seo.robots_instructions', 'noindex, follow');
    }

    public function test_categories_return_seo_and_image_via_api()
    {
        Storage::fake('public');

        $mediaUpload = MediaUpload::create([
            'title' => 'Category Banner',
        ]);
        $file = UploadedFile::fake()->image('cat.png');
        $mediaUpload->addMedia($file)->toMediaCollection('gallery');

        $category = \App\Models\Category::create([
            'name' => 'SEO Category',
            'slug' => 'seo-category',
            'type' => 'blog',
            'featured_image_id' => $mediaUpload->id,
        ]);

        SeoMetadata::create([
            'seoable_type' => \App\Models\Category::class,
            'seoable_id' => $category->id,
            'meta_title' => 'Category Meta Title',
            'meta_description' => 'Category Meta Description',
        ]);

        Post::create([
            'title' => 'Post with SEO Category',
            'slug' => 'post-with-seo-category',
            'content' => 'Post body content.',
            'status' => 'published',
            'author_id' => $this->adminUser->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonPath('posts.data.0.category.name', 'SEO Category')
            ->assertJsonPath('posts.data.0.category.seo.meta_title', 'Category Meta Title');
            
        $this->assertNotEmpty($response->json('posts.data.0.category.featured_image_url'));
    }

    public function test_seo_fallbacks_generate_correctly_when_metadata_missing()
    {
        $baseUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/');

        // 1. Page fallback
        $page = Page::create([
            'title' => 'Fallback Page',
            'slug' => 'fallback-page',
            'content' => '<p>This is a <strong>cool</strong> fallback content that has HTML tags and should be stripped.</p>',
            'status' => 'published',
        ]);

        $response = $this->getJson('/api/pages/fallback-page');
        $response->assertStatus(200)
            ->assertJsonPath('page.seo.meta_title', 'Fallback Page')
            ->assertJsonPath('page.seo.meta_description', 'This is a cool fallback content that has HTML tags and should be stripped.')
            ->assertJsonPath('page.seo.canonical_url', $baseUrl . '/fallback-page')
            ->assertJsonPath('page.seo.robots_instructions', 'index, follow');

        // 2. Post fallback
        $post = Post::create([
            'title' => 'Fallback Post',
            'slug' => 'fallback-post',
            'content' => 'Plain post content.',
            'status' => 'published',
            'author_id' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->getJson('/api/posts/' . $post->id);
        $response->assertStatus(200)
            ->assertJsonPath('post.seo.meta_title', 'Fallback Post')
            ->assertJsonPath('post.seo.meta_description', 'Plain post content.')
            ->assertJsonPath('post.seo.canonical_url', $baseUrl . '/blog/fallback-post');
    }

    public function test_sitemap_xml_contains_all_published_resources()
    {
        $baseUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/');

        // Create Page, Post, Category
        $page = Page::create([
            'title' => 'Sitemap Page',
            'slug' => 'sitemap-page',
            'content' => 'Content',
            'status' => 'published',
        ]);

        // Draft Page (should NOT be in sitemap)
        $draftPage = Page::create([
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'content' => 'Content',
            'status' => 'draft',
        ]);

        $post = Post::create([
            'title' => 'Sitemap Post',
            'slug' => 'sitemap-post',
            'content' => 'Content',
            'status' => 'published',
            'author_id' => $this->adminUser->id,
        ]);

        $category = \App\Models\Category::create([
            'name' => 'Sitemap Category',
            'slug' => 'sitemap-category',
            'type' => 'blog',
        ]);

        // Create Service Category
        $serviceCategory = \App\Models\Category::create([
            'name' => 'Sitemap Service Category',
            'slug' => 'sitemap-service-category',
            'type' => 'service',
        ]);

        // Create Service
        $service = \App\Models\Service::create([
            'title' => 'Sitemap Service',
            'slug' => 'sitemap-service',
            'content' => 'Content',
            'status' => 'published',
            'author_id' => $this->adminUser->id,
            'category_id' => $serviceCategory->id,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml');

        $content = $response->getContent();
        
        $this->assertStringContainsString('<loc>' . $baseUrl . '/sitemap-page</loc>', $content);
        $this->assertStringContainsString('<loc>' . $baseUrl . '/blog/sitemap-post</loc>', $content);
        $this->assertStringContainsString('<loc>' . $baseUrl . '/blog/category/sitemap-category</loc>', $content);
        $this->assertStringContainsString('<loc>' . $baseUrl . '/services/sitemap-service</loc>', $content);
        $this->assertStringContainsString('<loc>' . $baseUrl . '/services/category/sitemap-service-category</loc>', $content);
        
        $this->assertStringNotContainsString('<loc>' . $baseUrl . '/draft-page</loc>', $content);
    }

    public function test_auth_routes_are_rate_limited()
    {
        // Try logging in with fake credentials 5 times
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'hacker@example.com',
                'password' => 'wrong-password',
            ]);
            $this->assertNotEquals(429, $response->status());
        }

        // 6th attempt should be blocked with 429 Too Many Requests
        $response = $this->postJson('/api/login', [
            'email' => 'hacker@example.com',
            'password' => 'wrong-password',
        ]);
        $response->assertStatus(429);
    }

    public function test_tracking_endpoint_sanitizes_scripts_and_ids()
    {
        // Clear settings cache if any
        \App\Models\Setting::flushCache();

        // Set values directly in database
        \App\Models\Setting::set('google_analytics_id', 'G-12345<script>alert("XSS")</script>');
        \App\Models\Setting::set('custom_head_scripts', '<script>console.log("Safe");</script><iframe src="http://malicious.site"></iframe><img src=x onerror=alert(1)>');

        $response = $this->getJson('/api/tracking');
        $response->assertStatus(200);

        $json = $response->json();
        
        // Assert Google Analytics ID is sanitized (no script tag allowed)
        $this->assertEquals('G-12345alert(&quot;XSS&quot;)', $json['ids']['google_analytics_id']);

        // Assert custom head scripts have iframe and img tags stripped, but script tags are kept
        $this->assertStringContainsString('<script>console.log("Safe");</script>', $json['head_scripts']);
        $this->assertStringNotContainsString('<iframe', $json['head_scripts']);
        $this->assertStringNotContainsString('<img', $json['head_scripts']);
    }

    public function test_api_endpoints_return_structured_schema_org_json_ld()
    {
        // 1. WebPage Schema for Page
        $page = Page::create([
            'title' => 'Services Page',
            'slug' => 'services',
            'content' => 'Our clean content.',
            'status' => 'published',
        ]);

        $response = $this->getJson('/api/pages/services');
        $response->assertStatus(200)
            ->assertJsonPath('page.seo.schema_json_ld.@type', 'WebPage')
            ->assertJsonPath('page.seo.schema_json_ld.name', 'Services Page')
            ->assertJsonPath('page.seo.schema_json_ld.description', 'Our clean content.')
            ->assertJsonPath('page.seo.schema_json_ld.isPartOf.name', config('app.name', 'Laravel'));

        // 2. BlogPosting Schema for Post
        $post = Post::create([
            'title' => 'Article Post',
            'slug' => 'article-post',
            'content' => 'Blog content.',
            'status' => 'published',
            'author_id' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->getJson('/api/posts/' . $post->id);
        $response->assertStatus(200)
            ->assertJsonPath('post.seo.schema_json_ld.@type', 'BlogPosting')
            ->assertJsonPath('post.seo.schema_json_ld.headline', 'Article Post')
            ->assertJsonPath('post.seo.schema_json_ld.author.name', config('app.name', 'Laravel'))
            ->assertJsonPath('post.seo.schema_json_ld.publisher.name', config('app.name', 'Laravel'));
    }

    public function test_pages_and_posts_return_structured_media_with_alt_text_and_fallbacks()
    {
        Storage::fake('public');

        // Create media uploads:
        // 1. Media with explicit alt_text
        $mediaWithAlt = MediaUpload::create([
            'title' => 'Image with Alt Text',
            'alt_text' => 'Custom SEO Alt Text Description',
        ]);
        $file1 = UploadedFile::fake()->image('image_alt.jpg');
        $mediaWithAlt->addMedia($file1)->toMediaCollection('gallery');

        // 2. Media with blank alt_text, falls back to title
        $mediaFallback = MediaUpload::create([
            'title' => 'Fallback Title Only',
            'alt_text' => '',
        ]);
        $file2 = UploadedFile::fake()->image('image_fallback.jpg');
        $mediaFallback->addMedia($file2)->toMediaCollection('gallery');

        // Test static page with explicit alt text
        $page = Page::create([
            'title' => 'Privacy Page',
            'slug' => 'privacy',
            'content' => 'Content info.',
            'status' => 'published',
            'featured_image_id' => $mediaWithAlt->id,
        ]);

        $responsePage = $this->getJson('/api/pages/privacy');
        $responsePage->assertStatus(200)
            ->assertJsonPath('page.featured_image.alt_text', 'Custom SEO Alt Text Description')
            ->assertJsonPath('page.featured_image.url', $page->featuredImage->getFirstMediaUrl('gallery'))
            ->assertJsonPath('page.featured_image.thumbnail_url', $page->featuredImage->getFirstMediaUrl('gallery', 'thumb'));

        // Test blog post with fallback alt text (falls back to title)
        $post = Post::create([
            'title' => 'My Blog Post',
            'slug' => 'my-blog-post',
            'content' => 'Content details.',
            'status' => 'published',
            'author_id' => $this->adminUser->id,
            'featured_image_id' => $mediaFallback->id,
        ]);

        $responsePost = $this->actingAs($this->adminUser)->getJson('/api/posts/' . $post->id);
        $responsePost->assertStatus(200)
            ->assertJsonPath('post.featured_image.alt_text', 'Fallback Title Only')
            ->assertJsonPath('post.featured_image.url', $post->featuredImage->getFirstMediaUrl('gallery'));
    }
    public function test_global_seo_settings_output_and_verification_tags(): void
    {
        \App\Models\Setting::flushCache();

        // Enable verification keys and disable public indexing
        \App\Models\Setting::set('google_site_verification', 'google-abc-123');
        \App\Models\Setting::set('bing_site_verification', 'bing-xyz-789');
        \App\Models\Setting::set('public_indexing', '0');

        $response = $this->getJson('/api/tracking');
        $response->assertStatus(200);

        $headScripts = $response->json('head_scripts');

        // Verification meta tags must be present
        $this->assertStringContainsString('google-site-verification', $headScripts);
        $this->assertStringContainsString('google-abc-123', $headScripts);
        $this->assertStringContainsString('msvalidate.01', $headScripts);
        $this->assertStringContainsString('bing-xyz-789', $headScripts);

        // noindex meta tag must be injected when indexing is disabled
        $this->assertStringContainsString('noindex, nofollow', $headScripts);

        // Global killswitch: even a page with 'index, follow' on its own SEO record
        // must return 'noindex, nofollow' in the API when global indexing is OFF
        $page = Page::create([
            'title'  => 'Killswitch Test Page',
            'slug'   => 'killswitch-test',
            'content' => 'Content.',
            'status' => 'published',
        ]);

        \App\Models\SeoMetadata::create([
            'seoable_type'        => Page::class,
            'seoable_id'          => $page->id,
            'robots_instructions' => 'index, follow', // per-page says index…
        ]);

        $pageResponse = $this->getJson('/api/pages/killswitch-test');
        $pageResponse->assertStatus(200)
            // …but global killswitch forces noindex, nofollow
            ->assertJsonPath('page.seo.robots_instructions', 'noindex, nofollow');
    }


    public function test_organization_schema_is_generated_in_settings(): void
    {
        \App\Models\Setting::flushCache();

        \App\Models\Setting::set('site_name', 'Acme Corp');
        \App\Models\Setting::set('site_email', 'hello@acme.com');
        \App\Models\Setting::set('site_phone', '+1-800-ACME');
        \App\Models\Setting::set('social_facebook', 'https://facebook.com/acme');
        \App\Models\Setting::set('social_twitter', 'https://twitter.com/acme');

        $response = $this->getJson('/api/settings');
        $response->assertStatus(200);

        $schema = $response->json('settings.organization_schema');

        $this->assertNotNull($schema);
        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('Organization', $schema['@type']);
        $this->assertEquals('Acme Corp', $schema['name']);
        $this->assertEquals('hello@acme.com', $schema['email']);
        $this->assertEquals('+1-800-ACME', $schema['telephone']);
        $this->assertContains('https://facebook.com/acme', $schema['sameAs']);
        $this->assertContains('https://twitter.com/acme', $schema['sameAs']);
    }

    public function test_enhanced_settings_api_structure_and_location_fallbacks(): void
    {
        \App\Models\Setting::flushCache();
        \Illuminate\Support\Facades\Storage::fake('public');

        // Create a custom icon
        $mediaUpload = \App\Models\MediaUpload::create([
            'title' => 'Custom Icon',
        ]);
        $file = \Illuminate\Http\UploadedFile::fake()->image('custom-icon.png');
        $mediaUpload->addMedia($file)->toMediaCollection('gallery');

        // Set new dynamic settings
        $locations = [
            [
                'name' => 'HQ Office',
                'address' => '123 Main St, New York, NY',
                'phones' => ['+1-555-1234', '+1-555-5678'],
                'emails' => ['hq@example.com', 'info@example.com'],
            ],
            [
                'name' => 'Branch Office',
                'address' => '456 Side St, Boston, MA',
                'phones' => ['+1-555-9012'],
                'emails' => ['boston@example.com'],
            ]
        ];

        $socialLinks = [
            [
                'platform' => 'facebook',
                'url' => 'https://facebook.com/mybrand',
                'icon_id' => null,
            ],
            [
                'platform' => 'other',
                'custom_platform' => 'Threads',
                'url' => 'https://threads.net/mybrand',
                'icon_id' => $mediaUpload->id,
            ]
        ];

        \App\Models\Setting::set('site_name', 'Dynamic Brand');
        \App\Models\Setting::set('contact_locations', json_encode($locations));
        \App\Models\Setting::set('social_links', json_encode($socialLinks));

        $response = $this->getJson('/api/settings');
        $response->assertStatus(200);

        // Verify locations are decoded in the API response
        $response->assertJsonCount(2, 'settings.contact_locations')
            ->assertJsonPath('settings.contact_locations.0.name', 'HQ Office')
            ->assertJsonPath('settings.contact_locations.1.name', 'Branch Office');

        // Verify social links are decoded and custom icon URL is resolved
        $response->assertJsonCount(2, 'settings.social_links')
            ->assertJsonPath('settings.social_links.0.platform', 'facebook')
            ->assertJsonPath('settings.social_links.1.platform', 'other')
            ->assertJsonPath('settings.social_links.1.custom_platform', 'Threads');
        
        $this->assertNotEmpty($response->json('settings.social_links.1.icon_url'));

        // Verify legacy mapping compatibility
        // site_address should fallback to first location's address
        $response->assertJsonPath('settings.site_address', '123 Main St, New York, NY');
        // site_phone and site_email should be imploded strings from the first location
        $response->assertJsonPath('settings.site_phone', '+1-555-1234, +1-555-5678');
        $response->assertJsonPath('settings.site_email', 'hq@example.com, info@example.com');

        // Verify legacy social mapping compatibility
        $response->assertJsonPath('settings.social_facebook', 'https://facebook.com/mybrand');
        $response->assertJsonPath('settings.social_threads', 'https://threads.net/mybrand');

        // Verify Organization schema collects emails/phones/addresses from fallbacks and merges all social urls
        $schema = $response->json('settings.organization_schema');
        $this->assertEquals('Dynamic Brand', $schema['name']);
        $this->assertEquals('hq@example.com, info@example.com', $schema['email']);
        $this->assertEquals('+1-555-1234, +1-555-5678', $schema['telephone']);
        $this->assertEquals('123 Main St, New York, NY', $schema['address']);
        
        $this->assertContains('https://facebook.com/mybrand', $schema['sameAs']);
        $this->assertContains('https://threads.net/mybrand', $schema['sameAs']);
    }

    public function test_system_pages_cannot_be_deleted()
    {
        $admin = \App\Models\User::create([
            'name' => 'Admin User 2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $systemPage = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => 'Home page content',
            'status' => 'published',
            'is_system' => true,
        ]);

        $normalPage = Page::create([
            'title' => 'Contact Us',
            'slug' => 'contact-us',
            'content' => 'Contact content',
            'status' => 'published',
        ]);

        // Attempt delete via policy
        $policy = new \App\Policies\PagePolicy();
        
        $this->assertFalse($policy->delete($admin, $systemPage));
        $this->assertTrue($policy->delete($admin, $normalPage));
    }

    public function test_service_category_route_works()
    {
        $category = \App\Models\Category::create([
            'name' => 'Design Services',
            'slug' => 'design',
            'type' => 'service',
        ]);

        $response = $this->get('/services/category/design');
        $response->assertStatus(200)
            ->assertSee('Design Services');
    }
}


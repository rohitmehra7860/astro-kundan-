<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\SeoMetadata;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqSchemaTest extends TestCase
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
            'email_verified_at' => now(),
        ]);
        $this->adminUser->assignRole('admin');
    }

    /**
     * Test page creation and FAQ array casts saving to database.
     */
    public function test_faq_saves_correctly_as_array()
    {
        $page = Page::create([
            'title' => 'FAQ Help Page',
            'slug' => 'faq-help',
            'content' => 'Content here.',
            'status' => 'published',
        ]);

        $faqs = [
            ['question' => 'Q1?', 'answer' => 'A1.'],
            ['question' => 'Q2?', 'answer' => 'A2.'],
        ];

        $seo = SeoMetadata::create([
            'seoable_type' => Page::class,
            'seoable_id' => $page->id,
            'meta_title' => 'FAQ Help Page Title',
            'faqs' => $faqs,
        ]);

        $this->assertDatabaseHas('seo_metadatas', [
            'id' => $seo->id,
            'seoable_type' => Page::class,
            'seoable_id' => $page->id,
        ]);

        // Reload from database to verify cast
        $reloaded = SeoMetadata::find($seo->id);
        $this->assertIsArray($reloaded->faqs);
        $this->assertEquals($faqs, $reloaded->faqs);
    }

    /**
     * Test that page show endpoint returns structured FAQPage JSON-LD.
     */
    public function test_api_returns_structured_faq_json_ld()
    {
        $page = Page::create([
            'title' => 'Returns Policy',
            'slug' => 'returns-policy',
            'content' => 'Content goes here.',
            'status' => 'published',
        ]);

        SeoMetadata::create([
            'seoable_type' => Page::class,
            'seoable_id' => $page->id,
            'meta_title' => 'Returns Policy Title',
            'faqs' => [
                ['question' => 'How long does shipping take?', 'answer' => 'Shipping takes 3-5 days.'],
                ['question' => 'What is the return window?', 'answer' => 'You can return items within 30 days.'],
            ],
        ]);

        $response = $this->getJson('/api/pages/returns-policy');

        $response->assertStatus(200)
            ->assertJsonPath('page.seo.faq_json_ld.@type', 'FAQPage')
            ->assertJsonPath('page.seo.faq_json_ld.mainEntity.0.name', 'How long does shipping take?')
            ->assertJsonPath('page.seo.faq_json_ld.mainEntity.0.acceptedAnswer.text', 'Shipping takes 3-5 days.')
            ->assertJsonPath('page.seo.faq_json_ld.mainEntity.1.name', 'What is the return window?')
            ->assertJsonPath('page.seo.faq_json_ld.mainEntity.1.acceptedAnswer.text', 'You can return items within 30 days.');
    }

    /**
     * Test that empty FAQ structures return null in the API payload.
     */
    public function test_api_returns_null_faq_json_ld_when_empty()
    {
        $page = Page::create([
            'title' => 'No FAQ Page',
            'slug' => 'no-faq-page',
            'content' => 'Clean content.',
            'status' => 'published',
        ]);

        SeoMetadata::create([
            'seoable_type' => Page::class,
            'seoable_id' => $page->id,
            'meta_title' => 'No FAQ Title',
            'faqs' => [], // Empty array
        ]);

        $response = $this->getJson('/api/pages/no-faq-page');

        $response->assertStatus(200)
            ->assertJsonPath('page.seo.faq_json_ld', null);
    }
}

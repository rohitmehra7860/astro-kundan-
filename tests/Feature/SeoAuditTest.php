<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Page;
use App\Models\Post;
use App\Models\Category;
use App\Models\SeoMetadata;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Pages\SeoAudit;
use Livewire\Livewire;
use Tests\TestCase;

class SeoAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        // Admin User
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $this->adminUser->assignRole('admin');

        // Regular User
        $this->regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $this->regularUser->assignRole('user');
    }

    /**
     * Test page authorization for guest users.
     */
    public function test_guest_cannot_access_seo_audit_page()
    {
        $response = $this->get('/admin/seo-audit');
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test page authorization for regular users.
     */
    public function test_unauthorized_user_cannot_access_seo_audit_page()
    {
        $response = $this->actingAs($this->regularUser)->get('/admin/seo-audit');
        $response->assertStatus(403);
    }

    /**
     * Test page authorization for admin.
     */
    public function test_authorized_user_can_access_seo_audit_page()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/seo-audit');
        $response->assertStatus(200);
    }

    /**
     * Test overall audit score calculation with perfect items.
     */
    public function test_overall_score_calculation_with_perfect_seo()
    {
        $media = \App\Models\MediaUpload::create([
            'title' => 'Social Image',
        ]);

        $page = Page::create([
            'title' => 'Perfect Policy Page',
            'slug' => 'perfect-policy',
            'content' => 'Perfect content rules.',
            'status' => 'published',
        ]);

        SeoMetadata::create([
            'seoable_type' => Page::class,
            'seoable_id' => $page->id,
            'meta_title' => 'Healthy Title of Perfect Policy Page Chars',
            'meta_description' => 'Healthy Description that is long enough to fit within the 120 to 160 character limit. It contains plenty of descriptive phrases to meet optimization guidelines.',
            'og_image_id' => $media->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SeoAudit::class)
            ->assertSet('overallScore', 100)
            ->assertSet('stats.good', 1)
            ->assertSet('stats.critical', 0);
    }

    /**
     * Test score with missing SEO metadata entirely (yields 0%).
     */
    public function test_score_calculation_with_missing_seo()
    {
        Page::create([
            'title' => 'Unoptimized Policy Page',
            'slug' => 'unoptimized-policy',
            'content' => 'Unoptimized content.',
            'status' => 'published',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SeoAudit::class)
            ->assertSet('overallScore', 0)
            ->assertSet('stats.critical', 1);
    }

    /**
     * Test warning triggers for short meta descriptions.
     */
    public function test_warning_triggered_for_short_meta()
    {
        $media = \App\Models\MediaUpload::create([
            'title' => 'Social Image',
        ]);

        $post = Post::create([
            'title' => 'Short Post Title',
            'slug' => 'short-title',
            'content' => 'Short description.',
            'status' => 'published',
            'author_id' => $this->adminUser->id,
        ]);

        SeoMetadata::create([
            'seoable_type' => Post::class,
            'seoable_id' => $post->id,
            'meta_title' => 'Valid Title With Length 35 Chars',
            'meta_description' => 'Too short description',
            'og_image_id' => $media->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SeoAudit::class)
            ->assertSet('stats.warning', 1)
            ->assertSet('stats.critical', 0);
    }

    /**
     * Test category edit routes mapping logic.
     */
    public function test_category_urls_map_correctly_based_on_type()
    {
        $blogCat = Category::create([
            'name' => 'Blog Tech',
            'slug' => 'blog-tech',
            'type' => 'blog',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(SeoAudit::class)
            ->assertViewHas('auditItems', function ($items) use ($blogCat) {
                $blogItem = collect($items)->firstWhere('id', $blogCat->id);

                return str_contains($blogItem['edit_url'], 'blog-categories');
            });
    }
}

<?php

namespace Tests\Feature;

use App\Models\RedirectRule;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectsTest extends TestCase
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

    /**
     * Test active 301 redirect rule.
     */
    public function test_active_301_redirect_works()
    {
        RedirectRule::create([
            'old_path' => '/old-page',
            'new_path' => '/new-page',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $response = $this->get('/old-page');

        $response->assertRedirect('/new-page');
        $response->assertStatus(301);
    }

    /**
     * Test active 302 redirect rule.
     */
    public function test_active_302_redirect_works()
    {
        RedirectRule::create([
            'old_path' => '/temporary-deal',
            'new_path' => '/special-deals',
            'status_code' => 302,
            'is_active' => true,
        ]);

        $response = $this->get('/temporary-deal');

        $response->assertRedirect('/special-deals');
        $response->assertStatus(302);
    }

    /**
     * Test query parameter preservation.
     */
    public function test_redirect_preserves_query_parameters()
    {
        RedirectRule::create([
            'old_path' => '/old-utm-page',
            'new_path' => '/new-utm-page',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $response = $this->get('/old-utm-page?utm_campaign=summer_sale&ref=social');

        $response->assertStatus(301);
        $redirectUrl = $response->headers->get('Location');
        
        $parsed = parse_url($redirectUrl);
        $this->assertEquals('/new-utm-page', $parsed['path']);
        
        parse_str($parsed['query'] ?? '', $queryParams);
        $this->assertEquals('summer_sale', $queryParams['utm_campaign'] ?? null);
        $this->assertEquals('social', $queryParams['ref'] ?? null);
    }

    /**
     * Test that query parameter preservation handles existing query parameters in target path.
     */
    public function test_redirect_appends_query_parameters_to_existing_target_query()
    {
        RedirectRule::create([
            'old_path' => '/promo',
            'new_path' => '/deals?category=electronics',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $response = $this->get('/promo?coupon=save10');

        $response->assertStatus(301);
        $redirectUrl = $response->headers->get('Location');
        
        $parsed = parse_url($redirectUrl);
        $this->assertEquals('/deals', $parsed['path']);
        
        parse_str($parsed['query'] ?? '', $queryParams);
        $this->assertEquals('electronics', $queryParams['category'] ?? null);
        $this->assertEquals('save10', $queryParams['coupon'] ?? null);
    }

    /**
     * Test inactive redirects are ignored and return a normal response (404 in this case).
     */
    public function test_inactive_redirects_are_ignored()
    {
        RedirectRule::create([
            'old_path' => '/inactive-path',
            'new_path' => '/active-path',
            'status_code' => 301,
            'is_active' => false,
        ]);

        $response = $this->get('/inactive-path');

        $response->assertStatus(404);
    }

    /**
     * Test absolute URL redirection targets.
     */
    public function test_redirect_to_external_absolute_url_works()
    {
        RedirectRule::create([
            'old_path' => '/external-redirect',
            'new_path' => 'https://external-domain.com/blog',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $response = $this->get('/external-redirect');

        $response->assertRedirect('https://external-domain.com/blog');
    }

    /**
     * Test path normalization on save.
     */
    public function test_paths_are_normalized_on_save()
    {
        $rule = RedirectRule::create([
            'old_path' => '  /about-us///  ',
            'new_path' => 'our-story/info?ref=header#section1',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->assertEquals('/about-us', $rule->old_path);
        // Trailing query string/fragment should be preserved on redirect target, and relative path starts with '/'
        $this->assertEquals('/our-story/info?ref=header#section1', $rule->new_path);
    }

    /**
     * Test that loop prevention raises an exception on model level.
     */
    public function test_loop_prevention_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The old path cannot be the same as the new path.');

        RedirectRule::create([
            'old_path' => '/same-path',
            'new_path' => 'same-path/',
            'status_code' => 301,
            'is_active' => true,
        ]);
    }
}

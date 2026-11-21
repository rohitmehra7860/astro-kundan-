<?php

namespace Tests\Feature;

use App\Models\BrokenLink;
use App\Models\RedirectRule;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Resources\Redirects\BrokenLinkResource\Pages\ListBrokenLinks;
use Livewire\Livewire;
use Tests\TestCase;

class BrokenLinksTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $editorUser;
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

        // Editor User
        $this->editorUser = User::create([
            'name' => 'Editor User',
            'email' => 'editor@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $this->editorUser->assignRole('editor');

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
     * Test that non-existent request paths correctly trigger logging.
     */
    public function test_non_existent_request_path_triggers_logging()
    {
        $response = $this->withHeaders([
            'referer' => 'https://external-site.com/broken-page',
            'user-agent' => 'TestBot/1.0',
        ])->get('/this-is-broken');

        $response->assertStatus(404);

        $this->assertDatabaseHas('broken_links', [
            'path' => '/this-is-broken',
            'referrer' => 'https://external-site.com/broken-page',
            'user_agent' => 'TestBot/1.0',
            'hits_count' => 1,
        ]);
    }

    /**
     * Test that duplicate 404 requests aggregate (incrementing hits_count rather than inserting a new row).
     */
    public function test_duplicate_404_requests_aggregate_hits_count()
    {
        $this->get('/duplicate-broken');
        $this->get('/duplicate-broken');
        $this->get('/duplicate-broken');

        $this->assertEquals(1, BrokenLink::count());

        $this->assertDatabaseHas('broken_links', [
            'path' => '/duplicate-broken',
            'hits_count' => 3,
        ]);
    }

    /**
     * Test that redirected requests (301/302 matches) are not logged as 404s.
     */
    public function test_redirected_requests_are_not_logged_as_404s()
    {
        RedirectRule::create([
            'old_path' => '/redirected-path',
            'new_path' => '/new-destination',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $response = $this->get('/redirected-path');
        $response->assertStatus(301);

        $this->assertEquals(0, BrokenLink::count());
    }

    /**
     * Test that static files and system routes are ignored.
     */
    public function test_static_files_and_system_routes_are_ignored()
    {
        $this->get('/images/logo.png');
        $this->get('/js/app.js.map');
        $this->get('/livewire/livewire.js');
        $this->get('/_debugbar/assets/stylesheets');

        $this->assertEquals(0, BrokenLink::count());
    }

    /**
     * Test that path query parameters are stripped during normalization.
     */
    public function test_query_parameters_are_stripped_during_logging()
    {
        $this->get('/search-broken?query=laravel&page=2');

        $this->assertDatabaseHas('broken_links', [
            'path' => '/search-broken',
            'hits_count' => 1,
        ]);
    }

    /**
     * Test Filament permission gating on the resource.
     */
    public function test_unauthorized_user_cannot_access_broken_links_list()
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/redirects/broken-links');
        $response->assertStatus(403);
    }

    public function test_authorized_user_can_access_broken_links_list()
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/redirects/broken-links');
        $response->assertStatus(200);
    }

    /**
     * Test that the Filament "create_redirect" table action creates the corresponding RedirectRule and deletes the resolved log.
     */
    public function test_filament_create_redirect_action_resolves_broken_link()
    {
        $this->actingAs($this->adminUser);

        $brokenLink = BrokenLink::create([
            'path' => '/resolve-me',
            'hits_count' => 10,
            'last_seen_at' => now(),
        ]);

        Livewire::test(ListBrokenLinks::class)
            ->callTableAction('create_redirect', $brokenLink, [
                'new_path' => '/brand-new-page',
                'status_code' => 301,
            ])
            ->assertHasNoTableActionErrors();

        // Verify RedirectRule is created
        $this->assertDatabaseHas('redirect_rules', [
            'old_path' => '/resolve-me',
            'new_path' => '/brand-new-page',
            'status_code' => 301,
            'is_active' => 1,
        ]);

        // Verify BrokenLink is deleted
        $this->assertDatabaseMissing('broken_links', [
            'id' => $brokenLink->id,
        ]);
    }
}

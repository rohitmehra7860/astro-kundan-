<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\SeoMetadata;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filament\Shared\SeoForm;
use Livewire\Livewire;
use Tests\TestCase;

class RealTimeSeoAndSchemaTest extends TestCase
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
     * Test that the Filament SeoForm compiles successfully with new fields.
     */
    public function test_seo_form_compiles_successfully()
    {
        $this->actingAs($this->adminUser);

        $formSection = SeoForm::make();

        $this->assertInstanceOf(\Filament\Schemas\Components\Section::class, $formSection);
        $this->assertEquals('SEO & Metadata', $formSection->getHeading());
    }

    /**
     * Test Event Schema database saving and REST API output.
     */
    public function test_event_schema_output()
    {
        $page = Page::create([
            'title' => 'Tech Conference 2026',
            'slug' => 'tech-conf-2026',
            'content' => 'Join us for our annual tech conference.',
            'status' => 'published',
        ]);

        SeoMetadata::create([
            'seoable_type' => Page::class,
            'seoable_id' => $page->id,
            'schema_type' => 'event',
            'schema_data' => [
                'event_name' => 'Annual Tech Conference',
                'event_url' => 'https://example.com/events/tech-conf',
                'event_start' => '2026-10-15 09:00:00',
                'event_end' => '2026-10-16 17:00:00',
                'event_mode' => 'https://schema.org/OnlineEventAttendanceMode',
                'event_location_address_or_url' => 'https://zoom.us/j/12345',
                'event_ticket_price' => '99.50',
                'event_ticket_currency' => 'USD',
                'event_ticket_url' => 'https://example.com/tickets',
            ],
        ]);

        $response = $this->getJson('/api/pages/tech-conf-2026');
        $response->assertStatus(200);

        $response->assertJsonPath('page.seo.structured_schema_json_ld.@type', 'Event');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.name', 'Annual Tech Conference');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.url', 'https://example.com/events/tech-conf');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.eventAttendanceMode', 'https://schema.org/OnlineEventAttendanceMode');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.location.@type', 'VirtualLocation');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.location.url', 'https://zoom.us/j/12345');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.offers.price', '99.50');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.offers.priceCurrency', 'USD');
    }

    /**
     * Test Job Posting Schema database saving and REST API output.
     */
    public function test_job_posting_schema_output()
    {
        $post = Post::create([
            'title' => 'We are hiring a Senior Developer',
            'slug' => 'hiring-senior-developer',
            'content' => 'Senior PHP Laravel Developer wanted.',
            'status' => 'published',
            'author_id' => $this->adminUser->id,
        ]);

        SeoMetadata::create([
            'seoable_type' => Post::class,
            'seoable_id' => $post->id,
            'schema_type' => 'job_posting',
            'schema_data' => [
                'job_title' => 'Senior PHP Developer',
                'job_description' => 'Senior PHP Laravel Developer wanted.',
                'job_company' => 'Acme Corp',
                'job_company_website' => 'https://acmecorp.com',
                'job_location' => 'New York, NY',
                'job_employment_type' => 'FULL_TIME',
                'job_salary' => '120000',
                'job_salary_currency' => 'USD',
            ],
        ]);

        $response = $this->actingAs($this->adminUser)->getJson('/api/posts/' . $post->id);
        $response->assertStatus(200);

        $response->assertJsonPath('post.seo.structured_schema_json_ld.@type', 'JobPosting');
        $response->assertJsonPath('post.seo.structured_schema_json_ld.title', 'Senior PHP Developer');
        $response->assertJsonPath('post.seo.structured_schema_json_ld.hiringOrganization.name', 'Acme Corp');
        $response->assertJsonPath('post.seo.structured_schema_json_ld.jobLocation.address.addressLocality', 'New York, NY');
        $response->assertJsonPath('post.seo.structured_schema_json_ld.employmentType', 'FULL_TIME');
        $response->assertJsonPath('post.seo.structured_schema_json_ld.baseSalary.value.value', '120000.00');
    }

    /**
     * Test Local Business Schema database saving and REST API output.
     */
    public function test_local_business_schema_output()
    {
        $page = Page::create([
            'title' => 'Dental Consultation Page',
            'slug' => 'dental-consultation',
            'content' => 'Book a dental checkup.',
            'status' => 'published',
        ]);

        SeoMetadata::create([
            'seoable_type' => Page::class,
            'seoable_id' => $page->id,
            'schema_type' => 'local_business',
            'schema_data' => [
                'business_name' => 'New York Dental Clinic',
                'business_type' => 'Dentist',
                'business_phone' => '+1 555-123-4567',
                'business_street' => '123 Main St',
                'business_city' => 'New York',
                'business_state' => 'NY',
                'business_zip' => '10001',
                'business_country' => 'US',
                'business_hours' => 'Mo-Fr 09:00-17:00',
                'business_price_range' => '$$',
            ],
        ]);

        $response = $this->getJson('/api/pages/dental-consultation');
        $response->assertStatus(200);

        $response->assertJsonPath('page.seo.structured_schema_json_ld.@type', 'Dentist');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.name', 'New York Dental Clinic');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.telephone', '+1 555-123-4567');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.address.streetAddress', '123 Main St');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.openingHours', 'Mo-Fr 09:00-17:00');
        $response->assertJsonPath('page.seo.structured_schema_json_ld.priceRange', '$$');
    }
}

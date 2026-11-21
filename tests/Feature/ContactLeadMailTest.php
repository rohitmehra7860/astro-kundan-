<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Modules\ContactLeads\Models\ContactLead;
use App\Mail\ContactLeadNotificationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactLeadMailTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user for testing authenticated endpoints
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        
        Setting::flushCache();
    }

    public function test_contact_lead_creation_dispatches_email_to_configured_receivers()
    {
        Mail::fake();

        // 1. Configure receiver email addresses in database settings
        Setting::set('mail_receive_address', json_encode(['receiver1@example.com', 'receiver2@example.com']));

        $leadData = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'phone' => '+1234567890',
            'subject' => 'Website Quotation Request',
            'message' => 'Hello, I would like to get a quote for a new web application project.',
            'source' => 'Homepage Contact Form',
        ];

        // 2. Submit lead via public API endpoint
        $response = $this->postJson('/api/contact-leads', $leadData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        // 3. Assert lead is saved in database
        $this->assertDatabaseHas('contact_leads', [
            'email' => 'johndoe@example.com',
            'status' => 'new',
        ]);

        // 4. Assert mail was queued to both configured receivers
        Mail::assertQueued(ContactLeadNotificationMail::class, function ($mail) {
            return $mail->hasTo('receiver1@example.com') && 
                   $mail->hasTo('receiver2@example.com') &&
                   $mail->lead->name === 'John Doe';
        });
    }

    public function test_contact_lead_falls_back_to_site_email()
    {
        Mail::fake();

        // 1. Configure site_email but leave mail_receive_address empty
        Setting::set('site_email', 'sitefallback@example.com');
        Setting::set('mail_receive_address', '[]');

        $leadData = [
            'name' => 'Jane Smith',
            'email' => 'janesmith@example.com',
            'message' => 'Simple contact message.',
        ];

        // 2. Submit lead
        $response = $this->postJson('/api/contact-leads', $leadData);

        $response->assertStatus(201);

        // 3. Assert mail fell back to site_email and was queued
        Mail::assertQueued(ContactLeadNotificationMail::class, function ($mail) {
            return $mail->hasTo('sitefallback@example.com') && 
                   $mail->lead->name === 'Jane Smith';
        });
    }

    public function test_authenticated_user_can_list_and_view_leads()
    {
        // 1. Create a lead
        $lead = ContactLead::create([
            'name' => 'Lead to View',
            'email' => 'view@example.com',
            'message' => 'View message detail.',
            'status' => 'new',
        ]);

        // 2. List leads (authenticated)
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/contact-leads');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Lead to View',
            ]);

        // 3. Show lead detail (authenticated)
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/contact-leads/' . $lead->id);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Lead to View')
            ->assertJsonPath('status', 'read');
    }
}

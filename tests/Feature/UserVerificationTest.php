<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\EmailVerificationOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpVerificationMail;
use Tests\TestCase;

class UserVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_user_registration_sends_otp_and_does_not_verify_email(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('requires_verification', true);

        // Check user exists but is not verified
        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        // Check OTP was created in database
        $otpRecord = EmailVerificationOtp::where('user_id', $user->id)->first();
        $this->assertNotNull($otpRecord);

        // Check OTP mail was queued
        Mail::assertQueued(OtpVerificationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_unverified_user_cannot_login(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Unverified User',
            'email' => 'unverified@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'unverified@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Please verify your email first. A new OTP has been sent to your email.');
    }

    public function test_user_can_verify_email_with_correct_otp(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Verify Me',
            'email' => 'verify@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create an OTP
        $otp = '123456';
        EmailVerificationOtp::create([
            'user_id' => $user->id,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->postJson('/api/verify-email', [
            'email' => 'verify@example.com',
            'otp' => $otp,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);

        // Check user is now verified
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_user_cannot_verify_email_with_expired_otp(): void
    {
        $user = User::create([
            'name' => 'Verify Me',
            'email' => 'verify@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create an expired OTP
        $otp = '123456';
        EmailVerificationOtp::create([
            'user_id' => $user->id,
            'otp' => Hash::make($otp),
            'expires_at' => now()->subMinutes(1),
        ]);

        $response = $this->postJson('/api/verify-email', [
            'email' => 'verify@example.com',
            'otp' => $otp,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'OTP has expired. Please request a new one.');

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_user_can_be_unverified(): void
    {
        $user = User::create([
            'name' => 'Verified User',
            'email' => 'verified@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->assertNotNull($user->email_verified_at);

        // Simulate unverification action
        $user->update(['email_verified_at' => null]);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_unverified_user_cannot_login_to_filament(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Unverified Admin',
            'email' => 'unverifiedadmin@example.com',
            'password' => Hash::make('password123'),
        ]);

        \Livewire\Livewire::test(\App\Filament\Pages\Auth\Login::class)
            ->set('data.email', 'unverifiedadmin@example.com')
            ->set('data.password', 'password123')
            ->call('authenticate')
            ->assertRedirect(route('filament.admin.auth.verify-email') . '?email=' . urlencode('unverifiedadmin@example.com'));
    }

    public function test_user_can_verify_via_verify_email_page(): void
    {
        Mail::fake();
        
        $user = User::create([
            'name' => 'Verify Page User',
            'email' => 'verifypage@example.com',
            'password' => Hash::make('password123'),
        ]);

        $otp = '654321';
        EmailVerificationOtp::create([
            'user_id' => $user->id,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(15),
        ]);

        \Livewire\Livewire::test(\App\Filament\Pages\Auth\VerifyEmail::class)
            ->set('data.email', 'verifypage@example.com')
            ->set('data.otp', $otp)
            ->call('verify')
            ->assertRedirect(filament()->getUrl());

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertTrue(auth()->check());
    }

    public function test_unverified_logged_in_user_is_redirected_and_logged_out(): void
    {
        $user = User::create([
            'name' => 'Unverified Admin',
            'email' => 'unverifiedadmin@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null, // unverified
        ]);
        $user->assignRole('admin');

        $this->actingAs($user);

        // Try to access filament dashboard
        $response = $this->get(filament()->getUrl());

        // Assert that the user is logged out and redirected to login page
        $response->assertRedirect(filament()->getLoginUrl());
        $this->assertFalse(auth()->check());
    }

    public function test_comments_and_newsletter_policies_check_permissions(): void
    {
        $user = User::create([
            'name' => 'Editor User',
            'email' => 'editor@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('editor');

        // Editor user has view comments permission
        $this->assertTrue($user->can('view comments'));
        $this->assertTrue($user->can('view newsletter subscribers'));
        $this->assertTrue($user->can('view blog categories'));

        // Create a simple user who doesn't have comments permission
        $plainUser = User::create([
            'name' => 'Plain User',
            'email' => 'plain@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $plainUser->assignRole('user');

        $this->assertFalse($plainUser->can('view comments'));
        $this->assertFalse($plainUser->can('view newsletter subscribers'));
        $this->assertTrue($plainUser->can('view blog categories'));
    }
}

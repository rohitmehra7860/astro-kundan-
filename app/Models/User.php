<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

#[Fillable(['name', 'email', 'password', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, LogsActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Determine if the user can access the given panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->email_verified_at === null) {
            \Filament\Facades\Filament::auth()->logout();
            abort(redirect()->to($panel->getLoginUrl()));
        }

        return $this->hasRole('admin') || $this->hasRole('editor') || $this->can('view dashboard');
    }

    /**
     * Generate, store and send a 6-digit OTP to the user's email.
     */
    public function sendEmailVerificationOtp(): void
    {
        // Delete previous OTPs for this user
        \App\Models\EmailVerificationOtp::where('user_id', $this->id)->delete();

        // Generate a secure 6-digit OTP
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        \App\Models\EmailVerificationOtp::create([
            'user_id'    => $this->id,
            'otp'        => \Illuminate\Support\Facades\Hash::make($otp),
            'expires_at' => now()->addMinutes(15),
        ]);

        \Illuminate\Support\Facades\Mail::to($this->email)->send(new \App\Mail\OtpVerificationMail($otp, $this->name));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "User {$eventName}");
    }
}

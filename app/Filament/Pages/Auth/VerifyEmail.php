<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\SimplePage;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\User;
use App\Models\EmailVerificationOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Text;
use Illuminate\Support\HtmlString;

class VerifyEmail extends SimplePage
{
    protected string $view = 'filament-panels::pages.simple';

    public ?array $data = [];

    public function mount(): void
    {
        if (Auth::check()) {
            redirect()->intended(filament()->getUrl());
        }

        $this->form->fill([
            'email' => request()->query('email'),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    EmbeddedSchema::make('form'),
                ])
                ->id('form')
                ->livewireSubmitHandler('verify')
                ->footer([
                    Actions::make($this->getFormActions())
                        ->alignment($this->getFormActionsAlignment())
                        ->fullWidth($this->hasFullWidthFormActions())
                        ->key('form-actions'),
                ]),
                
                Text::make(new HtmlString(
                    '<div class="text-center mt-6 text-sm text-gray-500 dark:text-gray-400">' .
                    'Didn\'t receive the OTP? ' .
                    '<button type="button" wire:click="resendOtp" class="cursor-pointer font-bold text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300 hover:underline focus:outline-none">' .
                    'Resend OTP' .
                    '</button>' .
                    '</div>'
                )),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->autocomplete()
                    ->autofocus(),
                TextInput::make('otp')
                    ->label('Verification Code (OTP)')
                    ->length(6)
                    ->required()
                    ->placeholder('123456')
                    ->autocomplete('one-time-code'),
            ]);
    }

    public function verify()
    {
        $data = $this->form->getState();
        $email = $data['email'];
        $otp = $data['otp'];

        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'data.email' => 'User not found with this email.',
            ]);
        }

        if ($user->email_verified_at) {
            Notification::make()
                ->title('Email already verified. Please login.')
                ->success()
                ->send();
            return redirect()->to(filament()->getLoginUrl());
        }

        $otpRecord = EmailVerificationOtp::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$otpRecord) {
            throw ValidationException::withMessages([
                'data.otp' => 'No verification code found. Please request a new one.',
            ]);
        }

        if ($otpRecord->isExpired()) {
            $otpRecord->delete();
            throw ValidationException::withMessages([
                'data.otp' => 'Verification code has expired. Please request a new one.',
            ]);
        }

        if (!Hash::check($otp, $otpRecord->otp)) {
            throw ValidationException::withMessages([
                'data.otp' => 'Invalid verification code.',
            ]);
        }

        // Verify and save
        $user->email_verified_at = now();
        $user->save();

        $otpRecord->delete();

        // Log the user in
        Auth::login($user);

        Notification::make()
            ->title('Email verified successfully!')
            ->success()
            ->send();

        return redirect()->intended(filament()->getUrl());
    }

    public function resendOtp(): void
    {
        $email = $this->data['email'] ?? null;

        if (!$email) {
            Notification::make()
                ->title('Please enter your email address first.')
                ->danger()
                ->send();
            return;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            Notification::make()
                ->title('User not found with this email address.')
                ->danger()
                ->send();
            return;
        }

        if ($user->email_verified_at) {
            Notification::make()
                ->title('Email is already verified.')
                ->info()
                ->send();
            return;
        }

        try {
            $user->sendEmailVerificationOtp();
            Notification::make()
                ->title('A new verification code has been sent to your email.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to send verification code. Please try again.')
                ->danger()
                ->send();
        }
    }

    public function getTitle(): string | Htmlable
    {
        return 'Verify Email';
    }

    public function getHeading(): string | Htmlable | null
    {
        return 'Verify Your Email';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Enter the 6-digit code sent to your email to verify your account.';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('verify')
                ->label('Verify & Login')
                ->submit('verify'),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}

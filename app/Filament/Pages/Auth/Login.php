<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        
        $user = User::where('email', $data['email'] ?? '')->first();

        if ($user && !$user->email_verified_at) {
            $user->sendEmailVerificationOtp();
            
            redirect()->to(
                route('filament.admin.auth.verify-email') . '?email=' . urlencode($user->email)
            );

            return null;
        }

        return parent::authenticate();
    }
}

<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Users created via the admin panel are auto-verified —
     * they don't need to go through the OTP flow.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['email_verified_at'] = now();
        return $data;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailVerificationOtp extends Model
{
    protected $fillable = ['user_id', 'otp', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Check if this OTP has expired */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}

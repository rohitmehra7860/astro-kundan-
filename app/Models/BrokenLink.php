<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['path', 'referrer', 'user_agent', 'ip_address', 'hits_count', 'last_seen_at'])]
class BrokenLink extends Model
{
    protected $casts = [
        'hits_count' => 'integer',
        'last_seen_at' => 'datetime',
    ];
}

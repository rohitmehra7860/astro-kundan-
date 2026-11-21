<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class NewsletterSubscriber extends Model
{
    use LogsActivity;

    protected $fillable = [
        'email',
        'name',
        'status',
        'token',
        'subscribed_at',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
    ];

    /** Scope: only active subscribers */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Newsletter Subscriber {$eventName}");
    }
}

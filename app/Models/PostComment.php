<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class PostComment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'post_id',
        'parent_id',
        'name',
        'email',
        'content',
        'status',
    ];

    /** Parent post */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /** Parent comment (for replies) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    /** Child replies */
    public function replies(): HasMany
    {
        return $this->hasMany(PostComment::class, 'parent_id')->approved()->oldest();
    }

    /** Scope: only approved comments */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /** Scope: only pending comments */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /** Scope: only spam */
    public function scopeSpam(Builder $query): Builder
    {
        return $query->where('status', 'spam');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Comment {$eventName}");
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Page extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'dynamic_data',
        'status',
        'featured_image_id',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'dynamic_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Page $page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    /**
     * Get the page's featured image.
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(MediaUpload::class, 'featured_image_id');
    }

    /**
     * Get the page's SEO metadata settings.
     */
    public function seoMetadata(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Page {$eventName}");
    }
}

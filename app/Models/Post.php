<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['title', 'slug', 'content', 'status', 'author_id', 'category_id', 'featured_image_id', 'is_featured', 'is_pinned'])]
class Post extends Model implements HasMedia
{
    use SoftDeletes, LogsActivity, InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
            'is_featured' => 'boolean',
            'is_pinned'   => 'boolean',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function featuredImage()
    {
        return $this->belongsTo(MediaUpload::class, 'featured_image_id');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function seoMetadata(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }

    /** Comments on this post */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PostComment::class);
    }

    /** Scope: featured posts */
    public function scopeFeatured(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_featured', true);
    }

    /** Scope: pinned posts */
    public function scopePinned(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_pinned', true);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Post {$eventName}");
    }
}

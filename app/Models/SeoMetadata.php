<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMetadata extends Model
{
    protected $table = 'seo_metadatas';

    protected $appends = [
        'robots_indexable',
    ];

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image_id',
        'canonical_url',
        'robots_instructions',
        'robots_indexable',
        'faqs',
        'schema_type',
        'schema_data',
    ];

    protected $casts = [
        'faqs' => 'array',
        'schema_data' => 'array',
    ];

    /**
     * Mutate robots instructions to string when boolean is passed.
     */
    public function setRobotsInstructionsAttribute($value): void
    {
        if (is_bool($value)) {
            $this->attributes['robots_instructions'] = $value ? 'index, follow' : 'noindex, nofollow';
        } elseif ($value === '1' || $value === 1) {
            $this->attributes['robots_instructions'] = 'index, follow';
        } elseif ($value === '0' || $value === 0) {
            $this->attributes['robots_instructions'] = 'noindex, nofollow';
        } else {
            $this->attributes['robots_instructions'] = $value;
        }
    }

    /**
     * Get virtual boolean representation of search indexing status.
     */
    public function getRobotsIndexableAttribute(): bool
    {
        $val = $this->robots_instructions ?? 'index, follow';
        return !str_contains((string) $val, 'noindex');
    }

    /**
     * Set robots instructions from virtual boolean status.
     */
    public function setRobotsIndexableAttribute($value): void
    {
        $this->attributes['robots_instructions'] = $value ? 'index, follow' : 'noindex, nofollow';
    }

    /**
     * Get the parent seoable model (Page, Post, etc.).
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the Open Graph Image associated with the SEO settings.
     */
    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(MediaUpload::class, 'og_image_id');
    }
}

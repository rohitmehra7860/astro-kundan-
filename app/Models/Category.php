<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['name', 'slug', 'description', 'type', 'featured_image_id'])]
class Category extends Model
{
    use SoftDeletes, LogsActivity;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function featuredImage()
    {
        return $this->belongsTo(MediaUpload::class, 'featured_image_id');
    }

    public function seoMetadata()
    {
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Category {$eventName}");
    }
}

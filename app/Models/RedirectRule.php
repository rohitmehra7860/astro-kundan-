<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['old_path', 'new_path', 'status_code', 'is_active'])]
class RedirectRule extends Model
{
    use LogsActivity;

    protected $casts = [
        'is_active' => 'boolean',
        'status_code' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->old_path = static::normalizePath($model->old_path, true);
            $model->new_path = static::normalizePath($model->new_path, false);

            if ($model->old_path === $model->new_path) {
                throw new \InvalidArgumentException('The old path cannot be the same as the new path.');
            }
        });
    }

    /**
     * Normalize paths so that they are consistent:
     * - Retains absolute URLs as is.
     * - Relative paths are forced to start with '/' and trailing slashes are trimmed.
     */
    public static function normalizePath(string $path, bool $stripQuery = false): string
    {
        $path = trim($path);

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if ($path === '') {
            return '/';
        }

        if ($stripQuery) {
            // Parse path only to remove any query parameters/fragments from the redirection match path
            $parsed = parse_url($path, PHP_URL_PATH);
            $path = $parsed ?: $path;
        } else {
            // Preserve query parameters and fragments for the destination path, but normalize its path component
            $parts = parse_url($path);
            $pathPart = $parts['path'] ?? '';

            if (!str_starts_with($pathPart, '/')) {
                $pathPart = '/' . $pathPart;
            }

            if ($pathPart !== '/' && str_ends_with($pathPart, '/')) {
                $pathPart = rtrim($pathPart, '/');
            }

            $queryPart = isset($parts['query']) ? '?' . $parts['query'] : '';
            $fragmentPart = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

            return $pathPart . $queryPart . $fragmentPart;
        }

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Redirect Rule {$eventName}");
    }
}

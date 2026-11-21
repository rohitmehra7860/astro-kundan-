<?php

namespace App\Traits;

trait HasImageUrls
{
    /**
     * Resolve the first media URL from a media relation.
     *
     * @param  mixed  $relation
     * @param  string  $collection
     * @param  string  $conversion
     * @return string|null
     */
    protected function getMediaUrl($relation, string $collection = 'gallery', string $conversion = ''): ?string
    {
        return $relation?->getFirstMediaUrl($collection, $conversion) ?: null;
    }

    /**
     * Resolve structured media details from a media relation.
     *
     * @param  mixed  $relation
     * @param  string  $collection
     * @return array|null
     */
    protected function resolveMedia($relation, string $collection = 'gallery'): ?array
    {
        if (!$relation) {
            return null;
        }

        $altText = !empty(trim($relation->alt_text ?? '')) 
            ? trim($relation->alt_text) 
            : (!empty(trim($relation->title ?? '')) ? trim($relation->title) : null);

        return [
            'url' => $this->getMediaUrl($relation, $collection),
            'thumbnail_url' => $this->getMediaUrl($relation, $collection, 'thumb'),
            'alt_text' => $altText,
        ];
    }

    /**
     * Resolve structured media details directly from a Spatie Media object.
     *
     * @param  mixed  $media
     * @return array|null
     */
    protected function resolveSpatieMedia($media): ?array
    {
        if (!$media) {
            return null;
        }

        $altText = !empty(trim($media->getCustomProperty('alt_text') ?? '')) 
            ? trim($media->getCustomProperty('alt_text')) 
            : (!empty(trim($media->name ?? '')) ? trim($media->name) : null);

        return [
            'url' => $media->getUrl(),
            'thumbnail_url' => $media->getUrl('thumb'),
            'alt_text' => $altText,
        ];
    }
}



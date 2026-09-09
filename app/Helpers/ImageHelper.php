<?php

namespace App\Helpers;

class ImageHelper
{
    /**
     * Resolve image URL from relative path or external URL, with fallback to default no-image.
     *
     * @param string|null $path
     * @param string $defaultFallback
     * @return string
     */
    public static function resolve(?string $path, string $defaultFallback = 'images/no-image.png'): string
    {
        $fallbackUrl = asset($defaultFallback);

        if (empty($path)) {
            return $fallbackUrl;
        }

        // If it's already a full web URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');

        // Check if file exists in the public directory
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        // File is missing or not present in folder -> fallback to default no-image
        return $fallbackUrl;
    }

    /**
     * Resolve an array of gallery images with fallback.
     *
     * @param array|null $gallery
     * @param string|null $mainImage
     * @return array
     */
    public static function resolveGallery($gallery, ?string $mainImage = null): array
    {
        $urls = [];

        if (is_string($gallery)) {
            $gallery = json_decode($gallery, true) ?? [];
        }

        if (!empty($gallery) && is_array($gallery)) {
            foreach ($gallery as $img) {
                if (!empty($img)) {
                    $urls[] = self::resolve($img);
                }
            }
        }

        if (empty($urls) && !empty($mainImage)) {
            $urls[] = self::resolve($mainImage);
        }

        if (empty($urls)) {
            $urls[] = asset('images/no-image.png');
        }

        return array_values(array_unique($urls));
    }
}

<?php

namespace App\Traits;

use App\Observers\CacheInvalidationObserver;

/**
 * Trait HasCacheInvalidation
 *
 * Add this trait to any Eloquent model that uses caching.
 * The model must define a static $cacheKeys property with an array of cache key strings.
 *
 * Example:
 *   class Blog extends Model
 *   {
 *       use HasCacheInvalidation;
 *       public static array $cacheKeys = ['trending_blogs', 'latest_blogs'];
 *   }
 * 
 */
trait HasCacheInvalidation
{
    /**
     * Boot the trait — Laravel automatically calls boot{TraitName}
     * on every trait used by a model. This registers the observer.
     */
    public static function bootHasCacheInvalidation(): void
    {
        static::observe(CacheInvalidationObserver::class);
    }
}

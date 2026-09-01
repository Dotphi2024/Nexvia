<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * A common observer that automatically clears related cache keys
 * whenever a model is created, updated, or deleted.
 *
 * Models should define a static $cacheKeys property:
 *   public static array $cacheKeys = ['site_settings'];
 */
class CacheInvalidationObserver
{
    /**
     * Handle the "saved" event (fires on both create and update).
     */
    public function saved(Model $model): void
    {
        $this->clearCache($model);
    }

    /**
     * Handle the "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->clearCache($model);
    }

    /**
     * Clear all cache keys defined on the model.
     */
    protected function clearCache(Model $model): void
    {
        // 1. get the cache keys
        $cacheKeys = property_exists($model, 'cacheKeys') ? $model::$cacheKeys : [];

        // 2. get the extra cache keys from the model if method exists and merger with $cacheKeys
        if (method_exists($model, 'getExtraCacheKeys')) {
            $cacheKeys = array_merge($cacheKeys, $model->getExtraCacheKeys());
        }

        // 3. check if there are any cache keys
        if (empty($cacheKeys)) {
            return;
        }

        // 4. Clear the cache
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        Log::debug('Cache cleared for ' . get_class($model) . ': ' . implode(', ', $cacheKeys));
    }
}

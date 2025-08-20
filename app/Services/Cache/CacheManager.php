<?php

namespace App\Services\Cache;

use App\Contracts\CacheServiceInterface;
use App\Enums\CacheDriver;

class CacheManager
{
    private CacheServiceInterface $cacheService;

    public function __construct(CacheDriver|string|null $driver = null)
    {
        $driver = $driver ?? config('cache.custom_driver', CacheDriver::REDIS->value);
        $this->cacheService = CacheFactory::make($driver);
    }

    /**
     * Get value from cache
     */
    public function get(string $key): mixed
    {
        return $this->cacheService->get($key);
    }

    /**
     * Store value in cache
     */
    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        return $this->cacheService->put($key, $value, $ttl);
    }

    /**
     * Delete value from cache
     */
    public function forget(string $key): bool
    {
        return $this->cacheService->forget($key);
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        return $this->cacheService->has($key);
    }

    /**
     * Clear all cache
     */
    public function flush(): bool
    {
        return $this->cacheService->flush();
    }

    /**
     * Cache or fetch pattern - user requested method
     */
    public function remember(string $key, callable $callback, int $ttl = 3600): mixed
    {
        return $this->cacheService->remember($key, $callback, $ttl);
    }

    /**
     * Alias for remember method as requested
     */
    public function cacheOrFetch(string $key, callable $callback, int $ttl = 3600): mixed
    {
        return $this->remember($key, $callback, $ttl);
    }

    /**
     * Switch to different cache driver at runtime
     */
    public function driver(CacheDriver|string $driver): self
    {
        $instance = new self($driver);
        
        return $instance;
    }

    /**
     * Get underlying cache service instance
     */
    public function getService(): CacheServiceInterface
    {
        return $this->cacheService;
    }
}

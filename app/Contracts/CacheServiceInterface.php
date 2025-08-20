<?php

namespace App\Contracts;

interface CacheServiceInterface
{
    /**
     * Get value from cache by key
     */
    public function get(string $key): mixed;

    /**
     * Store value in cache with key and optional TTL
     */
    public function put(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Delete value from cache by key
     */
    public function forget(string $key): bool;

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool;

    /**
     * Clear all cache
     */
    public function flush(): bool;

    /**
     * Get from cache or execute callback and cache result
     */
    public function remember(string $key, callable $callback, int $ttl = 3600): mixed;

    /**
     * Get connection instance
     */
    public function connection(): mixed;
}

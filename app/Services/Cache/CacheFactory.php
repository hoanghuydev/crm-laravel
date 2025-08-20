<?php

namespace App\Services\Cache;

use App\Contracts\CacheServiceInterface;
use App\Enums\CacheDriver;
use InvalidArgumentException;

class CacheFactory
{
    private static ?CacheServiceInterface $instance = null;

    public static function make(CacheDriver|string $driver): CacheServiceInterface
    {
        // Convert string to enum if needed
        $driverEnum = $driver instanceof CacheDriver ? $driver : CacheDriver::fromString($driver);
        
        // Singleton pattern for cache instances
        if (!isset(self::$instance)) {
            self::$instance = match ($driverEnum) {
                CacheDriver::REDIS => new RedisCacheService(),
                CacheDriver::MEMCACHED => new MemcachedCacheService(),
            };
        }

        return self::$instance;
    }

    /**
     * Get available cache drivers as strings
     */
    public static function getAvailableDrivers(): array
    {
        return CacheDriver::values();
    }

    /**
     * Get available cache drivers as enum cases
     */
    public static function getAvailableDriverEnums(): array
    {
        return CacheDriver::cases();
    }

    /**
     * Clear all cached instances (useful for testing)
     */
    public static function clearInstance(): void
    {
        self::$instance = null;
    }
}

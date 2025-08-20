<?php

namespace App\Enums;

enum CacheDriver: string
{
    case REDIS = 'redis';
    case MEMCACHED = 'memcached';

    /**
     * Get all available cache drivers
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all cache driver names
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Check if driver is supported
     */
    public static function isSupported(string $driver): bool
    {
        return in_array($driver, self::values());
    }

    /**
     * Create from string with validation
     */
    public static function fromString(string $driver): self
    {
        return self::tryFrom($driver) ?? throw new \InvalidArgumentException("Unsupported cache driver: $driver");
    }
}

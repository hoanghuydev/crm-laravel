<?php

namespace App\Services\Cache;

use App\Contracts\CacheServiceInterface;
use Illuminate\Support\Facades\Redis;

class RedisCacheService implements CacheServiceInterface
{
    private static ?object $connection = null;

    private function getConnection(): object
    {
        if (self::$connection === null) {
            self::$connection = Redis::connection();
        }

        return self::$connection;
    }

    public function get(string $key): mixed
    {
        $value = $this->getConnection()->get($key);
        
        return $value ? unserialize($value) : null;
    }

    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        return $this->getConnection()->setex($key, $ttl, serialize($value));
    }

    public function forget(string $key): bool
    {
        return (bool) $this->getConnection()->del($key);
    }

    public function has(string $key): bool
    {
        return (bool) $this->getConnection()->exists($key);
    }

    public function flush(): bool
    {
        return $this->getConnection()->flushall();
    }

    public function remember(string $key, callable $callback, int $ttl = 3600): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    public function connection(): mixed
    {
        return $this->getConnection();
    }
}

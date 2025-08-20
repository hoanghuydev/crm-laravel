<?php

namespace App\Services\Cache;

use App\Contracts\CacheServiceInterface;
use Illuminate\Support\Facades\Cache;

class MemcachedCacheService implements CacheServiceInterface
{
    private static ?object $connection = null;

    private function getConnection(): object
    {
        if (self::$connection === null) {
            self::$connection = Cache::store('memcached');
        }

        return self::$connection;
    }

    public function get(string $key): mixed
    {
        return $this->getConnection()->get($key);
    }

    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        return $this->getConnection()->put($key, $value, $ttl);
    }

    public function forget(string $key): bool
    {
        return $this->getConnection()->forget($key);
    }

    public function has(string $key): bool
    {
        return $this->getConnection()->has($key);
    }

    public function flush(): bool
    {
        return $this->getConnection()->flush();
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

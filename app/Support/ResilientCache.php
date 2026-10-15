<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ResilientCache
{
    private static int $redisDisabledUntil = 0;

    /**
     * Redis accelerates reads, but must never take the application down.
     * A tiny circuit breaker avoids paying the Redis connection timeout on every request.
     */
    public static function remember(string $key, int $seconds, Closure $resolver): mixed
    {
        // Com phpredis ausente, nem tente conexão remota a cada requisição.
        $redisClient = config('database.redis.client', 'phpredis');
        $canUseRedis = $redisClient !== 'phpredis' || extension_loaded('redis');
        if ($canUseRedis && time() >= self::$redisDisabledUntil) {
            try {
                return Cache::store('redis')->remember($key, $seconds, $resolver);
            } catch (Throwable $redisError) {
                self::$redisDisabledUntil = time() + 30;

                Log::warning('Redis cache unavailable; circuit opened for 30 seconds.', [
                    'key' => $key,
                    'error' => $redisError->getMessage(),
                ]);
            }
        }

        try {
            return Cache::store('file')->remember($key, $seconds, $resolver);
        } catch (Throwable $fileError) {
            Log::warning('File cache unavailable; computing without cache.', [
                'key' => $key,
                'error' => $fileError->getMessage(),
            ]);
        }

        return $resolver();
    }
}

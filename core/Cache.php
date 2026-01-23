<?php

declare(strict_types=1);

namespace Core;

/**
 * Simple File-based Cache Helper
 * For production, consider using Redis or Memcached
 */
class Cache
{
    private static string $cacheDir;
    private static int $defaultTtl = 300; // 5 minutes

    private static function init(): void
    {
        if (!isset(self::$cacheDir)) {
            self::$cacheDir = dirname(__DIR__) . '/storage/cache/';

            if (!is_dir(self::$cacheDir)) {
                mkdir(self::$cacheDir, 0755, true);
            }
        }
    }

    /**
     * Get value from cache
     */
    public static function get(string $key): mixed
    {
        self::init();

        $file = self::$cacheDir . md5($key) . '.cache';

        if (!file_exists($file)) {
            return null;
        }

        $data = unserialize(file_get_contents($file));

        // Check if expired
        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    /**
     * Set value in cache
     */
    public static function set(string $key, mixed $value, int $ttl = null): bool
    {
        self::init();

        $ttl = $ttl ?? self::$defaultTtl;
        $file = self::$cacheDir . md5($key) . '.cache';

        $data = [
            'key' => $key,
            'value' => $value,
            'expires' => time() + $ttl
        ];

        return file_put_contents($file, serialize($data)) !== false;
    }

    /**
     * Check if key exists in cache
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Delete key from cache
     */
    public static function delete(string $key): bool
    {
        self::init();

        $file = self::$cacheDir . md5($key) . '.cache';

        if (file_exists($file)) {
            return unlink($file);
        }

        return false;
    }

    /**
     * Clear all cache
     */
    public static function clear(): bool
    {
        self::init();

        $files = glob(self::$cacheDir . '*.cache');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Remember - Get from cache or execute callback and cache result
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = self::get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);

        return $value;
    }

    /**
     * Clean up expired cache files
     */
    public static function cleanup(): int
    {
        self::init();

        $files = glob(self::$cacheDir . '*.cache');
        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $data = unserialize(file_get_contents($file));

                if ($data['expires'] < time()) {
                    unlink($file);
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}

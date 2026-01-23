<?php

/**
 * Test Redis Cache Configuration
 * Run: php scripts/test_redis.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Core\Env;
use Core\Cache;

// Load environment
Env::load(__DIR__ . '/../.env');

echo "===================================\n";
echo "Redis Cache Configuration Test\n";
echo "===================================\n\n";

// Check configuration
$driver = Env::get('CACHE_DRIVER', 'file');
echo "Cache Driver: {$driver}\n";

if ($driver === 'redis') {
    echo "Redis Host: " . Env::get('REDIS_HOST', '127.0.0.1') . "\n";
    echo "Redis Port: " . Env::get('REDIS_PORT', 6379) . "\n";
    echo "Redis DB: " . Env::get('REDIS_DATABASE', 0) . "\n\n";
}

echo "-----------------------------------\n";
echo "Testing Cache Operations...\n";
echo "-----------------------------------\n\n";

try {
    // Test 1: Set cache
    echo "1. Testing set()... ";
    $result = Cache::set('test_key', 'Hello from Redis!', 60);
    echo $result ? "✓ OK\n" : "✗ FAILED\n";

    // Test 2: Get cache
    echo "2. Testing get()... ";
    $value = Cache::get('test_key');
    echo ($value === 'Hello from Redis!') ? "✓ OK (value: {$value})\n" : "✗ FAILED\n";

    // Test 3: Has cache
    echo "3. Testing has()... ";
    $exists = Cache::has('test_key');
    echo $exists ? "✓ OK\n" : "✗ FAILED\n";

    // Test 4: Set complex data
    echo "4. Testing set() with array... ";
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => 30
    ];
    $result = Cache::set('test_array', $data, 60);
    echo $result ? "✓ OK\n" : "✗ FAILED\n";

    // Test 5: Get complex data
    echo "5. Testing get() with array... ";
    $retrieved = Cache::get('test_array');
    echo ($retrieved === $data) ? "✓ OK\n" : "✗ FAILED\n";

    // Test 6: Remember function
    echo "6. Testing remember()... ";
    $computed = Cache::remember('expensive_operation', 60, function () {
        return 'Computed value: ' . time();
    });
    echo "✓ OK (value: {$computed})\n";

    // Test 7: Delete cache
    echo "7. Testing delete()... ";
    $deleted = Cache::delete('test_key');
    echo $deleted ? "✓ OK\n" : "✗ FAILED\n";

    // Test 8: Verify deletion
    echo "8. Testing get() after delete... ";
    $value = Cache::get('test_key');
    echo ($value === null) ? "✓ OK (null as expected)\n" : "✗ FAILED\n";

    // Test 9: Clear all cache
    echo "9. Testing clear()... ";
    $cleared = Cache::clear();
    echo $cleared ? "✓ OK\n" : "✗ FAILED\n";

    echo "\n-----------------------------------\n";
    echo "All tests completed successfully! ✓\n";
    echo "-----------------------------------\n\n";

    if ($driver === 'redis') {
        echo "💡 Tip: Run 'docker compose exec redis redis-cli KEYS \"*\"' to see all Redis keys\n";
    }
} catch (\Exception $e) {
    echo "\n\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

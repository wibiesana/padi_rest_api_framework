<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Core\Database;

// Load environment variables
Core\Env::load(__DIR__ . '/../../.env');

try {
    $db = Database::connection();

    echo "Adding is_locked column to exam_result table...\n";
    $db->exec("ALTER TABLE exam_result ADD COLUMN is_locked TINYINT(1) DEFAULT 0 AFTER exam_status_id");

    echo "Migration successful!\n";
} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}

<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Core\Database;

// Load environment variables
Core\Env::load(__DIR__ . '/../../.env');

try {
    $db = Database::connection();

    echo "Creating exam_supervisors table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS exam_supervisors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exam_id INT NOT NULL,
        user_id INT NOT NULL,
        classroom_id INT,
        session_name VARCHAR(50),
        created_at DATETIME,
        updated_at DATETIME
    )");

    echo "Creating exam_examiners table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS exam_examiners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exam_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at DATETIME,
        updated_at DATETIME
    )");

    echo "Creating exam_reports table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS exam_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exam_id INT NOT NULL,
        classroom_id INT NOT NULL,
        supervisor_id INT NOT NULL,
        report_date DATETIME,
        student_count INT DEFAULT 0,
        present_count INT DEFAULT 0,
        absent_count INT DEFAULT 0,
        incident_report TEXT,
        created_at DATETIME,
        updated_at DATETIME
    )");

    echo "Migration successful!\n";
} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}

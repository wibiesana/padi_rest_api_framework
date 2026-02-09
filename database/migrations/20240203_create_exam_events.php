<?php
require_once __DIR__ . '/../../vendor/autoload.php';
\Core\Env::load(__DIR__ . '/../../.env');

use Core\Database;

function up()
{
    $db = Database::connection();

    // 1. Create exam_events table
    $db->exec("CREATE TABLE IF NOT EXISTS exam_events (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
school_year_id INT NULL,
semester_id INT NULL,
start_date DATETIME NULL,
end_date DATETIME NULL,
status VARCHAR(20) DEFAULT 'active',
description TEXT NULL,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
created_by INT NULL,
updated_by INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Add exam_event_id to exam table
    $db->exec("ALTER TABLE exam ADD COLUMN IF NOT EXISTS exam_event_id INT NULL AFTER id;");

    // 3. Add exam_event_id to question_bank table
    $db->exec("ALTER TABLE question_bank ADD COLUMN IF NOT EXISTS exam_event_id INT NULL AFTER id;");

    echo "Migration completed: exam_events table created and foreign keys added.\n";
}

function down()
{
    $db = Database::connection();
    $db->exec("ALTER TABLE question_bank DROP COLUMN IF EXISTS exam_event_id;");
    $db->exec("ALTER TABLE exam DROP COLUMN IF EXISTS exam_event_id;");
    $db->exec("DROP TABLE IF EXISTS exam_events;");
    echo "Migration reversed.\n";
}

up();

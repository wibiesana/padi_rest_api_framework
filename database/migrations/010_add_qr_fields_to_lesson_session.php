<?php

use Core\DatabaseManager;

return new class
{
    public function up(): void
    {
        $db = DatabaseManager::connection();

        // Add allow_self_attendance
        $db->exec("ALTER TABLE lesson_session ADD COLUMN allow_self_attendance INTEGER DEFAULT 0");

        // Add qr_token
        $db->exec("ALTER TABLE lesson_session ADD COLUMN qr_token VARCHAR(64) DEFAULT NULL");

        echo "✓ Added QR fields to lesson_session table\n";
    }

    public function down(): void
    {
        // SQLite doesn't support DROP COLUMN easily.
    }
};

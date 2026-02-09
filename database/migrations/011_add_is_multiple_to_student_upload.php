<?php

use Core\DatabaseManager;

return new class
{
    public function up(): void
    {
        $db = DatabaseManager::connection();

        // Add is_multiple
        $db->exec("ALTER TABLE student_upload ADD COLUMN is_multiple TINYINT(1) DEFAULT 0 AFTER target");

        echo "✓ Added is_multiple field to student_upload table\n";
    }

    public function down(): void
    {
        $db = DatabaseManager::connection();
        $db->exec("ALTER TABLE student_upload DROP COLUMN is_multiple");
    }
};

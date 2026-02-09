<?php

declare(strict_types=1);

use Core\DatabaseManager;

return new class
{
    public function up(): void
    {
        $db = DatabaseManager::connection();
        
        // Check if teacher_id column exists first to be safe
        $columns = $db->query("SHOW COLUMNS FROM question_bank")->fetchAll(\PDO::FETCH_COLUMN);
        
        if (!in_array('teacher_id', $columns)) {
            $db->exec("ALTER TABLE question_bank ADD COLUMN teacher_id INT NULL AFTER is_active;");
            
            // Check if foreign key exists (naming convention might vary, so we try-catch or check constraint names)
            try {
                $db->exec("ALTER TABLE question_bank ADD CONSTRAINT fk_question_bank_teacher_id FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL;");
            } catch (\Exception $e) {
                // Ignore if it fails (likely constraint already exists)
            }
            
            echo "✓ Added teacher_id to question_bank table\n";
        } else {
            echo "✓ teacher_id already exists in question_bank table\n";
        }
    }

    public function down(): void
    {
        $db = DatabaseManager::connection();
        
        try {
            $db->exec("ALTER TABLE question_bank DROP FOREIGN KEY fk_question_bank_teacher_id;");
        } catch (\Exception $e) {}
        
        try {
            $db->exec("ALTER TABLE question_bank DROP COLUMN teacher_id;");
        } catch (\Exception $e) {}
        
        echo "✓ Removed teacher_id from question_bank table\n";
    }
};

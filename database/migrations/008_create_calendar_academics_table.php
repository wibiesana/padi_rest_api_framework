<?php

declare(strict_types=1);

use Core\DatabaseManager;

return new class
{
    public function up(): void
    {
        $db = DatabaseManager::connection();
        $driver = DatabaseManager::getDriver();

        if ($driver === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS calendar_academics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                start_date DATE NOT NULL,
                end_date DATE,
                color TEXT,
                is_active INTEGER DEFAULT 1,
                created_by INTEGER,
                updated_by INTEGER,
                created_at INTEGER DEFAULT (strftime('%s', 'now')),
                updated_at INTEGER DEFAULT (strftime('%s', 'now'))
            )";
            $db->exec($sql);
        } elseif ($driver === 'pgsql') {
            $sql = "CREATE TABLE IF NOT EXISTS calendar_academics (
                id SERIAL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                start_date DATE NOT NULL,
                end_date DATE,
                color VARCHAR(10),
                is_active SMALLINT DEFAULT 1,
                created_by INTEGER,
                updated_by INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->exec($sql);
        } else {
            // MySQL/MariaDB
            $sql = "CREATE TABLE IF NOT EXISTS calendar_academics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                start_date DATE NOT NULL,
                end_date DATE,
                color VARCHAR(10),
                is_active TINYINT DEFAULT 1,
                created_by INT,
                updated_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $db->exec($sql);
        }

        echo "✓ Calendar Academics table created\n";
    }

    public function down(): void
    {
        $db = DatabaseManager::connection();
        $db->exec("DROP TABLE IF EXISTS calendar_academics");
        echo "✓ Calendar Academics table dropped\n";
    }
};

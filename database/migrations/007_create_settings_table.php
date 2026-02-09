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
            $sql = "CREATE TABLE IF NOT EXISTS settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting TEXT,
                created_at INTEGER DEFAULT (strftime('%s', 'now')),
                updated_at INTEGER DEFAULT (strftime('%s', 'now'))
            )";
            $db->exec($sql);
        } elseif ($driver === 'pgsql') {
            $sql = "CREATE TABLE IF NOT EXISTS settings (
                id SERIAL PRIMARY KEY,
                setting JSONB,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->exec($sql);

            // Create trigger for updated_at
            $db->exec("
                CREATE OR REPLACE FUNCTION update_updated_at_column()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    NEW.updated_at = CURRENT_TIMESTAMP;
                    RETURN NEW;
                END;
                \$\$ language 'plpgsql';
            ");

            $db->exec("
                DROP TRIGGER IF EXISTS update_settings_updated_at ON settings;
                CREATE TRIGGER update_settings_updated_at 
                    BEFORE UPDATE ON settings 
                    FOR EACH ROW 
                    EXECUTE FUNCTION update_updated_at_column();
            ");
        } else {
            // MySQL/MariaDB
            $sql = "CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $db->exec($sql);
        }

        echo "✓ Settings table created\n";
    }

    public function down(): void
    {
        $db = DatabaseManager::connection();
        $db->exec("DROP TABLE IF EXISTS settings");
        echo "✓ Settings table dropped\n";
    }
};

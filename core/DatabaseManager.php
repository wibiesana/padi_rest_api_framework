<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;

/**
 * Database Manager - Handles multiple database connections
 * 
 * Supports: MySQL, MariaDB, PostgreSQL, SQLite
 * 
 * @example
 * // Get default connection
 * $db = DatabaseManager::connection();
 * 
 * // Get specific connection
 * $postgres = DatabaseManager::connection('pgsql');
 * $mysql = DatabaseManager::connection('mysql');
 * $sqlite = DatabaseManager::connection('sqlite');
 */
class DatabaseManager
{
    /**
     * Store all database connections
     */
    private static array $connections = [];

    /**
     * Database configurations
     */
    private static ?array $config = null;

    /**
     * Default connection name
     */
    private static ?string $defaultConnection = null;

    /**
     * Get database connection by name
     * 
     * @param string|null $name Connection name from config, null for default
     * @return PDO
     * @throws PDOException
     */
    public static function connection(?string $name = null): PDO
    {
        // Load config if not loaded
        if (self::$config === null) {
            self::loadConfig();
        }

        // Use default connection if none specified
        if ($name === null) {
            $name = self::$defaultConnection;
        }

        // Return existing connection if already created
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        // Validate connection exists in config
        if (!isset(self::$config['connections'][$name])) {
            throw new PDOException("Database connection '{$name}' not configured");
        }

        // Create new connection
        self::$connections[$name] = self::createConnection(
            self::$config['connections'][$name]
        );

        return self::$connections[$name];
    }

    /**
     * Create PDO connection based on driver
     * 
     * @param array $config Connection configuration
     * @return PDO
     * @throws PDOException
     */
    private static function createConnection(array $config): PDO
    {
        $driver = $config['driver'] ?? 'mysql';

        try {
            switch ($driver) {
                case 'mysql':
                case 'mariadb':
                    return self::createMySQLConnection($config);

                case 'pgsql':
                case 'postgres':
                case 'postgresql':
                    return self::createPostgreSQLConnection($config);

                case 'sqlite':
                    return self::createSQLiteConnection($config);

                default:
                    throw new PDOException("Unsupported database driver: {$driver}");
            }
        } catch (PDOException $e) {
            throw new PDOException(
                "Failed to connect to {$driver} database: " . $e->getMessage()
            );
        }
    }

    /**
     * Create MySQL/MariaDB connection
     */
    private static function createMySQLConnection(array $config): PDO
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        return new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            $config['options'] ?? self::getDefaultOptions()
        );
    }

    /**
     * Create PostgreSQL connection
     */
    private static function createPostgreSQLConnection(array $config): PDO
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 5432;
        $database = $config['database'];

        $dsn = "pgsql:host={$host};port={$port};dbname={$database}";

        // Add schema if specified
        if (isset($config['schema'])) {
            $dsn .= ";options='--search_path={$config['schema']}'";
        }

        return new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            $config['options'] ?? self::getDefaultOptions()
        );
    }

    /**
     * Create SQLite connection
     */
    private static function createSQLiteConnection(array $config): PDO
    {
        $database = $config['database'];

        // Handle in-memory database
        if ($database === ':memory:') {
            $dsn = 'sqlite::memory:';
        } else {
            // Ensure database directory exists
            $dir = dirname($database);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $dsn = "sqlite:{$database}";
        }

        return new PDO(
            $dsn,
            null,
            null,
            $config['options'] ?? self::getDefaultOptions()
        );
    }

    /**
     * Get default PDO options
     */
    private static function getDefaultOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];
    }

    /**
     * Load database configuration
     */
    private static function loadConfig(): void
    {
        $configPath = dirname(__DIR__) . '/app/Config/database.php';

        if (!file_exists($configPath)) {
            throw new PDOException("Database configuration file not found");
        }

        self::$config = require $configPath;
        self::$defaultConnection = self::$config['default'] ?? 'mysql';
    }

    /**
     * Set default connection
     */
    public static function setDefaultConnection(string $name): void
    {
        self::$defaultConnection = $name;
    }

    /**
     * Get default connection name
     */
    public static function getDefaultConnection(): string
    {
        if (self::$config === null) {
            self::loadConfig();
        }

        return self::$defaultConnection;
    }

    /**
     * Add new connection at runtime
     */
    public static function addConnection(string $name, array $config): void
    {
        if (self::$config === null) {
            self::loadConfig();
        }

        self::$config['connections'][$name] = $config;
    }

    /**
     * Disconnect a specific connection
     */
    public static function disconnect(?string $name = null): void
    {
        if ($name === null) {
            $name = self::$defaultConnection;
        }

        if (isset(self::$connections[$name])) {
            unset(self::$connections[$name]);
        }
    }

    /**
     * Disconnect all connections
     */
    public static function disconnectAll(): void
    {
        self::$connections = [];
    }

    /**
     * Get all active connections
     */
    public static function getConnections(): array
    {
        return array_keys(self::$connections);
    }

    /**
     * Check if connection exists
     */
    public static function hasConnection(string $name): bool
    {
        if (self::$config === null) {
            self::loadConfig();
        }

        return isset(self::$config['connections'][$name]);
    }

    /**
     * Begin transaction on specific connection
     */
    public static function beginTransaction(?string $name = null): bool
    {
        return self::connection($name)->beginTransaction();
    }

    /**
     * Commit transaction on specific connection
     */
    public static function commit(?string $name = null): bool
    {
        return self::connection($name)->commit();
    }

    /**
     * Rollback transaction on specific connection
     */
    public static function rollback(?string $name = null): bool
    {
        return self::connection($name)->rollBack();
    }

    /**
     * Get database driver name
     */
    public static function getDriver(?string $name = null): string
    {
        if (self::$config === null) {
            self::loadConfig();
        }

        if ($name === null) {
            $name = self::$defaultConnection;
        }

        return self::$config['connections'][$name]['driver'] ?? 'mysql';
    }
}

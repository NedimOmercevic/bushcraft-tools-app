<?php

namespace App\Dao;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Shared PDO connection. Credentials come from backend/config/config.php.
 */
class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $configPath = dirname(__DIR__) . '/config/config.php';
        if (!is_readable($configPath)) {
            throw new RuntimeException(
                'Missing config.php. Copy backend/config/config.example.php to config.php.'
            );
        }

        $config = require $configPath;
        $db = $config['db'] ?? [];

        $host = $db['host'] ?? 'localhost';
        $port = (int) ($db['port'] ?? 3306);
        $name = $db['name'] ?? 'bushcraft_tools';
        $user = $db['user'] ?? 'root';
        $pass = $db['pass'] ?? '';
        $charset = $db['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        try {
            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }

        return self::$connection;
    }
}

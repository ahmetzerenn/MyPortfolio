<?php
/**
 * PDO database connection (lazy singleton).
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * @return PDO|null Connection or null if credentials invalid / server unreachable.
 */
function db(): ?PDO
{
    static $pdo = null;
    static $failed = false;

    if ($failed) {
        return null;
    }

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        $failed = true;
        error_log('Database connection failed: ' . $e->getMessage());
        return null;
    }
}

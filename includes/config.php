<?php
/**
 * Application configuration — copy to environment-specific values in production.
 *
 * APP_BASE_URL: path prefix when the site is not at domain root (no trailing slash).
 * DB_*: MySQL credentials used by includes/database.php (PDO).
 */
declare(strict_types=1);

define('APP_NAME', 'Portfolio');
define('APP_BASE_URL', ''); // e.g. '/portfolio' if deployed in subfolder; leave '' for root

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('DEFAULT_LANG', 'en');
define('SUPPORTED_LANGS', ['en', 'tr']);

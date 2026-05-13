<?php
/**
 * GET: JSON health check (DB optional).
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

$ok = db() !== null;

echo json_encode([
    'ok'      => true,
    'db'      => $ok,
    'app'     => APP_NAME,
    'version' => '0.1.0',
]);

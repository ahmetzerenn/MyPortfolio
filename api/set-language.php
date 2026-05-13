<?php
/**
 * POST: lang=en|tr — sets session + cookie, returns JSON.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
if (!is_string($token) || !is_string($sessionToken) || !hash_equals($sessionToken, $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'invalid_csrf']);
    exit;
}

$lang = isset($_POST['lang']) && is_string($_POST['lang']) ? strtolower($_POST['lang']) : '';
if (!in_array($lang, SUPPORTED_LANGS, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_lang']);
    exit;
}

set_lang($lang);
setcookie(LANG_COOKIE, $lang, [
    'expires'  => time() + LANG_COOKIE_LIFETIME,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => false,
    'samesite' => 'Lax',
]);

echo json_encode(['ok' => true, 'lang' => $lang]);

<?php
/**
 * Public front controller bootstrap: PHP session, CSRF token, language resolution, translations.
 * Include this (or ensure session + config) before any page that needs __() or csrf_token().
 */
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';

if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token(): string
{
    return is_string($_SESSION['csrf_token'] ?? null) ? $_SESSION['csrf_token'] : '';
}

function verify_csrf_token(?string $token): bool
{
    $session = csrf_token();
    if ($session === '' || $token === null || $token === '') {
        return false;
    }
    return hash_equals($session, $token);
}

const LANG_COOKIE = 'portfolio_lang';
const LANG_COOKIE_LIFETIME = 60 * 60 * 24 * 365; // 1 year

/**
 * Resolve language: GET ?lang= > POST/cookie from prior API call > session > cookie > default.
 */
function resolve_language(): string
{
    if (isset($_GET['lang']) && is_string($_GET['lang'])) {
        $code = strtolower($_GET['lang']);
        if (in_array($code, SUPPORTED_LANGS, true)) {
            set_lang($code);
            setcookie(LANG_COOKIE, $code, [
                'expires'  => time() + LANG_COOKIE_LIFETIME,
                'path'     => '/',
                'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
            return $code;
        }
    }

    if (isset($_SESSION['lang']) && is_string($_SESSION['lang'])) {
        return $_SESSION['lang'];
    }

    if (isset($_COOKIE[LANG_COOKIE]) && is_string($_COOKIE[LANG_COOKIE])) {
        $code = strtolower($_COOKIE[LANG_COOKIE]);
        if (in_array($code, SUPPORTED_LANGS, true)) {
            set_lang($code);
            return $code;
        }
    }

    set_lang(DEFAULT_LANG);
    return DEFAULT_LANG;
}

resolve_language();
$GLOBALS['_translations'] = load_translations();

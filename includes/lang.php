<?php
/**
 * Translation loader and __() helper.
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/** @var array<string, string>|null */
$GLOBALS['_translations'] = null;

function current_lang(): string
{
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}

function set_lang(string $code): void
{
    if (!in_array($code, SUPPORTED_LANGS, true)) {
        $code = DEFAULT_LANG;
    }
    $_SESSION['lang'] = $code;
}

function load_translations(?string $lang = null): array
{
    $lang = $lang ?? current_lang();
    if (!in_array($lang, SUPPORTED_LANGS, true)) {
        $lang = DEFAULT_LANG;
    }

    $file = __DIR__ . '/lang/' . $lang . '.php';
    if (!is_readable($file)) {
        $file = __DIR__ . '/lang/' . DEFAULT_LANG . '.php';
    }

    /** @var array<string, string> */
    return require $file;
}

function __(string $key, string $default = ''): string
{
    if ($GLOBALS['_translations'] === null) {
        $GLOBALS['_translations'] = load_translations();
    }

    return $GLOBALS['_translations'][$key] ?? ($default !== '' ? $default : $key);
}

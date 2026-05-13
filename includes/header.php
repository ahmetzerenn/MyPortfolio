<?php
declare(strict_types=1);

if (!isset($pageTitle)) {
    $pageTitle = __('meta_title');
}
if (!isset($activeNav)) {
    $activeNav = '';
}

$base = APP_BASE_URL;
$lang = current_lang();
$adminLoginUrl = ($base !== '' ? rtrim($base, '/') : '') . '/admin/login.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#07080d" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#f6f4ef" media="(prefers-color-scheme: light)">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta name="app-base" content="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="description" content="<?= htmlspecialchars(__('meta_description'), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/assets/css/main.css">
    <script>
    (function () {
        try {
            var t = localStorage.getItem('portfolio-theme');
            if (t === 'light' || t === 'dark') {
                document.documentElement.setAttribute('data-theme', t);
            } else if (window.matchMedia('(prefers-color-scheme: light)').matches) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        } catch (e) {}
    })();
    </script>
</head>
<body>
    <div class="noise" aria-hidden="true"></div>
    <a class="skip-link" href="#main"><?= htmlspecialchars(__('skip_content'), ENT_QUOTES, 'UTF-8') ?></a>
    <header class="site-header" role="banner">
        <div class="container header-inner">
            <a class="logo" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/index.php">
                <span class="logo-mark" aria-hidden="true"></span>
                <span class="logo-text"><?= htmlspecialchars(__('meta_title'), ENT_QUOTES, 'UTF-8') ?></span>
            </a>
            <nav class="main-nav" aria-label="Primary">
                <ul>
                    <li><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/index.php"<?= $activeNav === 'home' ? ' aria-current="page"' : '' ?>><?= htmlspecialchars(__('nav_home'), ENT_QUOTES, 'UTF-8') ?></a></li>
                    <li><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/about.php"<?= $activeNav === 'about' ? ' aria-current="page"' : '' ?>><?= htmlspecialchars(__('nav_about'), ENT_QUOTES, 'UTF-8') ?></a></li>
                    <li><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/projects.php"<?= $activeNav === 'projects' ? ' aria-current="page"' : '' ?>><?= htmlspecialchars(__('nav_projects'), ENT_QUOTES, 'UTF-8') ?></a></li>
                    <li><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/contact.php"<?= $activeNav === 'contact' ? ' aria-current="page"' : '' ?>><?= htmlspecialchars(__('nav_contact'), ENT_QUOTES, 'UTF-8') ?></a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <a class="header-admin-link" href="<?= htmlspecialchars($adminLoginUrl, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars(__('nav_admin_title'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('nav_admin'), ENT_QUOTES, 'UTF-8') ?></a>
                <button type="button" class="theme-toggle" data-theme-toggle data-label-to-light="<?= htmlspecialchars(__('theme_to_light'), ENT_QUOTES, 'UTF-8') ?>" data-label-to-dark="<?= htmlspecialchars(__('theme_to_dark'), ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars(__('theme_to_light'), ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars(__('theme_label'), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="theme-toggle__icon theme-toggle__icon--sun" aria-hidden="true"></span>
                    <span class="theme-toggle__icon theme-toggle__icon--moon" aria-hidden="true"></span>
                </button>
                <div class="lang-switcher" data-i18n-root>
                    <span class="visually-hidden" id="lang-label"><?= htmlspecialchars(__('lang_label'), ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="lang-toggle" role="group" aria-labelledby="lang-label">
                        <button type="button" class="lang-btn<?= $lang === 'en' ? ' is-active' : '' ?>" data-lang="en" aria-pressed="<?= $lang === 'en' ? 'true' : 'false' ?>"><?= htmlspecialchars(__('lang_en'), ENT_QUOTES, 'UTF-8') ?></button>
                        <button type="button" class="lang-btn<?= $lang === 'tr' ? ' is-active' : '' ?>" data-lang="tr" aria-pressed="<?= $lang === 'tr' ? 'true' : 'false' ?>"><?= htmlspecialchars(__('lang_tr'), ENT_QUOTES, 'UTF-8') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </header>

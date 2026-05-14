<?php
declare(strict_types=1);

/** @var string $pageTitle */
$base = APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '';
$adminUser = admin_current_user();
$username = is_array($adminUser) ? ($adminUser['username'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#07080d" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#f6f4ef" media="(prefers-color-scheme: light)">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/assets/css/admin.css">
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
<body class="admin-body">
    <header class="admin-bar">
        <div class="admin-bar__inner container">
            <a class="admin-bar__brand" href="<?= htmlspecialchars(admin_url('index.php'), ENT_QUOTES, 'UTF-8') ?>">Admin</a>
            <nav class="admin-bar__nav" aria-label="Admin">
                <a href="<?= htmlspecialchars(admin_url('index.php'), ENT_QUOTES, 'UTF-8') ?>">Projects</a>
                <a href="<?= htmlspecialchars(admin_url('messages.php'), ENT_QUOTES, 'UTF-8') ?>">Messages</a>
                <a href="<?= htmlspecialchars(admin_url('project-form.php'), ENT_QUOTES, 'UTF-8') ?>">New project</a>
                <a href="<?= htmlspecialchars(admin_url('change-password.php'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('admin_nav_password'), ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= htmlspecialchars($base !== '' ? $base . '/index.php' : '/index.php', ENT_QUOTES, 'UTF-8') ?>">Site</a>
            </nav>
            <div class="admin-bar__user">
                <?php if ($username !== ''): ?>
                    <span class="admin-bar__name"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <a class="admin-bar__logout" href="<?= htmlspecialchars(admin_url('logout.php'), ENT_QUOTES, 'UTF-8') ?>">Log out</a>
            </div>
        </div>
    </header>
    <main class="admin-main container">

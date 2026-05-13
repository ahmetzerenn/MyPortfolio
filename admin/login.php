<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/admin_auth.php';

$base = APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '';
$homeUrl = $base . '/index.php';
$langAttr = current_lang();

if (admin_current_user() !== null) {
    header('Location: ' . admin_url('index.php'));
    exit;
}

$hintUsername = admin_login_hint_username();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
    if (!verify_csrf_token($token)) {
        $error = 'Session expired. Refresh the page and try again.';
    } else {
        $user = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
        $pass = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
        if (!admin_attempt_login($user, $pass)) {
            $error = 'Invalid username or password.';
        } else {
            admin_set_login_hint_cookie($user);
            header('Location: ' . admin_url('index.php'));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($langAttr, ENT_QUOTES, 'UTF-8') ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#07080d" media="(prefers-color-scheme: dark)">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin login — <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
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
    <main class="admin-login">
        <div class="admin-login__card">
            <h1>Admin login</h1>
            <?php if ($error !== ''): ?>
                <p class="admin-flash admin-flash--error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <form class="admin-login__form" method="post" action="" autocomplete="on">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <label>
                    Username
                    <input type="text" name="username" required autocomplete="username" maxlength="64" value="<?= htmlspecialchars($hintUsername, ENT_QUOTES, 'UTF-8') ?>">
                </label>
                <label>
                    Password
                    <input type="password" name="password" required autocomplete="current-password">
                </label>
                <button type="submit" class="btn btn--primary">Sign in</button>
            </form>
            <p class="admin-login__back">
                <a class="btn btn--ghost admin-login__home-btn" href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('admin_back_home'), ENT_QUOTES, 'UTF-8') ?></a>
            </p>
        </div>
    </main>
</body>
</html>

<?php
/**
 * Admin authentication (session, optional login-hint cookie, admin_users table).
 */
declare(strict_types=1);

require_once __DIR__ . '/database.php';

const ADMIN_LOGIN_HINT_COOKIE = 'portfolio_admin_login_hint';

/** Minimum length for a new admin password (bytes / single-byte chars). */
const ADMIN_PASSWORD_MIN_LENGTH = 10;

/** bcrypt effectively uses the first 72 bytes; keep the limit explicit. */
const ADMIN_PASSWORD_MAX_LENGTH = 72;

/**
 * Optional convenience cookie: last successful admin username (non-secret).
 */
function admin_set_login_hint_cookie(string $username): void
{
    $u = trim($username);
    if ($u === '' || mb_strlen($u) > 64 || !preg_match('/\A[A-Za-z0-9._-]+\z/', $u)) {
        return;
    }
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    setcookie(ADMIN_LOGIN_HINT_COOKIE, $u, [
        'expires'  => time() + 60 * 60 * 24 * 120,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function admin_login_hint_username(): string
{
    if (!isset($_COOKIE[ADMIN_LOGIN_HINT_COOKIE]) || !is_string($_COOKIE[ADMIN_LOGIN_HINT_COOKIE])) {
        return '';
    }
    $u = trim($_COOKIE[ADMIN_LOGIN_HINT_COOKIE]);
    if ($u === '' || mb_strlen($u) > 64 || !preg_match('/\A[A-Za-z0-9._-]+\z/', $u)) {
        return '';
    }
    return $u;
}

function admin_url(string $path): string
{
    $base = APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '';
    return $base . '/admin/' . ltrim($path, '/');
}

function admin_redirect_to_login(): never
{
    header('Location: ' . admin_url('login.php'));
    exit;
}

function admin_clear_login_session(): void
{
    unset($_SESSION['admin_user_id'], $_SESSION['admin_username']);
}

function admin_user_exists(int $id): bool
{
    if ($id < 1) {
        return false;
    }
    $pdo = db();
    if ($pdo === null) {
        return false;
    }
    try {
        $stmt = $pdo->prepare('SELECT 1 FROM admin_users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('admin_user_exists: ' . $e->getMessage());
        return false;
    }
}

/**
 * Block unauthenticated or invalid sessions (user removed from DB).
 */
function admin_require_authenticated(): void
{
    $raw = $_SESSION['admin_user_id'] ?? null;
    $id = is_int($raw) ? $raw : (is_string($raw) && ctype_digit($raw) ? (int) $raw : 0);
    if ($id < 1) {
        admin_redirect_to_login();
    }
    if (!admin_user_exists($id)) {
        admin_clear_login_session();
        admin_redirect_to_login();
    }
}

/**
 * @return array{id:int,username:string}|null
 */
function admin_current_user(): ?array
{
    $raw = $_SESSION['admin_user_id'] ?? null;
    $id = is_int($raw) ? $raw : (is_string($raw) && ctype_digit($raw) ? (int) $raw : 0);
    if ($id < 1) {
        return null;
    }
    $name = $_SESSION['admin_username'] ?? '';
    $name = is_string($name) ? $name : '';
    return ['id' => $id, 'username' => $name];
}

function admin_attempt_login(string $username, string $password): bool
{
    $username = trim($username);
    if ($username === '' || $password === '') {
        return false;
    }

    $pdo = db();
    if ($pdo === null) {
        return false;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id, username, password_hash FROM admin_users WHERE username = :u LIMIT 1'
        );
        $stmt->execute([':u' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return false;
        }
        $hash = $row['password_hash'] ?? '';
        if (!is_string($hash) || !password_verify($password, $hash)) {
            return false;
        }
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $pdo->prepare('UPDATE admin_users SET password_hash = :h WHERE id = :id LIMIT 1');
            $upd->execute([':h' => $newHash, ':id' => (int) $row['id']]);
        }
        session_regenerate_id(true);
        $_SESSION['admin_user_id'] = (int) $row['id'];
        $_SESSION['admin_username'] = (string) $row['username'];
        return true;
    } catch (PDOException $e) {
        error_log('admin_attempt_login: ' . $e->getMessage());
        return false;
    }
}

function admin_logout(): void
{
    admin_clear_login_session();
    session_regenerate_id(true);
}

/**
 * Update the signed-in admin user's password after verifying the current one.
 *
 * @return string|null Translation key on failure, null on success.
 */
function admin_change_password(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): ?string
{
    if ($userId < 1) {
        return 'admin_password_error_session';
    }

    if ($newPassword !== $confirmPassword) {
        return 'admin_password_error_mismatch';
    }

    $len = strlen($newPassword);
    if ($len < ADMIN_PASSWORD_MIN_LENGTH) {
        return 'admin_password_error_short';
    }
    if ($len > ADMIN_PASSWORD_MAX_LENGTH) {
        return 'admin_password_error_long';
    }

    $pdo = db();
    if ($pdo === null) {
        return 'admin_password_error_db';
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT password_hash FROM admin_users WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return 'admin_password_error_current';
        }
        $hash = $row['password_hash'] ?? '';
        if (!is_string($hash) || $hash === '' || !password_verify($currentPassword, $hash)) {
            return 'admin_password_error_current';
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($newHash === false) {
            return 'admin_password_error_db';
        }

        $upd = $pdo->prepare('UPDATE admin_users SET password_hash = :h WHERE id = :id LIMIT 1');
        $upd->execute([':h' => $newHash, ':id' => $userId]);
        if ($upd->rowCount() !== 1) {
            return 'admin_password_error_db';
        }

        session_regenerate_id(true);
        return null;
    } catch (PDOException $e) {
        error_log('admin_change_password: ' . $e->getMessage());
        return 'admin_password_error_db';
    }
}

function admin_set_flash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

/**
 * @return array{type:string,message:string}|null
 */
function admin_consume_flash(): ?array
{
    if (empty($_SESSION['admin_flash']) || !is_array($_SESSION['admin_flash'])) {
        return null;
    }
    $f = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
    if (!isset($f['type'], $f['message']) || !is_string($f['type']) || !is_string($f['message'])) {
        return null;
    }
    return $f;
}

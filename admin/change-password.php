<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth-check.php';

$adminUser = admin_current_user();
$userId = is_array($adminUser) ? (int) ($adminUser['id'] ?? 0) : 0;

$pageTitle = __('admin_password_page_title');
$error = '';
$flash = admin_consume_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
    if (!verify_csrf_token($token)) {
        $error = __('admin_password_error_csrf');
    } else {
        $current = isset($_POST['current_password']) && is_string($_POST['current_password']) ? $_POST['current_password'] : '';
        $new = isset($_POST['new_password']) && is_string($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm = isset($_POST['confirm_password']) && is_string($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        $failKey = admin_change_password($userId, $current, $new, $confirm);
        if ($failKey !== null) {
            $error = __($failKey);
        } else {
            admin_set_flash('success', __('admin_password_success'));
            header('Location: ' . admin_url('change-password.php'));
            exit;
        }
    }
}

require_once __DIR__ . '/includes/admin-header.php';
?>

        <h1 class="admin-page-title"><?= htmlspecialchars(__('admin_password_heading'), ENT_QUOTES, 'UTF-8') ?></h1>

        <?php if (is_array($flash) && ($flash['type'] ?? '') === 'success' && isset($flash['message']) && is_string($flash['message'])): ?>
            <p class="admin-flash admin-flash--success" role="status"><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <p class="admin-flash admin-flash--error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form class="admin-form" method="post" action="<?= htmlspecialchars(admin_url('change-password.php'), ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <p class="admin-form__hint"><?= htmlspecialchars(__('admin_password_hint'), ENT_QUOTES, 'UTF-8') ?></p>
            <label>
                <?= htmlspecialchars(__('admin_password_current'), ENT_QUOTES, 'UTF-8') ?>
                <input type="password" name="current_password" required autocomplete="current-password" maxlength="128">
            </label>
            <label>
                <?= htmlspecialchars(__('admin_password_new'), ENT_QUOTES, 'UTF-8') ?>
                <input type="password" name="new_password" required autocomplete="new-password" maxlength="128">
            </label>
            <label>
                <?= htmlspecialchars(__('admin_password_confirm'), ENT_QUOTES, 'UTF-8') ?>
                <input type="password" name="confirm_password" required autocomplete="new-password" maxlength="128">
            </label>
            <div class="admin-form__actions">
                <button type="submit" class="btn btn--primary"><?= htmlspecialchars(__('admin_password_submit'), ENT_QUOTES, 'UTF-8') ?></button>
            </div>
        </form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>

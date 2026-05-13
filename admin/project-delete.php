<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth-check.php';
require_once dirname(__DIR__) . '/includes/admin_projects_repository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    admin_set_flash('error', 'Invalid request method.');
    header('Location: ' . admin_url('index.php'));
    exit;
}

$token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
if (!verify_csrf_token($token)) {
    admin_set_flash('error', 'Session expired. Try again.');
    header('Location: ' . admin_url('index.php'));
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    admin_set_flash('error', 'Invalid project.');
    header('Location: ' . admin_url('index.php'));
    exit;
}

if (admin_delete_project((int) $id)) {
    admin_set_flash('success', 'Project deleted.');
} else {
    admin_set_flash('error', 'Could not delete project.');
}

header('Location: ' . admin_url('index.php'));
exit;

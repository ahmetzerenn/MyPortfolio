<?php
/**
 * Contact form: validates CSRF, lengths, email; inserts into `messages` via PDO.
 * Used by contact.php for both full-page POST/redirect and AJAX JSON responses.
 */
declare(strict_types=1);

require_once __DIR__ . '/portfolio_repository.php';

const PORTFOLIO_CONTACT_BODY_MAX = 8000;

function portfolio_wants_json_request(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (is_string($accept) && str_contains($accept, 'application/json')) {
        return true;
    }
    $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    return is_string($xhr) && strtolower($xhr) === 'xmlhttprequest';
}

/**
 * @return array{ok:bool, message_key:string}
 */
function portfolio_process_contact_post(): array
{
    $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
    if (!verify_csrf_token($token)) {
        return ['ok' => false, 'message_key' => 'contact_error_csrf'];
    }

    $name = isset($_POST['name']) && is_string($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
    $message = isset($_POST['message']) && is_string($_POST['message']) ? trim($_POST['message']) : '';

    if ($name === '' || $email === '' || $message === '') {
        return ['ok' => false, 'message_key' => 'contact_error_validation'];
    }
    if (mb_strlen($name) > 120 || mb_strlen($email) > 255 || mb_strlen($message) > PORTFOLIO_CONTACT_BODY_MAX) {
        return ['ok' => false, 'message_key' => 'contact_error_validation'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message_key' => 'contact_error_validation'];
    }
    if (portfolio_insert_contact_message($name, $email, $message)) {
        return ['ok' => true, 'message_key' => 'contact_success'];
    }

    return ['ok' => false, 'message_key' => 'contact_error_server'];
}

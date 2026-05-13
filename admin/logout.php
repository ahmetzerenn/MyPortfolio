<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/admin_auth.php';

admin_logout();

header('Location: ' . admin_url('login.php'));
exit;

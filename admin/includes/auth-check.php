<?php
declare(strict_types=1);

if (!defined('APP_NAME')) {
    require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
}

require_once dirname(__DIR__, 2) . '/includes/database.php';
require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';

admin_require_authenticated();

<?php
declare(strict_types=1);

$base = APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '';
?>
    </main>
    <script src="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/assets/js/main.js" defer></script>
</body>
</html>

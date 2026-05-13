<?php
declare(strict_types=1);

$base = APP_BASE_URL;
?>
    <footer class="site-footer" role="contentinfo">
        <div class="container footer-inner">
            <p class="footer-line">
                <span class="footer-brand"><?= htmlspecialchars(__('meta_title'), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="footer-dot" aria-hidden="true"></span>
                <span><?= htmlspecialchars(__('footer_rights'), ENT_QUOTES, 'UTF-8') ?></span>
            </p>
        </div>
    </footer>
    <script src="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/assets/js/i18n.js" defer></script>
    <script src="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/assets/js/main.js" defer></script>
</body>
</html>

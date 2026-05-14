<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/contact_handler.php';

$pageTitle = __('meta_title') . ' — ' . __('nav_contact');
$activeNav = 'contact';

$contactFlash = null;
if (isset($_SESSION['contact_flash']) && is_array($_SESSION['contact_flash'])) {
    $contactFlash = $_SESSION['contact_flash'];
    unset($_SESSION['contact_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = portfolio_process_contact_post();

    if (portfolio_wants_json_request()) {
        header('Content-Type: application/json; charset=utf-8');
        $payload = json_encode(
            [
                'ok'      => $result['ok'],
                'message' => __($result['message_key']),
            ],
            JSON_UNESCAPED_UNICODE
        );
        echo $payload !== false ? $payload : '{"ok":false,"message":""}';
        exit;
    }

    $_SESSION['contact_flash'] = [
        'type' => $result['ok'] ? 'success' : 'error',
        'key'  => $result['message_key'],
    ];

    header('Location: ' . (APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '') . '/contact.php', true, 303);
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

    <main id="main" class="main-content">
        <header class="page-hero">
            <div class="container" data-reveal>
                <p class="page-hero__eyebrow"><?= htmlspecialchars(__('nav_contact'), ENT_QUOTES, 'UTF-8') ?></p>
                <h1><?= htmlspecialchars(__('contact_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
        </header>

        <section class="page-section contact-page-section" aria-labelledby="contact-form-heading">
            <div class="container contact-layout">
                <div class="contact-primary" data-reveal>
                    <header class="contact-intro">
                        <h2 id="contact-form-heading" class="contact-intro__title"><?= htmlspecialchars(__('contact_form_heading'), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="contact-intro__lead"><?= htmlspecialchars(__('contact_lead'), ENT_QUOTES, 'UTF-8') ?></p>
                    </header>
                    <?php if (is_array($contactFlash) && isset($contactFlash['type'], $contactFlash['key']) && is_string($contactFlash['key'])): ?>
                        <?php
                        $flashClass = $contactFlash['type'] === 'success' ? 'form-flash form-flash--success' : 'form-flash form-flash--error';
                        ?>
                        <p class="<?= htmlspecialchars($flashClass, ENT_QUOTES, 'UTF-8') ?>" role="status">
                            <?= htmlspecialchars(__($contactFlash['key']), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>
                    <div class="contact-form__alert" data-contact-alert hidden role="status" aria-live="polite"></div>
                    <form
                        class="contact-form"
                        method="post"
                        action="<?= htmlspecialchars((APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '') . '/contact.php', ENT_QUOTES, 'UTF-8') ?>"
                        data-ajax-contact
                        data-contact-network-error="<?= htmlspecialchars(__('contact_error_network'), ENT_QUOTES, 'UTF-8') ?>"
                        data-contact-validation-message="<?= htmlspecialchars(__('contact_error_validation'), ENT_QUOTES, 'UTF-8') ?>"
                        data-contact-body-max="<?= (string) PORTFOLIO_CONTACT_BODY_MAX ?>"
                        autocomplete="on"
                    >
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <label>
                            <?= htmlspecialchars(__('contact_name'), ENT_QUOTES, 'UTF-8') ?>
                            <input type="text" name="name" required autocomplete="name" maxlength="120">
                        </label>
                        <label>
                            <?= htmlspecialchars(__('contact_email'), ENT_QUOTES, 'UTF-8') ?>
                            <input type="email" name="email" required autocomplete="email" maxlength="255">
                        </label>
                        <label>
                            <?= htmlspecialchars(__('contact_message'), ENT_QUOTES, 'UTF-8') ?>
                            <textarea name="message" required maxlength="<?= (string) PORTFOLIO_CONTACT_BODY_MAX ?>"></textarea>
                        </label>
                        <button type="submit" class="btn btn--primary" data-contact-submit data-sending-label="<?= htmlspecialchars(__('contact_sending'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('contact_send'), ENT_QUOTES, 'UTF-8') ?></button>
                    </form>
                </div>
                <?php
                $projectsHref = (APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '') . '/projects.php';
                ?>
                <aside class="contact-aside" data-reveal aria-labelledby="contact-aside-title">
                    <h2 id="contact-aside-title" class="contact-aside__title"><?= htmlspecialchars(__('contact_aside_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="contact-aside__badge">
                        <span class="contact-aside__badge-label"><?= htmlspecialchars(__('contact_response_label'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="contact-aside__badge-value"><?= htmlspecialchars(__('contact_response_value'), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <h3 class="contact-aside__sub"><?= htmlspecialchars(__('contact_include_title'), ENT_QUOTES, 'UTF-8') ?></h3>
                    <ul class="contact-aside__tips">
                        <li><?= htmlspecialchars(__('contact_tip_1'), ENT_QUOTES, 'UTF-8') ?></li>
                        <li><?= htmlspecialchars(__('contact_tip_2'), ENT_QUOTES, 'UTF-8') ?></li>
                        <li><?= htmlspecialchars(__('contact_tip_3'), ENT_QUOTES, 'UTF-8') ?></li>
                    </ul>
                    <p class="contact-aside__note"><?= htmlspecialchars(__('contact_note'), ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="contact-aside__link" href="<?= htmlspecialchars($projectsHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('contact_projects_cta'), ENT_QUOTES, 'UTF-8') ?></a>
                </aside>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = __('meta_title') . ' — ' . __('nav_projects');
$activeNav = 'projects';

$base = APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '';
$projectsApiUrl = $base . '/api/projects.php';

require_once __DIR__ . '/includes/header.php';
?>

    <main id="main" class="main-content">
        <header class="page-hero">
            <div class="container" data-reveal>
                <p class="page-hero__eyebrow"><?= htmlspecialchars(__('nav_projects'), ENT_QUOTES, 'UTF-8') ?></p>
                <h1><?= htmlspecialchars(__('projects_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
        </header>

        <section class="page-section" aria-labelledby="projects-lead">
            <div
                class="container project-filter-shell"
                data-project-filter-root
                data-ajax-projects
                data-projects-endpoint="<?= htmlspecialchars($projectsApiUrl, ENT_QUOTES, 'UTF-8') ?>"
                data-projects-load-error="<?= htmlspecialchars(__('projects_load_error'), ENT_QUOTES, 'UTF-8') ?>"
                aria-busy="true"
                aria-describedby="projects-loading-label"
            >
                <div class="section-heading" data-reveal>
                    <h2 id="projects-lead"><?= htmlspecialchars(__('projects_lead'), ENT_QUOTES, 'UTF-8') ?></h2>
                </div>

                <div class="project-filters-host" data-projects-filters-host hidden></div>

                <p class="project-filter-empty" data-project-filter-empty hidden></p>

                <div class="project-grid project-grid--loading" data-projects-grid>
                    <div class="projects-loading-state" data-projects-loading-wrap>
                        <p class="projects-loading__text" id="projects-loading-label"><?= htmlspecialchars(__('projects_loading'), ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="projects-skeleton-cards" aria-hidden="true">
                            <div class="projects-skeleton-card"></div>
                            <div class="projects-skeleton-card"></div>
                            <div class="projects-skeleton-card"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <noscript>
            <div class="container page-section">
                <p class="projects-empty"><?= htmlspecialchars(__('projects_require_js'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </noscript>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

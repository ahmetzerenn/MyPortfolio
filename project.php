<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/portfolio_repository.php';

$base = APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '';
$placeholderImage = $base . '/assets/img/placeholder-project.svg';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(404);
}

$project = ($id !== false && $id !== null && $id >= 1)
    ? portfolio_fetch_published_project_by_id((int) $id)
    : null;

if ($project === null) {
    http_response_code(404);
    $pageTitle = __('meta_title') . ' — ' . __('project_not_found_title');
    $activeNav = 'projects';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <main id="main" class="main-content">
        <div class="container project-detail project-detail--notfound" data-reveal>
            <p class="project-detail__404eyebrow"><?= htmlspecialchars(__('nav_projects'), ENT_QUOTES, 'UTF-8') ?></p>
            <h1><?= htmlspecialchars(__('project_not_found_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="project-detail__lead"><?= htmlspecialchars(__('project_not_found_body'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><a class="btn btn--primary" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/projects.php"><?= htmlspecialchars(__('project_detail_back'), ENT_QUOTES, 'UTF-8') ?></a></p>
        </div>
    </main>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$title = (string) ($project['title'] ?? '');
$summary = (string) ($project['summary'] ?? '');
$descriptionRaw = isset($project['description']) && is_string($project['description']) ? trim($project['description']) : '';
$myRoleText = isset($project['my_role']) && is_string($project['my_role']) ? trim($project['my_role']) : '';
$imageSrc = portfolio_safe_image_url(
    isset($project['image_url']) && is_string($project['image_url']) ? $project['image_url'] : null,
    $placeholderImage
);
$heroVideoUrl = isset($project['hero_video_url']) && is_string($project['hero_video_url']) ? trim($project['hero_video_url']) : '';
$heroEmbed = $heroVideoUrl !== '' ? portfolio_hero_video_embed($heroVideoUrl) : null;
$github = portfolio_safe_github_url(
    isset($project['github_url']) && is_string($project['github_url']) ? $project['github_url'] : null
);
$liveUrl = portfolio_safe_http_url(
    isset($project['project_url']) && is_string($project['project_url']) ? $project['project_url'] : null
);
$categorySlug = isset($project['category_slug']) && is_string($project['category_slug']) ? $project['category_slug'] : 'general';
$techStack = isset($project['tech_stack']) && is_array($project['tech_stack']) ? $project['tech_stack'] : [];
$challengePairs = isset($project['challenges_solutions']) && is_array($project['challenges_solutions'])
    ? $project['challenges_solutions']
    : [];
$tagLine = isset($project['tag']) && is_string($project['tag']) ? trim($project['tag']) : '';

$descForBody = ($descriptionRaw !== '' && $descriptionRaw !== $summary) ? $descriptionRaw : '';
$hasRoleSection = $myRoleText !== '';
$hasTechSection = $techStack !== [];
$hasChallengeSection = $challengePairs !== [];

$pageTitle = $title . ' — ' . __('meta_title');
$activeNav = 'projects';

require_once __DIR__ . '/includes/header.php';
?>

    <main id="main" class="main-content">
        <article class="project-detail project-detail--modern" data-project-detail>
            <section id="project-hero" class="project-detail-hero" aria-label="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
                <div class="project-detail-hero__media">
                    <?php if ($heroEmbed !== null): ?>
                        <?php if ($heroEmbed['type'] === 'iframe'): ?>
                            <iframe
                                class="project-detail-hero__iframe"
                                src="<?= htmlspecialchars($heroEmbed['src'], ENT_QUOTES, 'UTF-8') ?>"
                                title="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
                                loading="eager"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        <?php else: ?>
                            <video
                                class="project-detail-hero__video"
                                src="<?= htmlspecialchars($heroEmbed['src'], ENT_QUOTES, 'UTF-8') ?>"
                                autoplay
                                muted
                                loop
                                playsinline
                                controls
                            ></video>
                        <?php endif; ?>
                    <?php else: ?>
                        <img
                            class="project-detail-hero__image"
                            src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>"
                            alt=""
                            width="1920"
                            height="1080"
                            loading="eager"
                            decoding="async"
                        >
                    <?php endif; ?>
                    <div class="project-detail-hero__scrim" aria-hidden="true"></div>
                </div>
                <div class="container project-detail-hero__content">
                    <p class="project-detail-hero__eyebrow">
                        <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/projects.php"><?= htmlspecialchars(__('project_detail_back'), ENT_QUOTES, 'UTF-8') ?></a>
                        <span class="project-detail-hero__eyebrow-sep" aria-hidden="true">/</span>
                        <span><?= htmlspecialchars(portfolio_category_label($categorySlug), ENT_QUOTES, 'UTF-8') ?></span>
                    </p>
                    <h1 class="project-detail-hero__title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
                    <?php if ($tagLine !== ''): ?>
                        <p class="project-detail-hero__tag"><?= htmlspecialchars($tagLine, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <p class="project-detail-hero__summary"><?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="project-detail-hero__actions">
                        <?php if ($github !== null): ?>
                            <a class="btn btn--primary" href="<?= htmlspecialchars($github, ENT_QUOTES, 'UTF-8') ?>" rel="noopener noreferrer" target="_blank"><?= htmlspecialchars(__('project_detail_github'), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                        <?php if ($liveUrl !== null): ?>
                            <a class="btn btn--ghost" href="<?= htmlspecialchars($liveUrl, ENT_QUOTES, 'UTF-8') ?>" rel="noopener noreferrer" target="_blank"><?= htmlspecialchars(__('project_detail_live'), ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <nav class="project-detail-toc" aria-label="<?= htmlspecialchars(__('project_detail_toc_aria'), ENT_QUOTES, 'UTF-8') ?>">
                <div class="container project-detail-toc__inner">
                    <a class="project-detail-toc__link is-active" href="#section-overview" data-toc-target="section-overview"><?= htmlspecialchars(__('project_detail_section_overview'), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php if ($hasRoleSection): ?>
                        <a class="project-detail-toc__link" href="#section-role" data-toc-target="section-role"><?= htmlspecialchars(__('project_detail_section_role'), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endif; ?>
                    <?php if ($hasTechSection): ?>
                        <a class="project-detail-toc__link" href="#section-tech" data-toc-target="section-tech"><?= htmlspecialchars(__('project_detail_section_tech'), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endif; ?>
                    <?php if ($hasChallengeSection): ?>
                        <a class="project-detail-toc__link" href="#section-challenges" data-toc-target="section-challenges"><?= htmlspecialchars(__('project_detail_section_challenges'), ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="project-detail-sections">
                <section id="section-overview" class="project-detail-section project-detail-section--overview" data-reveal data-toc-section="section-overview">
                    <div class="container project-detail-section__inner">
                        <header class="project-detail-section__head">
                            <h2 class="project-detail-section__title"><?= htmlspecialchars(__('project_detail_section_overview'), ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="project-detail-section__kicker"><?= htmlspecialchars(__('project_detail_overview_kicker'), ENT_QUOTES, 'UTF-8') ?></p>
                        </header>
                        <div class="project-detail-overview">
                            <p class="project-detail-overview__lead"><?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if ($descForBody !== ''): ?>
                                <div class="project-detail-overview__body">
                                    <?php foreach (preg_split('/\r\n|\r|\n/', $descForBody) as $para): ?>
                                        <?php if (trim($para) !== ''): ?>
                                            <p><?= nl2br(htmlspecialchars($para, ENT_QUOTES, 'UTF-8')) ?></p>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <?php if ($hasRoleSection): ?>
                    <section id="section-role" class="project-detail-section project-detail-section--role" data-reveal data-toc-section="section-role">
                        <div class="container project-detail-section__inner">
                            <header class="project-detail-section__head">
                                <h2 class="project-detail-section__title"><?= htmlspecialchars(__('project_detail_section_role'), ENT_QUOTES, 'UTF-8') ?></h2>
                            </header>
                            <div class="project-detail-role">
                                <?php foreach (preg_split('/\r\n|\r|\n/', $myRoleText) as $para): ?>
                                    <?php if (trim($para) !== ''): ?>
                                        <p><?= nl2br(htmlspecialchars($para, ENT_QUOTES, 'UTF-8')) ?></p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($hasTechSection): ?>
                    <section id="section-tech" class="project-detail-section project-detail-section--tech" data-reveal data-toc-section="section-tech">
                        <div class="container project-detail-section__inner">
                            <header class="project-detail-section__head">
                                <h2 class="project-detail-section__title"><?= htmlspecialchars(__('project_detail_section_tech'), ENT_QUOTES, 'UTF-8') ?></h2>
                            </header>
                            <ul class="project-detail-tech" role="list">
                                <?php foreach ($techStack as $tech): ?>
                                    <li class="project-detail-tech__badge"><?= htmlspecialchars((string) $tech, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($hasChallengeSection): ?>
                    <section id="section-challenges" class="project-detail-section project-detail-section--challenges" data-reveal data-toc-section="section-challenges">
                        <div class="container project-detail-section__inner">
                            <header class="project-detail-section__head">
                                <h2 class="project-detail-section__title"><?= htmlspecialchars(__('project_detail_section_challenges'), ENT_QUOTES, 'UTF-8') ?></h2>
                            </header>
                            <ol class="project-detail-challenges">
                                <?php foreach ($challengePairs as $pair): ?>
                                    <?php
                                    $ch = isset($pair['challenge']) ? trim((string) $pair['challenge']) : '';
                                    $sol = isset($pair['solution']) ? trim((string) $pair['solution']) : '';
                                    ?>
                                    <li class="project-detail-challenges__item">
                                        <div class="project-detail-challenges__card">
                                            <?php if ($ch !== ''): ?>
                                                <h3 class="project-detail-challenges__label"><?= htmlspecialchars(__('project_detail_challenge'), ENT_QUOTES, 'UTF-8') ?></h3>
                                                <p class="project-detail-challenges__text"><?= nl2br(htmlspecialchars($ch, ENT_QUOTES, 'UTF-8')) ?></p>
                                            <?php endif; ?>
                                            <?php if ($sol !== ''): ?>
                                                <h3 class="project-detail-challenges__label project-detail-challenges__label--solution"><?= htmlspecialchars(__('project_detail_solution'), ENT_QUOTES, 'UTF-8') ?></h3>
                                                <p class="project-detail-challenges__text project-detail-challenges__text--muted"><?= nl2br(htmlspecialchars($sol, ENT_QUOTES, 'UTF-8')) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </article>
    </main>

<?php
require_once __DIR__ . '/includes/footer.php';

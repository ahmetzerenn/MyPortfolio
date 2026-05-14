<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/portfolio_repository.php';

$pageTitle = __('meta_title') . ' — ' . __('nav_home');
$activeNav = 'home';

$featuredBeyondFallbackVideo = 'https://youtu.be/pGSwN6lFaB4';
$featuredBeyondResolvedUrl = $featuredBeyondFallbackVideo;
$featuredProjectHome = portfolio_fetch_published_project_by_id(1);
if (is_array($featuredProjectHome) && isset($featuredProjectHome['hero_video_url']) && is_string($featuredProjectHome['hero_video_url'])) {
    $hv = trim($featuredProjectHome['hero_video_url']);
    if ($hv !== '') {
        $featuredBeyondResolvedUrl = $hv;
    }
}
$featuredBeyondEmbed = portfolio_hero_video_embed($featuredBeyondResolvedUrl);
if ($featuredBeyondEmbed === null && $featuredBeyondResolvedUrl !== $featuredBeyondFallbackVideo) {
    $featuredBeyondEmbed = portfolio_hero_video_embed($featuredBeyondFallbackVideo);
}

$allPublishedHome = portfolio_fetch_published_projects();
$homePreviewProjects = [];
foreach ($allPublishedHome as $_homeProj) {
    if (!is_array($_homeProj)) {
        continue;
    }
    if ((int) ($_homeProj['id'] ?? 0) === 1) {
        continue;
    }
    $homePreviewProjects[] = $_homeProj;
}
$homePreviewProjects = array_slice($homePreviewProjects, 0, 6);

/** @var list<array{label_key:string, title_key:string, text_key:string}> */
$homeMilestones = [
    ['label_key' => 'about_ms1_label', 'title_key' => 'about_ms1_title', 'text_key' => 'about_ms1_text'],
    ['label_key' => 'about_ms2_label', 'title_key' => 'about_ms2_title', 'text_key' => 'about_ms2_text'],
    ['label_key' => 'about_ms3_label', 'title_key' => 'about_ms3_title', 'text_key' => 'about_ms3_text'],
];

require_once __DIR__ . '/includes/header.php';

$base = APP_BASE_URL;
$basePath = $base !== '' ? rtrim($base, '/') : '';
$placeholderImage = $basePath . '/assets/img/placeholder-project.svg';
?>

    <main id="main" class="main-content">
        <section class="hero" aria-labelledby="hero-name">
            <div class="hero__bg" aria-hidden="true">
                <span class="hero__bg-aurora"></span>
                <span class="hero__bg-grid"></span>
            </div>
            <div class="container hero__grid">
                <div class="hero__content">
                    <p class="hero__eyebrow"><?= htmlspecialchars(__('meta_title'), ENT_QUOTES, 'UTF-8') ?></p>
                    <h1 id="hero-name" class="hero__name"><?= htmlspecialchars(__('hero_name'), ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="hero__role"><?= htmlspecialchars(__('hero_role'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="hero__live-status" aria-label="<?= htmlspecialchars(__('hero_live_aria'), ENT_QUOTES, 'UTF-8') ?>">
                        <span class="hero__live-pulse" aria-hidden="true"></span>
                        <div class="hero__live-inner">
                            <p class="hero__live-title"><?= htmlspecialchars(__('hero_live_title'), ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="hero__live-line">
                                <span class="hero__live-k"><?= htmlspecialchars(__('hero_live_building'), ENT_QUOTES, 'UTF-8') ?></span>
                                <?= htmlspecialchars(__('hero_live_project'), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="hero__live-line hero__live-line--meta">
                                <span class="hero__live-k"><?= htmlspecialchars(__('hero_live_status_label'), ENT_QUOTES, 'UTF-8') ?></span>
                                <?= htmlspecialchars(__('hero_live_status_value'), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    </div>
                    <div class="hero__intro-wrap">
                        <p class="hero__intro"><?= htmlspecialchars(__('hero_intro'), ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="hero__intro"><?= htmlspecialchars(__('hero_intro_secondary'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="hero__actions">
                        <a class="btn btn--primary" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/projects.php">
                            <?= htmlspecialchars(__('hero_cta_primary'), ENT_QUOTES, 'UTF-8') ?>
                            <span class="btn__arrow" aria-hidden="true">→</span>
                        </a>
                        <a class="btn btn--ghost" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/contact.php">
                            <?= htmlspecialchars(__('hero_cta_secondary'), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </div>
                </div>
                <aside class="hero__panel" aria-label="<?= htmlspecialchars(__('hero_panel_aria'), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="hero__panel-inner">
                        <div class="hero__stat">
                            <span class="hero__stat-label"><?= htmlspecialchars(__('hero_panel_1_l'), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="hero__stat-value"><?= htmlspecialchars(__('hero_panel_1_v'), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="hero__stat">
                            <span class="hero__stat-label"><?= htmlspecialchars(__('hero_panel_2_l'), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="hero__stat-value"><?= htmlspecialchars(__('hero_panel_2_v'), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="hero__stat">
                            <span class="hero__stat-label"><?= htmlspecialchars(__('hero_panel_3_l'), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="hero__stat-value"><?= htmlspecialchars(__('hero_panel_3_v'), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                    <div class="hero__orbit" aria-hidden="true">
                        <span class="hero__orbit-dot"></span>
                    </div>
                </aside>
            </div>
        </section>

        <section class="featured-spotlight" aria-labelledby="featured-beyond-title">
            <div class="featured-spotlight__backdrop" aria-hidden="true">
                <span class="featured-spotlight__orb featured-spotlight__orb--a"></span>
                <span class="featured-spotlight__orb featured-spotlight__orb--b"></span>
            </div>
            <div class="container featured-spotlight__shell" data-reveal>
                <p class="featured-spotlight__kicker"><?= htmlspecialchars(__('featured_beyond_kicker'), ENT_QUOTES, 'UTF-8') ?></p>
                <div class="featured-spotlight__grid">
                    <div class="featured-spotlight__media-col">
                        <div class="featured-spotlight__media-frame">
                            <div class="featured-spotlight__media-glow" aria-hidden="true"></div>
                            <?php if ($featuredBeyondEmbed !== null): ?>
                                <div
                                    class="featured-spotlight__preview featured-spotlight__preview--embed"
                                    aria-label="<?= htmlspecialchars(__('featured_beyond_preview_aria'), ENT_QUOTES, 'UTF-8') ?>"
                                >
                                    <?php if ($featuredBeyondEmbed['type'] === 'iframe'): ?>
                                        <iframe
                                            class="featured-spotlight__iframe"
                                            src="<?= htmlspecialchars($featuredBeyondEmbed['src'], ENT_QUOTES, 'UTF-8') ?>"
                                            title="<?= htmlspecialchars(__('featured_beyond_title'), ENT_QUOTES, 'UTF-8') ?>"
                                            loading="lazy"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen
                                        ></iframe>
                                    <?php else: ?>
                                        <video
                                            class="featured-spotlight__video"
                                            src="<?= htmlspecialchars($featuredBeyondEmbed['src'], ENT_QUOTES, 'UTF-8') ?>"
                                            controls
                                            playsinline
                                            preload="metadata"
                                        ></video>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="featured-spotlight__preview" role="img" aria-label="<?= htmlspecialchars(__('featured_beyond_preview_aria'), ENT_QUOTES, 'UTF-8') ?>">
                                    <span class="featured-spotlight__preview-aurora" aria-hidden="true"></span>
                                    <span class="featured-spotlight__preview-grid" aria-hidden="true"></span>
                                    <span class="featured-spotlight__preview-vignette" aria-hidden="true"></span>
                                    <span class="featured-spotlight__preview-scan" aria-hidden="true"></span>
                                    <span class="featured-spotlight__preview-beam" aria-hidden="true"></span>
                                    <span class="featured-spotlight__preview-title" aria-hidden="true"><?= htmlspecialchars(__('featured_beyond_title'), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            <?php endif; ?>
                            <p class="featured-spotlight__preview-hint"><?= htmlspecialchars(__('featured_beyond_preview_hint'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                    <div class="featured-spotlight__detail">
                        <div class="featured-spotlight__title-row">
                            <h2 id="featured-beyond-title" class="featured-spotlight__title"><?= htmlspecialchars(__('featured_beyond_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                            <span class="featured-spotlight__badge">
                                <span class="featured-spotlight__badge-dot" aria-hidden="true"></span>
                                <?= htmlspecialchars(__('featured_beyond_status'), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                        <p class="featured-spotlight__lede"><?= htmlspecialchars(__('featured_beyond_desc'), ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="featured-spotlight__block">
                            <h3 class="featured-spotlight__h"><?= htmlspecialchars(__('featured_beyond_role_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="featured-spotlight__copy"><?= htmlspecialchars(__('featured_beyond_role'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="featured-spotlight__block">
                            <h3 class="featured-spotlight__h"><?= htmlspecialchars(__('featured_beyond_tech_heading'), ENT_QUOTES, 'UTF-8') ?></h3>
                            <ul class="featured-spotlight__tech" aria-label="<?= htmlspecialchars(__('featured_beyond_tech_heading'), ENT_QUOTES, 'UTF-8') ?>">
                                <li><?= htmlspecialchars(__('featured_beyond_tech_unity'), ENT_QUOTES, 'UTF-8') ?></li>
                                <li><?= htmlspecialchars(__('featured_beyond_tech_csharp'), ENT_QUOTES, 'UTF-8') ?></li>
                                <li><?= htmlspecialchars(__('featured_beyond_tech_tools'), ENT_QUOTES, 'UTF-8') ?></li>
                                <li><?= htmlspecialchars(__('featured_beyond_tech_pipeline'), ENT_QUOTES, 'UTF-8') ?></li>
                                <li><?= htmlspecialchars(__('featured_beyond_tech_steam'), ENT_QUOTES, 'UTF-8') ?></li>
                            </ul>
                        </div>
                        <div class="featured-spotlight__actions">
                            <a class="btn btn--primary" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/project.php?id=1">
                                <?= htmlspecialchars(__('featured_beyond_cta'), ENT_QUOTES, 'UTF-8') ?>
                                <span class="btn__arrow" aria-hidden="true">→</span>
                            </a>
                            <a class="btn btn--ghost" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/projects.php"><?= htmlspecialchars(__('featured_beyond_cta_secondary'), ENT_QUOTES, 'UTF-8') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if ($homePreviewProjects !== []): ?>
            <section class="page-section home-more-work" aria-labelledby="home-work-heading">
                <div class="container">
                    <div class="section-heading section-heading--home-work" data-reveal>
                        <p class="home-section-eyebrow"><?= htmlspecialchars(__('home_work_kicker'), ENT_QUOTES, 'UTF-8') ?></p>
                        <h2 id="home-work-heading"><?= htmlspecialchars(__('home_work_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="lead"><?= htmlspecialchars(__('home_work_lead'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="project-grid project-grid--home">
                        <?php foreach ($homePreviewProjects as $idx => $proj): ?>
                            <?php
                            $pid = (int) ($proj['id'] ?? 0);
                            if ($pid < 1) {
                                continue;
                            }
                            $slug = isset($proj['category_slug']) && is_string($proj['category_slug']) ? trim($proj['category_slug']) : '';
                            if ($slug === '') {
                                $slug = 'general';
                            }
                            $imgSrc = portfolio_safe_image_url(
                                isset($proj['image_url']) && is_string($proj['image_url']) ? $proj['image_url'] : null,
                                $placeholderImage
                            );
                            $pTitle = isset($proj['title']) ? (string) $proj['title'] : '';
                            $pSummary = isset($proj['summary']) ? (string) $proj['summary'] : '';
                            $pTag = isset($proj['tag']) && $proj['tag'] !== null ? (string) $proj['tag'] : '';
                            $detailUrl = $basePath . '/project.php?id=' . $pid;
                            $indexLabel = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);
                            $delayMs = $idx * 50;
                            ?>
                            <article
                                class="project-card"
                                data-reveal
                                data-reveal-delay="<?= (int) $delayMs ?>"
                                data-project-category="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <a class="project-card__media" href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') ?>" tabindex="-1" aria-hidden="true">
                                    <img
                                        class="project-card__thumb"
                                        src="<?= htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($pTitle, ENT_QUOTES, 'UTF-8') ?>"
                                        width="600"
                                        height="375"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </a>
                                <span class="project-card__index"><?= htmlspecialchars($indexLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                <p class="project-card__category"><?= htmlspecialchars(portfolio_category_label($slug), ENT_QUOTES, 'UTF-8') ?></p>
                                <h3>
                                    <a href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($pTitle, ENT_QUOTES, 'UTF-8') ?></a>
                                </h3>
                                <p><?= htmlspecialchars($pSummary, ENT_QUOTES, 'UTF-8') ?></p>
                                <?php if ($pTag !== ''): ?>
                                    <span class="project-card__tag"><?= htmlspecialchars($pTag, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                                <p class="project-card__link-wrap">
                                    <a class="project-card__link" href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(__('projects_view_details'), ENT_QUOTES, 'UTF-8') ?></a>
                                </p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <p class="home-more-work__actions" data-reveal>
                        <a class="btn btn--ghost" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/projects.php"><?= htmlspecialchars(__('home_work_cta'), ENT_QUOTES, 'UTF-8') ?></a>
                    </p>
                </div>
            </section>
        <?php endif; ?>

        <section class="page-section home-story" aria-labelledby="home-story-heading">
            <div class="container home-story__grid" data-reveal>
                <div class="home-story__intro">
                    <p class="home-section-eyebrow"><?= htmlspecialchars(__('home_story_kicker'), ENT_QUOTES, 'UTF-8') ?></p>
                    <h2 id="home-story-heading"><?= htmlspecialchars(__('home_story_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="home-story__lead"><?= htmlspecialchars(__('home_story_lead'), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="home-story__actions">
                        <a class="btn btn--primary" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/about.php">
                            <?= htmlspecialchars(__('home_story_cta'), ENT_QUOTES, 'UTF-8') ?>
                            <span class="btn__arrow" aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
                <div class="home-story__tracks" role="list">
                    <?php foreach ($homeMilestones as $m): ?>
                        <article class="home-story__track" role="listitem">
                            <span class="home-story__track-label"><?= htmlspecialchars(__($m['label_key']), ENT_QUOTES, 'UTF-8') ?></span>
                            <h3 class="home-story__track-title"><?= htmlspecialchars(__($m['title_key']), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="home-story__track-text"><?= htmlspecialchars(__($m['text_key']), ENT_QUOTES, 'UTF-8') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="page-section home-stack" aria-labelledby="home-stack-heading">
            <div class="container home-stack__inner" data-reveal>
                <div class="home-stack__head">
                    <p class="home-section-eyebrow"><?= htmlspecialchars(__('home_stack_kicker'), ENT_QUOTES, 'UTF-8') ?></p>
                    <h2 id="home-stack-heading"><?= htmlspecialchars(__('home_stack_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="home-stack__lead"><?= htmlspecialchars(__('home_stack_lead'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <ul class="home-stack__chips" aria-label="<?= htmlspecialchars(__('home_stack_title'), ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach (['TypeScript', 'PHP', 'MySQL', 'C++', 'Unity', 'C#', 'ASP.NET', 'Tailwind CSS', 'Git'] as $chip): ?>
                        <li><?= htmlspecialchars($chip, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>

        <section class="home-cta-band" aria-labelledby="home-cta-heading">
            <div class="home-cta-band__backdrop" aria-hidden="true"></div>
            <div class="container home-cta-band__content" data-reveal>
                <p class="home-section-eyebrow home-cta-band__eyebrow"><?= htmlspecialchars(__('home_cta_kicker'), ENT_QUOTES, 'UTF-8') ?></p>
                <h2 id="home-cta-heading" class="home-cta-band__title"><?= htmlspecialchars(__('home_cta_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="home-cta-band__lead"><?= htmlspecialchars(__('home_cta_lead'), ENT_QUOTES, 'UTF-8') ?></p>
                <div class="home-cta-band__actions">
                    <a class="btn btn--primary" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/contact.php">
                        <?= htmlspecialchars(__('home_cta_primary'), ENT_QUOTES, 'UTF-8') ?>
                        <span class="btn__arrow" aria-hidden="true">→</span>
                    </a>
                    <a class="btn btn--ghost" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/projects.php"><?= htmlspecialchars(__('home_cta_secondary'), ENT_QUOTES, 'UTF-8') ?></a>
                </div>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

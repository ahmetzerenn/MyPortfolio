<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = __('meta_title') . ' — ' . __('nav_about');
$activeNav = 'about';

require_once __DIR__ . '/includes/header.php';
?>

    <main id="main" class="main-content">
        <header class="page-hero">
            <div class="container" data-reveal>
                <p class="page-hero__eyebrow"><?= htmlspecialchars(__('nav_about'), ENT_QUOTES, 'UTF-8') ?></p>
                <h1><?= htmlspecialchars(__('about_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
        </header>

        <section class="page-section" aria-labelledby="about-heading">
            <div class="container">
                <div class="section-heading section-heading--about" data-reveal>
                    <h2 id="about-heading"><?= htmlspecialchars(__('about_section_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="about-lead"><?= htmlspecialchars(__('about_lead'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <?php
                /** @var list<array{label_key:string, title_key:string, text_key:string}> */
                $aboutMilestones = [
                    ['label_key' => 'about_ms1_label', 'title_key' => 'about_ms1_title', 'text_key' => 'about_ms1_text'],
                    ['label_key' => 'about_ms2_label', 'title_key' => 'about_ms2_title', 'text_key' => 'about_ms2_text'],
                    ['label_key' => 'about_ms3_label', 'title_key' => 'about_ms3_title', 'text_key' => 'about_ms3_text'],
                ];

                /** @var list<array{title_key:string, text_key:string}> */
                $aboutInfoCards = [
                    ['title_key' => 'about_card_now_title', 'text_key' => 'about_card_now_text'],
                    ['title_key' => 'about_card_focus_title', 'text_key' => 'about_card_focus_text'],
                    ['title_key' => 'about_card_collab_title', 'text_key' => 'about_card_collab_text'],
                ];

                /** @var list<array{skill:string, notes_key:string}> */
                $aboutSkillRows = [
                    ['skill' => 'TypeScript', 'notes_key' => 'about_skills_ts'],
                    ['skill' => 'PHP', 'notes_key' => 'about_skills_php'],
                    ['skill' => 'MySQL', 'notes_key' => 'about_skills_mysql'],
                    ['skill' => 'C++', 'notes_key' => 'about_skills_cpp'],
                    ['skill' => 'Unity', 'notes_key' => 'about_skills_unity'],
                    ['skill' => 'Shaders', 'notes_key' => 'about_skills_shaders'],
                ];
                ?>

                <div class="about-story" data-reveal>
                    <ol class="about-timeline" aria-label="<?= htmlspecialchars(__('about_timeline_aria'), ENT_QUOTES, 'UTF-8') ?>">
                        <?php foreach ($aboutMilestones as $m): ?>
                            <li class="about-timeline__item">
                                <span class="about-timeline__marker" aria-hidden="true"></span>
                                <div class="about-timeline__body">
                                    <span class="about-timeline__label"><?= htmlspecialchars(__($m['label_key']), ENT_QUOTES, 'UTF-8') ?></span>
                                    <h3 class="about-timeline__title"><?= htmlspecialchars(__($m['title_key']), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p class="about-timeline__text"><?= htmlspecialchars(__($m['text_key']), ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>

                    <div class="about-side-stack">
                        <?php foreach ($aboutInfoCards as $card): ?>
                            <article class="about-side-card">
                                <h3 class="about-side-card__title"><?= htmlspecialchars(__($card['title_key']), ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="about-side-card__text"><?= htmlspecialchars(__($card['text_key']), ENT_QUOTES, 'UTF-8') ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="about-skills-block" data-reveal>
                    <h2 class="about-skills-block__title"><?= htmlspecialchars(__('about_skills_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="about-skills-block__lead"><?= htmlspecialchars(__('about_skills_caption'), ENT_QUOTES, 'UTF-8') ?></p>
                    <ul class="about-skill-grid">
                        <?php foreach ($aboutSkillRows as $row): ?>
                            <li>
                                <article class="about-skill-card">
                                    <h3 class="about-skill-card__name"><?= htmlspecialchars($row['skill'], ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p class="about-skill-card__notes"><?= htmlspecialchars(__($row['notes_key']), ENT_QUOTES, 'UTF-8') ?></p>
                                </article>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

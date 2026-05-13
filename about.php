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
                <div class="section-heading" data-reveal>
                    <h2 id="about-heading"><?= htmlspecialchars(__('about_section_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="lead"><?= htmlspecialchars(__('about_lead'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="about-layout" data-reveal>
                    <div class="prose">
                        <p><?= htmlspecialchars(__('about_p1'), ENT_QUOTES, 'UTF-8') ?></p>
                        <p><?= htmlspecialchars(__('about_p2'), ENT_QUOTES, 'UTF-8') ?></p>
                        <p><?= htmlspecialchars(__('about_p3'), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <aside class="about-card">
                        <h3><?= htmlspecialchars(__('about_now'), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars(__('hero_role'), ENT_QUOTES, 'UTF-8') ?></p>
                    </aside>
                </div>

                <?php
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
                <div class="about-skills-block" data-reveal>
                    <h2 class="about-skills-block__title"><?= htmlspecialchars(__('about_skills_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="about-table-wrap">
                        <table class="about-skills-table">
                            <caption class="about-skills-table__caption">
                                <?= htmlspecialchars(__('about_skills_caption'), ENT_QUOTES, 'UTF-8') ?>
                            </caption>
                            <thead>
                                <tr>
                                    <th scope="col"><?= htmlspecialchars(__('about_skills_col_skill'), ENT_QUOTES, 'UTF-8') ?></th>
                                    <th scope="col"><?= htmlspecialchars(__('about_skills_col_notes'), ENT_QUOTES, 'UTF-8') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($aboutSkillRows as $row): ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($row['skill'], ENT_QUOTES, 'UTF-8') ?></th>
                                        <td><?= htmlspecialchars(__($row['notes_key']), ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

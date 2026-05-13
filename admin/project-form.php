<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth-check.php';
require_once dirname(__DIR__) . '/includes/admin_projects_repository.php';
require_once dirname(__DIR__) . '/includes/portfolio_repository.php';

$editId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$existing = null;
if ($editId !== false && $editId !== null && $editId > 0) {
    $existing = admin_get_project_by_id((int) $editId);
    if ($existing === null) {
        admin_set_flash('error', 'Project not found.');
        header('Location: ' . admin_url('index.php'));
        exit;
    }
}

$errors = [];
$form = [
    'title'             => '',
    'summary'           => '',
    'description'       => '',
    'my_role'           => '',
    'image_url'         => '',
    'hero_video_url'    => '',
    'tech_stack_text'   => '',
    'challenges_json'   => '',
    'github_url'        => '',
    'category_slug'     => 'general',
    'tag'               => '',
    'project_url'       => '',
    'display_order'     => '0',
    'is_published'      => true,
    'user_id'           => '',
];

if (is_array($existing)) {
    $form['title'] = (string) ($existing['title'] ?? '');
    $form['summary'] = (string) ($existing['summary'] ?? '');
    $form['description'] = (string) ($existing['description'] ?? '');
    $form['my_role'] = (string) ($existing['my_role'] ?? '');
    $form['image_url'] = (string) ($existing['image_url'] ?? '');
    $form['hero_video_url'] = (string) ($existing['hero_video_url'] ?? '');
    $form['github_url'] = (string) ($existing['github_url'] ?? '');
    $form['category_slug'] = (string) ($existing['category_slug'] ?? 'general');
    $form['tag'] = (string) ($existing['tag'] ?? '');
    $form['project_url'] = (string) ($existing['project_url'] ?? '');
    $form['display_order'] = (string) (int) ($existing['display_order'] ?? 0);
    $form['is_published'] = !empty($existing['is_published']);
    $uid = $existing['user_id'] ?? null;
    $form['user_id'] = $uid !== null && $uid !== '' ? (string) (int) $uid : '';

    $techRaw = $existing['tech_stack'] ?? null;
    $techItems = portfolio_decode_tech_stack($techRaw);
    $form['tech_stack_text'] = implode("\n", $techItems);

    $chalPairs = portfolio_decode_challenges_solutions($existing['challenges_solutions'] ?? null);
    $form['challenges_json'] = $chalPairs === []
        ? ''
        : (string) json_encode($chalPairs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
    if (!verify_csrf_token($token)) {
        $errors[] = 'Session expired. Refresh and try again.';
    } else {
        $form['title'] = isset($_POST['title']) && is_string($_POST['title']) ? trim($_POST['title']) : '';
        $form['summary'] = isset($_POST['summary']) && is_string($_POST['summary']) ? trim($_POST['summary']) : '';
        $form['description'] = isset($_POST['description']) && is_string($_POST['description']) ? trim($_POST['description']) : '';
        $form['my_role'] = isset($_POST['my_role']) && is_string($_POST['my_role']) ? trim($_POST['my_role']) : '';
        $form['image_url'] = isset($_POST['image_url']) && is_string($_POST['image_url']) ? trim($_POST['image_url']) : '';
        $form['hero_video_url'] = isset($_POST['hero_video_url']) && is_string($_POST['hero_video_url']) ? trim($_POST['hero_video_url']) : '';
        $form['tech_stack_text'] = isset($_POST['tech_stack']) && is_string($_POST['tech_stack']) ? $_POST['tech_stack'] : '';
        $form['challenges_json'] = isset($_POST['challenges_json']) && is_string($_POST['challenges_json']) ? $_POST['challenges_json'] : '';
        $form['github_url'] = isset($_POST['github_url']) && is_string($_POST['github_url']) ? trim($_POST['github_url']) : '';
        $form['category_slug'] = isset($_POST['category_slug']) && is_string($_POST['category_slug']) ? trim($_POST['category_slug']) : 'general';
        $form['tag'] = isset($_POST['tag']) && is_string($_POST['tag']) ? trim($_POST['tag']) : '';
        $form['project_url'] = isset($_POST['project_url']) && is_string($_POST['project_url']) ? trim($_POST['project_url']) : '';
        $form['display_order'] = isset($_POST['display_order']) && is_string($_POST['display_order']) ? trim($_POST['display_order']) : '0';
        $form['is_published'] = !empty($_POST['is_published']);
        $form['user_id'] = isset($_POST['user_id']) && is_string($_POST['user_id']) ? trim($_POST['user_id']) : '';

        if ($form['title'] === '' || mb_strlen($form['title']) > 255) {
            $errors[] = 'Title is required (max 255 characters).';
        }
        if ($form['summary'] === '') {
            $errors[] = 'Short summary is required.';
        }

        $slug = strtolower($form['category_slug']);
        $slug = preg_replace('/[^a-z0-9_-]+/', '', $slug) ?? '';
        if ($slug === '') {
            $slug = 'general';
        }
        $form['category_slug'] = $slug;

        $imageUrl = null;
        if ($form['image_url'] !== '') {
            if (mb_strlen($form['image_url']) > 512
                || (!preg_match('#\A/#', $form['image_url']) && !preg_match('#\Ahttps?://#i', $form['image_url']))) {
                $errors[] = 'Image URL must be empty, a path starting with /, or http(s) URL.';
            } else {
                $imageUrl = $form['image_url'];
            }
        }

        $heroVideoUrl = null;
        if ($form['hero_video_url'] !== '') {
            if (mb_strlen($form['hero_video_url']) > 512
                || (!preg_match('#\A/#', $form['hero_video_url']) && !preg_match('#\Ahttps?://#i', $form['hero_video_url']))) {
                $errors[] = 'Hero video URL must be empty, a path starting with /, or http(s) URL.';
            } else {
                $heroVideoUrl = $form['hero_video_url'];
            }
        }

        $myRoleVal = $form['my_role'] !== '' ? $form['my_role'] : null;

        $challengesJson = null;
        $chalNorm = admin_normalize_challenges_json_input($form['challenges_json']);
        if (empty($chalNorm['ok'])) {
            $errors[] = (string) ($chalNorm['error'] ?? 'Invalid challenges JSON.');
        } else {
            $challengesJson = $chalNorm['json'] ?? null;
        }

        $githubUrl = null;
        if ($form['github_url'] !== '') {
            if (!preg_match('#\Ahttps://github\.com/.+#i', $form['github_url']) || mb_strlen($form['github_url']) > 512) {
                $errors[] = 'GitHub URL must be a valid https://github.com/... link or empty.';
            } else {
                $githubUrl = $form['github_url'];
            }
        }

        $liveUrl = null;
        if ($form['project_url'] !== '') {
            if (!preg_match('#\Ahttps?://#i', $form['project_url']) || mb_strlen($form['project_url']) > 512) {
                $errors[] = 'Live URL must be http(s) or empty.';
            } else {
                $liveUrl = $form['project_url'];
            }
        }

        $tagVal = $form['tag'] !== '' ? $form['tag'] : null;
        if ($tagVal !== null && mb_strlen($tagVal) > 120) {
            $errors[] = 'Tag line is too long (120 max).';
        }

        $descVal = $form['description'] !== '' ? $form['description'] : null;

        $displayOrder = filter_var($form['display_order'], FILTER_VALIDATE_INT);
        if ($displayOrder === false || $displayOrder < 0) {
            $errors[] = 'Display order must be a non-negative integer.';
            $displayOrder = 0;
        }

        $ownerId = null;
        if ($form['user_id'] !== '') {
            $uid = filter_var($form['user_id'], FILTER_VALIDATE_INT);
            if ($uid === false || $uid < 1) {
                $errors[] = 'Owner user ID must be empty or a positive integer.';
            } else {
                $ownerId = $uid;
            }
        }

        $techItems = admin_parse_tech_stack_text($form['tech_stack_text']);

        if ($errors === [] && is_array($existing)) {
            $pid = (int) ($existing['id'] ?? 0);
            if ($pid < 1) {
                $errors[] = 'Invalid project.';
            } else {
                $row = admin_get_project_by_id($pid);
                if ($row === null) {
                    $errors[] = 'Project no longer exists.';
                } else {
                    $result = admin_update_project(
                        $pid,
                        $form['title'],
                        $form['summary'],
                        $descVal,
                        $myRoleVal,
                        $imageUrl,
                        $heroVideoUrl,
                        $techItems,
                        $challengesJson,
                        $githubUrl,
                        $form['category_slug'],
                        $tagVal,
                        $liveUrl,
                        $displayOrder,
                        $form['is_published'] ? 1 : 0,
                        $ownerId
                    );
                    if (!empty($result['ok'])) {
                        admin_set_flash('success', 'Project updated.');
                        header('Location: ' . admin_url('index.php'));
                        exit;
                    }
                    $errors[] = $result['error'] ?? 'Update failed.';
                }
            }
        } elseif ($errors === []) {
            $result = admin_create_project(
                $form['title'],
                $form['summary'],
                $descVal,
                $myRoleVal,
                $imageUrl,
                $heroVideoUrl,
                $techItems,
                $challengesJson,
                $githubUrl,
                $form['category_slug'],
                $tagVal,
                $liveUrl,
                $displayOrder,
                $form['is_published'] ? 1 : 0,
                $ownerId
            );
            if (!empty($result['ok'])) {
                admin_set_flash('success', 'Project created.');
                header('Location: ' . admin_url('index.php'));
                exit;
            }
            $errors[] = $result['error'] ?? 'Create failed.';
        }
    }
}

$pageTitle = $existing ? 'Edit project' : 'New project';
require_once __DIR__ . '/includes/admin-header.php';
?>

        <h1 class="admin-page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>

        <?php if ($errors !== []): ?>
            <ul class="admin-errors">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form class="admin-form" method="post" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <label>
                Title
                <input type="text" name="title" required maxlength="255" value="<?= htmlspecialchars($form['title'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Summary (card / teaser)
                <textarea name="summary" required rows="3"><?= htmlspecialchars($form['summary'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>
                Description (detail page — overview)
                <span class="admin-form__hint">Optional longer text; shown in the Overview section.</span>
                <textarea name="description" rows="6"><?= htmlspecialchars($form['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>
                My role
                <span class="admin-form__hint">What you personally owned (optional); shown in its own section.</span>
                <textarea name="my_role" rows="4"><?= htmlspecialchars($form['my_role'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>
                Image URL (hero fallback &amp; cards)
                <span class="admin-form__hint">https://… or a site path like /assets/img/photo.jpg</span>
                <input type="text" name="image_url" maxlength="512" value="<?= htmlspecialchars($form['image_url'], ENT_QUOTES, 'UTF-8') ?>" placeholder="/assets/img/placeholder-project.svg">
            </label>

            <label>
                Hero video URL
                <span class="admin-form__hint">YouTube or Vimeo page URL, or direct .mp4/.webm path (https or /path). When set, replaces the hero image.</span>
                <input type="text" name="hero_video_url" maxlength="512" value="<?= htmlspecialchars($form['hero_video_url'], ENT_QUOTES, 'UTF-8') ?>" placeholder="https://www.youtube.com/watch?v=…">
            </label>

            <label>
                Tech stack
                <span class="admin-form__hint">One item per line (e.g. PHP, MySQL).</span>
                <textarea name="tech_stack" rows="5"><?= htmlspecialchars($form['tech_stack_text'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>
                Challenges &amp; solutions (JSON)
                <span class="admin-form__hint">Array of objects, e.g. [{"challenge":"…","solution":"…"}]. Leave empty for none.</span>
                <textarea name="challenges_json" rows="8" spellcheck="false"><?= htmlspecialchars($form['challenges_json'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>
                GitHub URL
                <input type="url" name="github_url" maxlength="512" value="<?= htmlspecialchars($form['github_url'], ENT_QUOTES, 'UTF-8') ?>" placeholder="https://github.com/user/repo">
            </label>

            <label>
                Live / demo URL
                <input type="url" name="project_url" maxlength="512" value="<?= htmlspecialchars($form['project_url'], ENT_QUOTES, 'UTF-8') ?>" placeholder="https://">
            </label>

            <label>
                Category slug
                <span class="admin-form__hint">Used for filters on the public projects page (e.g. web, networking, tools).</span>
                <input type="text" name="category_slug" maxlength="64" value="<?= htmlspecialchars($form['category_slug'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Tag line
                <span class="admin-form__hint">Short label on cards (optional).</span>
                <input type="text" name="tag" maxlength="120" value="<?= htmlspecialchars($form['tag'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Display order
                <input type="number" name="display_order" min="0" step="1" value="<?= htmlspecialchars($form['display_order'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Owner user ID
                <span class="admin-form__hint">Optional FK to users.id; leave empty if none.</span>
                <input type="number" name="user_id" min="1" step="1" value="<?= htmlspecialchars($form['user_id'], ENT_QUOTES, 'UTF-8') ?>" placeholder="">
            </label>

            <label class="admin-form__check">
                <input type="checkbox" name="is_published" value="1"<?= $form['is_published'] ? ' checked' : '' ?>>
                Published on site
            </label>

            <div class="admin-form__actions">
                <button type="submit" class="btn btn--primary"><?= is_array($existing) ? 'Save changes' : 'Create project' ?></button>
                <a class="btn btn--ghost" href="<?= htmlspecialchars(admin_url('index.php'), ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
            </div>
        </form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>

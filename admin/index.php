<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth-check.php';
require_once dirname(__DIR__) . '/includes/admin_projects_repository.php';

$pageTitle = 'Admin — Projects';
$flash = admin_consume_flash();
$projects = admin_list_all_projects();

require_once __DIR__ . '/includes/admin-header.php';
?>

        <h1 class="admin-page-title">Projects</h1>

        <?php if (is_array($flash) && isset($flash['type'], $flash['message'])): ?>
            <p class="admin-flash admin-flash--<?= $flash['type'] === 'success' ? 'success' : 'error' ?>" role="status">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <div class="admin-actions">
            <a class="btn btn--primary" href="<?= htmlspecialchars(admin_url('project-form.php'), ENT_QUOTES, 'UTF-8') ?>">Add project</a>
            <a class="btn btn--ghost" href="<?= htmlspecialchars(admin_url('messages.php'), ENT_QUOTES, 'UTF-8') ?>">Contact messages</a>
        </div>

        <?php if ($projects === []): ?>
            <p class="admin-form__hint">No projects yet. Create one to get started.</p>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $row): ?>
                            <?php
                            $pid = (int) ($row['id'] ?? 0);
                            $title = isset($row['title']) ? (string) $row['title'] : '';
                            $cat = isset($row['category_slug']) ? (string) $row['category_slug'] : '';
                            $ord = (int) ($row['display_order'] ?? 0);
                            $pub = !empty($row['is_published']);
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $ord ?></td>
                                <td>
                                    <?php if ($pub): ?>
                                        <span class="admin-badge admin-badge--on">Published</span>
                                    <?php else: ?>
                                        <span class="admin-badge admin-badge--off">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= htmlspecialchars(admin_url('project-form.php?id=' . $pid), ENT_QUOTES, 'UTF-8') ?>">Edit</a>
                                    <form class="admin-inline-form admin-table__delete" method="post" action="<?= htmlspecialchars(admin_url('project-delete.php'), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?= $pid ?>">
                                        <button type="submit" class="admin-danger" data-confirm-delete>Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <script>
        document.querySelectorAll('[data-confirm-delete]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                if (!window.confirm('Delete this project? This cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
        </script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>

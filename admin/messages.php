<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth-check.php';
require_once dirname(__DIR__) . '/includes/admin_messages_repository.php';

$pageTitle = 'Admin — Messages';
$messages = admin_list_contact_messages(150);

require_once __DIR__ . '/includes/admin-header.php';
?>

        <h1 class="admin-page-title">Contact messages</h1>

        <p class="admin-form__hint admin-messages__intro">
            Submissions from the public <a href="<?= htmlspecialchars(APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') . '/contact.php' : '/contact.php', ENT_QUOTES, 'UTF-8') ?>">contact form</a>, stored in the <code>messages</code> table. Showing up to 150 most recent.
        </p>

        <?php if ($messages === []): ?>
            <p class="admin-form__hint">No messages yet.</p>
        <?php else: ?>
            <div class="admin-table-wrap admin-messages-table-wrap">
                <table class="admin-table admin-messages-table">
                    <thead>
                        <tr>
                            <th class="admin-messages-th--narrow">#</th>
                            <th class="admin-messages-th--date">Received</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $row): ?>
                            <?php
                            $mid = (int) ($row['id'] ?? 0);
                            $name = $row['sender_name'] ?? '';
                            $email = $row['sender_email'] ?? '';
                            $body = $row['body'] ?? '';
                            $at = $row['created_at'] ?? '';
                            ?>
                            <tr>
                                <td class="admin-messages-td--id admin-messages-td--mono"><?= $mid > 0 ? $mid : '—' ?></td>
                                <td class="admin-messages-td--mono admin-messages-td--date"><?= htmlspecialchars($at, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($email !== ''): ?>
                                        <a href="<?= htmlspecialchars('mailto:' . $email, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="admin-messages-body"><?= nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>

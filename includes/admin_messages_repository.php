<?php
/**
 * Admin: read contact form rows from `messages`.
 */
declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * Latest contact messages (newest first).
 *
 * @return list<array{id:int,user_id:int|null,sender_name:string,sender_email:string,body:string,created_at:string}>
 */
function admin_list_contact_messages(int $limit = 150): array
{
    if ($limit < 1) {
        $limit = 1;
    }
    if ($limit > 500) {
        $limit = 500;
    }

    $pdo = db();
    if ($pdo === null) {
        return [];
    }

    $sql = <<<'SQL'
        SELECT id, user_id, sender_name, sender_email, body, created_at
        FROM messages
        ORDER BY created_at DESC, id DESC
        LIMIT :lim
    SQL;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($rows)) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $out[] = [
                'id'            => isset($row['id']) ? (int) $row['id'] : 0,
                'user_id'       => isset($row['user_id']) && $row['user_id'] !== null ? (int) $row['user_id'] : null,
                'sender_name'   => isset($row['sender_name']) ? (string) $row['sender_name'] : '',
                'sender_email'  => isset($row['sender_email']) ? (string) $row['sender_email'] : '',
                'body'          => isset($row['body']) ? (string) $row['body'] : '',
                'created_at'    => isset($row['created_at']) ? (string) $row['created_at'] : '',
            ];
        }
        return $out;
    } catch (PDOException $e) {
        error_log('admin_list_contact_messages: ' . $e->getMessage());
        return [];
    }
}

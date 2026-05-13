<?php
/**
 * Admin CRUD for projects (PDO prepared statements).
 */
declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * @return list<array<string, mixed>>
 */
function admin_list_all_projects(): array
{
    $pdo = db();
    if ($pdo === null) {
        return [];
    }
    $sql = <<<'SQL'
        SELECT id, title, summary, category_slug, display_order, is_published, updated_at
        FROM projects
        ORDER BY display_order ASC, id ASC
    SQL;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    } catch (PDOException $e) {
        error_log('admin_list_all_projects: ' . $e->getMessage());
        return [];
    }
}

/**
 * @return array<string, mixed>|null
 */
function admin_get_project_by_id(int $id): ?array
{
    if ($id < 1) {
        return null;
    }
    $pdo = db();
    if ($pdo === null) {
        return null;
    }
    $sql = <<<'SQL'
        SELECT id, user_id, title, summary, description, my_role, image_url, hero_video_url, tech_stack,
               challenges_solutions, github_url, category_slug, tag, project_url, display_order, is_published
        FROM projects
        WHERE id = :id
        LIMIT 1
    SQL;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    } catch (PDOException $e) {
        error_log('admin_get_project_by_id: ' . $e->getMessage());
        return null;
    }
}

/**
 * @return array{ok:bool,json?:string|null,error?:string}
 */
function admin_normalize_challenges_json_input(string $text): array
{
    $t = trim($text);
    if ($t === '') {
        return ['ok' => true, 'json' => null];
    }
    try {
        $decoded = json_decode($t, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        return ['ok' => false, 'error' => 'Challenges & solutions must be valid JSON or empty.'];
    }
    if (!is_array($decoded)) {
        return ['ok' => false, 'error' => 'Challenges & solutions JSON must be an array.'];
    }
    $pairs = [];
    foreach ($decoded as $item) {
        if (!is_array($item)) {
            return ['ok' => false, 'error' => 'Each challenge entry must be an object with challenge and solution fields.'];
        }
        $c = isset($item['challenge']) ? trim((string) $item['challenge']) : '';
        $s = isset($item['solution']) ? trim((string) $item['solution']) : '';
        if ($c === '' && $s === '') {
            continue;
        }
        $pairs[] = ['challenge' => $c, 'solution' => $s];
    }
    if ($pairs === []) {
        return ['ok' => true, 'json' => null];
    }
    try {
        $json = json_encode($pairs, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    } catch (JsonException $e) {
        return ['ok' => false, 'error' => 'Could not encode challenges JSON.'];
    }
    return ['ok' => true, 'json' => $json];
}

/**
 * @param list<string> $techItems
 * @return array{ok:bool,error?:string,id?:int}
 */
function admin_create_project(
    string $title,
    string $summary,
    ?string $description,
    ?string $myRole,
    ?string $imageUrl,
    ?string $heroVideoUrl,
    array $techItems,
    ?string $challengesSolutionsJson,
    ?string $githubUrl,
    string $categorySlug,
    ?string $tag,
    ?string $projectUrl,
    int $displayOrder,
    int $isPublished,
    ?int $userId
): array {
    $pdo = db();
    if ($pdo === null) {
        return ['ok' => false, 'error' => 'Database unavailable.'];
    }

    $techJson = admin_tech_stack_to_json($techItems);
    if ($techJson === null) {
        return ['ok' => false, 'error' => 'Invalid tech stack.'];
    }

    $sql = <<<'SQL'
        INSERT INTO projects (
            user_id, title, summary, description, my_role, image_url, hero_video_url, tech_stack,
            challenges_solutions, github_url, category_slug, tag, project_url, display_order, is_published
        ) VALUES (
            :user_id, :title, :summary, :description, :my_role, :image_url, :hero_video_url, :tech_stack,
            :challenges_solutions, :github_url, :category_slug, :tag, :project_url, :display_order, :is_published
        )
    SQL;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'               => $userId,
            ':title'                 => $title,
            ':summary'               => $summary,
            ':description'           => $description,
            ':my_role'               => $myRole,
            ':image_url'             => $imageUrl,
            ':hero_video_url'        => $heroVideoUrl,
            ':tech_stack'            => $techJson,
            ':challenges_solutions'  => $challengesSolutionsJson,
            ':github_url'            => $githubUrl,
            ':category_slug'         => $categorySlug,
            ':tag'                   => $tag,
            ':project_url'           => $projectUrl,
            ':display_order'         => $displayOrder,
            ':is_published'          => $isPublished,
        ]);
        $id = (int) $pdo->lastInsertId();
        return ['ok' => true, 'id' => $id];
    } catch (PDOException $e) {
        error_log('admin_create_project: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Could not create project.'];
    }
}

/**
 * @param list<string> $techItems
 * @return array{ok:bool,error?:string}
 */
function admin_update_project(
    int $id,
    string $title,
    string $summary,
    ?string $description,
    ?string $myRole,
    ?string $imageUrl,
    ?string $heroVideoUrl,
    array $techItems,
    ?string $challengesSolutionsJson,
    ?string $githubUrl,
    string $categorySlug,
    ?string $tag,
    ?string $projectUrl,
    int $displayOrder,
    int $isPublished,
    ?int $userId
): array {
    if ($id < 1) {
        return ['ok' => false, 'error' => 'Invalid project.'];
    }
    $pdo = db();
    if ($pdo === null) {
        return ['ok' => false, 'error' => 'Database unavailable.'];
    }

    $techJson = admin_tech_stack_to_json($techItems);
    if ($techJson === null) {
        return ['ok' => false, 'error' => 'Invalid tech stack.'];
    }

    $sql = <<<'SQL'
        UPDATE projects SET
            user_id = :user_id,
            title = :title,
            summary = :summary,
            description = :description,
            my_role = :my_role,
            image_url = :image_url,
            hero_video_url = :hero_video_url,
            tech_stack = :tech_stack,
            challenges_solutions = :challenges_solutions,
            github_url = :github_url,
            category_slug = :category_slug,
            tag = :tag,
            project_url = :project_url,
            display_order = :display_order,
            is_published = :is_published
        WHERE id = :id
        LIMIT 1
    SQL;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id'                    => $id,
            ':user_id'               => $userId,
            ':title'                 => $title,
            ':summary'               => $summary,
            ':description'           => $description,
            ':my_role'               => $myRole,
            ':image_url'             => $imageUrl,
            ':hero_video_url'        => $heroVideoUrl,
            ':tech_stack'            => $techJson,
            ':challenges_solutions'  => $challengesSolutionsJson,
            ':github_url'            => $githubUrl,
            ':category_slug'         => $categorySlug,
            ':tag'                   => $tag,
            ':project_url'           => $projectUrl,
            ':display_order'         => $displayOrder,
            ':is_published'          => $isPublished,
        ]);
        return ['ok' => true];
    } catch (PDOException $e) {
        error_log('admin_update_project: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Could not update project.'];
    }
}

function admin_delete_project(int $id): bool
{
    if ($id < 1) {
        return false;
    }
    $pdo = db();
    if ($pdo === null) {
        return false;
    }
    try {
        $stmt = $pdo->prepare('DELETE FROM projects WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('admin_delete_project: ' . $e->getMessage());
        return false;
    }
}

/**
 * @param list<string> $items
 */
function admin_tech_stack_to_json(array $items): ?string
{
    $clean = [];
    foreach ($items as $item) {
        if (!is_string($item)) {
            return null;
        }
        $t = trim($item);
        if ($t !== '') {
            $clean[] = $t;
        }
    }
    try {
        return json_encode($clean, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    } catch (JsonException $e) {
        return null;
    }
}

/**
 * @return list<string>
 */
function admin_parse_tech_stack_text(string $text): array
{
    $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
    $out = [];
    foreach ($lines as $line) {
        $t = trim($line);
        if ($t !== '') {
            $out[] = $t;
        }
    }
    return $out;
}

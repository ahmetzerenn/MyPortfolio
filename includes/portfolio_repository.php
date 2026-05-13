<?php
/**
 * Public site data layer: published projects, contact messages, URL helpers.
 * All queries use PDO prepared statements (see admin_projects_repository for CMS writes).
 */
declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * @param mixed $raw JSON string or null from DB
 * @return list<string>
 */
function portfolio_decode_tech_stack(mixed $raw): array
{
    if (!is_string($raw) || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [];
    }
    $out = [];
    foreach ($decoded as $item) {
        if (is_string($item) && $item !== '') {
            $out[] = $item;
        }
    }
    return $out;
}

/**
 * @return list<array{challenge: string, solution: string}>
 */
function portfolio_decode_challenges_solutions(mixed $raw): array
{
    $decoded = null;
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
    } elseif (is_array($raw)) {
        $decoded = $raw;
    }
    if (!is_array($decoded)) {
        return [];
    }
    $out = [];
    foreach ($decoded as $item) {
        if (!is_array($item)) {
            continue;
        }
        $c = isset($item['challenge']) ? trim((string) $item['challenge']) : '';
        $s = isset($item['solution']) ? trim((string) $item['solution']) : '';
        if ($c === '' && $s === '') {
            continue;
        }
        $out[] = ['challenge' => $c, 'solution' => $s];
    }
    return $out;
}

/**
 * Same rules as hero images: https URL or site path.
 */
function portfolio_safe_hero_video_url(?string $url): ?string
{
    if ($url === null) {
        return null;
    }
    $u = trim($url);
    if ($u === '') {
        return null;
    }
    if (preg_match('#\Ahttps?://#i', $u) || preg_match('#\A/#', $u)) {
        return $u;
    }
    return null;
}

/**
 * Resolve a hero video URL into an iframe or native video source.
 *
 * @return array{type: 'iframe', src: string}|array{type: 'video', src: string}|null
 */
function portfolio_hero_video_embed(?string $url): ?array
{
    $safe = portfolio_safe_hero_video_url($url);
    if ($safe === null) {
        return null;
    }
    if (preg_match('#\A/#', $safe)) {
        $lower = strtolower($safe);
        if (str_ends_with($lower, '.mp4') || str_ends_with($lower, '.webm')) {
            return ['type' => 'video', 'src' => $safe];
        }
        return null;
    }
    if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([a-zA-Z0-9_-]{11})#', $safe, $m)) {
        $id = $m[1];
        $src = 'https://www.youtube-nocookie.com/embed/' . rawurlencode($id) . '?rel=0';
        return ['type' => 'iframe', 'src' => $src];
    }
    if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $safe, $m)) {
        $src = 'https://player.vimeo.com/video/' . rawurlencode($m[1]);
        return ['type' => 'iframe', 'src' => $src];
    }
    $lower = strtolower($safe);
    if (str_ends_with($lower, '.mp4') || str_ends_with($lower, '.webm')) {
        return ['type' => 'video', 'src' => $safe];
    }
    return null;
}

/**
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function portfolio_normalize_project_row(array $row): array
{
    $row['tech_stack'] = portfolio_decode_tech_stack($row['tech_stack'] ?? null);
    $row['challenges_solutions'] = portfolio_decode_challenges_solutions($row['challenges_solutions'] ?? null);
    return $row;
}

function portfolio_safe_http_url(?string $url): ?string
{
    if ($url === null) {
        return null;
    }
    $u = trim($url);
    if ($u === '') {
        return null;
    }
    return preg_match('#\Ahttps?://#i', $u) ? $u : null;
}

function portfolio_safe_image_url(?string $url, string $fallback): string
{
    if ($url === null) {
        return $fallback;
    }
    $u = trim($url);
    if ($u === '') {
        return $fallback;
    }
    if (preg_match('#\Ahttps?://#i', $u) || preg_match('#\A/#', $u)) {
        return $u;
    }
    return $fallback;
}

function portfolio_safe_github_url(?string $url): ?string
{
    if ($url === null) {
        return null;
    }
    $u = trim($url);
    if ($u === '') {
        return null;
    }
    return preg_match('#\Ahttps://github\.com/.+#i', $u) ? $u : null;
}

function portfolio_category_label(string $slug): string
{
    $safe = strtolower(preg_replace('/[^a-z0-9_-]/', '', $slug));
    if ($safe === '') {
        return __('project_cat_general', 'General');
    }
    $key = 'project_cat_' . str_replace('-', '_', $safe);
    return __($key, ucwords(str_replace(['-', '_'], ' ', $safe)));
}

/**
 * @return list<array<string, mixed>>
 */
function portfolio_fetch_published_projects(): array
{
    $pdo = db();
    if ($pdo === null) {
        return [];
    }

    $sql = <<<'SQL'
        SELECT id, title, summary, description, my_role, image_url, hero_video_url, tech_stack,
               challenges_solutions, github_url, category_slug, tag, project_url, display_order
        FROM projects
        WHERE is_published = 1
        ORDER BY display_order ASC, id ASC
    SQL;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($rows)) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            if (is_array($row)) {
                $out[] = portfolio_normalize_project_row($row);
            }
        }
        return $out;
    } catch (PDOException $e) {
        error_log('portfolio_fetch_published_projects: ' . $e->getMessage());
        return [];
    }
}

/**
 * @return array<string, mixed>|null
 */
function portfolio_fetch_published_project_by_id(int $id): ?array
{
    if ($id < 1) {
        return null;
    }

    $pdo = db();
    if ($pdo === null) {
        return null;
    }

    $sql = <<<'SQL'
        SELECT id, title, summary, description, my_role, image_url, hero_video_url, tech_stack,
               challenges_solutions, github_url, category_slug, tag, project_url, display_order
        FROM projects
        WHERE id = :id AND is_published = 1
        LIMIT 1
    SQL;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return null;
        }
        return portfolio_normalize_project_row($row);
    } catch (PDOException $e) {
        error_log('portfolio_fetch_published_project_by_id: ' . $e->getMessage());
        return null;
    }
}

/**
 * @param list<array<string, mixed>> $projects
 * @return list<string>
 */
function portfolio_distinct_category_slugs(array $projects): array
{
    $set = [];
    foreach ($projects as $p) {
        $slug = isset($p['category_slug']) && is_string($p['category_slug']) ? trim($p['category_slug']) : '';
        if ($slug !== '') {
            $set[$slug] = true;
        }
    }
    $slugs = array_keys($set);
    sort($slugs, SORT_STRING);
    return $slugs;
}

function portfolio_insert_contact_message(string $name, string $email, string $body): bool
{
    $pdo = db();
    if ($pdo === null) {
        return false;
    }

    $sql = <<<'SQL'
        INSERT INTO messages (sender_name, sender_email, body)
        VALUES (:sender_name, :sender_email, :body)
    SQL;

    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':sender_name'  => $name,
            ':sender_email' => $email,
            ':body'         => $body,
        ]);
    } catch (PDOException $e) {
        error_log('portfolio_insert_contact_message: ' . $e->getMessage());
        return false;
    }
}

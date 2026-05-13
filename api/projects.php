<?php
/**
 * GET: published projects as JSON (for Fetch on projects page).
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
// Short cache: faster repeat visits; invalidate by bumping query string if needed.
header('Cache-Control: public, max-age=60, stale-while-revalidate=120');

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/portfolio_repository.php';

$base = APP_BASE_URL !== '' ? rtrim(APP_BASE_URL, '/') : '';
$placeholderImage = $base . '/assets/img/placeholder-project.svg';

$projects = portfolio_fetch_published_projects();
$payload = [];

foreach ($projects as $p) {
    $id = isset($p['id']) ? (int) $p['id'] : 0;
    if ($id < 1) {
        continue;
    }
    $slug = isset($p['category_slug']) && is_string($p['category_slug']) ? $p['category_slug'] : 'general';
    $title = isset($p['title']) ? (string) $p['title'] : '';
    $summary = isset($p['summary']) ? (string) $p['summary'] : '';
    $tag = isset($p['tag']) && $p['tag'] !== null ? (string) $p['tag'] : '';

    $imgSrc = portfolio_safe_image_url(
        isset($p['image_url']) && is_string($p['image_url']) ? $p['image_url'] : null,
        $placeholderImage
    );

    $payload[] = [
        'id'              => $id,
        'title'           => $title,
        'summary'         => $summary,
        'tag'             => $tag,
        'category_slug'   => $slug,
        'category_label'  => portfolio_category_label($slug),
        'image_url'       => $imgSrc,
        'detail_url'      => $base . '/project.php?id=' . $id,
    ];
}

$json = json_encode(
    [
        'ok'       => true,
        'projects' => $payload,
        'i18n'     => [
            'empty'          => __('projects_empty'),
            'noneFiltered'   => __('projects_none_filtered'),
            'filterAll'      => __('projects_filter_all'),
            'filterLabel'    => __('projects_filter_label'),
            'filterAria'     => __('projects_filter_aria'),
            'viewDetails'    => __('projects_view_details'),
            'loading'        => __('projects_loading'),
            'loadError'      => __('projects_load_error'),
        ],
    ],
    JSON_UNESCAPED_UNICODE
);
if ($json === false) {
    http_response_code(500);
    echo '{"ok":false,"error":"encode"}';
    exit;
}
echo $json;

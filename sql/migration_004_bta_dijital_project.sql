-- Add BTA Dijital corporate site project (id 3). Safe to re-run: upserts by primary key.
-- mysql -u root -p portfolio_db < sql/migration_004_bta_dijital_project.sql

USE portfolio_db;

INSERT INTO projects (
    id, user_id, title, summary, description, image_url, tech_stack, github_url, category_slug,
    tag, project_url, display_order, is_published
) VALUES (
    3, 1,
    'BTA Dijital — Corporate website',
    'End-to-end production website for a growth-focused digital agency: strategy-led messaging, service pages, FAQ, and lead funnels — shipped as a fast, responsive experience.',
    'I designed and built https://btadijital.com/ from the ground up: information architecture, UI, front-end implementation, and integration of conversion-focused patterns (strategy CTAs, service breakdowns, social proof, and FAQ).\n\nThe site communicates BTA Dijital''s positioning around measurable demand and systematic growth rather than vanity metrics, with clear paths to contact and a free strategy analysis offer.\n\nThe build emphasizes responsive layout, readable typography, and performance-minded delivery so the marketing narrative stays fast and accessible on real devices.',
    '/assets/img/placeholder-project.svg',
    '["HTML", "CSS", "JavaScript", "Responsive design", "Performance"]',
    NULL,
    'web',
    'Marketing site · Full build',
    'https://btadijital.com/',
    3, 1
)
ON DUPLICATE KEY UPDATE
    user_id = VALUES(user_id),
    title = VALUES(title),
    summary = VALUES(summary),
    description = VALUES(description),
    image_url = VALUES(image_url),
    tech_stack = VALUES(tech_stack),
    github_url = VALUES(github_url),
    category_slug = VALUES(category_slug),
    tag = VALUES(tag),
    project_url = VALUES(project_url),
    display_order = VALUES(display_order),
    is_published = VALUES(is_published);

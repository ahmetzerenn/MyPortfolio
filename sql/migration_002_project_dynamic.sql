-- Add detail fields, tech stack, GitHub, category (run once on existing DBs).
-- mysql -u root -p portfolio_db < sql/migration_002_project_dynamic.sql

USE portfolio_db;

ALTER TABLE projects
    ADD COLUMN description TEXT NULL AFTER summary,
    ADD COLUMN image_url VARCHAR(512) NULL AFTER description,
    ADD COLUMN tech_stack JSON NULL AFTER image_url,
    ADD COLUMN github_url VARCHAR(512) NULL AFTER tech_stack,
    ADD COLUMN category_slug VARCHAR(64) NOT NULL DEFAULT 'general' AFTER github_url,
    ADD KEY idx_projects_category (category_slug);

UPDATE projects SET description = summary WHERE description IS NULL OR description = '';

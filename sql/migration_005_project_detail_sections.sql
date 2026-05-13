-- Hero video, role, and challenges/solutions for project detail page.
-- mysql -u root -p portfolio_db < sql/migration_005_project_detail_sections.sql

USE portfolio_db;

ALTER TABLE projects
    ADD COLUMN my_role TEXT NULL AFTER description,
    ADD COLUMN hero_video_url VARCHAR(512) NULL AFTER image_url,
    ADD COLUMN challenges_solutions JSON NULL AFTER tech_stack;

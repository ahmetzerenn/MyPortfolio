-- Shared hosting / phpMyAdmin import (InfinityFree, 000webhost, etc.).
-- No CREATE DATABASE or USE: select your existing database in phpMyAdmin, then Import this file.
-- Full local setup with a new database: use sql/portfolio_export.sql instead.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS site_settings (
    `key` VARCHAR(64) NOT NULL PRIMARY KEY,
    `value` TEXT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default: username admin, password changeme (rotate after first login)
INSERT IGNORE INTO admin_users (username, password_hash) VALUES (
    'admin',
    '$2y$12$y9FUk.OsfSgwQFSwjE0sj.y9dnsehSfcxKnuR4Vys/8Fdhl8rITm2'
);

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    summary TEXT NOT NULL,
    description TEXT NULL,
    my_role TEXT NULL,
    image_url VARCHAR(512) NULL,
    hero_video_url VARCHAR(512) NULL,
    tech_stack JSON NULL,
    challenges_solutions JSON NULL,
    github_url VARCHAR(512) NULL,
    category_slug VARCHAR(64) NOT NULL DEFAULT 'general',
    tag VARCHAR(120) NULL,
    project_url VARCHAR(512) NULL,
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_projects_published_order (is_published, display_order),
    KEY idx_projects_category (category_slug),
    CONSTRAINT fk_projects_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    sender_name VARCHAR(120) NOT NULL,
    sender_email VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_messages_created (created_at),
    CONSTRAINT fk_messages_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (id, email, full_name, password_hash) VALUES
    (1, 'hello@example.com', 'Ahmet Zeren', NULL);

INSERT INTO projects (
    id, user_id, title, summary, description, my_role, image_url, hero_video_url, tech_stack,
    challenges_solutions, github_url, category_slug, tag, project_url, display_order, is_published
) VALUES
    (
        1, 1,
        'Midnight Scour',
        'Midnight Scour is a horror game developed with Unity as part of a team project. The game focuses on atmosphere, tension, and immersive storytelling.',
        'Midnight Scour is a horror game developed with Unity as part of a team project. The game focuses on atmosphere, tension, and immersive storytelling.\n\nI contribute to the technical side of the project, working on core mechanics and system logic while collaborating with the team to create a cohesive experience.\n\nThe project is currently in development and planned for release on Steam.',
        'Gameplay systems, interaction logic, and performance-minded iteration — collaborating with art and design so mechanics stay readable under pressure.',
        '/assets/img/placeholder-project.svg',
        'https://youtu.be/pGSwN6lFaB4',
        '["Unity", "C#", "Horror", "Steam"]',
        '[{"challenge":"Keeping tension without tanking frame times","solution":"Profiling passes, sensible LOD choices, and staged complexity so scares stay sharp on mid-range hardware."},{"challenge":"Consistent interaction language across scenes","solution":"Shared interaction contracts and predictable player feedback loops the whole team could extend."}]',
        NULL,
        'general',
        'Horror · Unity · Steam',
        NULL,
        1, 1
    ),
    (
        2, 1,
        'Colins Internship',
        'During my internship at Colins, I worked on backend development using ASP.NET. I was involved in building and improving data-driven systems and gained practical experience with SQL databases.',
        'During my internship at Colins, I worked on backend development using ASP.NET. I was involved in building and improving data-driven systems and gained practical experience with SQL databases.\n\nThis experience strengthened my understanding of real-world software development, including system architecture, database design, and performance considerations.',
        'Backend features on ASP.NET services: data access patterns, SQL tuning, and shipping changes alongside senior developers.',
        '/assets/img/placeholder-project.svg',
        NULL,
        '["ASP.NET", "SQL", "C#", "Backend"]',
        '[{"challenge":"Translating business rules into reliable queries","solution":"Worked with mentors to model constraints in SQL and keep services testable and observable."}]',
        NULL,
        'web',
        'ASP.NET · SQL · Internship',
        NULL,
        2, 1
    ),
    (
        3, 1,
        'BTA Dijital — Corporate website',
        'End-to-end production website for a growth-focused digital agency: strategy-led messaging, service pages, FAQ, and lead funnels — shipped as a fast, responsive experience.',
        'I designed and built https://btadijital.com/ from the ground up: information architecture, UI, front-end implementation, and integration of conversion-focused patterns (strategy CTAs, service breakdowns, social proof, and FAQ).\n\nThe site communicates BTA Dijital''s positioning around measurable demand and systematic growth rather than vanity metrics, with clear paths to contact and a free strategy analysis offer.\n\nThe build emphasizes responsive layout, readable typography, and performance-minded delivery so the marketing narrative stays fast and accessible on real devices.',
        'Solo build: IA, visual design, responsive front-end, content structure, and performance-minded delivery for a marketing-led launch.',
        '/assets/img/placeholder-project.svg',
        NULL,
        '["HTML", "CSS", "JavaScript", "Responsive design", "Performance"]',
        '[{"challenge":"Clarifying services without drowning visitors in copy","solution":"Modular section rhythm, scannable headings, and repeated high-intent CTAs tuned to the agency funnel."},{"challenge":"Keeping the marketing story fast on mobile","solution":"Lean assets, predictable layout shifts avoided, and pragmatic CSS so the site stays responsive on real devices."}]',
        NULL,
        'web',
        'Marketing site · Full build',
        'https://btadijital.com/',
        3, 1
    );

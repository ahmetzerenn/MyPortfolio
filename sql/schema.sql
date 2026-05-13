-- Portfolio MySQL schema (8.0+).
-- Create database: CREATE DATABASE portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Optional: site settings / admin (legacy tables used elsewhere in the project)
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

-- Default login (change password in production): user "admin", password "changeme"
-- INSERT INTO admin_users (username, password_hash) VALUES ('admin', '<bcrypt hash>');

-- Application users (e.g. portfolio owner or future accounts)
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Published portfolio projects
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

-- Contact form / inbound messages
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

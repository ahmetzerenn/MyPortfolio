-- Optional: add default admin if table already exists without seed.
-- mysql -u root -p portfolio_db < sql/seed_admin.sql
-- Default password: changeme — change immediately in production.

USE portfolio_db;

INSERT IGNORE INTO admin_users (username, password_hash) VALUES (
    'admin',
    '$2y$12$y9FUk.OsfSgwQFSwjE0sj.y9dnsehSfcxKnuR4Vys/8Fdhl8rITm2'
);

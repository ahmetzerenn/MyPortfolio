# Portfolio (PHP + MySQL)

A bilingual (EN/TR) personal portfolio with a public site, JSON APIs, AJAX-enhanced projects and contact flows, and an admin area for managing projects.

## Features

- **Front site:** Home, About, Projects (loaded via `fetch` + category filters), project detail pages, contact form (AJAX with JSON fallback to classic POST/redirect).
- **Admin:** Session-based login (`password_hash` / `password_verify`), project CRUD, CSRF on forms.
- **Data:** PDO, prepared statements, UTF-8 (`utf8mb4`).
- **UX:** Dark/light theme, reduced-motion support, skeleton loading on projects, submit spinner on contact, safe-area insets for notched devices.

## Requirements

- PHP **8.1+** (extensions: `pdo_mysql`, `json`, `mbstring`, `session`)
- MySQL **8.0+** (or MariaDB 10.6+ with JSON column support for `projects.tech_stack`)
- Apache with `mod_rewrite` (optional) **or** nginx + PHP-FPM

## Quick start (local)

1. Clone or copy the project into your web root or vhost document root.

2. Create the database and import schema + seed data:

   ```bash
   mysql -u root -p < sql/portfolio_export.sql
   ```

   Or apply `sql/schema.sql` after creating `portfolio_db`, then run `sql/seed_admin.sql` for the default admin user.

3. Configure `includes/config.php`:

   - `APP_BASE_URL` — `''` if the site lives at `/`, or `'/your-subpath'` (no trailing slash).
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — MySQL credentials.

4. Point the web server document root at this directory (the folder that contains `index.php`).

5. Open the site in a browser. Default admin (if you used `portfolio_export.sql` or `seed_admin.sql`):

   - URL: `/admin/login.php`
   - Username: `admin`
   - Password: `changeme`  
   **Change this password before production** (update `admin_users.password_hash` with a new `password_hash()` value).

## Project layout

| Path | Role |
|------|------|
| `index.php`, `about.php`, `projects.php`, `contact.php`, `project.php` | Public pages |
| `api/*.php` | JSON endpoints (`projects.php`, `set-language.php`, `health.php`) |
| `admin/*` | Dashboard, login, project forms |
| `includes/` | Config, bootstrap, DB, repositories, auth |
| `assets/css`, `assets/js` | Styles and scripts (vanilla JS only) |
| `sql/` | Schema, export, migrations, admin seed |

## Security notes

- Use **HTTPS** in production; uncomment the HTTPS redirect in `.htaccess` when TLS is ready.
- Rotate the default admin password; restrict `/admin` by IP or HTTP auth if desired.
- `.htaccess` denies direct access to `*.sql` files when using Apache.
- CSRF tokens: meta tag + session; admin and contact forms verify them.

---

## Deployment instructions

### 1. Server preparation

- Install PHP-FPM or `mod_php`, MySQL, and a web server (Apache or nginx).
- Ensure `pdo_mysql` is enabled (`php -m | grep pdo_mysql`).

### 2. Database

On the server:

```bash
mysql -u YOUR_USER -p -e "CREATE DATABASE portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u YOUR_USER -p portfolio_db < sql/portfolio_export.sql
```

Or deploy schema only, then migrations/seed as needed. Set strong credentials and **do not** commit real passwords to git.

### 3. Application configuration

Edit `includes/config.php` on the server:

- `APP_BASE_URL` must match how the app is mounted (e.g. `''` for `https://example.com/`, or `'/portfolio'` for `https://example.com/portfolio/`).
- Set `DB_*` to production database values.

### 4. Apache

- DocumentRoot should be the project root (where `index.php` lives).
- Allow `.htaccess` overrides (`AllowOverride All` for the vhost) so caching/compression/security headers apply.
- Enable `mod_headers`, `mod_deflate`, and `mod_expires` if you rely on the bundled `.htaccess` rules.

### 5. nginx (alternative)

Example location block (adjust socket/path and `try_files` for your PHP setup):

```nginx
root /var/www/portfolio;
index index.php;

location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
}

location ~* \.(sql)$ {
    deny all;
}
```

Add gzip/brotli and cache headers for `/assets/` as appropriate.

### 6. File permissions

- Directories: typically `755`, files: `644`.
- Ensure the web user can read the tree; **do not** make `includes/config.php` world-writable.

### 7. Post-deploy checks

- Visit `/api/health.php` — expect JSON with `"ok": true` and `db: true` if the DB connection works.
- Load `/projects.php` — skeleton then project cards; check browser network tab for `/api/projects.php`.
- Submit contact form — success message without full page reload.
- Log into `/admin`, create/edit/delete a test project, confirm it appears on the public projects list after cache TTL (API uses a short `Cache-Control`).

### 8. Performance (optional)

- Put the site behind a CDN for static assets; bump cache lifetimes in `.htaccess` or nginx if you use fingerprinted asset names.
- OPcache enabled in `php.ini` is recommended for production.
- Tune MySQL connection pooling / max connections for your host.

---

## License

Use and modify for your own portfolio. No warranty implied.

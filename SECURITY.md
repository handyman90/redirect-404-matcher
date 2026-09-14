# Security Policy

## Supported Versions

| Version | Security Support |
|---------|------------------|
| 1.4.x   | ✅ Active         |
| < 1.4.0 | ❌ No longer supported — please update |

Security fixes are released as new 1.4.x patch versions. Only the latest stable tag receives updates.

---

## Reporting a Vulnerability

Please email [dev@zhrventure.com](mailto:dev@zhrventure.com) with:

- Plugin version, WordPress version, PHP version
- Steps to reproduce (request path, settings involved, user role)
- Expected vs. actual behavior, and any logs/screenshots
- Your assessment of impact, if known

Rules:

- Do not disclose security issues publicly (issues, forums, social) until fixed.
- Do not test on sites you do not own or have permission to test.
- Scope is this plugin only — not WordPress core, themes, hosting, or third-party plugins.

We aim to acknowledge within 72 hours, triage within 7 days, and ship a fix as a 1.4.x release depending on severity. Credit is given on request.

---

## Key Security Measures (1.4.0)

Access control:

- Settings page under `Settings > 404 Redirect Matcher` (`add_options_page`), `manage_options` required in menu registration AND page callback (`settings.php:9-15`, `83-85`).
- Admin assets (`wp_enqueue_media`, inline JS) load only on `settings_page_r4mp-settings` (`settings.php:23-26`).
- No frontend privileged actions; `template_redirect` handler is unauthenticated by design but read-only (redirect / 410 only).

Input sanitization (`register_setting` callbacks):

- `r4mp_enabled`, `r4mp_enable_410` → `absint`.
- `r4mp_post_types` → `r4mp_sanitize_post_types()`: `sanitize_key` + whitelist against `get_post_types(['public' => true])`, fallback to `post, page`.
- `r4mp_default_image_url`, `r4mp_custom_404_url` → `esc_url_raw`.
- `r4mp_manual_redirects` → `r4mp_sanitize_manual_redirects()`: max 50 lines, 500 chars/line, `sanitize_text_field`; runtime re-validated.

Output escaping:

- `esc_attr`, `esc_html`, `esc_url`, `esc_textarea`, `esc_js`, `wp_json_encode` throughout settings page and inline scripts.

Open-redirect prevention:

- All destinations (manual overrides, slug-map, fuzzy match via `get_permalink()`, custom 404, image fallback) must start with `home_url()` and differ from the current URL, sent via `wp_safe_redirect()` (`redirect-404-matcher.php:56-57`, `71-72`, `118-119`, `128`).
- Manual rules capped at 50, same-site only (relative `/path` resolved via `home_url()`), `sanitize_title` on source path.
- Image fallback host-checked with `wp_parse_url()` — external hosts rejected (`settings.php:189-193`).

SQL injection prevention:

- No raw input in SQL. Post types expanded as `%s` placeholders, slug fragment via `$wpdb->esc_like()` + `$wpdb->prepare()`, `LIMIT 15`, `post_status = 'publish'` only (`redirect-404-matcher.php:211-217`).

Abuse / DoS hardening:

- Slug normalization: lowercase, strip `.html/.php/.asp`, `sanitize_title()`, 200-char cap.
- Lookup scope: last 3 path segments only, `LIKE` on first 20 chars, 15 rows/fragment, combined `similar_text` (60%) + Levenshtein (40%) score, threshold 55.
- Bounded storage: slug-map max 200, gone-slugs max 200, manual rules max 50 (oldest trimmed).
- Exclusions: admin, feeds, embeds, robots, trackbacks, `wp-json`, `wp-admin`, `xmlrpc.php`, sitemaps, `.xml`, `feed`, `robots.txt`, `favicon.ico`, and `.php/.env/.ini/.cfg/.log/.sql/.bak/.git/.svn` probes never redirect (`r4mp_should_skip()`).
- Removed content returns proper `410` via `status_header(410)` + `nocache_headers()` + `wp_die(..., 410)` instead of soft-404 redirects.
- Redirect-loop guard: destination `!==` current URL on every path.

Privacy / supply chain:

- No external requests, no logs, no IP/UA/cookie storage, no custom tables, no cron, no bundled third-party libraries.
- `if (!defined('ABSPATH')) exit;` direct-access guard, `function_exists()` guards against redeclaration.
- Multisite-safe per-site `get_option()`; `uninstall.php` deletes all 8 options + legacy `r4mp_license_key` on every site.

---

## Hardening Tips for Admins

- Keep plugin, WordPress (Requires at least: 6.0), and PHP (Requires PHP: 7.4) updated.
- Restrict `manage_options` to trusted admins; review Manual Overrides (max 50) periodically.
- Use same-site URLs for Custom 404 and Default Image; leave empty to disable.
- Uncheck “Return 410” only if you prefer default 404 handling for trashed slugs.

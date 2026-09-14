# Changelog

## [1.4.0] – 2026-09-14

### ✅ Differentiators vs 404 Solution / 404-to-301 / 404-to-homepage
- Priority pipeline: manual overrides (exact) > renamed-post map > 410 gone > smart fuzzy match > custom 404 fallback.
- Slug-change auto-tracking via `post_updated` (max 200 old slug → new URL, same-site only).
- Removed-content handling via `transition_post_status`: trashed slugs return proper 410 with opt-out (`r4mp_enable_410`).
- Manual overrides textarea: `/old-path /new-path` one per line, max 50, same-site enforced, exact match wins.
- Smarter matching: slug normalization (lowercase + `.html/.php/.asp` strip) + 60% `similar_text` / 40% Levenshtein combined score, threshold 55, `LIMIT 15`.
- Exclusions: admin, feeds, embeds, sitemaps, `wp-json`, robots, favicon, `.php/.env`-style probes never redirect.
- No logs / no tracking retained as core differentiator (no IPs, no tables, no cron).

## [1.3.0] – 2026-09-13

### ✅ WordPress.org Submission Compliance
- Removed license-key gating (`r4mp_is_pro_active` + demo key `12345678`); all features free in directory version.
- Renamed plugin to `Redirect 404 Matcher`, version fixed to `1.3.0` (was `1.3d`).
- Added `readme.txt` with required headers (Requires at least, Tested up to, Stable tag), `uninstall.php` cleanup, `Requires at least: 6.0` / `Requires PHP: 7.4` headers, and `load_plugin_textdomain()`.
- Moved admin menu from top-level `add_menu_page` to `Settings > 404 Redirect Matcher` via `add_options_page()` (single page).
- Consolidated image / custom-404 / license subpages into one settings page; legacy `r4mp_license_key` removed on uninstall.

### 🛡 Security Hardening
- `wp_safe_redirect()` + same-site (`home_url()`) checks to prevent open redirects.
- Fully prepared SQL: whitelisted post types against `get_post_types(public)` + `%s` placeholders + `$wpdb->esc_like()`.
- `r4mp_sanitize_post_types()` with `sanitize_key` whitelist; `absint` / `esc_url_raw` elsewhere.
- `current_user_can('manage_options')` checked in menu registration AND page callback.
- Image fallback via `wp_enqueue_scripts` + `wp_register_script` + `wp_add_inline_script()` with `wp_json_encode(esc_url_raw())`; same-site image restriction.
- Per-site `get_option()` getter so Settings API save/load always match (fixes multisite mismatch).

---

## [1.3] – 2025-06-25

### ✅ Improvements & Fixes
- Wrapped all `r4mp_*` functions to avoid redeclaration errors when loaded multiple times.
- Implemented redirect loop protection: stops infinite redirects by limiting redirect depth.
- Improved matching performance by reversing fragments and reducing DB lookups.
- Compatibility added for multisite: shared settings when network-activated.
- All settings pages restricted to users with `manage_options` (Admins only).
- JavaScript for image fallback now uses `json_encode()` instead of `esc_url()` to prevent URL encoding issues in JS.
- Added helper `r4mp_get_setting()` to streamline settings retrieval with multisite awareness.

### 🧪 Pro Features (Demo-Enabled, pre-org version)
- Added placeholder license key support (`12345678`) to unlock Pro features.
- Added image fallback for broken `<img>` elements (Pro).
- Added custom 404 redirect URL when no match is found (Pro).

### 🛡 Security Hardening
- All settings use proper sanitization via `register_setting()` callbacks.
- Output escaping (`esc_attr`, `esc_html`) enforced on all fields.
- JavaScript fallback uses scoped conditions and safe attributes.
- Admin-only interface and options.
- Added basic `SECURITY.md` policy.

---

## [1.2] – 2025-06-24

- Added settings UI with options to enable matching and choose post types.
- Created separate submenu pages for:
  - Settings
  - Missing image URL replacement
  - Custom 404 fallback
  - License key input
- Enabled Pro features conditionally based on license key match.
- Fallback for unmatched URLs via custom redirect (302).
- Default image replacement for missing image URLs via JS injection.

---

## [1.1] – 2025-06-23

- Improved matching logic using `similar_text()` for slug fragment accuracy.
- Redirects now use `301` for successful match and `302` for fallback.
- Threshold matching percentage set at 50%.
- Matching logic checks up to 3 path segments.
- Early exit if 404 is not triggered or if path is empty.

---

## [1.0] – 2025-06-22

- Initial version.
- Basic redirect from 404 pages to closest matching `post_name` via partial match.
- Matching limited to `post` and `page` post types.
- Uses `wp_redirect()` with status `301`.

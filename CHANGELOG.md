# Changelog

All notable changes to the 404 Redirect Matcher Pro plugin will be documented here.

---

## [1.3c] – 2025-06-25
### 🔐 Security & Multisite Compatibility
- Restricted all settings and admin pages to users with `manage_options` capability (admins only).
- Added full multisite support:
  - Detects network-wide plugin activation.
  - Uses `get_site_option()` / `update_site_option()` when activated network-wide.
  - Compatible with per-site and global settings storage.
- Abstracted all settings access via `r4mp_get_setting()` and `r4mp_save_setting()` helpers.
- Improved broken image fallback logic for same-origin image replacement.

---

## [1.3b] – 2025-06-25
### 🔁 Redirect Loop Prevention
- Prevents redirecting to the current URL to avoid infinite loops.

### ⚡ Performance Optimization
- Limits fragment scanning to the last 3 segments of the URL path.
- Reduces unnecessary database queries.

---

## [1.3a] – 2025-06-25
### 🔐 Security & Stability
- Added input sanitization for all options via `register_setting()` sanitizers.
- Escaped all dynamic HTML output in admin pages.
- Wrapped `img.onerror` fallback to ensure it doesn’t trigger for external images.
- Guarded all plugin functions with `function_exists()` to prevent redeclare errors.

---

## [1.2] – 2025-06-24
### 🌟 Pro Feature Framework
- Added Pro-only features: custom 404 page redirection and missing image fallback.
- Introduced basic license key field (static, dummy key logic).
- Settings split into tabs: General, Missing Image, Custom 404, License.

---

## [1.0] – 2025-06-23
### 🎉 Initial Release
- Automatically redirects 404 errors to closest matching post/page based on URL slug similarity.
- Uses `similar_text()` to score matches.
- Redirects only if a match exceeds 50% similarity.
- Supports configurable post types.

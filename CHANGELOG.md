# Changelog

## [1.3] – 2025-06-25

### ✅ Improvements & Fixes
- Wrapped all `r4mp_*` functions to avoid redeclaration errors when loaded multiple times.
- Implemented redirect loop protection: stops infinite redirects by limiting redirect depth.
- Improved matching performance by reversing fragments and reducing DB lookups.
- Compatibility added for multisite: shared settings when network-activated.
- All settings pages restricted to users with `manage_options` (Admins only).
- JavaScript for image fallback now uses `json_encode()` instead of `esc_url()` to prevent URL encoding issues in JS.
- Added helper `r4mp_get_setting()` to streamline settings retrieval with multisite awareness.

### 🧪 Pro Features (Demo-Enabled)
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

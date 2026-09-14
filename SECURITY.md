# Security Policy

## Supported Versions

| Version | Security Support |
|---------|------------------|
| 1.3.0   | ✅ Active         |
| < 1.3.0 | ❌ No longer supported |

---

## Reporting a Vulnerability

Please email [dev@zhrventure.com](mailto:dev@zhrventure.com) with details.
Do not disclose security issues publicly.

---

## Key Security Measures

- Admin-only setting access (`manage_options` checked in menu + page callback).
- Full input sanitization with `register_setting()` callbacks (`absint`, whitelisted post types, `esc_url_raw`).
- Safe HTML and JS escaping using `esc_attr`, `esc_html`, `esc_url`, `wp_json_encode`.
- `wp_safe_redirect()` with same-site enforcement; open-redirect prevention.
- Fully prepared SQL (`$wpdb->prepare` + `$wpdb->esc_like`).
- Image fallback via `wp_add_inline_script()`, same-site URLs only.
- No external requests, no tracking, no bundled third-party libraries.
- Redirect loop prevention and scope limiting (3 segments, 10 results per fragment).

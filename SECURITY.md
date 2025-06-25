# Security Policy

## Supported Versions

| Version | Security Support |
|---------|------------------|
| 1.3d    | ✅ Active         |
| < 1.3d  | ❌ No longer supported |

---

## Reporting a Vulnerability

Please email [dev@zhrventure.com](mailto:dev@zhrventure.com) with details.  
Do not disclose security issues publicly.

---

## Key Security Measures

- Admin-only setting access.
- Full input sanitization with `register_setting()` callbacks.
- Safe HTML and JS escaping using `esc_attr`, `esc_html`, and `json_encode`.
- JavaScript fallback is scoped and safe.
- Redirect loop prevention and scope limiting.

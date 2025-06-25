# Security Policy

## Supported Versions

Only the latest stable release of this plugin is actively supported and maintained.

| Version | Security Support |
|---------|------------------|
| 1.3c    | ✅ Active         |
| < 1.3c  | ❌ No longer supported |

---

## Reporting a Vulnerability

If you discover a security vulnerability, please **do not open a public GitHub issue**.

Instead, contact the maintainer directly:

- 📧 Email: [dev@zhrventure.com](mailto:dev@zhrventure.com)
- 🌐 Website: [https://zhrventure.com](https://zhrventure.com)

We will respond as quickly as possible and address the issue with a patched release.

---

## Plugin Security Features

As of **version 1.3c**, the following security measures are in place:

### 🔐 Admin Access Control
- Only users with the `manage_options` capability can view or edit plugin settings.

### 🧼 Input Sanitization
- All settings fields use `register_setting()` with appropriate sanitization callbacks (`sanitize_text_field`, `esc_url`, etc).

### 🛡 Output Escaping
- All dynamic output in admin pages is properly escaped using `esc_attr()`, `esc_html()`, etc.

### 🔁 Redirect Loop Protection
- Redirects are prevented if the resolved permalink matches the current 404 request.

### 🌐 Multisite Compatibility
- Plugin detects network-wide activation and uses `get_site_option()` / `update_site_option()` accordingly.
- License and settings data can be stored globally or per-site based on activation mode.

### ⚠️ External Scripts and Resources
- Inline fallback image script only replaces images with same-origin sources.
- No third-party JavaScript is loaded.

---

## Roadmap for Security Enhancements

- [ ] Optional nonce verification for future AJAX or remote activation.
- [ ] Harden fallback script with CSP compatibility.
- [ ] Future license validation (if external) will be signed, rate-limited, and secured with HTTPS.

---

Thank you for helping keep 404 Redirect Matcher Pro secure and safe for the WordPress community!

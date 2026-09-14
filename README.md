# Redirect 404 Matcher (1.4.0)

Lightweight automatic 404 fixer with no logs or tracking — the alternative to heavy managers and homepage-dump plugins.

**How it resolves a 404 (in order):**
1. Manual overrides (`/old-path /new-path`, max 50, exact match)
2. Renamed posts (old slug auto-tracked → 301, max 200)
3. Removed content (trashed slug → 410 Gone, opt-out available)
4. Smart match (similar-text + typo score ≥55 → 301)
5. Custom 404 fallback (same-site 302) or native 404

**Free features in WordPress.org version:**
- Smart slug + typo matching (301, same-site only)
- Renamed-post auto-redirects + 410 handling
- Manual overrides + custom fallback 404 URL
- Default image replacement for missing media (same-site only)
- No license key, no logs, no tracking required

---

## 🚀 Features

- Redirect 404s to the closest matching content by URL similarity + typo tolerance (301).
- Renamed-post tracking and 410 Gone for trashed content (SEO-correct, no soft-404s).
- Manual overrides for migrations without building a full rule table.
- Excludes admin / feeds / sitemaps / wp-json / probes — scanners never get redirected.
- Configurable post types (e.g. post, page, custom types).
- Custom fallback 404 page (302, same-site only).
- Default image URL replacement for broken `<img>` links (same-site only).
- Settings under **Settings → 404 Redirect Matcher**, accessible **only to admins** (`manage_options`).
- Per-site settings, compatible with **multisite environments**.
- No external requests, no tracking, no bundled third-party libraries.
- Built with security best practices.

---

## 🛠 Installation

1. Upload the plugin to your WordPress `/wp-content/plugins/` directory.
2. Activate the plugin through the “Plugins” menu in WordPress.
3. Go to **Settings → 404 Redirect Matcher** to configure.

---

## 🔧 Usage

- Enable redirect matching.
- Select the post types to match against.
- Optionally enter a same-site fallback 404 page URL and default image URL.

---

## 🧪 Multisite Support

Each site stores its own configuration via standard options. Network activation is supported; settings remain per-site so the Settings API save/load always matches.

---

## 🔐 Security

This plugin uses:

- `current_user_can('manage_options')` to restrict settings to administrators (menu + page callback).
- Proper sanitization of all inputs via `register_setting()` (`absint`, whitelisted `sanitize_key` post types, `esc_url_raw`).
- Output escaping (`esc_attr`, `esc_html`, `esc_url`) and `wp_json_encode` for inline JS.
- `wp_safe_redirect()` + same-site checks to prevent open redirects.
- Fully prepared SQL with `$wpdb->prepare()` + `$wpdb->esc_like()`.
- Redirect loop prevention.
- Limited redirect match scope (3 path segments max, 10 results per fragment).
- Enqueued inline script via `wp_add_inline_script()` instead of raw `echo`.

📄 See [SECURITY.md](./SECURITY.md) for full details.

---

## 👨‍💻 Author

Developed by **ZHR Venture**
🔗 [https://zhrventure.com/404-redirect-matcher/](https://zhrventure.com/404-redirect-matcher/)

---

## 📬 Feedback

We’d love to hear your thoughts!

💡 Have ideas or issues?
📧 Email: [dev@zhrventure.com](mailto:dev@zhrventure.com)

Appreciate any feedback or ideas for improvements!

---

## 📄 License

GPL v2.0 or later
See [`LICENSE`](./LICENSE) for full terms.

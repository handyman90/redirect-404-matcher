# 404 Redirect Matcher Pro

Smart 404 handling for WordPress — automatically redirects visitors from broken URLs to the closest matching post or page.

**Pro features include:**
- Custom fallback 404 URL
- Default image replacement for missing media
- License-ready structure (currently dummy)

---

## 🚀 Features

- Redirect 404s to the closest matching content by URL similarity.
- Configurable post types (e.g. post, page, custom types).
- Custom fallback 404 page (Pro).
- Default image URL replacement for broken `<img>` links (Pro).
- Settings accessible **only to site admins**.
- Compatible with **multisite environments**.
- Built with security best practices.

---

## 🛠 Installation

1. Upload the plugin to your WordPress `/wp-content/plugins/` directory.
2. Activate the plugin through the “Plugins” menu in WordPress.
3. Go to **Admin → 404 Redirect Matcher** to configure.

---

## 🔧 Usage

- Enable redirect matching.
- Select the post types to match against.
- (Optional) Enter a fallback 404 page URL or image replacement (Pro).

---

## 🧪 Multisite Support

If the plugin is **network-activated**, settings are shared across all sites using WordPress site options.  
Otherwise, settings are stored per-site.

---

## 🔐 Security

This plugin uses:

- `current_user_can()` to restrict settings to administrators.
- Proper sanitization of all inputs via `register_setting()`.
- Output escaping of all settings content.
- Redirect loop prevention.
- Limited redirect match scope (3 path segments max, 10 results per fragment).

📄 See [SECURITY.md](./SECURITY.md) for full details.

---

## 👨‍💻 Author

Developed by **Handyman**  
🔗 [https://zhrventure.com](https://zhrventure.com)

---

## 📬 Feedback

We’d love to hear your thoughts!

💡 Have ideas or issues?  
📧 Email: [dev@zhrventure.com](mailto:dev@zhrventure.com)

---

## 📄 License

GPL v2.0 or later  
See [`LICENSE`](./LICENSE) for full terms.


# 🔁 404 Redirect Matcher Pro

Automatically redirect 404 errors to the closest matching post or page using smart slug similarity.
Pro features include broken image fallback, custom 404 redirection, and license key activation.

---

## ✨ Features

### ✅ Free Features

* 🔄 Redirects 404s to the most similar post/page by slug.
* 🧠 Smart partial matching using `similar_text()`.
* 📂 Match across selected post types (posts, pages, CPTs).
* ⚙️ Admin settings panel in WordPress dashboard.

### 🔐 Pro Features (Requires License Key)

* 🖼️ Replace broken image URLs with a default fallback.
* 🚧 Custom 404 redirect when no match is found.
* 🔑 License key activation panel.

---

## 📦 Installation

1. Download or clone the repository:

   ```bash
   git clone https://github.com/your-username/redirect-404-matcher.git
   ```

2. Upload the plugin folder to your WordPress plugins directory:

   ```
   /wp-content/plugins/redirect-404-matcher/
   ```

3. Activate the plugin from **Plugins > Installed Plugins** in your WordPress admin.

4. Go to **Settings > 404 Redirect Matcher** to configure.

---

## ⚙️ How It Works

* Intercepts all 404 requests using `template_redirect`.
* Extracts last part(s) of the URL and compares them to existing post slugs.
* Uses `similar_text()` to find the best match.
* Redirects to the closest post/page using a 301 redirect.
* If no match exceeds threshold:

  * \[Free] Do nothing (default 404).
  * \[Pro] Redirect to custom 404 page (302).
* Replaces broken images with a default fallback (Pro only).

---

## 🔑 Demo License Key

Use this key to unlock Pro features for testing or development:

```
12345678
```

You can modify the license logic inside `r4mp_is_pro_active()` in `settings.php`.

---

## 📁 File Structure

```plaintext
redirect-404-matcher/
├── redirect-404-matcher.php   # Main plugin logic
├── settings.php               # Admin UI and Pro settings
├── README.md                  # This file
```

---

## 🧠 Developer Notes

* Built with plain PHP and WordPress APIs (no frameworks).
* Fully compatible with WordPress 5.x – 6.x+.
* Easily extensible: license server, logging, matching algorithm, etc.
* No JavaScript dependencies (only optional JS for image fallback).

---

## 👨‍👷️ Author

**Handyman**
🌐 Website: [https://zhrventure.com](https://zhrventure.com)

---

## 📄 License

This plugin is licensed under the **GNU General Public License v2.0 or later**.

> You can redistribute it and/or modify it under the terms of the GPL as published by the Free Software Foundation.
> See the full license: [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

# Changelog

All notable changes to this project will be documented in this file.

---

## [1.3a] – 2025-06-25
### 🔐 Security & Stability
- ✅ **Sanitized all saved settings** using `register_setting()` callbacks.
- ✅ **Validated all redirect URLs** to prevent open redirect vulnerabilities.
- ✅ **Image fallback JS** now restricts fallback only to same-origin URLs.
- ✅ All functions wrapped with `function_exists()` to avoid redeclaration errors.
- 🔼 Version updated from 1.3 to 1.3a for patch-level distinction.

---

## [1.3] – Not officially released
### 🛠 Internal enhancements
- Pro feature framework integrated (custom 404 and image fallback).
- License key check stub (`12345678`) added for future upgrade handling.

---

## [1.2] – Initial "Pro" Label
### 🚀 Feature Additions
- Smart redirect engine: redirects 404s to closest matching post slug.
- Settings panel in WP admin with enable toggle and post type selector.
- Pro-only custom 404 redirect URL setting.
- Pro-only fallback image for missing 404 images.
- Basic license key field added (manual matching only).

---

## [1.0] – Initial Release
### ✨ Base Functionality
- Detects 404 requests and attempts to find the best-matching post slug.
- Automatically redirects if a suitable match is found (similarity threshold).

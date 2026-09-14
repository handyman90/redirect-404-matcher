=== Redirect 404 Matcher ===
Contributors: handyman90
Donate link: https://zhrventure.com/404-redirect-matcher/
Tags: 404, redirect, broken link, seo, slug
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight automatic 404 fixer with no logs or tracking. Smart slug matching, renamed-post handling, 410 for removed content, manual overrides.

== Description ==

Redirect 404 Matcher is the lightweight alternative to heavy redirect managers. No 404 logs to triage, no IP addresses stored, no cron jobs, no external requests — broken URLs just find the right post by themselves.

How a 404 is resolved (in order):

1. **Manual overrides** — exact path match from your list (max 50, same-site only).
2. **Renamed posts** — old slug auto-tracked on rename, 301 to the new permalink (max 200).
3. **Removed content** — trashed post slug returns proper 410 Gone, not a soft-404 to the homepage.
4. **Smart match** — last 3 URL segments compared against published slugs with combined similar-text + typo (Levenshtein) scoring, 301 on confident match.
5. **Custom 404 fallback** — optional same-site URL with 302, otherwise the theme 404 stays.

Also included: same-site broken-image fallback, per-site settings for multisite, exclusions for admin / feeds / sitemaps / API / security probes so monitoring tools and scanners never get redirected.

Unlike plugins that dump every 404 to the homepage (bad for SEO — Google treats it as a soft 404), this redirects to the correct destination or returns a correct 410.

Unlike full redirect managers with log queues, dashboards and email digests, there is nothing to babysit here. One settings screen, four options plus overrides.

All functionality is free with no license key required.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install via Plugins > Add New > Upload Plugin.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to Settings > 404 Redirect Matcher to configure.
4. Enable matching, pick post types, optionally set manual overrides (`/old-path /new-path` one per line), custom 404 URL and default image URL.

== Frequently Asked Questions ==

= How does matching work? =

Slugs are normalized (lowercase, retired .html/.php/.asp stripped) and scored with 60% similar-text + 40% typo similarity. Best score across the last 3 path segments wins if >= 55. Redirect loops are prevented and only same-site destinations are used.

= What about renamed posts? =

On `post_updated`, the old slug is mapped to the new permalink automatically. No manual rule needed.

= What about deleted posts? =

If enabled, a request matching a trashed post slug returns 410 Gone via `wp_die(..., 410)` instead of a redirect. Uncheck "Return 410" to disable.

= Does it redirect admin / feeds / sitemaps? =

No. Admin, wp-json, feeds, sitemaps, robots, favicon and `.php/.env`-style probes are excluded.

= Does it contact external servers or store visitor data? =

No. No logs, no IP/UA storage, no tracking. Image and 404 fallbacks are restricted to same-site URLs only.

= Does it work with multisite? =

Yes. Each site stores its own settings and slug maps.

== Screenshots ==

1. Settings screen under Settings > 404 Redirect Matcher.

== Changelog ==

= 1.4.0 =
* Differentiator release: priority pipeline manual > renamed-post map > 410 > smart match > custom fallback.
* Added slug-change auto-tracking (max 200) and trashed-post 410 handling with opt-out.
* Added manual overrides textarea (max 50, same-site only, exact match wins).
* Improved matching: slug normalization (.html/.php stripping) + similar_text/Levenshtein combined score, threshold 55.
* Added exclusions for admin, feeds, sitemaps, wp-json, robots, probes.
* Bumped to 1.4.0.

= 1.3.0 =
* WordPress.org submission compliance release.
* Removed license-key gating; all features free in directory version.
* Moved admin menu from top-level to Settings > 404 Redirect Matcher.
* Fixed version numbering to 1.3.0.
* Added readme.txt, uninstall cleanup, textdomain loading.
* Hardened queries: whitelisted post types, fully prepared SQL.
* Image fallback now uses wp_enqueue_scripts + wp_add_inline_script, same-site URLs only.
* Added capability checks inside settings page, full i18n escaping.

= 1.2 =
* Added settings UI with enable toggle and post type selection.
* Added custom 404 fallback and image replacement.

= 1.1 =
* Improved matching logic using similarity scoring, 301/302 handling.

= 1.0 =
* Initial version with basic 404 to closest post_name redirect.

== Upgrade Notice ==

= 1.4.0 =
New: manual overrides, renamed-post auto-map, 410 for removed content, smarter typo matching, request exclusions. Existing options preserved; new options are r4mp_enable_410, r4mp_manual_redirects, r4mp_slug_map, r4mp_gone_slugs.

== Privacy ==

This plugin does not collect, store, or transmit any user data and makes no external requests. No 404 logs, no IP addresses, no cookies.

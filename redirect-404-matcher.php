<?php
/**
 * Plugin Name: Redirect 404 Matcher
 * Plugin URI: https://zhrventure.com/404-redirect-matcher/
 * Description: Lightweight automatic 404 fixer with no logs or tracking. Redirects broken URLs to the best matching post or page, auto-handles renamed posts, returns 410 for removed content, plus optional manual overrides, custom 404 fallback and broken image fallback.
 * Version: 1.4.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: ZHR Venture
 * Author URI: https://zhrventure.com/404-redirect-matcher/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: redirect-404-matcher
 */

if (!defined('ABSPATH')) exit;

if (!defined('R4MP_VERSION')) {
    define('R4MP_VERSION', '1.4.0');
}
if (!defined('R4MP_BASENAME')) {
    define('R4MP_BASENAME', plugin_basename(__FILE__));
}

if (!function_exists('r4mp_redirect_partial_404_match')) {
    require_once plugin_dir_path(__FILE__) . 'settings.php';

    add_action('plugins_loaded', 'r4mp_load_textdomain');
    add_action('template_redirect', 'r4mp_redirect_partial_404_match');
    add_action('post_updated', 'r4mp_track_slug_change', 10, 3);
    add_action('transition_post_status', 'r4mp_track_gone_slug', 10, 3);

    function r4mp_load_textdomain() {
        load_plugin_textdomain('redirect-404-matcher', false, dirname(R4MP_BASENAME) . '/languages');
    }

    function r4mp_redirect_partial_404_match() {
        if (!is_404() || !r4mp_get_setting('r4mp_enabled', true)) return;

        global $wp;
        $req = isset($wp->request) && is_string($wp->request) ? $wp->request : '';
        $requested_path = trim($req, '/');
        $current_url = home_url($req);
        if (empty($requested_path)) return;

        // Never hijack admin, feeds, embeds, sitemaps, API or probe requests.
        if (r4mp_should_skip($requested_path)) return;

        $home = home_url();

        // 1) Manual overrides (exact path match, max 50 rules, same-site only).
        $manual = r4mp_get_manual_rules();
        $lookup = strtolower($requested_path);
        if (isset($manual[$lookup])) {
            $dest = $manual[$lookup];
            if (!empty($dest) && strpos($dest, $home) === 0 && $dest !== $current_url) {
                wp_safe_redirect($dest, 301);
                exit;
            }
        }

        $parts = array_slice(array_reverse(explode('/', $requested_path)), 0, 3);

        // 2) Renamed-post map (old slug -> new URL, exact slug match).
        $slug_map = get_option('r4mp_slug_map', array());
        if (is_array($slug_map) && !empty($slug_map)) {
            foreach ($parts as $fragment) {
                $norm = r4mp_normalize_slug($fragment);
                if ($norm !== '' && isset($slug_map[$norm])) {
                    $dest = esc_url_raw($slug_map[$norm]);
                    if (!empty($dest) && strpos($dest, $home) === 0 && $dest !== $current_url) {
                        wp_safe_redirect($dest, 301);
                        exit;
                    }
                }
            }
        }

        // 3) Removed content -> proper 410 Gone (SEO-correct, avoids soft-404s).
        if ((int) r4mp_get_setting('r4mp_enable_410', 1)) {
            $gone = get_option('r4mp_gone_slugs', array());
            if (is_array($gone) && !empty($gone)) {
                foreach ($parts as $fragment) {
                    $norm = r4mp_normalize_slug($fragment);
                    if ($norm !== '' && isset($gone[$norm])) {
                        status_header(410);
                        nocache_headers();
                        wp_die(
                            esc_html__('This content has been removed.', 'redirect-404-matcher'),
                            esc_html__('Gone', 'redirect-404-matcher'),
                            array('response' => 410)
                        );
                    }
                }
            }
        }

        // 4) Fuzzy match: similar_text + levenshtein combined score.
        $best_match = null;
        $best_score = 0;
        $threshold = 55;

        foreach ($parts as $fragment) {
            $matches = r4mp_get_potential_matches($fragment);
            $norm_frag = r4mp_normalize_slug($fragment);
            if ($norm_frag === '') continue;
            foreach ($matches as $match) {
                $score = r4mp_match_score($norm_frag, r4mp_normalize_slug($match->post_name));
                if ($score > $best_score) {
                    $best_score = $score;
                    $best_match = $match;
                }
            }
        }

        if ($best_score >= $threshold && $best_match) {
            $url = get_permalink($best_match);
            if ($url && strpos($url, $home) === 0 && $url !== $current_url) {
                wp_safe_redirect($url, 301);
                exit;
            }
        }

        // 5) Fallback to user-configured custom 404 URL (same-site only).
        $custom_404 = r4mp_get_setting('r4mp_custom_404_url', '');
        if (!empty($custom_404)) {
            $custom_404 = esc_url_raw($custom_404);
            if (!empty($custom_404) && strpos($custom_404, $home) === 0 && $custom_404 !== $current_url) {
                wp_safe_redirect($custom_404, 302);
                exit;
            }
        }
    }

    function r4mp_should_skip($path) {
        if (function_exists('is_admin') && is_admin()) {
            return true;
        }
        if (function_exists('is_feed') && is_feed()) {
            return true;
        }
        if (function_exists('is_embed') && is_embed()) {
            return true;
        }
        if (function_exists('is_robots') && is_robots()) {
            return true;
        }
        if (function_exists('is_trackback') && is_trackback()) {
            return true;
        }
        $lower = strtolower($path);
        // API, sitemaps, feeds.
        foreach (array('wp-json', 'wp-admin', 'xmlrpc.php', 'sitemap', '.xml', 'feed', 'robots.txt', 'favicon.ico') as $needle) {
            if (strpos($lower, $needle) !== false) {
                return true;
            }
        }
        // Security-probe / file extensions we never redirect (prevents log spam and loops).
        if (preg_match('/\.(php|env|ini|cfg|log|sql|bak|git|svn)$/', $lower)) {
            return true;
        }
        return false;
    }

    function r4mp_normalize_slug($slug) {
        $slug = strtolower(trim($slug));
        // Strip common retired extensions: /page.html, /page.php, /page.asp
        $slug = preg_replace('/\.(html?|php|aspx?)$/', '', $slug);
        $slug = sanitize_title($slug);
        return is_string($slug) ? substr($slug, 0, 200) : '';
    }

    function r4mp_match_score($a, $b) {
        if ($a === '' || $b === '') return 0;
        if ($a === $b) return 100;
        similar_text($a, $b, $percent);
        $a_t = substr($a, 0, 255);
        $b_t = substr($b, 0, 255);
        $dist = function_exists('levenshtein') ? levenshtein($a_t, $b_t) : 999;
        $max = max(strlen($a_t), strlen($b_t), 1);
        $lev_score = max(0, (1 - ($dist / $max)) * 100);
        // 60% similar_text (substring-friendly) + 40% levenshtein (typo-friendly).
        return ($percent * 0.6) + ($lev_score * 0.4);
    }

    function r4mp_get_potential_matches($slug_fragment) {
        global $wpdb;
        $slug_fragment = r4mp_normalize_slug($slug_fragment);
        if ($slug_fragment === '') {
            return array();
        }

        $saved = r4mp_get_setting('r4mp_post_types', array('post', 'page'));
        if (!is_array($saved)) {
            $saved = array('post', 'page');
        }

        // Whitelist against registered public post types to avoid stale/injected values.
        $allowed = get_post_types(array('public' => true));
        $post_types = array_values(array_intersect(array_map('sanitize_key', $saved), $allowed));
        if (empty($post_types)) {
            $post_types = array('post', 'page');
        }

        $placeholders = implode(',', array_fill(0, count($post_types), '%s'));

        // Short prefix keeps LIKE selective for typo fragments (first 10 chars).
        $like = '%' . $wpdb->esc_like(substr($slug_fragment, 0, 20)) . '%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- read-only lookup with prepared query.
        return $wpdb->get_results(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- placeholders built above, all values prepared.
                "SELECT ID, post_name FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ($placeholders) AND post_name LIKE %s LIMIT 15",
                array_merge($post_types, array($like))
            )
        );
    }

    function r4mp_track_slug_change($post_id, $post_after, $post_before) {
        if (!isset($post_after->post_name, $post_before->post_name)) return;
        if ($post_after->post_name === $post_before->post_name) return;
        if ($post_after->post_status !== 'publish') return;
        if (!in_array($post_after->post_type, get_post_types(array('public' => true)), true)) return;

        $old = r4mp_normalize_slug($post_before->post_name);
        if ($old === '') return;
        $new_url = get_permalink($post_id);
        if (!$new_url || strpos($new_url, home_url()) !== 0) return;

        $map = get_option('r4mp_slug_map', array());
        if (!is_array($map)) $map = array();
        // Remove any stale entry pointing elsewhere for same slug, then cap at 200.
        $map[$old] = esc_url_raw($new_url);
        if (count($map) > 200) {
            $map = array_slice($map, -200, 200, true);
        }
        update_option('r4mp_slug_map', $map);

        // Renamed back to life: no longer gone.
        $gone = get_option('r4mp_gone_slugs', array());
        if (is_array($gone) && isset($gone[$old])) {
            unset($gone[$old]);
            update_option('r4mp_gone_slugs', $gone);
        }
    }

    function r4mp_track_gone_slug($new_status, $old_status, $post) {
        if (!isset($post->post_name, $post->post_type)) return;
        if (!in_array($post->post_type, get_post_types(array('public' => true)), true)) return;
        $slug = r4mp_normalize_slug($post->post_name);
        if ($slug === '') return;

        if (($new_status === 'trash' && $old_status === 'publish') || $new_status === 'trash') {
            $gone = get_option('r4mp_gone_slugs', array());
            if (!is_array($gone)) $gone = array();
            $gone[$slug] = time();
            if (count($gone) > 200) {
                $gone = array_slice($gone, -200, 200, true);
            }
            update_option('r4mp_gone_slugs', $gone);
        } elseif ($new_status === 'publish' && $old_status !== 'publish') {
            $gone = get_option('r4mp_gone_slugs', array());
            if (is_array($gone) && isset($gone[$slug])) {
                unset($gone[$slug]);
                update_option('r4mp_gone_slugs', $gone);
            }
        }
    }

    function r4mp_get_manual_rules() {
        $raw = r4mp_get_setting('r4mp_manual_redirects', '');
        if (!is_string($raw) || trim($raw) === '') {
            return array();
        }
        $rules = array();
        $home = home_url();
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        foreach (array_slice($lines, 0, 50) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            // Accepted: "/old-path /new-path" or "/old-path > /new-path".
            $line = str_replace('>', ' ', $line);
            $bits = preg_split('/\s+/', $line, 2);
            if (count($bits) !== 2) continue;
            $from = strtolower(trim(trim($bits[0]), '/'));
            $to = trim($bits[1]);
            if ($from === '' || $to === '') continue;
            // Destination must be same-site (relative path or absolute home URL).
            if (strpos($to, '/') === 0) {
                $to = home_url($to);
            }
            $to = esc_url_raw($to);
            if (empty($to) || strpos($to, $home) !== 0) continue;
            $rules[sanitize_title($from)] = $to;
        }
        return $rules;
    }
}

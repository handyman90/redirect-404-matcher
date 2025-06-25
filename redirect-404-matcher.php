<?php
/**
 * Plugin Name: 404 Redirect Matcher Pro
 * Plugin URI: https://zhrventure.com
 * Description: Automatically redirect 404 errors to the best matching post or page using smart slug matching. Pro version includes broken image fallback, custom 404 redirect, and license key activation.
 * Version: 1.3d
 * Author: Handyman
 * Author URI: https://zhrventure.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: redirect-404-matcher
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

if (!function_exists('r4mp_redirect_partial_404_match')) {
    require_once plugin_dir_path(__FILE__) . 'settings.php';

    add_action('template_redirect', 'r4mp_redirect_partial_404_match');

    function r4mp_redirect_partial_404_match() {
        if (!is_404() || !r4mp_get_setting('r4mp_enabled', true)) return;

        global $wp;
        $requested_path = trim($wp->request, '/');
        $current_url = home_url($wp->request);
        if (empty($requested_path)) return;

        $parts = array_slice(array_reverse(explode('/', $requested_path)), 0, 3); // Limit to 3 parts

        $best_match = null;
        $best_score = 0;
        $threshold = 50;

        foreach ($parts as $fragment) {
            $matches = r4mp_get_potential_matches($fragment);
            foreach ($matches as $match) {
                similar_text($fragment, $match->post_name, $percent);
                if ($percent > $best_score) {
                    $best_score = $percent;
                    $best_match = $match;
                }
            }
        }

        if ($best_score >= $threshold && $best_match) {
            $url = get_permalink($best_match);
            if ($url && strpos($url, home_url()) === 0 && $url !== $current_url) {
                wp_redirect($url, 301);
                exit;
            }
        }

        if (function_exists('r4mp_is_pro_active') && r4mp_is_pro_active()) {
            $custom_404 = esc_url(r4mp_get_setting('r4mp_custom_404_url', ''));
            if (!empty($custom_404) && strpos($custom_404, home_url()) === 0 && $custom_404 !== $current_url) {
                wp_redirect($custom_404, 302);
                exit;
            }
        }
    }

    function r4mp_get_potential_matches($slug_fragment) {
        global $wpdb;
        $slug_fragment = sanitize_title($slug_fragment);
        $post_types = r4mp_get_setting('r4mp_post_types', ['post', 'page']);
        $in_types = implode("','", array_map('esc_sql', $post_types));

        $query = "
            SELECT ID, post_name FROM $wpdb->posts
            WHERE post_status = 'publish'
              AND post_type IN ('$in_types')
              AND post_name LIKE %s
            LIMIT 10
        ";

        $like = '%' . $wpdb->esc_like($slug_fragment) . '%';
        return $wpdb->get_results($wpdb->prepare($query, $like));
    }
}

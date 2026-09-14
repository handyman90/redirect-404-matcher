<?php
if (!defined('ABSPATH')) exit;

// =====================
// Admin Menu Registration (under Settings, not top-level)
// =====================
add_action('admin_menu', 'r4mp_register_admin_menu');
function r4mp_register_admin_menu() {
    add_options_page(
        esc_html__('Redirect 404 Matcher Settings', 'redirect-404-matcher'),
        esc_html__('404 Redirect Matcher', 'redirect-404-matcher'),
        'manage_options',
        'r4mp-settings',
        'r4mp_settings_page'
    );
}

// =====================
// Admin assets: media library picker (settings page only)
// =====================
add_action('admin_enqueue_scripts', 'r4mp_admin_assets');
if (!function_exists('r4mp_admin_assets')) {
function r4mp_admin_assets($hook) {
    if ($hook !== 'settings_page_r4mp-settings') {
        return;
    }
    if (!defined('R4MP_VERSION')) {
        return;
    }
    wp_enqueue_media();
    wp_register_script('r4mp-admin-media', '', array('jquery'), R4MP_VERSION, true);
    wp_enqueue_script('r4mp-admin-media');
    $title = esc_js(__('Select fallback image', 'redirect-404-matcher'));
    $use = esc_js(__('Use this image', 'redirect-404-matcher'));
    $js = 'jQuery(function($){var frame;$("#r4mp-select-image").on("click",function(e){e.preventDefault();if(frame){frame.open();return;}frame=wp.media({title:"' . $title . '",button:{text:"' . $use . '"},library:{type:"image"},multiple:false});frame.on("select",function(){var att=frame.state().get("selection").first().toJSON();if(att&&att.url){$("#r4mp_default_image_url").val(att.url);$("#r4mp-image-preview").attr("src",att.url).show();}});frame.open();});$("#r4mp-clear-image").on("click",function(e){e.preventDefault();$("#r4mp_default_image_url").val("");$("#r4mp-image-preview").hide();});});';
    wp_add_inline_script('r4mp-admin-media', $js);
}
}

// =====================
// Settings Registration
// =====================
add_action('admin_init', 'r4mp_register_settings');
function r4mp_register_settings() {
    register_setting('r4mp_settings_group', 'r4mp_enabled', array('sanitize_callback' => 'absint', 'default' => 1));
    register_setting('r4mp_settings_group', 'r4mp_post_types', array('sanitize_callback' => 'r4mp_sanitize_post_types', 'default' => array('post', 'page')));
    register_setting('r4mp_settings_group', 'r4mp_default_image_url', array('sanitize_callback' => 'esc_url_raw', 'default' => ''));
    register_setting('r4mp_settings_group', 'r4mp_custom_404_url', array('sanitize_callback' => 'esc_url_raw', 'default' => ''));
    register_setting('r4mp_settings_group', 'r4mp_enable_410', array('sanitize_callback' => 'absint', 'default' => 1));
    register_setting('r4mp_settings_group', 'r4mp_manual_redirects', array('sanitize_callback' => 'r4mp_sanitize_manual_redirects', 'default' => ''));
}

function r4mp_sanitize_array($input) {
    return is_array($input) ? array_map('sanitize_text_field', $input) : array();
}

function r4mp_sanitize_post_types($input) {
    if (!is_array($input)) {
        return array('post', 'page');
    }
    $allowed = get_post_types(array('public' => true));
    $clean = array_values(array_intersect(array_map('sanitize_key', $input), $allowed));
    return !empty($clean) ? $clean : array('post', 'page');
}

function r4mp_sanitize_manual_redirects($input) {
    if (!is_string($input)) return '';
    // Keep max 50 lines, 5000 chars; detailed validation happens at runtime (same-site enforced).
    $lines = array_slice(preg_split('/\r\n|\r|\n/', $input), 0, 50);
    $clean = array();
    foreach ($lines as $line) {
        $line = trim(substr($line, 0, 500));
        if ($line === '') continue;
        $clean[] = sanitize_text_field($line);
    }
    return implode("\n", $clean);
}

// =====================
// Settings Page (all features free, single page)
// =====================
function r4mp_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    $slug_map = get_option('r4mp_slug_map', array());
    $auto_count = is_array($slug_map) ? count($slug_map) : 0;
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Redirect 404 Matcher Settings', 'redirect-404-matcher'); ?></h1>
        <p class="description"><?php echo esc_html__('Lightweight automatic 404 fixer. No logs, no tracking. Priority: manual overrides > renamed-post map > removed-content (410) > smart match > custom 404 fallback.', 'redirect-404-matcher'); ?></p>
        <form method="post" action="options.php">
            <?php settings_fields('r4mp_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php echo esc_html__('Enable Redirect Matching', 'redirect-404-matcher'); ?></th>
                    <td>
                        <input type="checkbox" name="r4mp_enabled" value="1" <?php checked(1, (int) r4mp_get_setting('r4mp_enabled', 1)); ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Post Types to Match', 'redirect-404-matcher'); ?></th>
                    <td>
                        <?php
                        $types = get_post_types(array('public' => true), 'objects');
                        $selected = r4mp_get_setting('r4mp_post_types', array('post', 'page'));
                        if (!is_array($selected)) {
                            $selected = array('post', 'page');
                        }
                        foreach ($types as $type) {
                            $name = esc_attr($type->name);
                            $label = esc_html($type->labels->singular_name);
                            $is_checked = in_array($type->name, $selected, true);
                            echo '<label><input type="checkbox" name="r4mp_post_types[]" value="' . $name . '" ' . checked($is_checked, true, false) . ' /> ' . $label . '</label><br />';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Return 410 for Removed Content', 'redirect-404-matcher'); ?></th>
                    <td>
                        <input type="checkbox" name="r4mp_enable_410" value="1" <?php checked(1, (int) r4mp_get_setting('r4mp_enable_410', 1)); ?> />
                        <p class="description"><?php echo esc_html__('When a trashed post slug is requested, return 410 Gone instead of redirecting. Better for SEO than redirecting everything to the homepage.', 'redirect-404-matcher'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Manual Overrides', 'redirect-404-matcher'); ?></th>
                    <td>
                        <textarea name="r4mp_manual_redirects" rows="5" cols="60" class="large-text code" placeholder="/old-shop/shoes /shop/shoes&#10;/old-page /new-page"><?php echo esc_textarea(r4mp_get_setting('r4mp_manual_redirects', '')); ?></textarea>
                        <p class="description"><?php echo esc_html__('One per line: "/old-path /new-path" (max 50). Same-site destinations only. Exact match wins over automatic matching.', 'redirect-404-matcher'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Renamed Posts', 'redirect-404-matcher'); ?></th>
                    <td>
                        <p class="description">
                            <?php
                            /* translators: %d: number of auto-tracked slug redirects */
                            printf(esc_html__('%d old slug(s) auto-tracked. Old URLs redirect to the new permalink automatically (max 200, oldest trimmed).', 'redirect-404-matcher'), (int) $auto_count);
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Custom 404 Page URL', 'redirect-404-matcher'); ?></th>
                    <td>
                        <input type="url" name="r4mp_custom_404_url" value="<?php echo esc_attr(r4mp_get_setting('r4mp_custom_404_url', '')); ?>" class="regular-text" placeholder="<?php echo esc_attr(home_url('/page-not-found/')); ?>" />
                        <p class="description"><?php echo esc_html__('Optional. Same-site URL used when no match is found. Leave empty to keep the default 404 page.', 'redirect-404-matcher'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Default Image URL', 'redirect-404-matcher'); ?></th>
                    <td>
                        <?php $img_url = r4mp_get_setting('r4mp_default_image_url', ''); ?>
                        <input type="url" id="r4mp_default_image_url" name="r4mp_default_image_url" value="<?php echo esc_attr($img_url); ?>" class="regular-text" />
                        <button type="button" class="button" id="r4mp-select-image"><?php echo esc_html__('Select from Media Library', 'redirect-404-matcher'); ?></button>
                        <button type="button" class="button-link" id="r4mp-clear-image"><?php echo esc_html__('Clear', 'redirect-404-matcher'); ?></button>
                        <br />
                        <img id="r4mp-image-preview" src="<?php echo esc_url($img_url); ?>" alt="" style="max-width:200px;height:auto;margin-top:8px;<?php echo empty($img_url) ? 'display:none;' : ''; ?>" />
                        <p class="description"><?php echo esc_html__('Optional. Pick from Media Library or paste a same-site image URL. Used as a fallback when an image fails to load.', 'redirect-404-matcher'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// =====================
// Front-end: broken image fallback via enqueued inline script
// =====================
add_action('wp_enqueue_scripts', 'r4mp_enqueue_image_fallback');
if (!function_exists('r4mp_enqueue_image_fallback')) {
function r4mp_enqueue_image_fallback() {
    if (!defined('R4MP_VERSION')) {
        return;
    }
    $default_img = r4mp_get_setting('r4mp_default_image_url', '');
    if (empty($default_img)) {
        return;
    }
    $default_img = esc_url_raw($default_img);
    if (empty($default_img)) {
        return;
    }

    // Only allow same-site fallback images (prevents third-party tracking / open image proxy).
    $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
    $img_host = wp_parse_url($default_img, PHP_URL_HOST);
    if (!empty($img_host) && $img_host !== $site_host) {
        return;
    }

    wp_register_script('r4mp-image-fallback', '', array(), R4MP_VERSION, true);
    wp_enqueue_script('r4mp-image-fallback');
    $inline = sprintf(
        'document.addEventListener("DOMContentLoaded",function(){var fallback=%s;var origin=window.location.origin;document.querySelectorAll("img").forEach(function(img){img.addEventListener("error",function(){if(!this.dataset.r4mpDefault&&(!this.currentSrc||this.currentSrc.indexOf(origin)===0)){this.dataset.r4mpDefault="true";this.src=fallback;}});});});',
        wp_json_encode(esc_url_raw($default_img))
    );
    wp_add_inline_script('r4mp-image-fallback', $inline);
}
}

// =====================
// Settings Getter (per-site; multisite-safe)
// =====================
function r4mp_get_setting($key, $default = '') {
    // Intentionally per-site so Settings API (options.php) save/load always match,
    // including when network-activated. Each site stores its own configuration.
    return get_option($key, $default);
}

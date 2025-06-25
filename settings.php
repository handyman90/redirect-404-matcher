<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function() {
    add_menu_page('404 Redirect Matcher', '404 Redirect Matcher', 'manage_options', 'r4mp-settings', 'r4mp_settings_page', 'dashicons-randomize');
    add_submenu_page('r4mp-settings', '404 Redirect Matcher Settings', 'Settings', 'manage_options', 'r4mp-settings', 'r4mp_settings_page');
    add_submenu_page('r4mp-settings', 'Missing Image Settings', 'Missing Image', 'manage_options', 'r4mp-image-settings', 'r4mp_image_settings_page');
    add_submenu_page('r4mp-settings', 'Custom 404 Page', 'Custom 404 Page', 'manage_options', 'r4mp-custom-404', 'r4mp_custom_404_page');
    add_submenu_page('r4mp-settings', 'License', 'License', 'manage_options', 'r4mp-license', 'r4mp_license_page');
});

add_action('admin_init', function() {
    register_setting('r4mp_settings_group', 'r4mp_enabled', 'absint');
    register_setting('r4mp_settings_group', 'r4mp_post_types', function($value) {
        return is_array($value) ? array_map('sanitize_text_field', $value) : [];
    });
    register_setting('r4mp_image_group', 'r4mp_default_image_url', 'esc_url_raw');
    register_setting('r4mp_404_group', 'r4mp_custom_404_url', 'esc_url_raw');
    register_setting('r4mp_license_group', 'r4mp_license_key', 'sanitize_text_field');
});

if (!function_exists('r4mp_is_pro_active')) {
    function r4mp_is_pro_active() {
        return get_option('r4mp_license_key') === '12345678';
    }
}

if (!function_exists('r4mp_settings_page')) {
    function r4mp_settings_page() {
        ?>
        <div class="wrap">
            <h1>404 Redirect Matcher Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('r4mp_settings_group'); do_settings_sections('r4mp_settings_group'); ?>
                <table class="form-table">
                    <tr><th>Enable Redirect Matching</th>
                    <td><input type="checkbox" name="r4mp_enabled" value="1" <?php checked(1, get_option('r4mp_enabled', 1)); ?> /></td></tr>
                    <tr><th>Post Types to Match</th><td>
                    <?php
                    $types = get_post_types(['public' => true], 'objects');
                    $selected = get_option('r4mp_post_types', array('post', 'page'));
                    foreach ($types as $type) {
                        echo '<label><input type="checkbox" name="r4mp_post_types[]" value="' . esc_attr($type->name) . '" ' . checked(in_array($type->name, $selected), true, false) . '> ' . esc_html($type->labels->singular_name) . '</label><br>';
                    }
                    ?>
                    </td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

if (!function_exists('r4mp_image_settings_page')) {
    function r4mp_image_settings_page() {
        if (!r4mp_is_pro_active()) {
            echo '<div class="wrap"><h2>Pro Feature</h2><p>This feature is only available in the Pro version.</p></div>'; return;
        }
        ?>
        <div class="wrap">
            <h1>Missing Image Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('r4mp_image_group'); do_settings_sections('r4mp_image_group'); ?>
                <table class="form-table">
                    <tr><th>Default Image URL</th>
                    <td><input type="text" name="r4mp_default_image_url" value="<?php echo esc_attr(get_option('r4mp_default_image_url', '')); ?>" style="width: 400px;" />
                    <p class="description">This image will be used when an image is not found (404).</p></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

if (!function_exists('r4mp_custom_404_page')) {
    function r4mp_custom_404_page() {
        if (!r4mp_is_pro_active()) {
            echo '<div class="wrap"><h2>Pro Feature</h2><p>This feature is only available in the Pro version.</p></div>'; return;
        }
        ?>
        <div class="wrap">
            <h1>Custom 404 Page Redirect</h1>
            <form method="post" action="options.php">
                <?php settings_fields('r4mp_404_group'); do_settings_sections('r4mp_404_group'); ?>
                <table class="form-table">
                    <tr><th>Custom 404 Page URL</th>
                    <td><input type="text" name="r4mp_custom_404_url" value="<?php echo esc_attr(get_option('r4mp_custom_404_url', '')); ?>" style="width: 400px;" />
                    <p class="description">Redirect to this URL when no match is found (Pro feature).</p></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

if (!function_exists('r4mp_license_page')) {
    function r4mp_license_page() {
        ?>
        <div class="wrap">
            <h1>Pro License Activation</h1>
            <form method="post" action="options.php">
                <?php settings_fields('r4mp_license_group'); do_settings_sections('r4mp_license_group'); ?>
                <table class="form-table">
                    <tr><th>License Key</th>
                    <td><input type="text" name="r4mp_license_key" value="<?php echo esc_attr(get_option('r4mp_license_key', '')); ?>" style="width: 400px;" />
                    <p class="description">Enter your Pro license key to unlock advanced features.</p></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

if (r4mp_is_pro_active()) add_action('wp_head', function() {
    $default_img = esc_url(get_option('r4mp_default_image_url', ''));
    if ($default_img) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('img').forEach(function(img) {
                    img.onerror = function() {
                        if (!this.dataset.r4mpDefault && this.src.startsWith(window.location.origin)) {
                            this.dataset.r4mpDefault = true;
                            this.src = '{$default_img}';
                        }
                    };
                });
            });
        </script>";
    }
});

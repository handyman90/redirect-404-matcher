<?php
// Exit if uninstall not called from WordPress.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options (per-site; multisite loops sites on network uninstall).
$options = array(
    'r4mp_enabled',
    'r4mp_post_types',
    'r4mp_default_image_url',
    'r4mp_custom_404_url',
    'r4mp_enable_410',
    'r4mp_manual_redirects',
    'r4mp_slug_map',
    'r4mp_gone_slugs',
    // Legacy option from pre-1.3.0 versions.
    'r4mp_license_key',
);

if (is_multisite()) {
    $site_ids = get_sites(array('fields' => 'ids', 'number' => 0));
    foreach ($site_ids as $site_id) {
        switch_to_blog($site_id);
        foreach ($options as $opt) {
            delete_option($opt);
        }
        restore_current_blog();
    }
} else {
    foreach ($options as $opt) {
        delete_option($opt);
    }
}

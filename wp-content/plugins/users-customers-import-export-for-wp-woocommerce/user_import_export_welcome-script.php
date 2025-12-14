<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

add_action('admin_init', 'wt_user_welcome');
// add_action('admin_menu', 'order_welcome_screen');
// add_action('admin_head', 'hf_subscription_welcome_screen_remove_menus');

function wt_user_activation_check() {
    set_transient('_user_welcome_screen_activation_redirect', true, 30);
}

function wt_user_welcome() {

    if (!get_transient('_user_welcome_screen_activation_redirect')) {
        return;
    }
    delete_transient('_user_welcome_screen_activation_redirect');
    wp_safe_redirect(add_query_arg(array('page' => 'wt_import_export_for_woo_basic_export'), admin_url('admin.php')));
}


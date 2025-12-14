<?php

/**
 * Plugin Name:       Qyrr-Code
 * Plugin URI:        https://patrickposner.com/plugins/qyrr
 * Description:       QR-Code generation, management and tracking as it should be.
 * Version:           2.0.7
 * Author:            Patrick Posner
 * Author URI:        https://patrickposner.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       qyrr-code
 * Domain Path:       /languages
 *
 */
define( 'QYRR_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'QYRR_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'QYRR_VERSION', '2.0.7' );
// load setup.
require_once QYRR_PATH . '/inc/freemius-setup.php';
add_action( 'plugins_loaded', 'qyrr_run_plugin' );
// run plugin.
if ( !function_exists( 'qyrr_run_plugin' ) ) {
    add_action( 'plugins_loaded', 'qyrr_run_plugin' );
    /**
     * Run plugin
     *
     * @return void
     */
    function qyrr_run_plugin() {
        require_once QYRR_PATH . '/inc/class-qyrr-block-editor.php';
        require_once QYRR_PATH . '/inc/class-qyrr-admin.php';
        require_once QYRR_PATH . '/inc/admin/inc/class-qyrr-settings.php';
        require_once QYRR_PATH . '/inc/class-qyrr-meta.php';
        require_once QYRR_PATH . '/inc/class-qyrr-rest.php';
        require_once QYRR_PATH . '/inc/class-qyrr-shortcode.php';
        qyrr\QYRR_Block_Editor::get_instance();
        qyrr\QYRR_Admin::get_instance();
        qyrr\QYRR_Settings::get_instance();
        qyrr\QYRR_Meta::get_instance();
        qyrr\QYRR_Rest::get_instance();
        qyrr\QYRR_Shortcode::get_instance();
    }

    // Register block.
    add_action( 'init', 'qyrr_register_qyrr_block' );
    function qyrr_register_qyrr_block() {
        if ( qyrr_is_post_type() ) {
            register_block_type( __DIR__ . '/build/qr' );
        }
    }

    add_action( 'wp_enqueue_scripts', 'qyrr_register_scripts' );
    function qyrr_register_scripts() {
        $qr_asset_file = (include plugin_dir_path( __FILE__ ) . 'build/qr/index.asset.php');
        wp_register_script(
            'qyrr-code-script',
            plugins_url( 'build/qr/index.js', __FILE__ ),
            $qr_asset_file['dependencies'],
            $qr_asset_file['version']
        );
    }

    // Register Block styles and scripts.
    add_action( 'enqueue_block_editor_assets', 'qyrr_add_block_editor_assets' );
    function qyrr_add_block_editor_assets() {
        if ( qyrr_is_post_type() ) {
            $qr_asset_file = (include plugin_dir_path( __FILE__ ) . 'build/qr/index.asset.php');
            $qyrr_options = get_option( 'qyrr' );
            $script_args = array(
                'is_pro' => false,
            );
            if ( isset( $qyrr_options['fonts_api_key'] ) ) {
                $script_args['google_fonts_api_key'] = $qyrr_options['fonts_api_key'];
            }
            // Ad home URL to args.
            $script_args['home_url'] = home_url();
            // Handle rerender on update.
            if ( qyrr_is_post_type() ) {
                wp_enqueue_script(
                    'qyrr-code-rerender',
                    QYRR_URL . '/assets/rerender.js',
                    array(
                        'wp-api',
                        'wp-components',
                        'wp-element',
                        'wp-api-fetch',
                        'wp-data',
                        'wp-i18n'
                    ),
                    QYRR_VERSION,
                    true
                );
            }
            wp_enqueue_script(
                'qyrr-code-script',
                plugins_url( 'build/qr/index.js', __FILE__ ),
                $qr_asset_file['dependencies'],
                $qr_asset_file['version']
            );
            wp_localize_script( 'qyrr-code-script', 'license', $script_args );
            wp_enqueue_style( 'qyrr-code-style', plugins_url( 'build/qr/index.css', __FILE__ ) );
        }
        $selector_asset_file = (include plugin_dir_path( __FILE__ ) . 'build/qr-selector/index.asset.php');
        $uploads_dir = wp_upload_dir();
        wp_enqueue_script(
            'qyrr-code-selector-script',
            plugins_url( 'build/qr-selector/index.js', __FILE__ ),
            $selector_asset_file['dependencies'],
            $selector_asset_file['version']
        );
        wp_localize_script( 'qyrr-code-selector-script', 'qyrr_options', array(
            'baseurl' => $uploads_dir['baseurl'],
        ) );
        wp_enqueue_style( 'qyrr-code-selector-style', plugins_url( 'build/qr-selector/index.css', __FILE__ ) );
        // Make the blocks translatable.
        if ( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'qyrr-code-script', 'qyrr-code', plugin_dir_path( __FILE__ ) . 'languages' );
            wp_set_script_translations( 'qyrr-code-selector-script', 'qyrr-code', plugin_dir_path( __FILE__ ) . 'languages' );
        }
    }

    register_activation_hook( __FILE__, 'qyrr_activate' );
    /**
     * Add a flag that will allow to flush the rewrite rules when needed.
     */
    function qyrr_activate() {
        if ( !get_option( 'qyrr_activated' ) ) {
            add_option( 'qyrr_activated', true );
        }
    }

    add_action(
        'upgrader_process_complete',
        'qyrr_upgrade',
        10,
        2
    );
    /**
     * Add a flag that will allow to flush the rewrite rules when needed.
     * @throws Exception
     */
    function qyrr_upgrade(  $upgrader_object, $options  ) {
        $current_plugin_path_name = plugin_basename( __FILE__ );
        if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
            foreach ( $options['plugins'] as $each_plugin ) {
                if ( $each_plugin == $current_plugin_path_name ) {
                    if ( !get_option( 'qyrr_activated' ) ) {
                        add_option( 'qyrr_activated', true );
                    }
                }
            }
        }
    }

    function qyrr_is_post_type() {
        // Check if this is the intended custom post type
        if ( is_admin() ) {
            global $pagenow;
            $typenow = '';
            if ( 'post-new.php' === $pagenow ) {
                if ( isset( $_REQUEST['post_type'] ) && post_type_exists( $_REQUEST['post_type'] ) ) {
                    $typenow = $_REQUEST['post_type'];
                }
            } elseif ( 'post.php' === $pagenow ) {
                if ( isset( $_GET['post'] ) && isset( $_POST['post_ID'] ) && (int) $_GET['post'] !== (int) $_POST['post_ID'] ) {
                    // Do nothing
                } elseif ( isset( $_GET['post'] ) ) {
                    $post_id = (int) $_GET['post'];
                } elseif ( isset( $_POST['post_ID'] ) ) {
                    $post_id = (int) $_POST['post_ID'];
                }
                if ( $post_id ) {
                    $post = get_post( $post_id );
                    $typenow = $post->post_type;
                }
            }
            if ( $typenow === 'qr' ) {
                return true;
            }
        }
        return false;
    }

}
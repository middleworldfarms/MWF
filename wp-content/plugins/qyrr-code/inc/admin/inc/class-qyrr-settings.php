<?php

namespace qyrr;

class Qyrr_Settings {
    /**
     * Contains instance or null
     *
     * @var object|null
     */
    private static $instance = null;

    /**
     * Returns instance of Qyrr_Settings.
     *
     * @return object
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Setting up admin fields
     *
     * @return void
     */
    public function __construct() {
        add_action( 'admin_menu', array($this, 'add_menu') );
        add_action( 'rest_api_init', array($this, 'rest_api_init') );
    }

    public function add_menu() {
        $settings_suffix = add_submenu_page(
            'edit.php?post_type=qr',
            __( 'Settings', 'qyrr-code' ),
            __( 'Settings', 'qyrr-code' ),
            'manage_options',
            'settings',
            array($this, 'render_settings')
        );
        add_action( "admin_print_scripts-{$settings_suffix}", array($this, 'add_settings_scripts') );
    }

    public function add_settings_scripts() {
        wp_enqueue_script(
            'qyrr-code-settings',
            QYRR_URL . '/inc/admin/build/index.js',
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
        $args = array(
            'screen'  => 'settings',
            'version' => QYRR_VERSION,
            'logo'    => QYRR_URL . '/assets/qyrr-logo.svg',
            'is_pro'  => false,
        );
        wp_localize_script( 'qyrr-code-settings', 'options', $args );
        // Make the blocks translatable.
        if ( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'qyrr-code-settings', 'qyrr-code', QYRR_PATH . '/languages' );
        }
        wp_enqueue_style( 'qyrr-settings-style', QYRR_URL . '/inc/admin/build/index.css', array('wp-components') );
    }

    public function render_settings() {
        ?>
        <div id="qyrr-settings"></div>
		<?php 
    }

    public function rest_api_init() {
        register_rest_route( 'qyrr/v1', '/settings', array(
            'methods'             => 'GET',
            'callback'            => [$this, 'get_settings'],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        register_rest_route( 'qyrr/v1', '/system-status', array(
            'methods'             => 'GET',
            'callback'            => [$this, 'get_system_status'],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        register_rest_route( 'qyrr/v1', '/settings', array(
            'methods'             => 'POST',
            'callback'            => [$this, 'save_settings'],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
    }

    public function get_settings() {
        return get_option( 'qyrr' );
    }

    public function get_system_status() {
        if ( !defined( 'DISABLE_WP_CRON' ) || DISABLE_WP_CRON !== true ) {
            $is_cron = true;
        } else {
            $is_cron = false;
        }
        return array(
            'PHP'       => array(
                'Version'     => phpversion(),
                'ZIP Archive' => extension_loaded( 'zip' ),
            ),
            'WordPress' => array(
                'WP-Cron'    => $is_cron,
                'Permalinks' => strlen( get_option( 'permalink_structure' ) ) !== 0,
                'SSL'        => is_ssl(),
            ),
        );
    }

    public function save_settings( $request ) {
        if ( $request->get_params() ) {
            $options = sanitize_option( 'qyrr', $request->get_params() );
            foreach ( $options as $key => $value ) {
                if ( $key !== 'supported_post_types' ) {
                    $options[$key] = sanitize_text_field( $value );
                }
            }
            update_option( 'qyrr', $options );
        }
        return json_encode( [
            "status"  => 200,
            "message" => "Ok",
        ] );
    }

    /**
     * Get post types.
     * @return array
     */
    public function get_post_types() {
        $post_types = get_post_types( array(
            'public'              => true,
            'exclude_from_search' => false,
        ), 'names' );
        $post_type_list = [];
        foreach ( $post_types as $post_type ) {
            $post_type_list[] = $post_type;
        }
        return $post_type_list;
    }

}

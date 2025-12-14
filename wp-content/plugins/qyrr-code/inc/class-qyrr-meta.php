<?php

namespace qyrr;

/**
 * Admin Meta Class
 */
class QYRR_Meta {
    /**
     * Contains instance or null
     *
     * @var object|null
     */
    private static $instance = null;

    /**
     * Returns instance of QYRR_Meta.
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
     * Constructor for QYRR_Meta.
     */
    public function __construct() {
        add_action( 'init', array($this, 'register_meta_fields') );
        add_action( 'rest_api_init', array($this, 'rest_api_init') );
    }

    public function register_meta_fields() {
        // Meta fields for Source Settings.
        register_meta( 'post', 'source', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => '',
        ) );
        register_meta( 'post', 'post-type', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 'post',
        ) );
        register_meta( 'post', 'post-link', array(
            'object_subtype' => 'qr',
            'type'           => 'string',
            'single'         => true,
            'show_in_rest'   => true,
        ) );
        register_meta( 'post', 'url', array(
            'object_subtype' => 'qr',
            'type'           => 'string',
            'single'         => true,
            'show_in_rest'   => true,
        ) );
        // Meta fields for General Settings.
        register_meta( 'post', 'qr-content', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => '',
        ) );
        // Meta fields for General Settings.
        register_meta( 'post', 'render-mode', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 'plain',
        ) );
        register_meta( 'post', 'size', array(
            'type'           => 'number',
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 400,
        ) );
        register_meta( 'post', 'logo-max-size', array(
            'type'           => 'number',
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 100,
        ) );
        register_meta( 'post', 'fill-color', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => '#FFF',
        ) );
        register_meta( 'post', 'background-color', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => '#000',
        ) );
        register_meta( 'post', 'min-version', array(
            'object_subtype' => 'qr',
            'type'           => 'number',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 10,
        ) );
        register_meta( 'post', 'error-handling-level', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 'H',
        ) );
        register_meta( 'post', 'quiet-zone', array(
            'object_subtype' => 'qr',
            'type'           => 'number',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 0,
        ) );
        register_meta( 'post', 'corner-radius', array(
            'object_subtype' => 'qr',
            'type'           => 'number',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 0,
        ) );
        register_meta( 'post', 'template', array(
            'object_subtype' => 'qr',
            'type'           => 'boolean',
            'single'         => true,
            'show_in_rest'   => true,
        ) );
        // Meta fields for Label/Logo Settings.
        register_meta( 'post', 'logo-size', array(
            'object_subtype' => 'qr',
            'type'           => 'number',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 30,
        ) );
        register_meta( 'post', 'position-x', array(
            'object_subtype' => 'qr',
            'type'           => 'number',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 50,
        ) );
        register_meta( 'post', 'position-y', array(
            'object_subtype' => 'qr',
            'type'           => 'number',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 50,
        ) );
        register_meta( 'post', 'label-text', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
        ) );
        register_meta( 'post', 'font', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
        ) );
        register_meta( 'post', 'font-color', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
        ) );
        register_meta( 'post', 'logo-upload', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
        ) );
        // Meta fields for Download Settings.
        register_meta( 'post', 'download_format', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 'image',
        ) );
        register_meta( 'post', 'qr-image-url', array(
            'object_subtype' => 'qr',
            'single'         => true,
            'show_in_rest'   => true,
            'default'        => 'image',
        ) );
    }

    /**
     * Render meta fields.
     *
     * @return void
     */
    public function render_settings() {
        ?>
        <div id="qyrr-metabox"></div>
		<?php 
    }

    /**
     * Register custom Rest API routes.
     *
     * @return void
     */
    public function rest_api_init() {
        register_rest_route( 'qyrr/v1', '/meta', array(
            'methods'             => 'POST',
            'callback'            => [$this, 'save_meta'],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
        register_rest_route( 'qyrr/v1', '/meta', array(
            'methods'             => 'GET',
            'callback'            => [$this, 'get_meta'],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ) );
    }

    /**
     * Save meta via Rest API.
     *
     * @param object $request given request.
     *
     * @return false|string|void
     */
    public function save_meta( object $request ) {
        if ( $request->get_params() ) {
            $params = $request->get_params();
            $post_id = esc_html( $params['post_id'] );
            $meta_key = esc_html( $params['meta_key'] );
            // Check which action to perform.
            if ( isset( $params['meta_value'] ) ) {
                $meta_value = sanitize_meta( $meta_key, $params['meta_value'], 'post' );
                update_post_meta( $post_id, $meta_key, $meta_value );
            } else {
                if ( isset( $params['delete'] ) ) {
                    delete_post_meta( $post_id, $meta_key );
                }
            }
            return json_encode( [
                "status"  => 200,
                "message" => "Ok",
            ] );
        }
    }

    /**
     * Get meta via Rest API.
     *
     * @param object $request given request.
     *
     * @return false|string|void
     */
    public function get_meta( object $request ) {
        if ( $request->get_params() ) {
            $params = $request->get_params();
            $post_id = esc_html( $params['post_id'] );
            $meta_key = esc_html( $params['meta_key'] );
            $meta = get_post_meta( $post_id, $meta_key, true );
            if ( !empty( $meta ) ) {
                return json_encode( [
                    "status"  => 200,
                    "message" => "Ok",
                    "data"    => $meta,
                ] );
            } else {
                return json_encode( [
                    "status"  => 400,
                    "message" => "Empty value",
                    "data"    => '',
                ] );
            }
        }
    }

    /**
     * Get the current post type.
     *
     * @return mixed|string|\WP_Post_Type|null
     */
    public function get_current_post_type() {
        global $post, $typenow, $current_screen;
        if ( $post && $post->post_type ) {
            return $post->post_type;
        } elseif ( $typenow ) {
            return $typenow;
        } elseif ( $current_screen && $current_screen->post_type ) {
            return $current_screen->post_type;
        } elseif ( isset( $_REQUEST['post_type'] ) ) {
            return sanitize_key( $_REQUEST['post_type'] );
        }
        return null;
    }

}

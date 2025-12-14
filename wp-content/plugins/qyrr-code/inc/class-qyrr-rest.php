<?php

namespace qyrr;

/**
 * Rest Handler
 */
class QYRR_Rest {
    /**
     * Contains instance or null
     *
     * @var object|null
     */
    private static $instance = null;

    /**
     * Returns instance of QYRR_Rest.
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
     * Constructor for QYRR_Rest.
     */
    public function __construct() {
        add_action( 'rest_api_init', array($this, 'register_routes') );
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( 'qyrr/v1', '/blob-to-file/', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'blob_to_file'),
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ) );
        register_rest_route( 'qyrr/v1', '/qr-codes/url', array(
            'methods'             => 'GET',
            'callback'            => [$this, 'get_qr_codes_with_url'],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ) );
        register_rest_route( 'qyrr/v1', '/file-name', array(
            'methods'             => 'POST',
            'callback'            => [$this, 'get_file_name'],
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ) );
        // Maybe flush rewrite rules.
        if ( get_option( 'qyrr_activated' ) ) {
            flush_rewrite_rules();
            delete_option( 'qyrr_activated' );
        }
    }

    public function blob_to_file( $request ) {
        global $wp_filesystem;
        $data = $request->get_param( 'source' );
        $id = $request->get_param( 'post_id' );
        $format = $request->get_param( 'format' );
        // build the path.
        $uploads_directory = wp_upload_dir();
        $qyrr_directory = $uploads_directory['basedir'] . DIRECTORY_SEPARATOR . 'qyrr' . DIRECTORY_SEPARATOR . $id;
        if ( !function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if ( is_null( $wp_filesystem ) ) {
            WP_Filesystem();
        }
        // if directory not exists, create one.
        $file_path = $qyrr_directory . DIRECTORY_SEPARATOR . apply_filters( 'qyrr_file_name', 'qr-code', $id ) . '.' . $format;
        if ( !file_exists( $qyrr_directory ) ) {
            wp_mkdir_p( $qyrr_directory );
        } else {
            if ( file_exists( $file_path ) ) {
                unlink( $file_path );
            }
        }
        // Convert data string for file creation.
        list( $type, $data ) = explode( ';', $data );
        list( , $data ) = explode( ',', $data );
        $data = base64_decode( $data );
        $wp_filesystem->put_contents( $file_path, $data, FS_CHMOD_FILE );
        return json_encode( [
            "message" => "Success",
        ] );
    }

    public function get_qr_codes_with_url() : array {
        $args = array(
            'numberposts' => -1,
            'post_type'   => 'qr',
            'meta_query'  => array(
                'relation' => 'OR',
                array(
                    'key'   => 'source',
                    'value' => 'url',
                ),
                array(
                    'key'   => 'source',
                    'value' => 'post',
                ),
            ),
        );
        return get_posts( $args );
    }

    public function get_file_name( $request ) {
        $post_id = $request->get_param( 'post_id' );
        if ( $post_id ) {
            return apply_filters( 'qyrr_file_name', 'qr-code', $post_id );
        }
    }

    public function get_selectable_post_types() {
        $post_types = get_post_types( array(), 'objects', 'and' );
        $selectable_post_types = [];
        foreach ( $post_types as $post_type ) {
            $selectable_post_types[] = [
                'label' => $post_type->label,
                'value' => $post_type->name,
            ];
        }
        return $selectable_post_types;
    }

}

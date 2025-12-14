<?php

namespace qyrr;

/**
 * Admin Options Class
 */
class QYRR_Admin {
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
     * Setting up admin fields
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', array($this, 'register_qr') );
        add_action( 'init', array($this, 'register_campaigns') );
        add_filter( 'manage_qr_posts_columns', array($this, 'set_columns') );
        add_action(
            'manage_qr_posts_custom_column',
            array($this, 'set_columns_content'),
            10,
            2
        );
        add_action( 'init', array($this, 'register_post_type_template') );
        add_filter(
            'allowed_block_types_all',
            array($this, 'restrict_allowed_block_types'),
            10,
            2
        );
        add_action( 'admin_head', array($this, 'resize_icon') );
        add_action(
            'before_delete_post',
            array($this, 'remove_files'),
            99,
            2
        );
    }

    /**
     * Register a custom post type called "qr".
     *
     * @see get_post_type_labels() for label keys.
     */
    public function register_qr() {
        $labels = array(
            'name'                  => _x( 'Qyrr', 'Post type general name', 'qyrr-code' ),
            'singular_name'         => _x( 'QR Code', 'Post type singular name', 'qyrr-code' ),
            'menu_name'             => _x( 'Qyrr', 'Admin Menu text', 'qyrr-code' ),
            'name_admin_bar'        => _x( 'QR Code', 'Add New on Toolbar', 'qyrr-code' ),
            'add_new'               => __( 'Add New', 'qyrr-code' ),
            'add_new_item'          => __( 'Add New QR Code', 'qyrr-code' ),
            'new_item'              => __( 'New QR Code', 'qyrr-code' ),
            'edit_item'             => __( 'Edit QR Code', 'qyrr-code' ),
            'view_item'             => __( 'View QR Code', 'qyrr-code' ),
            'all_items'             => __( 'All QR Codes', 'qyrr-code' ),
            'search_items'          => __( 'Search QR Codes', 'qyrr-code' ),
            'parent_item_colon'     => __( 'Parent QR Codes:', 'qyrr-code' ),
            'not_found'             => __( 'No QR codes found.', 'qyrr-code' ),
            'not_found_in_trash'    => __( 'No QR codes found in Trash.', 'qyrr-code' ),
            'archives'              => _x( 'QR codes archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'qyrr-code' ),
            'insert_into_item'      => _x( 'Insert into QR Code', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'qyrr-code' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this QR Code', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'qyrr-code' ),
            'filter_items_list'     => _x( 'Filter QR codes list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'qyrr-code' ),
            'items_list_navigation' => _x( 'QR list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'qyrr-code' ),
            'items_list'            => _x( 'QR codes list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'qyrr-code' ),
        );
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'query_var'          => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'supports'           => array('title', 'editor', 'custom-fields'),
            'menu_icon'          => QYRR_URL . '/assets/qr-code-light.svg',
        );
        register_post_type( 'qr', $args );
    }

    public function register_campaigns() {
        // Add new taxonomy, NOT hierarchical (like tags)
        $labels = array(
            'name'                       => _x( 'Campaigns', 'taxonomy general name', 'qyrr-code' ),
            'singular_name'              => _x( 'Campaign', 'taxonomy singular name', 'qyrr-code' ),
            'search_items'               => __( 'Search Campaigns', 'qyrr-code' ),
            'popular_items'              => __( 'Popular Campaigns', 'qyrr-code' ),
            'all_items'                  => __( 'All Campaigns', 'qyrr-code' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Campaign', 'qyrr-code' ),
            'update_item'                => __( 'Update Campaign', 'qyrr-code' ),
            'add_new_item'               => __( 'Add New Campaign', 'qyrr-code' ),
            'new_item_name'              => __( 'New Campaign Name', 'qyrr-code' ),
            'separate_items_with_commas' => __( 'Separate campaigns with commas', 'qyrr-code' ),
            'add_or_remove_items'        => __( 'Add or remove campaigns', 'qyrr-code' ),
            'choose_from_most_used'      => __( 'Choose from the most used campaigns', 'qyrr-code' ),
            'not_found'                  => __( 'No campaigns found.', 'qyrr-code' ),
            'menu_name'                  => __( 'Campaigns', 'qyrr-code' ),
        );
        $args = array(
            'hierarchical'          => false,
            'public'                => false,
            'labels'                => $labels,
            'show_in_rest'          => true,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
        );
        register_taxonomy( 'campaign', 'qr', $args );
    }

    public function register_post_type_template() {
        $page_type_object = get_post_type_object( 'qr' );
        $page_type_object->template = [['qyrr-code/qr']];
        $page_type_object->template_lock = 'all';
    }

    public function restrict_allowed_block_types( $allowed_block_types, $editor_context ) {
        if ( 'qr' === $editor_context->post->post_type ) {
            $allowed_block_types = array('qyrr-code/qr');
        }
        return $allowed_block_types;
    }

    /**
     * Set column headers-
     *
     * @param array $columns array of columns.
     *
     * @return array
     */
    public function set_columns( $columns ) {
        unset($columns['date']);
        $columns['qr_code'] = __( 'QR-Code', 'qyrr-code' );
        $columns['download'] = __( 'Download QR-Code', 'qyrr-code' );
        return $columns;
    }

    /**
     * Add content to registered columns.
     *
     * @param string $column name of the column.
     * @param int $post_id current id.
     *
     * @return void
     */
    public function set_columns_content( $column, $post_id ) {
        $uploads_directory = wp_upload_dir();
        $qyrr_directory_url = $uploads_directory['baseurl'] . DIRECTORY_SEPARATOR . 'qyrr' . DIRECTORY_SEPARATOR . $post_id . DIRECTORY_SEPARATOR;
        $qyrr_directory_path = $uploads_directory['basedir'] . DIRECTORY_SEPARATOR . 'qyrr' . DIRECTORY_SEPARATOR . $post_id . DIRECTORY_SEPARATOR;
        $file_name = apply_filters( 'qyrr_file_name', 'qr-code', $post_id );
        // Check file type.
        if ( file_exists( $qyrr_directory_path . $file_name . '.png' ) ) {
            $image_url = $qyrr_directory_url . $file_name . '.png';
            $download_url = $qyrr_directory_url . $file_name . '.png';
        } elseif ( file_exists( $qyrr_directory_path . $file_name . '.svg' ) ) {
            $image_url = $qyrr_directory_url . $file_name . '.svg';
            $download_url = $qyrr_directory_url . $file_name . '.svg';
        } else {
            $image_url = '';
            $download_url = '';
        }
        // Cache busting per URL.
        $timestamp = time();
        switch ( $column ) {
            case 'qr_code':
                ?>
					<?php 
                if ( !empty( $image_url ) ) {
                    ?>
                    <img width=100px" height="100px"
                         src="<?php 
                    echo esc_url( $image_url ) . '?ts=' . esc_attr( $timestamp );
                    ?>"/>
				<?php 
                } else {
                    ?>

                    <div><?php 
                    esc_html_e( 'No preview available.', 'qyrr-code' );
                    ?></div>
				<?php 
                }
                ?>
					<?php 
                break;
            case 'download':
                ?>
					<?php 
                if ( !empty( $download_url ) ) {
                    ?>
                    <a href="<?php 
                    echo esc_url( $download_url );
                    ?>"
                       id="column-download" class="button-primary"
                       download><?php 
                    echo esc_html__( 'Download', 'qyrr-code' );
                    ?></a>
				<?php 
                }
                ?>
					<?php 
                break;
        }
    }

    public function resize_icon() {
        ?>
        <style>
            .menu-icon-qr .wp-menu-image img {
                max-width: 16px;
            }
        </style>
		<?php 
    }

    public function remove_files( $post_id, $post ) {
        global $wp_filesystem;
        if ( !function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if ( is_null( $wp_filesystem ) ) {
            WP_Filesystem();
        }
        if ( 'qr' !== $post->post_type ) {
            return;
        }
        $uploads_directory = wp_upload_dir();
        $wp_filesystem->rmdir( $uploads_directory['basedir'] . DIRECTORY_SEPARATOR . 'qyrr' . DIRECTORY_SEPARATOR . $post_id . DIRECTORY_SEPARATOR, true );
    }

}

<?php

if ( !function_exists( 'qyrr_fs' ) ) {
    // Create a helper function for easy SDK access.
    function qyrr_fs() {
        global $qyrr_fs;
        if ( !isset( $qyrr_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $qyrr_fs = fs_dynamic_init( array(
                'id'              => '5292',
                'slug'            => 'qyrr-code',
                'premium_slug'    => 'qr-premium',
                'type'            => 'plugin',
                'public_key'      => 'pk_4ecb1bb8e14d1ef36183b1f5b032f',
                'is_premium'      => false,
                'premium_suffix'  => 'Pro',
                'has_addons'      => false,
                'has_paid_plans'  => true,
                'has_affiliation' => 'selected',
                'menu'            => array(
                    'slug'        => 'edit.php?post_type=qr',
                    'contact'     => false,
                    'support'     => false,
                    'affiliation' => false,
                ),
                'is_live'         => true,
            ) );
        }
        return $qyrr_fs;
    }

    // Init Freemius.
    qyrr_fs();
    // Signal that SDK was initiated.
    do_action( 'qyrr_fs_loaded' );
    /**
     * Return freemius settings URL
     *
     * @return string
     */
    function qyrr_fs_settings_url() {
        return admin_url( 'edit.php?post_type=qr' );
    }

    qyrr_fs()->add_filter( 'connect_url', 'qyrr_fs_settings_url' );
    qyrr_fs()->add_filter( 'after_skip_url', 'qyrr_fs_settings_url' );
    qyrr_fs()->add_filter( 'after_connect_url', 'qyrr_fs_settings_url' );
    qyrr_fs()->add_filter( 'after_pending_connect_url', 'qyrr_fs_settings_url' );
    /**
     * Remove freemius pages.
     *
     * @param bool $is_visible indicates if visible or not.
     * @param int $submenu_id current submenu id.
     *
     * @return bool
     */
    qyrr_fs()->add_filter(
        'is_submenu_visible',
        '__return_false',
        10,
        2
    );
    /**
     * Add custom icon for Freemius.
     *
     * @return string
     */
    qyrr_fs()->add_filter( 'plugin_icon', function () {
        return QYRR_PATH . '/assets/qr-code-icon.png';
    } );
    /**
     * Clean up qyrr settings after uninstallation
     *
     * @return void
     */
    function qyrr_cleanup() {
        global $wp_filesystem;
        if ( !function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if ( is_null( $wp_filesystem ) ) {
            WP_Filesystem();
        }
        // Delete options first.
        delete_option( 'qyrr' );
        // Delete QR Codes directory.
        $uploads_dir = wp_upload_dir();
        $qyrr_dir = $uploads_dir['basedir'] . DIRECTORY_SEPARATOR . 'qyrr';
        if ( file_exists( $qyrr_dir ) ) {
            $wp_filesystem->delete( $qyrr_dir, true );
        }
        // Delete all CPT posts.
        $myproducts = get_pages( array(
            'post_type' => 'products',
        ) );
        foreach ( $myproducts as $myproduct ) {
            // Delete all products.
            wp_delete_post( $myproduct->ID, true );
            // Set to False if you want to send them to Trash.
        }
    }

    qyrr_fs()->add_action( 'after_uninstall', 'qyrr_cleanup' );
}
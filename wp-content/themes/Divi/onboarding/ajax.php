<?php
/**
 * This file handles AJAX requests for the Divi Onboarding module.
 *
 * @package Divi
 */

namespace Divi\Onboarding\Ajax;

use WP_Query;

use \Divi\Onboarding\Helpers;

/**
 * Handles AJAX.
 *
 * @package Divi
 */


/**
 * Handles activating a plugin via AJAX.
 *
 * @since ??
 */
function activate_plugin() {
	\et_core_security_check( 'manage_options', 'et_onboarding_activate_plugin', '_ajax_nonce' );

	$allowed_plugins = array(
		'woocommerce/woocommerce.php',
	);

	$plugin_name = isset( $_POST['plugin'] ) ? sanitize_text_field( $_POST['plugin'] ) : '';

	$activated = false;

	if ( isset( $_POST['plugin'] ) && in_array( $plugin_name, $allowed_plugins, true ) ) {
		$activated = \activate_plugin( $plugin_name );
	}

	if ( is_wp_error( $activated ) ) {
		wp_send_json_error();
	}

	Helpers\create_woocommerce_pages();

	wp_send_json_success();
}

add_action( 'wp_ajax_et_onboarding_activate_plugin', __NAMESPACE__ . '\\activate_plugin' );


/**
 * Handles creating a menu with pages via AJAX.
 *
 * @since ??
 */
function create_menu_with_pages() {
	\et_core_security_check( 'manage_options', 'et_onboarding_create_menu_with_pages', '_ajax_nonce' );

	$result = false;

	if ( isset( $_POST['page_titles'] ) ) {
		$page_titles = array_map( 'sanitize_text_field', $_POST['page_titles'] );
		$result      = Helpers\create_menu_with_pages( $page_titles );
	}

	if ( ! $result ) {
		wp_send_json_error();
	}

	wp_send_json_success( $result );
}

add_action( 'wp_ajax_et_onboarding_create_menu_with_pages', __NAMESPACE__ . '\\create_menu_with_pages' );

/**
 * Handles updating site info via AJAX.
 *
 * @since ??
 */
function update_site_info() {
	\et_core_security_check( 'manage_options', 'et_onboarding_update_site_info', '_ajax_nonce' );

	$site_logo        = isset( $_POST['site_logo'] ) ? sanitize_text_field( $_POST['site_logo'] ) : '';
	$site_title       = isset( $_POST['site_title'] ) ? sanitize_text_field( $_POST['site_title'] ) : '';
	$site_tagline     = isset( $_POST['site_tagline'] ) ? sanitize_text_field( $_POST['site_tagline'] ) : '';
	$site_description = isset( $_POST['site_description'] ) ? sanitize_text_field( $_POST['site_description'] ) : '';

	$site_info = Helpers\get_site_info();

	Helpers\update_site_info( $site_logo, $site_title, $site_tagline, $site_description );

	wp_send_json_success();
}

add_action( 'wp_ajax_et_onboarding_update_site_info', __NAMESPACE__ . '\\update_site_info' );

/**
 * Handles uploading a layout image via AJAX.
 *
 * @since ??
 */
function upload_layout_image() {
	\et_core_security_check( 'manage_options', 'et_onboarding_upload_layout_image', 'wp_nonce' );

	$image_url_raw = isset( $_POST['imageURL'] ) ? esc_url_raw( $_POST['imageURL'] ) : '';

	if ( $image_url_raw && '' !== $image_url_raw ) {
		$upload = media_sideload_image( $image_url_raw, get_the_id(), null, 'id' );

		if ( is_wp_error( $upload ) ) {
			wp_send_json_error( [ 'message' => $upload->get_error_message() ] );
		}

		$attachment_id = is_wp_error( $upload ) ? 0 : $upload;
		$image_url     = get_attached_file( $attachment_id );

		$image_editor = wp_get_image_editor( $image_url );
		if ( ! is_wp_error( $image_editor ) ) {
			$image_editor->set_quality( 80 );
			$saved = $image_editor->save( null, 'image/jpeg' );

			if ( ! is_wp_error( $saved ) ) {
				wp_delete_attachment( $attachment_id, true );
				$attachment_id = wp_insert_attachment(
					[
						'post_mime_type' => 'image/jpeg',
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $saved['path'] ) ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					],
					$saved['path']
				);
				wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $saved['path'] ) );
			}
		}

		wp_send_json_success(
			[
				'localImageID'  => $attachment_id,
				'localImageURL' => wp_get_attachment_url( $attachment_id ),
			]
		);
	}
}

add_action( 'wp_ajax_et_onboarding_upload_layout_image', __NAMESPACE__ . '\\upload_layout_image' );

/**
 * Handles creating a page via AJAX.
 *
 * @since ??
 */
function create_page() {
	\et_core_security_check( 'manage_options', 'et_onboarding_create_page', '_ajax_nonce' );

	$page_title = isset( $_POST['page_title'] ) ? sanitize_text_field( $_POST['page_title'] ) : '';

	$result = wp_insert_post(
		[
			'post_title'   => $page_title,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		]
	);

	if ( ! $result ) {
		wp_send_json_error();
	}

	update_post_meta( $result, '_et_pb_use_builder', 'on' );
	update_post_meta( $result, '_et_pb_page_layout', 'et_full_width_page' );
	update_post_meta( $result, '_et_pb_built_for_post_type', 'page' );
	update_post_meta( $result, '_et_onboarding_created', '1' );
	wp_send_json_success( $result );
}

add_action( 'wp_ajax_et_onboarding_create_page', __NAMESPACE__ . '\\create_page' );

/**
 * Handles restoring pre-generation state via AJAX.
 *
 * @since ??
 */
function restore_pre_generation_state() {
	\et_core_security_check( 'manage_options', 'et_onboarding_restore_pre_generation_state', '_ajax_nonce' );

	// Restore site info.
	Helpers\restore_theme_builder();
	Helpers\restore_primary_menu();
	Helpers\purge_onboarding_pages();
	Helpers\restore_homepage_settings();

	wp_send_json_success();
}

add_action( 'wp_ajax_et_onboarding_restore_pre_generation_state', __NAMESPACE__ . '\\restore_pre_generation_state' );

/**
 * Publish pages.
 */
function publish_new_pages() {
	\et_core_security_check( 'manage_options', 'et_onboarding_publish_new_pages', '_ajax_nonce' );

	$args = array(
		'post_type'      => 'page',
		'post_status'    => 'draft',
		'meta_key'       => '_et_onboarding_created',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	);

	$query = new \WP_Query( $args );
	$posts = $query->get_posts();

	if ( $query->have_posts() ) {
		foreach ( $posts as $post_id ) {
			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'publish',
				)
			);
		}
	}

	wp_reset_postdata();
}

add_action( 'wp_ajax_et_onboarding_publish_new_pages', __NAMESPACE__ . '\\publish_new_pages' );

/**
 * Clean up '_et_onboarding_created' meta from pages, theme builder template and menu.
 */
function clear_flag_meta() {
	\et_core_security_check( 'manage_options', 'et_onboarding_clear_flag_meta', '_ajax_nonce' );

	$args = array(
		'post_type'      => [ 'page', ET_THEME_BUILDER_TEMPLATE_POST_TYPE ],
		'post_status'    => 'publish',
		'meta_key'       => '_et_onboarding_created',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	);

	$posts = get_posts( $args );

	if ( ! empty( $posts ) ) {
		foreach ( $posts as $post_id ) {
			delete_post_meta( $post_id, '_et_onboarding_created' );
		}
	}

	$menus = wp_get_nav_menus();
	foreach ( $menus as $menu ) {
			delete_term_meta( $menu->term_id, '_et_onboarding_created' );
	}
}

add_action( 'wp_ajax_et_onboarding_clear_flag_meta', __NAMESPACE__ . '\\clear_flag_meta' );

/*
 *  AJAX handler to check if WooCommerce is installed.
 */
function is_woocommerce_installed() {
	\et_core_security_check( 'manage_options', 'et_onboarding_is_woocommerce_installed', '_ajax_nonce' );

	$is_installed = false;

	$all_plugins = get_plugins();

	if ( array_key_exists( 'woocommerce/woocommerce.php', $all_plugins ) ) {
		$is_installed = true;
	}

	wp_send_json_success( [ 'is_installed' => $is_installed ] );

}

add_action( 'wp_ajax_et_onboarding_is_woocommerce_installed', __NAMESPACE__ . '\\is_woocommerce_installed' );

/**
 * AJAX handler to check if WooCommerce is active.
 */
function is_woocommerce_active() {
	\et_core_security_check( 'manage_options', 'et_onboarding_is_woocommerce_active', '_ajax_nonce' );

	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	$is_active = false;

	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$is_active = true;
	}

	wp_send_json_success( [ 'is_active' => $is_active ] );
}

add_action( 'wp_ajax_et_onboarding_is_woocommerce_active', __NAMESPACE__ . '\\is_woocommerce_active' );

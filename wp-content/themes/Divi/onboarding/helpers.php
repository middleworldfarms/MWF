<?php
namespace Divi\Onboarding\Helpers;

/**
 * Helper functions.
 *
 * @package Divi
 * @since ??
 */

/**
 * Create a menu with pages.
 *
 * @param array  $page_titles  The titles of the pages to create.
 * @return array An array containing the menu ID and an array of page IDs.
 *
 * @since ??
 */
function create_menu_with_pages( $page_titles ) {
	$locations = get_nav_menu_locations();

	$base_name   = esc_html__( 'Onboarding Primary Menu', 'Divi' );
	$counter     = 1;
	$unique_name = $base_name;

	while ( wp_get_nav_menu_object( $unique_name ) ) {
			$unique_name = $base_name . ' ' . $counter;
			$counter++;
	}

	$onboarding_menu_id = wp_create_nav_menu( $unique_name );

	// Save old primary menu in order to restore if canceled.
	if ( isset( $locations['primary-menu'] ) ) {
		$primary_menu = wp_get_nav_menu_object( $locations['primary-menu'] );

		if ( $primary_menu ) {
			$menu_id = $primary_menu->term_id;

			update_term_meta( $onboarding_menu_id, '_et_old_primary_menu', $menu_id );
		}
	}

	$locations                 = get_theme_mod( 'nav_menu_locations' );
	$locations['primary-menu'] = $onboarding_menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	update_term_meta( $onboarding_menu_id, '_et_onboarding_created', '1' );

	$result = [
		'menu_id'  => $onboarding_menu_id,
		'page_ids' => [],
	];

	$home_page_id = 0;

	foreach ( $page_titles as $page_title ) {
		$page_id = wp_insert_post(
			array(
				'post_title'   => $page_title,
				'post_content' => '',
				'post_status'  => 'draft',
				'post_type'    => 'page',
			)
		);

		wp_reset_postdata();

		update_post_meta( $page_id, '_et_pb_use_builder', 'on' );
		update_post_meta( $page_id, '_et_pb_page_layout', 'et_full_width_page' );
		update_post_meta( $page_id, '_et_pb_built_for_post_type', 'page' );
		update_post_meta( $page_id, '_et_onboarding_created', '1' );

		$result['page_ids'][ strtolower( $page_title ) ] = $page_id;

		wp_update_nav_menu_item(
			$onboarding_menu_id,
			0,
			array(
				'menu-item-title'     => $page_title,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);

		if ( str_contains( $page_title, 'home' ) || str_contains( $page_title, 'Home' ) ) {
			$home_page_id = $page_id;
		} elseif ( ! $home_page_id ) {
			$home_page_id = $page_id;
		}
	}

	if ( $home_page_id ) {
		$current_page_on_front = get_option( 'page_on_front' );
		$current_show_on_front = get_option( 'show_on_front' );

		update_option( '_et_old_page_on_front', $current_page_on_front );
		update_option( '_et_old_show_on_front', $current_show_on_front );

		update_option( 'page_on_front', $home_page_id );
		update_option( 'show_on_front', 'page' );
		set_transient( 'home_page_set', true, 0 );
	}

	return $result;
}

/**
 * Update site information.
 *
 * @param string $site_logo        The site logo url.
 * @param string $site_title       The site title.
 * @param string $site_tagline     The site tagline.
 * @param string $site_description The site description.
 *
 * @return void
 *
 * @since ??
 */
function update_site_info( $site_logo, $site_title, $site_tagline, $site_description = '' ) {
	global $shortname;

	if ( ! empty( $site_title ) ) {
		update_option( 'blogname', wp_unslash( $site_title ) );
	}

	if ( ! empty( $site_tagline ) ) {
		update_option( 'blogdescription', wp_unslash( $site_tagline ) );
	}

	if ( ! empty( $site_logo ) ) {
		et_update_option( $shortname . '_logo', $site_logo );
	}

	if ( ! empty( $site_description ) ) {
		et_update_option( 'et_ai_layout_site_description', $site_description );
	}
}

/**
 * Get site information.
 *
 * @return array
 */
function get_site_info() {
	global $shortname;

	$site_logo    = et_get_option( $shortname . '_logo' );
	$site_title   = get_option( 'blogname' );
	$site_tagline = get_option( 'blogdescription' );

	return [
		'site_logo'    => $site_logo,
		'site_title'   => $site_title,
		'site_tagline' => $site_tagline,
	];
}

/**
 * Get primary menu data.
 *
 * @return array
 */
function get_primary_menu_data() {
	$menu_location = 'primary-menu';

	$locations = get_nav_menu_locations();
	if ( ! isset( $locations[ $menu_location ] ) ) {
			return array(
				'menu_id'    => null,
				'menu_items' => array(),
			);
	}

	$menu_id = $locations[ $menu_location ];

	$menu_items = wp_get_nav_menu_items( $menu_id );

	return array(
		'menu_id'    => $menu_id,
		'menu_items' => $menu_items,
	);
}

/**
 * Restore theme builder.
 */
function restore_theme_builder() {
	$template = new \WP_Query(
		[
			'post_type'      => ET_THEME_BUILDER_TEMPLATE_POST_TYPE,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		]
	);

	if ( $template->have_posts() ) {
		foreach ( $template->posts as $template_id ) {
			$onboarding_created = get_post_meta( $template_id, '_et_onboarding_created', true );

			if ( $onboarding_created ) {
				$body   = (int) get_post_meta( $template_id, '_et_body_layout_id', true );
				$header = (int) get_post_meta( $template_id, '_et_header_layout_id', true );
				$footer = (int) get_post_meta( $template_id, '_et_footer_layout_id', true );

				if ( $body ) {
					wp_delete_post( $body, true );
				}

				if ( $header ) {
					wp_delete_post( $header, true );
				}

				if ( $footer ) {
					wp_delete_post( $footer, true );
				}

				wp_delete_post( $template_id, true );
			} else {
				$old_use_on       = get_post_meta( $template_id, '_et_old_use_on', false );
				$old_exclude_from = get_post_meta( $template_id, '_et_old_exclude_from', false );

				if ( $old_use_on ) {
					foreach ( $old_use_on as $condition ) {
						update_post_meta( $template_id, '_et_use_on', $condition );
					}

					delete_post_meta( $template_id, '_et_old_use_on' );
				}

				if ( $old_exclude_from ) {
					foreach ( $old_exclude_from as $condition ) {
						update_post_meta( $template_id, '_et_exclude_from', $condition );
					}
					delete_post_meta( $template_id, '_et_old_exclude_from' );
				}
			}
		}
	}
}

/**
 * Restore primary menu.
 */
function restore_primary_menu() {
	$menus = get_terms(
		array(
			'taxonomy'   => 'nav_menu',
			'hide_empty' => false,
			'orderby'    => 'id',
			'order'      => 'DESC',
			'meta_query' => array(
				array(
					'key'   => '_et_onboarding_created',
					'value' => '1',
				),
			),
		)
	);

	if ( ! empty( $menus ) ) {
		foreach ( $menus as $menu ) {
			$primary_menu_id = (int) get_term_meta( $menu->term_id, '_et_old_primary_menu', true );
			// Restore previous primary menu if it exists.
			if ( $primary_menu_id ) {
				$locations                 = get_theme_mod( 'nav_menu_locations' );
				$locations['primary-menu'] = $primary_menu_id;

				set_theme_mod( 'nav_menu_locations', $locations );
			}

			wp_delete_nav_menu( $menu->term_id );
		}
	}
}

/**
 * Purge onboarding pages.
 */
function purge_onboarding_pages() {
	$pages = get_posts(
		array(
			'meta_key'       => '_et_onboarding_created',
			'meta_value'     => '1',
			'post_type'      => 'page',
			'posts_per_page' => -1,
			'post_status'    => 'draft',
		)
	);

	foreach ( $pages as $page ) {
		wp_delete_post( $page->ID, true );
	}
}

/**
 * Restore homepage display settings.
 */
function restore_homepage_settings() {
	$current_page_on_front = get_option( '_et_old_page_on_front' );
	$current_show_on_front = get_option( '_et_old_show_on_front' );

	update_option( 'page_on_front', $current_page_on_front );
	update_option( 'show_on_front', $current_show_on_front );
}

/**
 * Function to create WooCommerce pages if they do not exist.
 */
function create_woocommerce_pages() {
	$pages = [
		'shop'      => [
			'title'   => 'Shop',
			'content' => '[woocommerce_shop]',
		],
		'cart'      => [
			'title'   => 'Cart',
			'content' => '[woocommerce_cart]',
		],
		'checkout'  => [
			'title'   => 'Checkout',
			'content' => '[woocommerce_checkout]',
		],
		'myaccount' => [
			'title'   => 'My Account',
			'content' => '[woocommerce_my_account]',
		],
		'terms'     => [
			'title'   => 'Terms and Conditions',
			'content' => 'Your terms and conditions content here.',
		],
	];

	foreach ( $pages as $page_slug => $page_info ) {
		$page_id = \wc_get_page_id( $page_slug );

		if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
			$page_id = wp_insert_post(
				[
					'post_title'   => $page_info['title'],
					'post_content' => $page_info['content'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
				]
			);

			update_post_meta( $page_id, '_et_pb_built_for_post_type', 'page' );
			update_post_meta( $page_id, '_et_pb_page_layout', 'et_full_width_page' );
			update_post_meta( $page_id, '_et_onboarding_created', '1' );
			update_option( 'woocommerce_' . $page_slug . '_page_id', $page_id );
		}
	}
}

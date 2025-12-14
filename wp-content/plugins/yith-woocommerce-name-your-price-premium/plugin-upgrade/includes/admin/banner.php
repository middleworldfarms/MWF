<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @author  YITH
 * @package YITH/PluginUpgrade
 */

declare( strict_types = 1 );

namespace YITH\PluginUpgrade\Admin;

use YITH\PluginUpgrade\Licences;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

if ( ! class_exists( 'YITH\PluginUpgrade\Admin\Banner' ) ) :
	/**
	 * Handle licence banner
	 *
	 * @since 5.0.0
	 */
	class Banner {

		/**
		 * Init class hooks
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public static function init() {
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_scripts' ), 5 );
			add_action( 'yith_plugin_fw_panel_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_and_render_licence_banner' ) );
			add_action( 'wp_ajax_yith_plugin_upgrade_licence_modal_dismiss', array( __CLASS__, 'dismiss_licence_modal' ) );
		}

		/**
		 * Register dedicated banner scripts
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public static function register_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && \SCRIPT_DEBUG ? '' : '.min';

			wp_register_style( 'yith-licence-banner', YITH_PLUGIN_UPGRADE_URL . '/assets/css/banner.css', array(), YITH_PLUGIN_UPGRADE_VERSION );
			wp_register_script( 'yith-licence-banner', YITH_PLUGIN_UPGRADE_URL . "/assets/js/banner$suffix.js", array( 'jquery', 'yith-ui' ), YITH_PLUGIN_UPGRADE_VERSION, true );
		}

		/**
		 * Maybe enqueue and render licence banner.
		 *
		 * @param \YIT_Plugin_Panel $panel The panel.
		 * @return void
		 */
		public static function maybe_enqueue_and_render_licence_banner( \YIT_Plugin_Panel $panel ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$slug = is_callable( array( $panel, 'get_plugin_slug' ) ) ? $panel->get_plugin_slug() : '';
			if ( empty( $slug ) ) {
				return;
			}

			// Get the licence.
			$licence = array_filter(
				Licences::instance()->get_licences( 'yith' ),
				function ( $licence ) use ( $slug ) {
					return $slug === $licence->product_id && ! $licence->is_activated();
				}
			);

			if ( empty( $licence ) ) {
				return;
			}

			$plugins     = get_plugins();
			$plugin_init = key( $licence );
			$plugin_data = $plugins[ $plugin_init ] ?? array();
			if ( empty( $plugin_data ) ) {
				return;
			}

			wp_enqueue_style( 'yith-licence-banner' );
			$mode = get_option( 'yith_plugin_upgrade_licence_banner_' . $slug, 'modal' ) === 'modal' ? 'modal' : 'inline';
			if ( 'modal' === $mode ) {
				wp_enqueue_script( 'yith-licence-banner' );
			}

			$template_args = array(
				'slug'           => $slug,
				'mode'           => $mode,
				'plugin_name'    => str_replace( ' Premium', '', $plugin_data['Name'] ?? '' ),
				'landing_url'    => $panel->add_utm_data( $plugin_data['PluginURI'] ?? 'https://www.yithemes.com', 'licence-banner' ),
				'activation_url' => Licences::instance()->get_admin_panel()->get_url(),
			);

			add_action(
				'yith_plugin_fw_panel_before_panel_header',
				function () use ( $template_args ) {
					Banner::output( $template_args );
				}
			);
		}

		/**
		 * Dismiss licence modal.
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public static function dismiss_licence_modal() {
			$slug     = sanitize_text_field( wp_unslash( $_REQUEST['slug'] ?? '' ) );
			$security = sanitize_text_field( wp_unslash( $_REQUEST['security'] ?? '' ) );
			if ( $slug && $security && wp_verify_nonce( $security, $slug ) && update_option( 'yith_plugin_upgrade_licence_banner_' . $slug, 'inline' ) ) {
				wp_send_json_success();
			}
			wp_send_json_error();
		}

		/**
		 * Output banner
		 *
		 * @since  5.0.0
		 * @param array $template_args The banner template arguments.
		 * @return void
		 */
		public static function output( array $template_args ) {
			extract( $template_args ); // phpcs:ignore
			include YITH_PLUGIN_UPGRADE_PATH . '/templates/banner/banner.php';
		}
	}
endif;

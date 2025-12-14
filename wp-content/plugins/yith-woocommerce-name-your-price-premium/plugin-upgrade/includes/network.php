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

namespace YITH\PluginUpgrade;

use const DAY_IN_SECONDS;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

if ( ! class_exists( 'YITH\PluginUpgrade\Network' ) ) :
	/**
	 * Collect WP network methods to handle licences.
	 *
	 * @since 5.0.0
	 */
	class Network {

		/**
		 * Network plugins activated transient name
		 *
		 * @const string
		 */
		const TRANSIENT = 'yith_plugin_global_licence_activation';

		/**
		 * Class constructor
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public function __construct() {
			add_action( 'yith_plugin_upgrade_yith_licences_saved', array( $this, 'check_licences_data' ) );

			// Delete the global licence activation for all blogs if the admin add a new site, delete or edit a site.
			foreach ( array( 'wp_delete_site', 'wp_insert_site', 'wp_update_site' ) as $action ) {
				add_action( $action, array( $this, 'delete_licences_data' ) );
			}
		}

		/**
		 * If is a WP network, check licence globally for all blog and store in transient.
		 *
		 * @param string $option_name The option saved.
		 * @return void
		 */
		public function check_licences_data( string $option_name ) {
			$new_data = array();
			$blog_ids = wp_list_pluck( get_sites(), 'blog_id' );
			foreach ( $blog_ids as $blog_id ) {
				$blog_licences = wp_list_pluck( get_blog_option( $blog_id, $option_name, array() ), 'activated' );
				foreach ( $blog_licences as $plugin_slug => $activated ) {
					if ( empty( $new_data[ $plugin_slug ] ) || ! $activated ) {
						$new_data[ $plugin_slug ] = $activated;
					}
				}
			}

			$this->set_licences_data( $new_data );
		}

		/**
		 * Check if given plugin is activated in all blog.
		 *
		 * @since 5.0.0
		 * @param string $plugin_init The plugin init.
		 * @return bool True if the plugin is enabled in all blogs, false otherwise.
		 */
		public function is_activated_in_all_blogs( string $plugin_init ): bool {
			$licence = Licences::instance()->get_single_licence( $plugin_init );
			$data    = $this->get_licences_data();
			return $licence && ! empty( $data[ $licence->product_id ] );
		}

		/**
		 * Get the global licence information for all networks
		 *
		 * @return mixed Activation array check if exists.
		 */
		public function get_licences_data(): array {
			$data = get_site_transient( self::TRANSIENT );
			return ! empty( $data ) ? $data : array();
		}

		/**
		 * Save the global licence information in a transient
		 *
		 * @param array $data An array of data to set.
		 * @return void
		 */
		public function set_licences_data( array $data ) {
			$expiration = apply_filters( 'yith_check_global_licence_expiration', DAY_IN_SECONDS );
			set_site_transient( self::TRANSIENT, $data, $expiration );
		}

		/**
		 * Delete the global licence information in a transient
		 *
		 * @return void
		 */
		public function delete_licences_data() {
			delete_site_transient( self::TRANSIENT );
		}
	}
endif;

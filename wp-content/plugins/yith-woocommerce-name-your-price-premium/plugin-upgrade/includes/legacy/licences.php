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

namespace YITH\PluginUpgrade\Legacy;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

use Exception;
use YITH\PluginUpgrade\Admin\Panel;
use YITH\PluginUpgrade\Licence;
use YITH\PluginUpgrade\Network;
use YITH\PluginUpgrade\Utils;

if ( ! class_exists( 'YITH\PluginUpgrade\Legacy\Licences' ) ) :
	/**
	 * Legacy licences class
	 *
	 * @since 5.0.0
	 */
	abstract class Licences {

		/**
		 * Panel class instance.
		 *
		 * @since 5.0.0
		 * @var Panel
		 */
		protected $panel;

		/**
		 * Network handler class instance.
		 *
		 * @since 5.0.0
		 * @var Network
		 */
		protected $network;

		/**
		 * List of registered licences
		 *
		 * @var Licence[]
		 */
		protected $licences = array();

		/**
		 * List of DB data for licences
		 *
		 * @var array
		 */
		protected $data = array();

		/**
		 * Check global licence on network transient name
		 *
		 * @var string
		 */
		protected $check_global_licence_transient = 'yith_plugin_global_licence_activation';

		/**
		 * Get licence activation URL
		 *
		 * @since 5.0.0
		 * @param string $plugin_slug The plugin slug.
		 * @return string
		 * @deprecated
		 */
		public function get_license_activation_url( string $plugin_slug = '' ): string {
			$params = ! empty( $plugin_slug ) ? array( 'plugin' => $plugin_slug ) : array();
			return ! empty( $this->panel ) ? $this->panel->get_url( $params ) : '';
		}

		/**
		 * Get the activation page url
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return string The activation page url.
		 * @deprecated
		 */
		public function get_licence_activation_page_url(): string {
			return ! empty( $this->panel ) ? $this->panel->get_url() : '';
		}

		/**
		 * Get array products
		 *
		 * @since  5.0.0
		 * @return array
		 */
		public function get_products(): array {
			return array();
		}

		/**
		 * Get a specific product information
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param string $init Product init file.
		 * @return mixed array
		 * @deprecated
		 */
		public function get_product( string $init ) {
			$products = $this->get_products();
			return $products[ $init ] ?? false;
		}

		/**
		 * Get product id by given plugin_init
		 *
		 * @param string $init Product init file.
		 * @return mixed array
		 * @deprecated
		 */
		public function get_product_id( string $init ) {
			$product = $this->get_product( $init );
			return $product['product_id'] ?? false;
		}

		/**
		 * Get the licence information
		 *
		 * @return array The licence array.
		 * @deprecated
		 */
		public function get_licence(): array {
			return $this->data['yith'] ?? array();
		}

		/**
		 * Check Plugins Licence. Send a request to API server to check if plugins is activated
		 *
		 * @param string  $product_init The plugin init slug.
		 * @param boolean $regenerate_transient True to regenerate transient, false otherwise.
		 * @param boolean $force_check True to force check, false otherwise.
		 * @return bool True if activated, false otherwise.
		 * @deprecated
		 */
		public function check( string $product_init, bool $regenerate_transient = true, bool $force_check = false ): bool {
			try {
				return ! empty( $this->licences[ $product_init ] ) && $this->licences[ $product_init ]->check();
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Admin Enqueue Scripts
		 *
		 * @return void
		 * @deprecated
		 */
		public function admin_enqueue_scripts() {
			empty( $this->panel ) || $this->panel->admin_enqueue_scripts();
		}

		/**
		 * Get the licence option name
		 *
		 * @return string licence option name
		 * @deprecated
		 */
		public function get_licence_option_name(): string {
			return 'yit_plugin_licence_activation';
		}

		/**
		 * Get The home url without protocol
		 *
		 * @return string The home url.
		 * @deprecated
		 */
		public function get_home_url(): string {
			return Utils::get_home_url();
		}

		/**
		 * If is a WP network, check licence globally for all blog and store in transient.
		 *
		 * @param string  $product_init The plugin init.
		 * @param boolean $activated True if licence is activated, false otherwise.
		 * @param string  $product_type The product type.
		 */
		public function check_global_license_for_all_blogs( string $product_init, bool $activated, string $product_type ) {
			empty( $this->network ) || $this->network->check_licences_data( $this->get_licence_option_name() );
		}

		/**
		 * Get the global licence information for all networks
		 *
		 * @return mixed Activation array check if exists. False otherwise
		 * @deprecated
		 */
		public function get_global_license_transient() {
			return ! empty( $this->network ) ? $this->network->get_licences_data() : false;
		}

		/**
		 * Save the global licence information in a transient
		 *
		 * @param array $data An array of data to set.
		 * @return void
		 * @deprecated
		 */
		public function set_global_license_transient( array $data = array() ) {
			empty( $this->network ) || $this->network->set_licences_data( $data );
		}

		/**
		 * Delete the global licence information in a transient
		 *
		 * @return void
		 * @deprecated
		 */
		public function delete_global_license_transient() {
			empty( $this->network ) || $this->network->delete_licences_data();
		}
	}
endif;

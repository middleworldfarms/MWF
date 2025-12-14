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

use YITH\PluginUpgrade\Admin\Banner;
use YITH\PluginUpgrade\Admin\Panel;
use YITH\PluginUpgrade\Legacy\Licences as LicencesLegacy;
use const ABSPATH;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

if ( ! class_exists( 'YITH\PluginUpgrade\Licences' ) ) :
	/**
	 * Licences main class.
	 *
	 * @since   5.0.0
	 * @package YITH/PluginUpgrade
	 */
	class Licences extends LicencesLegacy {

		/**
		 * The single instance of the class
		 *
		 * @since 5.0
		 * @var Licences
		 */
		protected static $instance = null;

		/**
		 * An array of licence types and its option name
		 *
		 * @since 5.0.0
		 * @var array
		 */
		protected $licence_types = array(
			'yith' => 'yit_plugin_licence_activation',
			'pls'  => 'yith_registered_pls_licences',
		);

		/**
		 * Main plugin Instance
		 *
		 * @since  5.0.0
		 * @return Licences Main instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Class constructor
		 *
		 * @since 5.0.0
		 * @return void
		 */
		protected function __construct() {
			$this->load_pls();
			$this->load_data();
			$this->load_onboarding();
			$this->load_network_handler();

			add_action( 'wp_loaded', array( $this, 'load_yith_admin' ), 9999 );
			add_action( 'shutdown', array( $this, 'save_licences' ) );
		}

		/**
		 * Plugin registration
		 *
		 * @since    5.0.0
		 * @param string $plugin_init The plugin init file.
		 * @param string $secret_key  The product secret key.
		 * @param string $product_id  The plugin slug (product_id).
		 * @return void
		 */
		public function register( string $plugin_init, string $secret_key, string $product_id ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$classname = $this->get_classname_from_product_id( $product_id );
			$data_key  = strtolower( str_replace( 'YITH\PluginUpgrade\Licences\\', '', $classname ) );
			$default   = array(
				'secret_key' => $secret_key,
				'product_id' => $product_id,
			);
			$data      = isset( $this->data[ $data_key ][ $product_id ] ) ? array_merge( $this->data[ $data_key ][ $product_id ], $default ) : $default;

			$this->licences[ $plugin_init ] = new $classname( $plugin_init, $data );
		}

		/**
		 * Load admin class if panel is needed
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public function load_yith_admin() {
			$yith_licences = $this->get_licences( 'yith' );
			if ( ! empty( $yith_licences ) && is_admin() ) {
				$this->panel = new Panel( $yith_licences );
				// Licence banner.
				Banner::init();
			}
		}

		/**
		 * Maybe load onboarding class
		 *
		 * @since  4.3.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function load_onboarding() {
			new Onboarding();
		}

		/**
		 * Maybe load onboarding class
		 *
		 * @since  4.3.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function load_network_handler() {
			if ( is_multisite() ) {
				$this->network = new Network();
			}
		}

		/**
		 * Get the admin panel instance
		 *
		 * @since 5.0.0
		 * @return Panel|null
		 */
		public function get_admin_panel(): Panel {
			return $this->panel;
		}

		/**
		 * Get the admin panel instance
		 *
		 * @since 5.0.0
		 * @return Network|null
		 */
		public function get_network_handler(): Network {
			return $this->network;
		}

		/**
		 * Get array products
		 *
		 * @since  5.0.0
		 * @return array
		 */
		public function get_products(): array {
			$products = get_plugins();
			// Get only registered plugins.
			$products = array_intersect_key( $products, $this->licences );
			array_walk(
				$products,
				function ( &$data, $plugin_init ) {
					$data['secret_key'] = $this->licences[ $plugin_init ]->secret_key;
					$data['product_id'] = $this->licences[ $plugin_init ]->product_id;
				}
			);

			return $products;
		}

		/**
		 * Get array of registered licences
		 *
		 * @since  5.0.0
		 * @param string $type (Optional )The licence type to retrieve. Default is empty string.
		 * @return array The licence by type or all licences if type is empty string
		 */
		public function get_licences( string $type = '' ): array {
			if ( empty( $this->licences ) ) {
				return array();
			}

			if ( empty( $type ) ) {
				return $this->licences;
			}

			return array_filter(
				$this->licences,
				function ( $licence ) use ( $type ) {
					return $type === $licence->get_type();
				}
			);
		}

		/**
		 * Get single registered licences
		 *
		 * @since  5.0.0
		 * @param string $init The licence plugin init to retrieve.
		 * @return Licence|null The licence requested
		 */
		public function get_single_licence( string $init ) {
			return $this->licences[ $init ] ?? null;
		}

		/**
		 * Save licences
		 *
		 * @since 5.0.0
		 * @return void
		 */
		public function save_licences() {
			foreach ( $this->licence_types as $type => $option_name ) {
				$licences_to_save = array();
				foreach ( $this->get_licences( $type ) as $licence ) {
					$licence_data = $licence->get_data();
					// Compare with current value.
					$must_be_saved = array_diff_assoc( $licence_data, $this->data[ $type ][ $licence->product_id ] ?? array() );
					if ( empty( $must_be_saved ) ) {
						continue;
					}

					$licences_to_save[ $licence->product_id ] = $licence_data;
				}

				if ( empty( $licences_to_save ) ) {
					continue;
				}

				$licences_to_save = array_merge( $this->data[ $type ], $licences_to_save );
				update_option( $option_name, $licences_to_save );

				do_action( "yith_plugin_upgrade_{$type}_licences_saved", $option_name, $licences_to_save );
			}
		}

		/**
		 * Get classname from product id
		 *
		 * @since 5.0.0
		 * @param string $product_id The product ID.
		 * @return string
		 */
		protected function get_classname_from_product_id( string $product_id ): string {
			return $this->get_classname_from_type( $this->is_pls_licence( $product_id ) ? 'pls' : 'yith' );
		}

		/**
		 * Get classname from licence type
		 *
		 * @since 5.0.0
		 * @param string $type The licence type.
		 * @return string
		 */
		protected function get_classname_from_type( string $type ): string {
			$type = strtoupper( $type );
			return "YITH\PluginUpgrade\Licences\\$type";
		}

		/**
		 * Check if plugin is pls
		 *
		 * @since 5.0.0
		 * @param string $product_id The product ID to check.
		 * @return boolean
		 */
		protected function is_pls_licence( string $product_id ): bool {
			return class_exists( '\NewfoldLabs\WP\PLS\PLS' ) && ! is_wp_error( \NewfoldLabs\WP\PLS\PLS::get_license_id( $product_id ) );
		}

		/**
		 * Maybe load PLS library
		 *
		 * @since 5.0.0
		 * @return void
		 */
		protected function load_pls() {

			// Make sure autoload file exist.
			if ( ! file_exists( YITH_PLUGIN_UPGRADE_PATH . '/vendor/autoload.php' ) ) {
				return;
			}

			if ( ! class_exists( '\NewfoldLabs\WP\PLS\PLS' ) ) {
				require_once YITH_PLUGIN_UPGRADE_PATH . '/vendor/autoload.php';
			}

			\NewfoldLabs\WP\PLS\PLS::config(
				array(
					'environment' => Debug::is_enabled() ? 'staging' : 'production',
				)
			);
		}

		/**
		 * Load licences array
		 *
		 * @since 5.0.0
		 * @return void
		 */
		protected function load_data() {
			// Build data array.
			foreach ( $this->licence_types as $type => $option_name ) {
				$this->data[ $type ] = get_option( $option_name, array() ) ?: array();
			}
		}
	}
endif;

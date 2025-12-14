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

use const HOUR_IN_SECONDS;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

if ( ! class_exists( 'YITH\PluginUpgrade\Onboarding' ) ) :
	/**
	 * Handle licence onboarding process.
	 *
	 * @since 5.0.0
	 */
	class Onboarding {

		/**
		 * Page slug
		 *
		 * @since 4.3.0
		 * @const string
		 */
		const PAGE_SLUG = 'yith-licence-activation';

		/**
		 * Processing product
		 *
		 * @since 4.3.0
		 * @var string
		 */
		protected $product = '';

		/**
		 * Constructor
		 *
		 * @since  4.3.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'create_page' ) );

			add_action( 'admin_init', array( $this, 'init' ), 1 );
			add_action( 'admin_init', array( $this, 'handle_redirect' ), 5 );
			add_action( 'admin_init', array( $this, 'output' ) );
		}

		/**
		 * Create the hidden onboarding page
		 *
		 * @since  4.3.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function create_page() {
			add_submenu_page(
				'',
				__( 'YITH Licence Activation', 'yith-plugin-upgrade-fw' ),
				__( 'YITH Licence Activation', 'yith-plugin-upgrade-fw' ),
				'activate_plugins',
				self::PAGE_SLUG
			);
		}

		/**
		 * Get onboarding queue
		 *
		 * @since  4.3.0
		 * @author Francesco Licandro
		 * @return array
		 */
		protected function get_onboarding_queue(): array {
			$queue = get_transient( 'yith_plugin_licence_onboarding_queue' );
			delete_transient( 'yith_plugin_licence_onboarding_queue' );

			if ( empty( $queue ) || ! is_array( $queue ) || wp_doing_ajax() || ( defined( 'WP_CLI' ) && \WP_CLI ) || apply_filters( 'yith_plugin_licence_onboarding_deactivate', false ) ) {
				$queue = array();
			}

			return apply_filters( 'yith_plugin_licence_onboarding_queue', $queue );
		}

		/**
		 * Maybe load onboarding class
		 *
		 * @since  4.3.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function init() {
			$onboarding_queue = $this->get_onboarding_queue();

			while ( ! empty( $onboarding_queue ) ) {
				$plugin_init = array_shift( $onboarding_queue );
				if ( ! $this->is_onboarding_valid( $plugin_init ) ) {
					continue;
				}

				$licence = Licences::instance()->get_single_licence( $plugin_init );
				if ( ! empty( $licence ) && ! get_transient( "{$licence->product_id}_onboarding_processed" ) ) {
					$this->product = $licence->product_id;
					break;
				}
			}
		}

		/**
		 * Handle redirect to start the onboarding process
		 *
		 * @since  4.3.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function handle_redirect() {

			if ( empty( $this->product ) ) {
				return;
			}

			// Redirect to onboarding page.
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'   => self::PAGE_SLUG,
						'plugin' => $this->product,
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}

		/**
		 * Output onboarding process
		 *
		 * @since  4.3.0
		 * @author Francesco Licandro
		 * @return void
		 */
		public function output() {

			if ( empty( $_GET['page'] ) || empty( $_GET['plugin'] ) || self::PAGE_SLUG !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {  // phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			$product_id = sanitize_text_field( wp_unslash( $_GET['plugin'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$products   = Licences::instance()->get_products();
			// Get plugin by product ID and check if is a valid YITH plugin.
			foreach ( $products as $init => $data ) {
				if ( isset( $data['product_id'] ) && $product_id === $data['product_id'] ) {
					$plugin_init = $init;
					break;
				}
			}

			if ( empty( $plugin_init ) || ! $this->is_onboarding_valid( $plugin_init ) ) { // Double check if the onboarding is valid.
				wp_safe_redirect( admin_url( 'plugins.php' ) );
				exit;
			}

			set_transient( "{$product_id}_onboarding_processed", 'yes', 2 * HOUR_IN_SECONDS );

			$this->register_assets();
			// Set template args.
			$return_url = apply_filters( 'yith_licence_onboarding_return_url', admin_url( 'plugins.php' ), $plugin_init, $product_id );
			$assets_url = plugin_dir_url( __DIR__ ) . 'assets/';
			$product    = $products[ $plugin_init ];

			include plugin_dir_path( __DIR__ ) . 'templates/onboarding/onboarding-activation.php';
			exit;
		}

		/**
		 * Check if onboarding is valid for plugin
		 *
		 * @since  4.3.0
		 * @author Francesco Licandro
		 * @param string $plugin_init The plugin init to check.
		 * @return boolean
		 */
		protected function is_onboarding_valid( string $plugin_init ): bool {
			$licence = Licences::instance()->get_single_licence( $plugin_init );
			/**
			 * Conditions:
			 * 1. current user can manage plugins
			 * 2. plugin is not active for network
			 * 3. plugin licence is YITH
			 * 4. plugin licence is not active for site
			 */
			return current_user_can( 'activate_plugins' ) && ! is_plugin_active_for_network( $plugin_init ) && ! empty( $licence ) && 'yith' === $licence->get_type() && ! $licence->is_activated();
		}

		/**
		 * Register onboarding assets
		 *
		 * @since  4.3.0
		 * @author Francesco Licandro
		 * @return void
		 */
		protected function register_assets() {

			// Load plugin FW assets.
			if ( class_exists( 'YIT_Assets' ) ) {
				\YIT_Assets::instance()->register_common_scripts();
				\YIT_Assets::instance()->register_styles_and_scripts();
			}

			$min = ( defined( 'SCRIPT_DEBUG' ) && \SCRIPT_DEBUG ) ? '' : '.min';
			wp_register_script( 'yith-licence-onboarding-js', YITH_PLUGIN_UPGRADE_URL . "/assets/js/onboarding$min.js", array( 'jquery' ), YITH_PLUGIN_UPGRADE_VERSION, true );
			wp_localize_script(
				'yith-licence-onboarding-js',
				'onboardingJS',
				array(
					'ajaxUrl'     => admin_url( 'admin-ajax.php', 'relative' ),
					'ajaxAction'  => 'yith_licence_ajax_request',
					'ajaxNonce'   => wp_create_nonce( 'yith_licence_ajax_request' ),
					// translators: $s stand for the field name.
					'error'       => sprintf( esc_html_x( 'Please, insert a valid %s', '%s = field name', 'yith-plugin-upgrade-fw' ), '%field%' ),
					'server'      => esc_html__( 'Unable to contact remote server: this occurs when there are issues with connecting to our own servers. Trying again after a few minutes should solve the issue. If the problem persists please submit a support ticket and we will be happy to help you.', 'yith-plugin-upgrade-fw' ),
					'email'       => esc_html__( 'email address', 'yith-plugin-upgrade-fw' ),
					'licence_key' => esc_html__( 'licence key', 'yith-plugin-upgrade-fw' ),
				)
			);
			wp_register_style( 'yith-licence-onboarding-css', YITH_PLUGIN_UPGRADE_URL . '/assets/css/onboarding.css', array(), YITH_PLUGIN_UPGRADE_VERSION );
		}
	}
endif;

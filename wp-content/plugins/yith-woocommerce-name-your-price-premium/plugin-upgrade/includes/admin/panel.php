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

use Exception;
use YITH\PluginUpgrade\Licence;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

if ( ! class_exists( 'YITH\PluginUpgrade\Admin\Panel' ) ) :
	/**
	 * Admin Panel class.
	 *
	 * @since 5.0.0
	 */
	class Panel {

		/**
		 * Licences array
		 *
		 * @since 5.0.0
		 * @var array
		 */
		protected $licences = array();

		/**
		 * Panel slug
		 *
		 * @const string
		 */
		const PANEL_SLUG = 'yith_plugins_activation';

		/**
		 * Class construct
		 *
		 * @since 5.0.0
		 * @param array $licences The licences for panel.
		 * @return void
		 */
		public function __construct( array $licences ) {
			$this->licences = $licences;

			add_action( 'admin_menu', array( $this, 'add_page' ), 99 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts' ), 20 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 100 );
			add_action( 'admin_init', array( $this, 'licence_check' ) );
			// Handle AJAX request.
			add_action( 'wp_ajax_yith_licence_ajax_request', array( $this, 'handle_ajax_request' ) );
		}

		/**
		 * Add "Activation" page under YITH Plugins or as single page
		 *
		 * @since  5.0.0
		 * @return void
		 */
		public function add_page() {
			global $menu;

			foreach ( $menu as $single_menu ) {
				if ( 'yith_plugin_panel' === $single_menu[2] ) {
					$is_yith_parent_page_registered = true;
					break;
				}
			}

			$licences_to_activate = count( $this->get_licences_to_activate() );
			$bubble               = ! empty( $licences_to_activate ) ? " <span data-count='$licences_to_activate' id='yith-licence-to-activate-count' class='awaiting-mod count-$licences_to_activate'><span class='expired-count'>$licences_to_activate</span></span>" : '';

			$title = __( 'License Panel', 'yith-plugin-upgrade-fw' );
			$args  = array(
				'manage_options',
				self::PANEL_SLUG,
				array( $this, 'output_panel' ),
			);

			if ( ! empty( $is_yith_parent_page_registered ) ) {
				add_submenu_page( 'yith_plugin_panel', $title, $title . $bubble, ...$args );
			} else {
				add_menu_page( $title, 'YITH' . $bubble, ...$args );
			}
		}

		/**
		 * Return panel page url
		 *
		 * @since 5.0.0
		 * @param array $params An array of additional params to add in the url.
		 * @return string
		 */
		public function get_url( array $params = array() ): string {
			$params = array_merge( $params, array( 'page' => self::PANEL_SLUG ) );
			return add_query_arg( $params, admin_url( 'admin.php' ) );
		}

		/**
		 * Include activation page template
		 *
		 * @since  5.0.0
		 * @return void
		 */
		public function output_panel() {
			$activated_licences   = $this->get_licences_activated();
			$licences_to_activate = $this->get_licences_to_activate();
			$upsell_products      = $this->get_upsell_products();

			// Check if there are expired licences. This is useful to print the update status notice.
			$have_expired_licences = ! empty(
				array_filter(
					$activated_licences,
					function ( Licence $licence ) {
						return $licence->is_expired();
					}
				)
			);

			$licence_check_status_url = $this->get_url(
				array(
					'action'   => 'yith-licences-check',
					'security' => wp_create_nonce( 'yith-licences-check' ),
				)
			);

			include YITH_PLUGIN_UPGRADE_PATH . '/templates/panel/activation-panel.php';
		}

		/**
		 * Include activation row template
		 *
		 * @since  5.0.0
		 * @param string $init The licence init key to print.
		 * @return void
		 */
		public function output_panel_activation_row( string $init ) {
			$licence = $this->licences[ $init ] ?? null;
			if ( empty( $licence ) ) {
				return;
			}

			include YITH_PLUGIN_UPGRADE_PATH . '/templates/panel/activation-row.php';
		}

		/**
		 * Include activation page template
		 *
		 * @since  5.0.0
		 * @param string $init The licence init key to print.
		 * @return void
		 */
		public function output_panel_activation_form( string $init ) {
			$licence = $this->licences[ $init ] ?? null;
			if ( empty( $licence ) ) {
				return;
			}

			include YITH_PLUGIN_UPGRADE_PATH . '/templates/panel/activation-form.php';
		}

		/**
		 * Handle panel ajax request
		 *
		 * @since 5.0.0
		 * @return void
		 * @throws Exception Errors on AJAX request handling.
		 */
		public function handle_ajax_request() {
			try {

				if ( ! isset( $_REQUEST['action'], $_REQUEST['security'], $_REQUEST['request'] )
					|| ! wp_verify_nonce( sanitize_text_field( $_REQUEST['security'] ), 'yith_licence_ajax_request' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					throw new Exception( 'Unauthorized', 400 );
				}

				$method = sanitize_text_field( wp_unslash( $_REQUEST['request'] ) );
				if ( ! method_exists( $this, $method ) ) {
					throw new Exception( sprintf( 'Invalid request %s', $method ), 400 );
				}

				$this->$method();

				wp_send_json_success();

			} catch ( Exception $e ) {
				wp_send_json_error( array( 'message' => $e->getMessage() ), $e->getCode() );
			}
		}

		/**
		 * Activate a licence from panel
		 *
		 * @since 5.0.0
		 * @return void
		 * @throws Exception Errors on licence activation process.
		 */
		protected function licence_activation() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! isset( $_POST['licence_key'], $_POST['email'], $_POST['product_init'] ) ) {
				throw new Exception( 'Required param missing', 400 );
			}

			$plugin_init = sanitize_text_field( $_POST['product_init'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$licence     = $this->licences[ $plugin_init ] ?? null;
			if ( empty( $licence ) ) {
				throw new Exception( 'Requested product is not available', 400 );
			}

			$licence->activate(
				array(
					'email'       => sanitize_email( wp_unslash( $_POST['email'] ) ),
					'licence_key' => sanitize_text_field( wp_unslash( $_POST['licence_key'] ) ),
				)
			);

			ob_start();
			$this->output_panel_activation_row( $plugin_init );
			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html ) );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Deactivate a licence from panel
		 *
		 * @since 5.0.0
		 * @return void
		 * @throws Exception Errors on licence activation process.
		 */
		protected function licence_deactivation() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! isset( $_POST['product_init'] ) ) {
				throw new Exception( esc_html__( 'Required param missing', 'yith-plugin-upgrade-fw' ), 400 );
			}

			$plugin_init = sanitize_text_field( $_POST['product_init'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$licence     = $this->licences[ $plugin_init ] ?? null;

			if ( empty( $licence ) ) {
				throw new Exception( esc_html__( 'Requested product is not available', 'yith-plugin-upgrade-fw' ), 400 );
			}

			$licence->deactivate();

			ob_start();
			$this->output_panel_activation_form( $plugin_init );
			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html ) );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Check licences status from panel
		 *
		 * @since 5.0.0
		 * @return void
		 * @throw Exception Errors on licence activation process
		 */
		public function licence_check() {

			if ( ! isset( $_GET['action'], $_GET['security'] ) ||
				'yith-licences-check' !== sanitize_text_field( wp_unslash( $_GET['action'] ) ) ||
				! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['security'] ) ), 'yith-licences-check' )
			) {
				return;
			}

			foreach ( $this->get_licences_activated() as $licence ) {
				try {
					$licence->check( array(), true );
				} catch ( Exception $e ) {
					// catch exceptions to let all licences check its status.
				}
			}

			wp_safe_redirect( $this->get_url() );
			exit;
		}

		/**
		 * Get an array of licence to activate
		 *
		 * @since 5.0.0
		 * @return array
		 */
		protected function get_licences_activated(): array {
			return array_filter(
				$this->licences,
				function ( $licence ) {
					return ! ! $licence->licence_key;
				}
			);
		}

		/**
		 * Get an array of licence to activate
		 *
		 * @since 5.0.0
		 * @return array
		 */
		protected function get_licences_to_activate(): array {
			return array_diff_key( $this->licences, $this->get_licences_activated() );
		}

		/**
		 * Get an array of upsell products to add in panel
		 * Remote request to yithemes.com, cached.
		 *
		 * @since 5.0.0
		 * @return array
		 */
		public function get_upsell_products(): array {

			$software_ids = implode( ',', wp_list_pluck( $this->licences, 'product_id' ) );
			$transient    = 'yith_plugin_licence_upsell_' . md5( $software_ids );
			$products     = get_site_transient( $transient );

			if ( false === $products ) {

				$response = wp_remote_get(
					'https://yithemes.com/wp-json/wc/v3/upsells-data',
					array(
						'body' => array(
							'lang'         => get_locale(),
							'software_ids' => $software_ids,
						),
					)
				);

				// Store response in transient.
				$products = ( is_wp_error( $response ) || empty( $response['body'] ) ) ? array() : json_decode( $response['body'], true );
				$products = ! is_array( $products ) ? array() : $products;
				$products = array_map(
					function ( $product ) {
						$product['permalink'] = add_query_arg(
							array(
								'utm_source'   => 'wp-premium-dashboard',
								'utm_medium'   => 'license-activation-panel',
								'utm_campaign' => 'license-panel-upsell',
							),
							$product['permalink']
						);
						return $product;
					},
					$products
				);
				set_site_transient( $transient, $products, empty( $products ) ? HOUR_IN_SECONDS : ( 48 * HOUR_IN_SECONDS ) );
			}

			return $products;
		}

		/**
		 * Admin register panel scripts
		 *
		 * @since  5.0.0
		 * @return void
		 */
		public function admin_register_scripts() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && \SCRIPT_DEBUG ? '' : '.min';

			if ( ! wp_script_is( 'yith-ui', 'registered' ) ) {
				wp_register_style( 'yith-plugin-fw-icon-font', YITH_PLUGIN_UPGRADE_URL . '/assets/css/yith-icon.css', array(), YITH_PLUGIN_UPGRADE_VERSION );
				wp_register_style( 'yith-plugin-ui', YITH_PLUGIN_UPGRADE_URL . '/assets/css/yith-plugin-ui.css', array( 'yith-plugin-fw-icon-font' ), YITH_PLUGIN_UPGRADE_VERSION );
				wp_register_script( 'yith-ui', YITH_PLUGIN_UPGRADE_URL . "/assets/js/yith-ui$suffix.js", array( 'jquery' ), YITH_PLUGIN_UPGRADE_VERSION, true );

				wp_localize_script(
					'yith-ui',
					'yith_plugin_fw_ui',
					array(
						'i18n' => array(
							'confirm' => _x( 'Confirm', 'Button text', 'yith-plugin-upgrade-fw' ),
							'cancel'  => _x( 'Cancel', 'Button text', 'yith-plugin-upgrade-fw' ),
						),
					)
				);
			}

			// Register style.
			wp_register_style( 'yith-licence-panel', YITH_PLUGIN_UPGRADE_URL . '/assets/css/panel.css', array( 'yith-plugin-ui' ), YITH_PLUGIN_UPGRADE_VERSION );

			// Register scrips.
			if ( ! wp_script_is( 'jquery-blockui', 'registered' ) ) {
				wp_register_script( 'jquery-blockui', YITH_PLUGIN_UPGRADE_URL . "/assets/js/jquery-blockui/jquery.blockUI$suffix.js", array( 'jquery' ), '2.70', true );
			}
			wp_register_script( 'yith-licence-panel', YITH_PLUGIN_UPGRADE_URL . "/assets/js/panel$suffix.js", array( 'jquery', 'jquery-blockui', 'yith-ui' ), YITH_PLUGIN_UPGRADE_VERSION, true );

			// translators: You can find the License e-mail and the License key in your License & Download page.
			$modal_description_text = esc_html__( 'You can find the License e-mail and the License key in your', 'yith-plugin-upgrade-fw' );
			// translators: My account section name. Use the same translation of the yithemes.com website.
			$licence_and_download     = esc_html_x( 'Licenses and Downloads page >', 'yithemes.com website section', 'yith-plugin-upgrade-fw' );
			$licence_and_download_url = 'https://yithemes.com/my-account/recent-downloads/';
			$modal_description        = sprintf( '<div class="yith-license-modal-description">%s <a href="%s" target="_blank" rel="nofollow noopener">%s</a></div>', $modal_description_text, $licence_and_download_url, $licence_and_download );
			$modal_image_src          = YITH_PLUGIN_UPGRADE_URL . '/assets/images/license-and-downloads.png';
			// translators: Button label, use a short text please.
			$cta_text      = esc_html_x( 'Go to your Licenses page', 'Button text', 'yith-plugin-upgrade-fw' );
			$cta           = sprintf( '<a href="%s" target="_blank" rel="nofollow noopener" class="yith-license-modal-cta-link yith-plugin-fw__button--primary yith-plugin-fw__button--xxl">%s</a>', $licence_and_download_url, $cta_text );
			$modal_image   = sprintf( '<div class="yith-license-modal-image"><img src="%s" /></div>', $modal_image_src );
			$modal_content = $modal_description . $modal_image;

			wp_localize_script(
				'yith-licence-panel',
				'yithLicenceData',
				array(
					'ajaxUrl'                  => admin_url( 'admin-ajax.php', 'relative' ),
					'ajaxAction'               => 'yith_licence_ajax_request',
					'ajaxNonce'                => wp_create_nonce( 'yith_licence_ajax_request' ),
					'errors'                   => array(
						// translators: %s is a placeholder for an HTML tag.
						'email'       => sprintf( esc_html_x( 'Please, insert a valid email address.%sBe sure you entered the e-mail used for your order in YITH', 'error message for activation panel', 'yith-plugin-upgrade-fw' ), '</br>' ),
						'licence_key' => esc_html_x( 'Please, insert a valid license key', 'error message for activation panel', 'yith-plugin-upgrade-fw' ),
					),
					'deactivationConfirmTitle' => esc_html__( 'Confirm action', 'yith-plugin-upgrade-fw' ),
					'deactivationConfirm'      => esc_html_x( 'Are you sure you want to deactivate the {{plugin_name}} license for current site?', '{{plugin_name}} is a placeholder for the plugin name', 'yith-plugin-upgrade-fw' ),
					'modal'                    => array(
						// translators: Modal title. Shown on activation license page to help the customer to find the license information.
						'title'   => esc_html__( 'Where to find the e-mail and license ?', 'yith-plugin-upgrade-fw' ),
						'content' => $modal_content,
						'footer'  => $cta,
					),
				)
			);
		}

		/**
		 * Admin enqueue scripts
		 *
		 * @since  5.0.0
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			// Enqueue scripts only in Licence Activation page of plugins and themes.
			$current_screen = get_current_screen();
			if ( empty( $current_screen ) || false === strpos( strval( $current_screen->id ), self::PANEL_SLUG ) ) {
				return;
			}

			wp_enqueue_script( 'yith-licence-panel' );
			wp_enqueue_style( 'yith-licence-panel' );
		}
	}
endif;

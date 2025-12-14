<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Name Your Price Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WC_Name_Your_Price_Multivendor_Compatibility' ) ) {
	/**
	 * Multivendor Compatibility Class
	 *
	 * @class   YITH_WC_Name_Your_Price_Multivendor_Compatibility
	 * @package YITHEMES
	 * @since   1.0.2
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_WC_Name_Your_Price_Multivendor_Compatibility {


		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WC_Name_Your_Price_Multivendor_Compatibility
		 * @since 1.0.2
		 */
		protected static $instance;

		/**
		 * Vendor object 
		 * 
		 * @var YITH_Vendor
		 */
		protected $vendor;


		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WC_Surveys_Multivendor_Compatibility
		 * @since 1.0.2
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @access public
		 * @since  1.0.2
		 */
		public function __construct() {

			if ( ! YITH_WC_Name_Your_Price_Compatibility::has_multivendor_plugin() ) {
				return;
			}

			$this->vendor = function_exists( 'yith_wcmv_get_vendor' ) ? yith_wcmv_get_vendor( 'current', 'user' ) : yith_get_vendor( 'current', 'user' );

			if ( $this->vendor->is_valid() && $this->vendor->has_limited_access() && ywcnp_is_multivendor_name_your_price_enabled() ) {

				add_action( 'admin_menu', array( $this, 'add_name_your_price_tab_for_vendor' ) );
				require_once YWCNP_TEMPLATE_PATH . 'admin/vendor/ywcnp-category-vendor-rules.php';
				add_action( 'yith_wc_name_your_price_category_vendor_rules', array( YWCNP_Category_Vendor_Rule(), 'output' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

				if ( version_compare( YITH_WPV_VERSION, '4.0.0', '>=' ) ) {

					add_filter( 'yith_wcmv_admin_vendor_menu_items', array( $this, 'add_menu_in_vendor_dashboard' ), 20 );
				}
			}

		}

		/**
		 * Enqueue_scripts
		 *
		 * @return void
		 */
		public function enqueue_scripts() {
			wp_enqueue_script( 'selectWoo' );
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_style( 'woocommerce_admin_styles' );

			$css = ".ywcnp_product_list .select2.select2-container{
				min-width:200px!important;
			}";

			wp_add_inline_style( 'woocommerce_admin_styles', $css );

		}

		/**
		 * Add_name_your_price_tab_for_vendor
		 *
		 * @since 1.0.2
		 */
		public function add_name_your_price_tab_for_vendor() {

			$tabs['category-rules-vendor'] = __( 'Active Rules', 'yith-woocommerce-name-your-price' );
			$vendor_id                     = version_compare( YITH_WPV_VERSION, '4.0.0', '>=' ) ? $this->vendor->get_id() : $this->vendor->id;
			$args                          = array(
				'create_menu_page' => false,
				'parent_slug'      => '',
				'page_title'       => 'YITH WooCommerce Name Your Price',
				'menu_title'       => 'Name Your Price',
				'capability'       => 'manage_vendor_store',
				'parent'           => 'vendor_' . $vendor_id,
				'parent_page'      => '',
				'page'             => 'yith_vendor_nyp_settings',
				'admin-tabs'       => $tabs,
				'options-path'     => YWCNP_DIR . 'plugin-options/vendor',
				'icon_url'         => 'dashicons-admin-plugins',
				'position'         => 40,
				'class'            => yith_set_wrapper_class(),
			);

			/* === Fixed: not updated theme/old plugin framework  === */
			if ( ! class_exists( 'YIT_Plugin_Panel' ) ) {
				require_once YWCNP_DIR . 'plugin-fw/lib/yit-plugin-panel.php';
			}

			$this->_vendor_panel = new YIT_Plugin_Panel( $args );
		}

		/**
		 * Add menu item in Vendor dashboard ( MV 4.0 )
		 *
		 * @param array $items The items.
		 *
		 * @return array
		 * @since 1.11.0
		 */
		public function add_menu_in_vendor_dashboard( $items ) {

			$items[] = 'yith_vendor_nyp_settings';

			return $items;
		}

	}
}

/**
 * Unique access to instance of YITH_WC_Name_Your_Price_Multivendor_Compatibility class
 *
 * @return YITH_WC_Name_Your_Price_Multivendor_Compatibility
 * @since 1.0.0
 */
function YITH_Name_Your_Price_Multivendor_Compatibility() { // phpcs:ignore WordPress.NamingConventions
	return YITH_WC_Name_Your_Price_Multivendor_Compatibility::get_instance();
}

YITH_Name_Your_Price_Multivendor_Compatibility();

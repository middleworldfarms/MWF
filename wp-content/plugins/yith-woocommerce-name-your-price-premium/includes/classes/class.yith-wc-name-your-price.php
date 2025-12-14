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
}

if ( ! class_exists( 'YITH_WooCommerce_Name_Your_Price' ) ) {
	/**
	 * Class YITH_WooCommerce_Name_Your_Price
	 */
	class YITH_WooCommerce_Name_Your_Price {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WooCommerce_Name_Your_Price, single instance
		 */
		protected static $instance;

		/**
		 * YITH WooCommerce Name Your Price Premium Panel object
		 *
		 * @var YIT_Plugin_Panel_Woocommerce instance
		 */
		protected $panel;

		/**
		 * YITH WooCommerce Name Your Price Premium panel page
		 *
		 * @var string $panel_page
		 */
		protected $panel_page = 'yith_wcnp_panel';


		/**
		 * YITH WooCommerce Name Your Price Premium Premium page
		 *
		 * @var string Premium page
		 */
		protected $premium = 'premium.php';


		/**
		 * __construct function
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function __construct() {

			/* Plugin Informations */
			add_filter(
				'plugin_action_links_' . plugin_basename( YWCNP_DIR . '/' . basename( YWCNP_FILE ) ),
				array(
					$this,
					'action_links',
				)
			);
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );
			add_action( 'yith_wc_name_your_price_premium', array( $this, 'show_premium_tab' ) );

			// Replace default price with minimum name your price !
			add_filter( 'woocommerce_get_price_html', array( $this, 'get_nameyourprice_price_html' ), 20, 2 );

			/*Add Name Your Price in YITH PLUGIN*/
			add_action( 'admin_menu', array( $this, 'add_name_your_price_menu' ), 5 );

			// Load Admin Class !
			if ( is_admin() && ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ! isset( $_REQUEST['action'] ) || 'yith_wacp_add_item_cart' !== $_REQUEST['action'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

				YITH_Name_Your_Price_Admin();
			} else {
				// Load FrontEnd Class !
				YITH_Name_Your_Price_Frontend();
			}

			// Set product as purchasable !
			add_filter( 'woocommerce_is_purchasable', array( $this, 'ywcnp_is_purchasable' ), 20, 2 );
			add_filter( 'woocommerce_variation_is_purchasable', array( $this, 'ywcnp_is_purchasable' ), 20, 2 );
			add_filter( 'woocommerce_product_is_on_sale', array( $this, 'ywcnp_is_on_sale' ), 20, 2 );
		}

		/**
		 * Ywcnp_is_purchasable
		 *
		 * @param mixed      $purchasable purchasable.
		 * @param WC_Product $product product.
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function ywcnp_is_purchasable( $purchasable, $product ) {

			$product_id = $product->get_id();

			$product_type_supported = ywcnp_get_product_type_allowed();

			if ( $product->is_type( $product_type_supported ) && ywcnp_product_is_name_your_price( $product ) ) {

				return true;
			}

			return $purchasable;

		}

		/**
		 * Ywcnp_is_on_sale
		 *
		 * @param mixed      $on_sale on sale.
		 * @param WC_Product $product product.
		 *
		 * @return bool
		 * @since 1.0.3
		 */
		public function ywcnp_is_on_sale( $on_sale, $product ) {

			$product_id = $product->get_id();

			$product_type_supported = ywcnp_get_product_type_allowed();

			if ( $product->is_type( $product_type_supported ) && ywcnp_product_is_name_your_price( $product ) ) {
				return false;
			}

			return $on_sale;
		}

		/**
		 * Return single instance
		 *
		 * @return YITH_WooCommerce_Name_Your_Price
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Action Links
		 *
		 * Add the action links to plugin admin page
		 *
		 * @param  array $links | links plugin array.
		 *
		 * @return   mixed Array
		 * @use plugin_action_links_{$plugin_file_name}
		 * @since    1.0
		 */
		public function action_links( $links ) {
			$is_premium = defined( 'YWCNP_PREMIUM' );
			$links      = yith_add_action_links( $links, $this->panel_page, $is_premium, YWCNP_SLUG );

			return $links;
		}

		/**
		 * Plugin_row_meta
		 *
		 * @param mixed  $new_row_meta_args new_row_meta_args.
		 * @param mixed  $plugin_meta plugin_meta.
		 * @param mixed  $plugin_file plugin_file.
		 * @param mixed  $plugin_data plugin_data.
		 * @param mixed  $status status.
		 * @param string $init_file init file.
		 *
		 * @return   array
		 * @since    1.0.0
		 * @use $new_row_meta_args
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWCNP_FREE_INIT' ) {

			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YWCNP_SLUG;

			}

			return $new_row_meta_args;
		}


		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function show_premium_tab() {
			$premium_tab_template = YWCNP_TEMPLATE_PATH . '/admin/' . $this->premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once $premium_tab_template;
			}
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @use     /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function add_name_your_price_menu() {
			if ( ! empty( $this->panel ) ) {
				return;
			}

			$admin_tabs['general-settings'] = __( 'General Settings', 'yith-woocommerce-name-your-price' );

			if ( ! defined( 'YWCNP_PREMIUM' ) ) {
				$admin_tabs['premium-landing'] = __( 'Premium Version', 'yith-woocommerce-name-your-price' );
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'plugin_slug'      => YWCNP_SLUG,
				'page_title'       => 'YITH WooCommerce Name Your Price',
				'menu_title'       => 'Name Your Price',
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yith_plugin_panel',
				'page'             => $this->panel_page,
				'admin-tabs'       => apply_filters( 'ywcnp_add_premium_tab', $admin_tabs ),
				'options-path'     => YWCNP_DIR . '/plugin-options',
				'class'            => yith_set_wrapper_class(),
				'is_premium'       => true,
			);

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Print the minimum price html
		 *
		 * @param  float      $price price.
		 * @param WC_Product $product product.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function get_nameyourprice_price_html( $price, $product ) {

			$product_type_supported = ywcnp_get_product_type_allowed();
			if ( $product->is_type( $product_type_supported ) && ywcnp_product_is_name_your_price( $product ) ) {

				$price = '';

				/**
				 * APPLY_FILTERS: ywcnp_get_product_price_html
				 *
				 * filter product price html.
				 *
				 * @param string $price
				 * @param WC_Product $product
				 */
				return apply_filters( 'ywcnp_get_product_price_html', $price, $product );
			} else {
				return $price;
			}

		}


	}
}

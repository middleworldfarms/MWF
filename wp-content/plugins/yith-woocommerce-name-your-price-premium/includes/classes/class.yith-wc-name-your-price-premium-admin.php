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

if ( ! class_exists( 'YITH_WC_Name_Your_Price_Premium_Admin' ) ) {
	/**
	 * Implement premium admin features
	 * Class YITH_WC_Name_Your_Price_Admin
	 */
	class YITH_WC_Name_Your_Price_Premium_Admin extends YITH_WC_Name_Your_Price_Admin {


		/**
		 *  Single instance of the class
		 *
		 * @var YITH_WC_Name_Your_Price_Premium_Admin , single instance
		 */
		protected static $instance;

		/**
		 * __construct function
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function __construct() {

			parent::__construct();

			remove_action( 'woocommerce_product_options_pricing', array( $this, 'add_option_general_product_data' ) );
			// Add premium script !
			add_action( 'admin_enqueue_scripts', array( $this, 'include_premium_scripts' ) );
			// Add premium style !
			add_action( 'admin_enqueue_scripts', array( $this, 'include_premium_styles' ) );

			// Print category rule table !
			add_action( 'yith_wc_name_your_price_category_rules', array( YWCNP_Category_Rule(), 'output' ) );

			// Add name your price tab in single product !
			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'yith_name_your_price_tab_content' ) );

			// Save product meta for simple product !
			add_filter( 'ywcnp_add_premium_single_meta', array( $this, 'save_product_meta' ), 10, 2 );

			// Manage product variation with nameyourprice !
			add_action( 'woocommerce_variation_options', array( $this, 'add_name_your_price_variation_option' ), 20, 3 );
			add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'add_name_your_price_variable_attributes' ), 13, 3 );
			add_action( 'woocommerce_save_product_variation', array( $this, 'save_product_variation_meta' ), 10, 2 );

			// Enable or disable name your price option in product !
			add_filter( 'ywcnp_product_name_your_price_option_enabled', array( $this, 'filter_name_your_price_option_enabled' ), 10, 2 );

		}

		/**
		 * Enable or disable name your price option in product
		 *
		 * @param bool $enabled enabled.
		 * @param int  $product_id the id of the product.
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public function filter_name_your_price_option_enabled( $enabled, $product_id ) {
			if ( ywcnp_is_multivendor_active() ) {
				$vendor = yith_get_vendor( 'current', 'user' );
				if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
					$product = wc_get_product( $product_id );
					if ( ! ywcnp_is_multivendor_name_your_price_enabled() && ! ywcnp_product_is_name_your_price( $product ) ) {
						return false;
					} else {
						return true;
					}
				}
			}

			return $enabled;
		}

		/**
		 * Include premium scripts
		 *
		 * @since 1.0.0
		 */
		public function include_premium_scripts() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'ywcnp_premium_script', YWCNP_ASSETS_URL . 'js/ywcnp_premium_admin' . $suffix . '.js', array( 'jquery' ), YWCNP_VERSION, true );
		}

		/**
		 * Include premium styles
		 *
		 * @since 1.0.0
		 */
		public function include_premium_styles() {

			wp_enqueue_style( 'ywcnp_admin_style', YWCNP_ASSETS_URL . 'css/ywcnp_admin_style.css', array(), YWCNP_VERSION );
		}


		/**
		 * Add name your price tab
		 *
		 * @param array $product_data_tabs product data tabs.
		 * @return array
		 */
		public function yith_name_your_price_tab( $product_data_tabs ) {

			$product_data_tabs ['nameyourprice'] = array(
				'label'  => __( 'Name Your Price', '' ),
				'target' => 'ywcnp_nameyourprice_data',
				'class'  => array( 'show_if_simple', 'show_if_grouped', 'show_if_nameyourprice' ),
			);

			return $product_data_tabs;
		}

		/**
		 * Print custom tab in product data
		 *
		 * @since 1.0.0
		 */
		public function yith_name_your_price_tab_content() {

			$vendor_dir = '';

			if ( ywcnp_is_multivendor_active() ) {

				$vendor = yith_get_vendor( 'current', 'user' );

				if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
					$vendor_dir = 'vendor/';
				}
			}

			ob_start();

			wc_get_template( 'metaboxes/' . $vendor_dir . 'name_your_price_tab.php', array(), '', YWCNP_TEMPLATE_PATH );
			$template = ob_get_contents();

			ob_end_clean();

			echo $template; // phpcs:ignore WordPress.Security.EscapeOutput

		}

		/**
		 * Add premium meta data
		 *
		 * @since 1.0.0
		 * @param array  $product_meta product meta.
		 * @param string $product_id product id..
		 * @return mixed
		 */
		public function save_product_meta( $product_meta, $product_id ) {

			$product_meta['_ywcnp_simple_suggest_price'] = isset( $_REQUEST['ywcnp_simple_suggest_price'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_simple_suggest_price'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
			$product_meta['_ywcnp_simple_min_price']     = isset( $_REQUEST['ywcnp_simple_min_price'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_simple_min_price'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
			$product_meta['_ywcnp_simple_max_price']     = isset( $_REQUEST['ywcnp_simple_max_price'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_simple_max_price'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
			$product_meta['_ywcnp_simple_is_override']   = isset( $_REQUEST['ywcnp_simple_is_override'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_simple_is_override'] ) ) : 'no'; //phpcs:ignore WordPress.Security.NonceVerification

			$product = wc_get_product( $product_id );

			if ( $product->is_type( 'variable' ) ) {
				$is_name_your_price = false;
				$children           = $product->get_children();
				foreach ( $children as $child_id ) {
					$child = wc_get_product( $child_id );
					if ( ywcnp_product_is_name_your_price( $child ) ) {
						$is_name_your_price = true;
						break;
					}
				}

				$product->update_meta_data( '_variation_has_nameyourprice', $is_name_your_price );
				$product->save();

			}

			return $product_meta;
		}

		/**
		 * Add_name_your_price_variation_option
		 *
		 * @since 1.0.0
		 * @param mixed                $loop loop.
		 * @param WC_Product_Variation $variation_data variation data.
		 * @param WP_Post              $variation variation.
		 */
		public function add_name_your_price_variation_option( $loop, $variation_data, $variation ) {

			$product_id = $variation->ID;
			/**
			 * APPLY_FILTERS: ywcnp_product_name_your_price_option_enabled
			 *
			 * Filter to show or not the Name Your Price option in product data header for variation.
			 *
			 * @param bool $value default is true
			 * @param int  $post_id
			 */
			$enabled = apply_filters( 'ywcnp_product_name_your_price_option_enabled', true, $product_id );
			if ( ! $enabled ) {
				return;
			}

			$args         = array(
				'loop'           => $loop,
				'variation_data' => $variation_data,
				'variation'      => $variation,
			);
			$args['args'] = $args;

			ob_start();
			wc_get_template( 'metaboxes/name_your_price_variation_option.php', $args, '', YWCNP_TEMPLATE_PATH );
			$template = ob_get_contents();
			ob_end_clean();

			echo $template; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		/**
		 * Add metabox in edit product variable
		 *
		 * @since 1.0.0
		 * @param mixed  $loop loop.
		 * @param mixed  $variation_data variation data.
		 * @param string $variation variation.
		 */
		public function add_name_your_price_variable_attributes( $loop, $variation_data, $variation ) {

			$vendor_dir = '';

			if ( ywcnp_is_multivendor_active() ) {

				$vendor = yith_get_vendor( 'current', 'user' );

				if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
					$vendor_dir = 'vendor/';
				}
			}

			$args         = array(
				'loop'           => $loop,
				'variation_data' => $variation_data,
				'variation'      => $variation,
			);
			$args['args'] = $args;

			ob_start();
			wc_get_template( 'metaboxes/' . $vendor_dir . 'name_your_price_tab_variation.php', $args, '', YWCNP_TEMPLATE_PATH );
			$template = ob_get_contents();
			ob_end_clean();

			echo $template; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		/**
		 * Save product variation meta
		 *
		 * @YITHEMES
		 * @since 1.0.0
		 * @param int   $variation_id variation id.
		 * @param mixed $loop loop.
		 */
		public function save_product_variation_meta( $variation_id, $loop ) {
			$variation_meta = array(
				'_ywcnp_enabled_variation'     => isset( $_REQUEST['variable_is_nameyourprice'][ $loop ] ) ? 'yes' : 'no', //phpcs:ignore WordPress.Security.NonceVerification
				'_ywcnp_simple_suggest_price'  => isset( $_REQUEST['ywcnp_variation_suggest_price'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_variation_suggest_price'][ $loop ] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification
				'_ywcnp_simple_min_price'      => isset( $_REQUEST['ywcnp_variation_min_price'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_variation_min_price'][ $loop ] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification
				'_ywcnp_simple_max_price'      => isset( $_REQUEST['ywcnp_variation_max_price'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_variation_max_price'][ $loop ] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification
				'_ywcnp_variation_is_override' => isset( $_REQUEST['ywcnp_variation_is_override'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_variation_is_override'][ $loop ] ) ) : 'no', //phpcs:ignore WordPress.Security.NonceVerification
			);

			$variation = wc_get_product( $variation_id );

			foreach ( $variation_meta as $meta_key => $meta_value ) {
				$variation->update_meta_data( $meta_key, $meta_value );
			}

			$is_nameyour_price = 'yes' === $variation_meta['_ywcnp_enabled_variation'];

			$variation->update_meta_data( '_is_nameyourprice', $is_nameyour_price );
			$variation->save();
		}

		/**
		 * Return single instance
		 *
		 * @since 1.0.0
		 * @return YITH_WC_Name_Your_Price_Premium_Admin
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}
}

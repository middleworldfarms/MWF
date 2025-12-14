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

if ( ! class_exists( 'YITH_WC_Name_Your_Price_Admin' ) ) {
	/**
	 * Implement free admin features
	 * Class YITH_WC_Name_Your_Price_Admin
	 */
	class YITH_WC_Name_Your_Price_Admin {


		/**
		 * Single instance of the class
		 *
		 * @var YITH_WC_Name_Your_Price_Admin , single instance
		 */
		protected static $instance;

		/**
		 * __construct function
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function __construct() {

			// Add metaboxes in edit product !
			add_action( 'woocommerce_product_options_pricing', array( $this, 'add_option_general_product_data' ) );
			add_filter( 'product_type_options', array( $this, 'add_product_name_your_price_option' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_nameyourprice_meta' ), 20, 2 );
			add_action( 'save_nameyourprice_meta', array( $this, 'save_nameyourprice_meta' ) );

			// Include admin script !
			add_action( 'admin_enqueue_scripts', array( $this, 'include_admin_scripts' ) );
		}


		/**
		 * Save nameyourprice product meta
		 *
		 * @since 1.0.0
		 * @param int     $post_id post id.
		 * @param WP_Post $post post.
		 */
		public function save_product_nameyourprice_meta( $post_id, $post ) {

			$product_type_support = ywcnp_get_product_type_allowed();

			$product = wc_get_product( $post_id );

			if ( $product->is_type( $product_type_support ) ) {
				/**
				 * DO_ACTION: save_nameyourprice_meta
				 *
				 * hook to save Name Your Price meta.
				 *
				 * @param int $post_id
				 */
				do_action( 'save_nameyourprice_meta', $post_id );
			}

		}

		/**
		 * Save product simple meta
		 *
		 * @since 1.0.0
		 * @param int $product_id product id.
		 */
		public function save_nameyourprice_meta( $product_id ) {

			$product_meta = apply_filters(
				'ywcnp_add_premium_single_meta',
				array(
					'_ywcnp_enabled_product' => isset( $_REQUEST['_ywcnp_enabled_product'] ) ? 'yes' : 'no', //phpcs:ignore WordPress.Security.NonceVerification
				),
				$product_id
			);

			$product = wc_get_product( $product_id );

			if ( ! $product->is_type( 'variable' ) ) {

				foreach ( $product_meta as $meta_key => $meta_value ) {
					$product->update_meta_data( $meta_key, $meta_value );
				}

				$is_nameyourprice = 'yes' === $product_meta['_ywcnp_enabled_product'];

				$product->update_meta_data( '_is_nameyourprice', $is_nameyourprice );
				$product->save();
			}
		}


		/**
		 * Add checkbox in product data header
		 *
		 * @since 1.0.0
		 * @param mixed $type_options type options.
		 * @return array
		 */
		public function add_product_name_your_price_option( $type_options ) {

			global $post;
			/**
			 * APPLY_FILTERS: ywcnp_product_name_your_price_option_enabled
			 *
			 * Filter to show or not the Name Your Price option in product data header.
			 *
			 * @param bool $value default is true
			 * @param int  $post_id
			 */
			$enabled = apply_filters( 'ywcnp_product_name_your_price_option_enabled', true, $post->ID );

			if ( ! $enabled ) {
				return $type_options;
			}

			/**
			 * APPLY_FILTERS: ywcnp_wrapper_class
			 *
			 * filter the option row classes.
			 *
			 * @param array $classes
			 */
			$wrapper_class = apply_filters( 'ywcnp_wrapper_class', array( 'show_if_simple' ) );

			/**
			 * APPLY_FILTERS: ywcnp_change_default_type_options
			 *
			 * Filter if the product is Name Your Price product or not.
			 *
			 * @param bool $value default no
			 */
			$default              = apply_filters( 'ywcnp_change_default_type_options', ! ( defined( 'YWCNP_PREMIUM' ) && YWCNP_PREMIUM ) ? esc_attr( 'no' ) : ( ( ywcnp_product_has_rule( $post->ID ) !== '' ) ? esc_attr( 'yes' ) : esc_attr( 'no' ) ) );
			$nameyourprice_option = array(
				'ywcnp_enabled_product' => array(
					'id'            => esc_attr( '_ywcnp_enabled_product' ),
					'wrapper_class' => esc_attr( implode( ' ', $wrapper_class ) ),
					'label'         => esc_attr( __( 'Name Your Price', 'yith-woocommerce-name-your-price' ) ),
					'description'   => esc_attr( __( 'Enable "Name Your Price" for this product', 'yith-woocommerce-name-your-price' ) ),
					'default'       => $default,
				),
			);

			return array_merge( $type_options, $nameyourprice_option );
		}

		/**
		 * Print custom template in general product data
		 *
		 * @since 1.0.0
		 */
		public function add_option_general_product_data() {

			ob_start();

			wc_get_template( 'metaboxes/general_product_data_name_your_price_enabled.php', array(), '', YWCNP_TEMPLATE_PATH );
			$template = ob_get_contents();

			ob_end_clean();
			echo $template;  // phpcs:ignore WordPress.Security.EscapeOutput
		}

		/**
		 * Include admin script
		 *
		 * @since 1.0.0
		 */
		public function include_admin_scripts() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'ywcnp_admin_script', YWCNP_ASSETS_URL . 'js/ywcnp_free_admin' . $suffix . '.js', array( 'jquery' ), YWCNP_VERSION, true );

		}

		/**
		 * Return single instance
		 *
		 * @since 1.0.0
		 * @return YITH_WC_Name_Your_Price_Admin
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}
}
/**
 * YITH_Name_Your_Price_Admin
 *
 * @return YITH_WC_Name_Your_Price_Admin|YITH_WC_Name_Your_Price_Premium_Admin
 */
function YITH_Name_Your_Price_Admin() { // phpcs:ignore WordPress.NamingConventions
	if ( defined( 'YWCNP_PREMIUM' ) && YWCNP_PREMIUM ) {
		return YITH_WC_Name_Your_Price_Premium_Admin::get_instance();
	}

	return YITH_WC_Name_Your_Price_Admin::get_instance();
}

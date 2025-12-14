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

if ( ! class_exists( 'YITH_Name_Your_Price_RAQ' ) ) {

	/**
	 * YITH_Name_Your_Price_RAQ
	 */
	class YITH_Name_Your_Price_RAQ {

		/**
		 * __construct function
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function __construct() {

			add_filter( 'ywraq_ajax_add_item_prepare', array( $this, 'add_name_your_price_info' ), 20, 1 );
			add_filter( 'ywraq_add_item', array( $this, 'add_item' ), 10, 2 );
			add_action( 'ywraq_quote_adjust_price', array( $this, 'adjust_price' ), 10, 2 );

			// Add order item meta to check if product is name your price !
			add_action( 'ywraq_from_cart_to_order_item', array( $this, 'check_if_name_your_price' ), 20, 4 );
			add_action( 'ywraq_before_order_accepted', array( $this, 'add_name_your_price_filter' ), 10, 1 );
			add_action( 'ywraq_after_order_accepted', array( $this, 'remove_name_your_price_filter' ), 10, 1 );

			add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ), 20 );

		}

		/**
		 * Add_name_your_price_info
		 *
		 * @param array $postdata postdata.
		 *
		 * @return array
		 */
		public function add_name_your_price_info( $postdata ) {

			return $postdata;
		}

		/**
		 * Add_item
		 *
		 * @param mixed $product_raq product_raq.
		 * @param array $raq raq.
		 *
		 * @return mixed
		 */
		public function add_item( $product_raq, $raq ) {

			if ( isset( $product_raq['ywcnp_amount'] ) ) {
				$raq['ywcnp_amount'] = $product_raq['ywcnp_amount'];
			}

			return $raq;
		}


		/**
		 * Adjust_price
		 *
		 * @param array      $raq raq.
		 * @param WC_Product $product product.
		 */
		public function adjust_price( $raq, $product ) {

			if ( isset( $raq['ywcnp_amount'] ) ) {
				$product->set_price( $raq['ywcnp_amount'] );
			}
		}

		/**
		 * Check_if_name_your_price
		 *
		 * @param array    $values values.
		 * @param string   $cart_item_key cart_item_key.
		 * @param int      $item_id item_id.
		 * @param WC_Order $order order.
		 */
		public function check_if_name_your_price( $values, $cart_item_key, $item_id, $order ) {
			/**
			 * Product
			 *
			 * @var WC_Product $product
			 */
			$product = $values['data'];
			if ( ywcnp_product_is_name_your_price( $product ) ) {

				$nyp_in_order = $order->get_meta('_ywraq_nyp');
				if ( empty( $nyp_in_order ) ) {
					$nyp_in_order = array();
				}

				$product_id = $product->get_id();

				if ( ! in_array( $product_id, $nyp_in_order, true ) ) {
					$nyp_in_order[] = $product_id;
				}

				$order->update_meta_data( '_ywraq_nyp', $nyp_in_order );
				$order->save();
			}
		}

		/**
		 * Add_name_your_price_filter
		 *
		 * @param int $order_id order_id.
		 */
		public function add_name_your_price_filter( $order_id ) {

			$nyp_in_order = get_post_meta( $order_id, '_ywraq_nyp', true );
			if ( function_exists( 'YITH_Vendors' ) ) {
				if ( empty( $nyp_in_order ) ) {
					$parent_id    = wp_get_post_parent_id( $order_id );
					$nyp_in_order = get_post_meta( $parent_id, '_ywraq_nyp', true );
				}
			}
			if ( is_array( $nyp_in_order ) && count( $nyp_in_order ) > 0 ) {
				add_filter( 'ywcnp_is_name_your_price', '__return_false' );
			}
		}

		/**
		 * Remove_name_your_price_filter
		 *
		 * @param int $order_id order_id.
		 *
		 * @return void
		 */
		public function remove_name_your_price_filter( $order_id ) {

			remove_filter( 'ywcnp_is_name_your_price', '__return_false' );
		}

		/**
		 * Check_if_is_valid
		 *
		 * @param bool $is_valid is valid.
		 * @param int  $product_id product id.
		 */
		public function check_if_is_valid( $is_valid, $product_id ) {

			$product = wc_get_product( $product_id );

			if ( ywcnp_product_is_name_your_price( $product ) ) {

				$nyp_amount = isset( $_POST['ywcnp_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['ywcnp_amount'] ) ) : false; //phpcs:ignore WordPress.Security.NonceVerification

				if ( $nyp_amount ) {
					$min_price = ywcnp_get_min_price( $product_id );
					$max_price = ywcnp_get_max_price( $product_id );

					$is_valid = ! ( ! is_numeric( $nyp_amount ) || ( $nyp_amount < $min_price ) || ( $nyp_amount > $max_price ) );

					return $is_valid;
				}
			}

			return $is_valid;
		}

		/**
		 * Add scripts
		 *
		 */
		public function add_scripts() {

			if ( is_product() ) {

				global $post;

				if ( ! is_null( $post ) ) {

					$product = wc_get_product( $post->ID );

					if ( ywcnp_product_is_name_your_price( $product ) ) {

						$args = array(
							'ajax_url'                  => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
							'messages'                  => array(
								'errors' => array(
									'format'   => ywcnp_get_error_message( 'invalid_price' ),
									'negative' => ywcnp_get_error_message( 'negative_price' ),
									'min'      => ywcnp_get_error_message( 'min_error' ),
									'max'      => ywcnp_get_error_message( 'max_error' ),
								),
							),
							'decimal_separator'         => wc_get_price_decimal_separator(),
							'woocommerce_notice_anchor' => apply_filters( 'ywcnps_woocommerce_notice_anchor', '#content .woocommerce' ), // APPLY_FILTERS: ywcnps_woocommerce_notice_anchor | filter css class anchor. | @param string $css_selector
							'send_raq_without_min'      => apply_filters( 'ywcnps_send_raq_without_min', 'no' ), // APPLY_FILTERS: ywcnps_send_raq_without_min | filter option to send quote without min. | @param bool
						);

						wp_register_script( 'ywcnp_raq_integration', YWCNP_ASSETS_URL . 'js/' . yit_load_js_file( 'ywcnp_raq_integration.js' ), array( 'jquery' ), YWCNP_VERSION, true );
						wp_localize_script( 'ywcnp_raq_integration', 'ywcnp_raq', $args );

						wp_enqueue_script( 'ywcnp_raq_integration' );
					}
				}
			}
		}

	}
}

new YITH_Name_Your_Price_RAQ();

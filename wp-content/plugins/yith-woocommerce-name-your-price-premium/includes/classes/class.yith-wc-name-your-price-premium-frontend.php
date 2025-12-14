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

if ( ! class_exists( 'YITH_WC_Name_Your_Price_Premium_Frontend' ) ) {
	/**
	 * Implement free frontend features
	 * Class YITH_WC_Name_Your_Price_Frontend
	 */
	class YITH_WC_Name_Your_Price_Premium_Frontend extends YITH_WC_Name_Your_Price_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WC_Name_Your_Price_Frontend , single instance
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
			// Print the template for grouped product !
			add_action( 'woocommerce_grouped_product_list_before_price', array( $this, 'print_template_grouped_product' ) );
			add_filter( 'woocommerce_available_variation', array( $this, 'set_nameyourprice_in_variation' ), 20, 3 );

			add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'set_add_to_cart_text' ), 5, 2 );

			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'change_price_in_cart_html' ), 100, 3 );
			add_filter( 'woocommerce_cart_item_price', array( $this, 'change_price_in_cart_html' ), 100, 3 );

			// Include frontend style !
			add_action( 'wp_enqueue_scripts', array( $this, 'include_frontend_style' ) );
			// Include premium script !
			add_action( 'wp_enqueue_scripts', array( $this, 'include_frontend_script' ) );

		}


		/**
		 * Print_template_grouped_product
		 *
		 * @param WC_Product $product product.
		 * @since 1.0.0
		 */
		public function print_template_grouped_product( $product ) {

			ob_start();

			wc_get_template( 'single-product/nameyourprice-grouped.php', array( 'product' => $product ), '', YWCNP_TEMPLATE_PATH );
			$template = ob_get_contents();

			ob_end_clean();
			echo $template; // phpcs:ignore WordPress.Security.EscapeOutput

		}

		/**
		 * Yith_wc_name_your_price_add_cart_item_data
		 *
		 * @param cart   $cart_item_data cart_item_data.
		 * @param string $product_id product_id.
		 * @param string $variation_id variation_id.
		 *
		 * @since 1.0.0
		 */
		public function yith_wc_name_your_price_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {

			if ( isset( $_REQUEST['ywcnp_amount'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

				if ( $variation_id ) {
					$product_id = $variation_id;
				}

				// Add compatibility for grouped !
				$amount = isset( $_REQUEST['ywcnp_amount'][ $product_id ] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_amount'][ $product_id ] ) ) : sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_amount'] ) ); //phpcs:ignore WordPress.Security.NonceVerification

				$cart_item_data['ywcnp_amount']   = floatval( ywcnp_format_number( $amount ) );
				$cart_item_data['ywcnp_currency'] = isset( $_REQUEST['ywcnp_currency'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_currency'] ) ) : get_woocommerce_currency(); //phpcs:ignore WordPress.Security.NonceVerification

				/**
				 * APPLY_FILTERS: ywcnp_add_cart_item_data
				 *
				 * filter cart item data for Name Your Price product.
				 *
				 * @param cart $cart_item_data
				 * @param int  $product_id
				 */
				$cart_item_data = apply_filters( 'ywcnp_add_cart_item_data', $cart_item_data, $product_id );

			}

			return $cart_item_data;
		}

		/**
		 * Change_price_in_cart_html
		 *
		 * @param mixed $price_html price_html.
		 * @param cart  $cart_item cart_item.
		 * @param mixed $cart_item_key cart_item_key.
		 *
		 * @return string
		 */
		public function change_price_in_cart_html( $price_html, $cart_item, $cart_item_key ) {

			$product_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
			$product    = wc_get_product( $product_id );

			if ( ywcnp_product_is_name_your_price( $product ) ) {

				$is_subtotal    = current_filter() === 'woocommerce_cart_item_subtotal' ? true : false;
				$sub_price_html = '';

				// Set quantity for 1 item if it's "price" column !
				if ( ! $is_subtotal ) {

					$quantity = 1;
				} else {
					$quantity = $cart_item['quantity'];

					if ( $product->is_taxable() ) {
						$tax_mode = version_compare( WC()->version, '4.5.0', '>=' ) ? WC()->cart->get_tax_price_display_mode() : WC()->cart->tax_display_cart;

						if ( 'excl' === $tax_mode ) {
							$sub_price_html = ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
						} else {
							$sub_price_html = ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
						}
					}
				}

				$tax_mode = version_compare( WC()->version, '4.5.0', '>=' ) ? WC()->cart->get_tax_price_display_mode() : WC()->cart->tax_display_cart;

				// Get current item price in cart depending on tax display mode !
				if ( 'excl' === $tax_mode ) {
					$price = $cart_item['line_subtotal'];
				} else {
					$price = $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'];
				}

				/**
				 * Product
				 *
				 * @var WC_Product $product
				 */
				$product = $cart_item['data'];

				$price = $product->get_price();

				$amount = isset( $cart_item['ywcnp_amount'] ) ? $cart_item['ywcnp_amount'] : $price;
				/**
				 * APPLY_FILTERS: ywcnp_amount_cart_html
				 *
				 * filter amount value html.
				 *
				 * @param string $amount
				 */
				$amount = apply_filters( 'ywcnp_amount_cart_html', $amount );

				if ( ywcnp_product_has_subscription( $product ) ) {

					$price_html = ywcnp_get_price_subscription( $product, wc_price( yit_get_display_price( $product, $amount, $quantity ) ) );
				} else {
					$price_html = wc_price( yit_get_display_price( $product, $amount, $quantity ) ) . $sub_price_html;
				}

				if ( $is_subtotal ) {

					$price_html = '<span class="subtotal">' . $price_html . '</span>';
				}
			}

			return $price_html;

		}

		/**
		 * Validation  product
		 *
		 * @param bool  $passed passed.
		 * @param float $amount amount.
		 * @param int   $product_id product id.
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function ywcnp_add_cart_validation( $passed, $amount, $product_id ) {

			$error_message = '';

			// If add a grouped product !
			if ( is_array( $amount ) ) {

				$amount = $amount[ $product_id ];
			}

			$amount    = wc_format_decimal( $amount );
			$min_price = wc_format_decimal( ywcnp_get_min_price( $product_id ) );
			$max_price = wc_format_decimal( ywcnp_get_max_price( $product_id ) );

			if ( ! is_numeric( $amount ) ) {
				$error_message = ywcnp_get_error_message( 'invalid_price' );
				$passed        = false;
			} else {
				if ( $amount < 0 ) {
					$error_message = ywcnp_get_error_message( 'negative_price' );
					$passed        = false;
				}

				if ( is_numeric( $min_price ) && $min_price > 0 && $amount < $min_price ) {
					$error_message = ywcnp_get_error_message( 'min_error' );
					$error_message = str_replace( '{ywcnp_minimum_price}', wc_price( $min_price ), $error_message );
					$passed        = false;
				}

				if ( is_numeric( $max_price ) && $max_price > 0 && $amount > $max_price ) {
					$error_message = ywcnp_get_error_message( 'max_error' );
					$error_message = str_replace( '{ywcnp_maximum_price}', wc_price( $max_price ), $error_message );
					$passed        = false;
				}
			}

			$product = wc_get_product( $product_id );
			if ( $product->is_sold_individually() && $this->check_product_in_the_cart( $product ) ) {
				/* translators: %s is the url cart with a error message*/
				$error_message = sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', wc_get_cart_url(), __( 'View Cart', 'woocommerce' ), sprintf( __( 'You cannot add another &quot;%s&quot; to your cart.', 'woocommerce' ), $product->get_title() ) );
				$passed        = false;
			}

			if ( $error_message ) {
				wc_add_notice( $error_message, 'error' );
			}

			return $passed;

		}

		/**
		 * Check if a product is in the cart
		 *
		 * @param WC_Product $product product.
		 *
		 * @return bool
		 * @since 1.0.11
		 */
		public function check_product_in_the_cart( $product ) {

			$result = false;

			if ( ! empty( WC()->cart ) && ! WC()->cart->is_empty() ) {

				$product_id = $product->get_id();

				foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
					/** Cart product @var WC_Product $cart_product cart_product.*/
					$cart_product = $cart_item['data'];

					$cart_product_id = $cart_product->get_id();

					if ( $product_id === $cart_product_id ) {
						$result = true;
						break;
					}
				}
			}

			return $result;
		}

		/**
		 * Include style in frontend
		 *
		 * @since 1.0.0
		 */
		public function include_frontend_style() {
			global $post;

			$product_id = isset( $post ) ? $post->ID : - 1;
			$product    = wc_get_product( $product_id );

			if ( is_product() && ( $product && ywcnp_product_is_name_your_price( $product ) ) ) {

				wp_enqueue_style( 'ywcnp_premium_style', YWCNP_ASSETS_URL . 'css/ywcnp_frontend_style.css', array(), YWCNP_VERSION, 'all' );
			}
		}

		/**
		 * Include frontend script
		 *
		 * @since 1.0.0
		 */
		public function include_frontend_script() {
			global $post;

			$product_id = isset( $post ) ? $post->ID : - 1;
			$product    = wc_get_product( $product_id );

			if ( is_product() && ( $product && ywcnp_product_is_name_your_price( $product ) ) ) {
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

				wp_enqueue_script( 'yit_name_your_price_premium_frontend', YWCNP_ASSETS_URL . 'js/ywcnp_premium_frontend' . $suffix . '.js', array( 'jquery' ), YWCNP_VERSION, true );
			}
		}

		/**
		 * Set add to cart text for simple product
		 *
		 * @param  mixed      $add_to_cart_text add to cart text.
		 * @param WC_Product $product product.
		 *
		 * @return mixed|void
		 * @since 1.0.0
		 */
		public function set_add_to_cart_text( $add_to_cart_text, $product ) {

			if ( ywcnp_product_is_name_your_price( $product ) && $product->is_type( 'simple' ) ) {

				if ( ywcnp_product_has_subscription( $product ) ) {
					return get_option( 'ywsbs_add_to_cart_label' );
				} else {
					return get_option( 'ywcnp_button_single_label' );
				}
			}

			return $add_to_cart_text;
		}

		/**
		 * Returns an array of date for a variation. Used in the add to cart form.
		 *
		 * @param mixed                $variation_data variation data.
		 * @param WC_Product           $product product.
		 * @param WC_Product_Variation $variation variation.
		 *
		 * @since 1.0.0
		 */
		public function set_nameyourprice_in_variation( $variation_data, $product, $variation ) {

			$is_name_your_price = $variation->get_meta( '_ywcnp_enabled_variation' );
			$variation_id       = is_callable( array( $variation, 'get_id' ) ) ? $variation->get_id() : yit_get_prop( $variation, 'variation_id' );

			if ( 'yes' === $is_name_your_price ) {
				$price_format = get_woocommerce_price_format();
				$currency     = get_woocommerce_currency_symbol();

				$price                                        = sprintf( $price_format, $currency, ywcnp_get_suggest_price( $variation_id ) );
				$variation_data['ywcnp_variation']            = 'yes';
				$variation_data['ywcnp_variation_sugg_price'] = ywcnp_get_suggest_price( $variation_id );
				$variation_data['ywcnp_variation_min_price']  = ywcnp_get_min_price( $variation_id );
				$variation_data['ywcnp_variation_max_price']  = ywcnp_get_max_price( $variation_id );
				$variation_data['ywcnp_variation_sugg_price_html']   = ywcnp_get_suggest_price_html( $variation_id );
				$variation_data['ywcnp_variation_sugg_price_format'] = $price;
				$variation_data['ywcnp_variation_min_price_html']    = ywcnp_get_min_price_html( $variation_id );
				$variation_data['ywcnp_variation_max_price_html']    = ywcnp_get_max_price_html( $variation_id );
				$variation_data['add_to_cart_text']                  = ywcnp_product_has_subscription( $variation ) ? get_option( 'ywsbs_add_to_cart_label' ) : get_option( 'ywcnp_button_single_label' );

			}

			/**
			 * APPLY_FILTERS: yith_name_your_price_variation_data
			 *
			 * filter Name Your Price product variation data.
			 *
			 * @param mixed                $variation_data
			 * @param WC_Product           $product
			 * @param WC_Product_Variation $variation
			 */
			return apply_filters( 'yith_name_your_price_variation_data', $variation_data, $product, $variation );
		}


		/**
		 * Return single instance
		 *
		 * @return YITH_WC_Name_Your_Price_Frontend
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


	}
}

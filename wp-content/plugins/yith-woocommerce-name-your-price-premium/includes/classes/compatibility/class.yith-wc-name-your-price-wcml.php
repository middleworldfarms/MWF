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

if ( ! class_exists( 'YITH_Name_Your_Price_WCML' ) ) {

	/**
	 * YITH_Name_Your_Price_WCML
	 */
	class YITH_Name_Your_Price_WCML {
		/**
		 * __construct function
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function __construct() {

			add_filter( 'ywcnp_get_suggest_price', array( $this, 'convert_base_currency_to_user_currency' ), 10, 2 );
			add_filter( 'ywcnp_get_min_price', array( $this, 'convert_base_currency_to_user_currency' ), 10, 2 );
			add_filter( 'ywcnp_get_max_price', array( $this, 'convert_base_currency_to_user_currency' ), 10, 2 );
			add_filter( 'ywcnp_get_amount', array( $this, 'convert_base_currency_to_user_currency' ), 10, 2 );

			add_filter( 'ywcnp_session_cart_item_amount', array( $this, 'ywcnp_session_cart_item_amount' ), 10, 3 );
			add_filter( 'ywcnp_add_cart_item_data', array( $this, 'ywcnp_add_cart_item_data' ), 10, 2 );
		}


		/**
		 * Convert the amount into right currency
		 *
		 * @since 1.0.23
		 * @param float      $amount amount.
		 * @param WC_Product $product product.
		 */
		public function convert_base_currency_to_user_currency( $amount, $product ) {
			/**
			 * APPLY_FILTERS: wcml_raw_price_amount
			 *
			 * filter woocommerce multi language plugin conversion.
			 *
			 * @param float $amount
			 */
			return apply_filters( 'wcml_raw_price_amount', $amount );
		}

		/**
		 * Performs the conversion of amounts entered via the Name Your Price
		 * plugin for items that are already in the cart.
		 *
		 * @param float $amount amount.
		 * @param array $cart_item cart_item.
		 * @param array $values values.
		 *
		 * @return float
		 */
		public function ywcnp_session_cart_item_amount( $amount, $cart_item, $values ) {
			if ( isset( $values['ywcnp_original_amount'] ) &&
				isset( $values['ywcnp_original_currency'] ) ) {
				// Convert the original amount to the active currency !

				$amount = $values['ywcnp_original_amount'];

				$admin_currency    = get_woocommerce_currency();
				$customer_currency = $values['ywcnp_original_currency'];

				global $woocommerce_wpml;

				if ( ! is_null( $woocommerce_wpml ) ) {

					if ( $admin_currency !== $customer_currency ) {
						$amount = $woocommerce_wpml->multi_currency->prices->unconvert_price_amount( $amount, $customer_currency );
					}
				}
			}

			return $amount;
		}

		/**
		 * Tracks additional data for items added to the cart, in order to allow
		 * performing a currency conversion when the currency changes.
		 *
		 * @param array $cart_item_data cart_item_data.
		 * @param int   $product_id product_id.
		 *
		 * @return array
		 */
		public function ywcnp_add_cart_item_data( $cart_item_data, $product_id ) {
			if ( ! empty( $cart_item_data['ywcnp_amount'] ) ) {
				// Keep track of the original amount entered by the customer, as well
				// as the original currency. These elements will make it possible to
				// convert the amount to any target currency !
				$cart_item_data['ywcnp_original_amount']   = $cart_item_data['ywcnp_amount'];
				$cart_item_data['ywcnp_original_currency'] = get_woocommerce_currency();
			}

			return $cart_item_data;
		}
	}
}

new YITH_Name_Your_Price_WCML();

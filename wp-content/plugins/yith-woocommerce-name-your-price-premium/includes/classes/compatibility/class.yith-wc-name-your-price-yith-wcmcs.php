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

if ( ! class_exists( 'YITH_Name_Your_Price_YITH_WCMCS' ) ) {

	/**
	 * YITH_Name_Your_Price_YITH_WCMCS
	 */
	class YITH_Name_Your_Price_YITH_WCMCS {

		/**
		 * __construct function
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function __construct() {

			if ( defined( 'YITH_WCMCS_INIT' ) && ! is_admin() ) {

				add_filter( 'ywcnp_get_suggest_price', array( $this, 'convert_base_currency_to_user_currency' ), 10, 2 );
				add_filter( 'ywcnp_get_min_price', array( $this, 'convert_base_currency_to_user_currency' ), 10, 2 );
				add_filter( 'ywcnp_get_max_price', array( $this, 'convert_base_currency_to_user_currency' ), 10, 2 );
				add_filter( 'ywcnp_get_amount_admin_currency', array( $this, 'convert_manual_amount_to_base_currency' ), 10, 3 );
				add_filter( 'ywcnp_session_cart_item_amount', array( $this, 'ywcnp_session_cart_item_amount' ), 10, 3 );
				add_filter( 'ywcnp_add_cart_item_data', array( $this, 'ywcnp_add_cart_item_data' ), 10, 2 );
			}
		}


		/**
		 * Convert the amount into right currency
		 *
		 * @param float      $amount amount.
		 * @param WC_Product $product product.
		 *
		 * @since 1.0.23
		 */
		public function convert_base_currency_to_user_currency( $amount, $product ) {
			if ( ! is_null( WC()->session ) ) {
				$wc_currency   = WC()->session->get( 'yith_wcmcs_client_currency_id' );
				$base_currency = get_option( 'woocommerce_currency' );

				if ( $base_currency !== $wc_currency ) {
					/**
					 * APPLY_FILTERS: yith_wcmcs_convert_price
					 *
					 * filter to adjust price.
					 *
					 * @param float $amount
					 * @param array $from_to
					 */
					$amount = apply_filters(
						'yith_wcmcs_convert_price',
						$amount,
						array(
							'from' => $base_currency,
							'to'   => $wc_currency,
						)
					);
				}
			}

			return $amount;
		}

		/**
		 * Convert_manual_amount_to_base_currency
		 *
		 * @param float $amount amount.
		 * @param mixed $currency currency.
		 *
		 * @return $amount
		 */
		public function convert_manual_amount_to_base_currency( $amount, $currency, $data ) {
			if ( ! is_null( WC()->session ) ) {

				//$wc_currency   = WC()->session->get( 'yith_wcmcs_client_currency_id' );
				$base_currency = get_option( 'woocommerce_currency' );

				if ( $base_currency !== $currency ) {
					$amount = apply_filters(
						'yith_wcmcs_convert_price',
						$data['ywcnp_original_amount'],
						array(
							'to'   => $base_currency,
							'from' => $currency,
						)
					);
				}
			}

			return $amount;
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
			if ( isset( $values['ywcnp_original_amount'] ) && isset( $values['ywcnp_original_currency'] ) ) {
				// Convert the original amount to the active currency !

				$amount = $values['ywcnp_original_amount'];

				$current_currency = WC()->session->get( 'yith_wcmcs_client_currency_id' );
				if ( is_null( $current_currency ) ) {
					$current_currency = isset( $_GET['currency'] ) ? $_GET['currency'] : null;
				}
				$old_currency = $values['ywcnp_original_currency'];

				if ( ! is_null( $current_currency ) && $old_currency !== $current_currency ) {
					$amount                            = apply_filters(
						'yith_wcmcs_convert_price',
						$amount,
						array(
							'from' => $old_currency,
							'to'   => $current_currency,
						)
					);
					$values['ywcnp_original_currency'] = $old_currency;
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
				$cart_item_data['ywcnp_original_currency'] = $cart_item_data['ywcnp_currency'];

			}

			return $cart_item_data;
		}
	}
}

new YITH_Name_Your_Price_YITH_WCMCS();

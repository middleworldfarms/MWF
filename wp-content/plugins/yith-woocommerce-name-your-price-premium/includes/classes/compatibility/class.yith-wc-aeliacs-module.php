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

if ( ! class_exists( 'YITH_YWCNP_Aelia_Module' ) ) {

	/**
	 * YITH_YWCNP_Aelia_Module
	 */
	class YITH_YWCNP_Aelia_Module {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_YWCNP_Aelia_Module , single instance
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Shop's base currency. Used for caching.
		 *
		 * @var string
		 * @since 1.0.6
		 */
		protected static $base_currency;
		/**
		 * __construct function
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function __construct() {
			add_filter(
				'ywcnp_get_suggest_price',
				array(
					$this,
					'convert_base_currency_amount_to_user_currency',
				),
				10,
				2
			);
			add_filter( 'ywcnp_get_min_price', array( $this, 'convert_base_currency_amount_to_user_currency' ), 10, 2 );
			add_filter( 'ywcnp_get_max_price', array( $this, 'convert_base_currency_amount_to_user_currency' ), 10, 2 );
			add_filter( 'ywcnp_get_amount', array( $this, 'convert_base_currency_amount_to_user_currency' ), 10, 2 );
			add_filter(
				'ywcnp_get_amount_admin_currency',
				array(
					$this,
					'convert_manual_amount_to_base_currency',
				),
				10,
				2
			);
			add_filter( 'ywcnp_session_cart_item_amount', array( $this, 'ywcnp_session_cart_item_amount' ), 10, 3 );
			add_filter( 'ywcnp_add_cart_item_data', array( $this, 'ywcnp_add_cart_item_data' ), 10, 2 );
		}


		/**
		 * Convenience method. Returns WooCommerce base currency.
		 *
		 * @return string
		 * @since 1.0.6
		 */
		public static function base_currency() {

			if ( empty( self::$base_currency ) ) {
				self::$base_currency = get_option( 'woocommerce_currency' );
			}

			return self::$base_currency;
		}


		/**
		 * Convert the amount from base currency to current currency
		 *
		 * @param float      $amount amount.
		 * @param WC_Product $product product.
		 *
		 * @return float
		 * @since  1.0.0
		 */
		public function convert_base_currency_amount_to_user_currency( $amount, $product ) {

			if ( ! ywcnp_product_is_name_your_price( $product ) ) {

				return $amount;
			}

			$currency = $product->get_meta( 'currency' );
			if ( ! empty( $currency ) ) {

				$amount = self::get_amount_in_currency( $amount, null, $currency );
			}

			$amount = self::get_amount_in_currency( $amount );

			return $amount;
		}

		/**
		 * Basic integration with WooCommerce Currency Switcher, developed by Aelia
		 * (https://aelia.co). This method can be used by any 3rd party plugin to
		 * return prices converted to the active currency.
		 *
		 * @param double $amount The source price.
		 * @param string $to_currency The target currency. If empty, the active currency
		 *                              will be taken.
		 * @param string $from_currency The source currency. If empty, WooCommerce base
		 *                              currency will be taken.
		 *
		 * @return double The price converted from source to destination currency.
		 * @link   https://aelia.co
		 * @since  1.0.6
		 */
		public static function get_amount_in_currency( $amount, $to_currency = null, $from_currency = null ) {

			if ( empty( $from_currency ) ) {
				$from_currency = self::base_currency();
			}
			if ( empty( $to_currency ) ) {
				$to_currency = get_woocommerce_currency();
			}

			/**
			 * APPLY_FILTERS: wc_aelia_cs_convert
			 *
			 * filter to integrate WooCommerce Currency Switcher by Aelia.
			 *
			 * @param double $amount The source price.
			 * @param string $from_currency The source currency. If empty, WooCommerce base currency will be taken.
			 * @param string $to_currency The target currency. If empty, the active currency will be taken.
			 */
			return apply_filters( 'wc_aelia_cs_convert', $amount, $from_currency, $to_currency );
		}

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Convert_manual_amount_to_base_currency
		 *
		 * @param  float $amount amount.
		 * @param  mixed $currency currency.
		 * @return $amount
		 */
		public function convert_manual_amount_to_base_currency( $amount, $currency ) {

			$base_currency = self::base_currency();
			$wc_currency   = get_woocommerce_currency();

			$from = '';
			$to   = '';

			if ( ! empty( $currency ) ) {

				if ( $base_currency === $currency ) {

					$from = $currency;

					if ( $currency !== $wc_currency ) {

						$to = $wc_currency;
					}
				} else {

					$from = $currency;
					if ( $currency !== $wc_currency && $wc_currency === $base_currency ) {

						$to = $base_currency;
					}
				}
			}

			if ( ! empty( $from ) && ! empty( $to ) ) {
				$amount = $this->get_amount_in_currency( $amount, $to, $from );
			}

			return $amount;
		}

		/**
		 * Convert the amount from current currency to base currency
		 *
		 * @param float $amount amount.
		 *
		 * @return float
		 * @since  1.0.0
		 */
		public function convert_user_currency_amount_to_base_currency( $amount ) {

			return self::get_amount_in_currency( $amount, self::base_currency(), get_woocommerce_currency() );
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
				$amount = self::get_amount_in_currency(
					$values['ywcnp_original_amount'],
					get_woocommerce_currency(),
					$values['ywcnp_original_currency']
				);
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

YITH_YWCNP_Aelia_Module::get_instance();

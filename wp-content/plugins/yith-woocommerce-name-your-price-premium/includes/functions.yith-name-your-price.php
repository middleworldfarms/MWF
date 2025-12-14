<?php
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 *  @package YITH WooCommerce Name Your Price Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ywcnp_get_product_type_allowed' ) ) {
	/** Get free product type allowed
	 *
	 * @author YITH <plugins@yithemes.com>
	 * @since 1.0.0
	 * @return mixed|void
	 */
	function ywcnp_get_product_type_allowed() {

		/**
		 * APPLY_FILTERS: ywcnp_product_types
		 *
		 * filter products types allowed to have a Name Your Price.
		 *
		 * @param array $types
		 */
		return apply_filters( 'ywcnp_product_types', array( 'simple' ) );
	}
}

if ( ! function_exists( 'ywcnp_get_error_message' ) ) {
	/** Get free message error
	 *
	 * @since 1.0.0
	 * @param mixed $message_type message type.
	 * @return mixed
	 */
	function ywcnp_get_error_message( $message_type ) {

		/**
		 * APPLY_FILTERS: ywcnp_add_error_message
		 *
		 * filter error messages.
		 *
		 * @param array $error_messages
		 */
		$messages = apply_filters(
			'ywcnp_add_error_message',
			array(
				'negative_price' => get_option( 'ywcnp_negative_price_label', __( 'Please enter a value greater or equal to 0', 'yith-woocommerce-name-your-price' ) ),
				'invalid_price'  => get_option( 'ywcnp_invalid_price_label', __( 'Please enter a valid price', 'yith-woocommerce-name-your-price' ) ),
			)
		);

		return $messages[ $message_type ];
	}
}


if ( ! function_exists( 'ywcnp_product_is_name_your_price' ) ) {
	/** Check if product is "name your price"
	 *
	 * @since 1.0.0
	 * @param WC_Product $product product.
	 * @return bool
	 */
	function ywcnp_product_is_name_your_price( $product ) {

		// Return value !
		$r = false;

		if ( $product ) {
			if ( $product->is_type( array( 'simple', 'variation' ) ) ) {
				$r = $product->get_meta( '_is_nameyourprice' );

			} elseif ( $product->is_type( 'variable' ) ) {
					$r = $product->get_meta( '_variation_has_nameyourprice' );

				if ( ! $r ) {

					$children = $product->get_children();

					foreach ( $children as $child_id ) {
						$variation = wc_get_product( $child_id );
						$rr        = $variation->get_meta( '_is_nameyourprice' );
						if ( $rr ) {
							return true;
						}
					}
				}
			}
		}

		/**
		 * APPLY_FILTERS: ywcnp_is_name_your_price
		 *
		 * filter check if product is a Name Your Price product.
		 *
		 * @param bool $r return value
		 * @param WC_Product $product
		 */
		return apply_filters( 'ywcnp_is_name_your_price', $r, $product );
        // @codingStandardsIgnoreStart
		// Return get_post_meta( $product_id, '_is_nameyourprice' , true ) || get_post_meta( $product_id, '_variation_has_nameyourprice', true );
        // @codingStandardsIgnoreEnd
	}
}

if ( ! function_exists( 'ywcnp_format_number' ) ) {

	/**
	 * Ywcnp_format_number
	 *
	 * @param  mixed $number number.
	 * @return wc_format_decimal
	 */
	function ywcnp_format_number( $number ) {

		$number = str_replace( get_option( 'woocommerce_price_thousand_sep' ), '', $number );

		return wc_format_decimal( $number );
	}
}

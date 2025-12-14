<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This class manage the compatibility with Save for later plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Name_Your_Price_Save_for_later' ) ) {

	/**
	 * YITH_Name_Your_Price_Save_for_later
	 */
	class YITH_Name_Your_Price_Save_for_later { // phpcs:ignore
		/**
		 * __construct function
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function __construct() {
			add_filter( 'ywsfl_product_price_html', array( $this, 'change_price_html' ), 10, 2 );
			add_filter( 'ywsfl_product_add_to_cart_button_html', array( $this, 'change_add_to_cart_html' ), 10, 2 );
		}

		/**
		 * Check if the product is a NyP product and return the right price html
		 *
		 * @param string     $price_html price_html.
		 * @param WC_Product $product product.
		 * @return string
		 */
		public function change_price_html( $price_html, $product ) {

			global $YWC_Name_Your_Price; // phpcs:ignore WordPress.NamingConventions
			if ( $YWC_Name_Your_Price instanceof YITH_WooCommerce_Name_Your_Price_Premium && ywcnp_product_is_name_your_price( $product ) ) { // phpcs:ignore WordPress.NamingConventions

				$price_html = $YWC_Name_Your_Price->get_nameyourprice_html( $price_html, $product ); // phpcs:ignore WordPress.NamingConventions
			}

			return $price_html;
		}

		/**
		 * Check if the product is a NyP product and change the button html.
		 *
		 * @param string     $button_html button_html.
		 * @param WC_Product $product product.
		 * @return string
		 */
		public function change_add_to_cart_html( $button_html, $product ) {
			global $YWC_Name_Your_Price; // phpcs:ignore WordPress.NamingConventions
			if ( $YWC_Name_Your_Price instanceof YITH_WooCommerce_Name_Your_Price_Premium && ywcnp_product_is_name_your_price( $product ) ) { // phpcs:ignore WordPress.NamingConventions

				$button_text = get_option( 'ywcnp_button_loop_label' );

				$button_html = sprintf( '<a href="%1$s" class="%2$s">%3$s</a>', $product->get_permalink(), 'button single_add_to_cart_button', $button_text );
			}

			return $button_html;
		}
	}

}

new YITH_Name_Your_Price_Save_for_later();

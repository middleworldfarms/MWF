<?php // phpcs:ignore WordPress.NamingConventions
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

$variation_id      = $variation->ID;
$variation_product = wc_get_product( $variation_id );
// Multivendor Compatibility !
/**
 * Comment
 * if (!ywcnp_is_multivendor_name_your_price_enabled() && !ywcnp_product_is_name_your_price($variation_id))
 * return;
 */


$is_nameyourprice = $variation_product->get_meta( '_ywcnp_enabled_variation' );

?>
	<label><input type="checkbox" class="checkbox variable_is_nameyourprice"
		name="variable_is_nameyourprice[<?php echo esc_attr( $loop ); ?>]" <?php checked( isset( $is_nameyourprice ) ? $is_nameyourprice : '', 'yes' ); ?> /> <?php esc_html_e( 'Name Your Price', 'yith-woocommerce-name-your-price' ); ?>
		<a class="tips"
			data-tip="<?php esc_attr_e( 'Check this option to enable "Name Your Price" for this variation', 'yith-woocommerce-name-your-price' ); ?>"
			href="#">[?]</a>
	</label>
<?php

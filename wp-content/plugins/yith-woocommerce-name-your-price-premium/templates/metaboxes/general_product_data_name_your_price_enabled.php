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

global $post;

$supported_type = ywcnp_get_product_type_allowed();

$product          = wc_get_product( $post->ID );
$is_nameyourprice = get_post_meta( $post->ID, '_is_nameyourprice', true );

$is_visible = $is_nameyourprice && $product->is_type( $supported_type ) ? 'display:block;' : 'display:none;';


?>
<div id="ywcnp_disabled_field_message" class="options_group" style="<?php echo esc_attr( $is_visible ); ?>">

	<span
		class="description"><?php esc_html_e( 'These fields have been disabled because you have activated the "Name Your Price" option', 'yith-woocommerce-name-your-price' ); ?></span>

</div>

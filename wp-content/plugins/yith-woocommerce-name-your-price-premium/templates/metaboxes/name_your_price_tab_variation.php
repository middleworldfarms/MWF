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
$product_id        = $variation_product->get_parent_id();

$is_nameyourprice      = $variation_product->get_meta( '_ywcnp_enabled_variation' );
$last_category_rule_id = '';

$is_override = $variation_product->get_meta( '_ywcnp_variation_is_override' );
$is_override = empty( $is_override ) ? 'no' : $is_override;

if ( 'no' === $is_override ) {

	$last_category_rule_id = ywcnp_product_has_rule( $product_id );

}

$suggest_price_label = sprintf( '%s %s ', __( 'Suggested Price', 'yith-woocommerce-name-your-price' ), '( ' . get_woocommerce_currency_symbol() . ' )' );
$min_price_label     = sprintf( '%s %s ', __( 'Minimum Price', 'yith-woocommerce-name-your-price' ), '( ' . get_woocommerce_currency_symbol() . ' )' );
$max_price_label     = sprintf( '%s %s ', __( 'MaximumPrice', 'yith-woocommerce-name-your-price' ), '( ' . get_woocommerce_currency_symbol() . ' )' );

$sugg_price = empty( $last_category_rule_id ) ? get_post_meta( $variation_id, '_ywcnp_simple_suggest_price', true ) : get_term_meta( $last_category_rule_id, '_ywcnp_suggest_price', true );
$min_price  = empty( $last_category_rule_id ) ? get_post_meta( $variation_id, '_ywcnp_simple_min_price', true ) : get_term_meta( $last_category_rule_id, '_ywcnp_min_price', true );
$max_price  = empty( $last_category_rule_id ) ? get_post_meta( $variation_id, '_ywcnp_simple_max_price', true ) : get_term_meta( $last_category_rule_id, '_ywcnp_max_price', true );

$visibility_button_override = ( 'no' === $is_override && ! empty( $last_category_rule_id ) ) ? 'display:block;' : 'display:none;';
$disable_input_field        = ( 'no' === $is_override && ! empty( $last_category_rule_id ) ) ? 'readonly' : '';

?>
<div class="show_if_variation_nameyourprice"
	style="display: <?php echo 'yes' === $is_nameyourprice ? 'block' : 'none'; ?>;">
	<p class="form-row form-row-full">
		<label><?php echo esc_html( $suggest_price_label ); ?></label>
		<input type="text" class="short wc_input_price" name="ywcnp_variation_suggest_price[<?php echo esc_attr( $loop ); ?>]"
			value="<?php echo esc_attr( $sugg_price ); ?>" <?php echo esc_html( $disable_input_field ); ?> />
	</p>

	<p class="form-row form-row-full">
		<label><?php echo esc_html( $min_price_label ); ?></label>
		<input type="text" class="short wc_input_price" name="ywcnp_variation_min_price[<?php echo esc_attr( $loop ); ?>]"
			value="<?php echo esc_attr( $min_price ); ?>" <?php echo esc_html( $disable_input_field ); ?> />
	</p>

	<p class="form-row form-row-full">
		<label><?php echo esc_html( $max_price_label ); ?></label>
		<input type="text" class="short wc_input_price" name="ywcnp_variation_max_price[<?php echo esc_attr( $loop ); ?>]"
			value="<?php echo esc_attr( $max_price ); ?>" <?php echo esc_html( $disable_input_field ); ?> />
	</p>

	<div class="show_if_variation_nameyourprice">
		<p class="form-row form-row-full ywcnp_container_override " style="<?php echo esc_attr( $visibility_button_override ); ?>">
			<button type="button"
					class="button ywcnp_btn_override"><?php esc_html_e( 'Overwrite this rule', 'yith-woocommerce-name-your-price' ); ?></button>
			<span
				class="description"><?php esc_html_e( 'These fields have been disabled because you have set a general rule for this category', 'yith-woocommerce-name-your-price' ); ?></span>
		</p>
	</div>
	<input type="hidden" name="ywcnp_variation_is_override[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $is_override ); ?>">
</div>

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


global $post, $product_object;
$product_id = ! empty( $product_object ) && is_callable( array( $product_object, 'get_id' ) ) ? $product_object->get_id() : $product_id;
$product    = wc_get_product( $product_id );
// Multivendor Compatibility !
if ( ! ywcnp_is_multivendor_name_your_price_enabled() && ! ywcnp_product_is_name_your_price( $product ) ) {
	return;
}

$last_category_rule_id = '';
$is_override           = $product->get_meta( '_ywcnp_simple_is_override' );
$is_override           = empty( $is_override ) ? 'no' : $is_override;
if ( 'no' === $is_override ) {

	$last_category_rule_id = ywcnp_product_has_rule( $product_id );
}

$suggest_price_label = sprintf( '%s %s ', __( 'Suggested Price', 'yith-woocommerce-name-your-price' ), '( ' . get_woocommerce_currency_symbol() . ' )' );
$min_price_label     = sprintf( '%s %s ', __( 'Minimum Price', 'yith-woocommerce-name-your-price' ), '( ' . get_woocommerce_currency_symbol() . ' )' );
$max_price_label     = sprintf( '%s %s ', __( 'MaximumPrice', 'yith-woocommerce-name-your-price' ), '( ' . get_woocommerce_currency_symbol() . ' )' );

$sugg_price = empty( $last_category_rule_id ) ? ywcnp_get_suggest_price( $product_id ) : ywcnp_get_category_rule_vendor( $last_category_rule_id, 'sugg_price' );
$min_price  = empty( $last_category_rule_id ) ? ywcnp_get_min_price( $product_id ) : ywcnp_get_category_rule_vendor( $last_category_rule_id, 'min_price' );
$max_price  = empty( $last_category_rule_id ) ? ywcnp_get_max_price( $product_id ) : ywcnp_get_category_rule_vendor( $last_category_rule_id, 'max_price' );

$visibility_button_override = ( 'no' === $is_override && ! empty( $last_category_rule_id ) ) ? 'display:block;' : 'display:none;';
$disable_input_field        = ( 'no' === $is_override && ! empty( $last_category_rule_id ) ) ? array( 'readonly' => 'readonly' ) : array();


?>


<div class="options_group show_if_simple group_nameyourprice">
	<?php
	woocommerce_wp_text_input(
		array(
			'id'                => 'ywcnp_simple_suggest_price',
			'label'             => $suggest_price_label,
			'description'       => __( 'Set the suggested price for your product, leave blank not to suggest a price', 'yith-woocommerce-name-your-price' ),
			'data_type'         => 'price',
			'value'             => $sugg_price,
			'custom_attributes' => $disable_input_field,
		)
	);
	?>
	<?php
	woocommerce_wp_text_input(
		array(
			'id'                => 'ywcnp_simple_min_price',
			'label'             => $min_price_label,
			'description'       => __( 'Set the minimum price for your product, leave blank not to set a minimum price', 'yith-woocommerce-name-your-price' ),
			'data_type'         => 'price',
			'value'             => $min_price,
			'custom_attributes' => $disable_input_field,
		)
	);
	?>
	<?php
	woocommerce_wp_text_input(
		array(
			'id'                => 'ywcnp_simple_max_price',
			'label'             => $max_price_label,
			'description'       => __( 'Set the maximum price for your product, leave blank not to set a maximum price', 'yith-woocommerce-name-your-price' ),
			'data_type'         => 'price',
			'value'             => $max_price,
			'custom_attributes' => $disable_input_field,
		)
	);
	?>
	<?php
	woocommerce_wp_hidden_input(
		array(
			'id'    => 'ywcnp_simple_is_override',
			'value' => $is_override,
		)
	);
	?>
</div>
<div class="options_group show_if_simple group_nameyourprice">
	<p class="form-field" style="<?php echo esc_attr( $visibility_button_override ); ?>">
		<button type="button" id="ywcnp_btn_override"
				class="button"><?php esc_html_e( 'Overwrite this rule', 'yith-woocommerce-name-your-price' ); ?></button>
		<span
			class="description"><?php esc_html_e( 'These fields have been disabled because you have set a general rule for this category', 'yith-woocommerce-name-your-price' ); ?></span>
	</p>
</div>

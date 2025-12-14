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

global $product;

$product_id        = $product->get_id();
$sugg_price        = ywcnp_get_suggest_price( $product_id );
$min_price         = ywcnp_get_min_price( $product_id );
$max_price         = ywcnp_get_max_price( $product_id );
$span_subscription = '';

$show_form = ( $product instanceof WC_Product_Variable ) ? 'display:none;' : 'display:block;';

$decimal_separator  = wc_get_price_decimal_separator();
$thousand_separator = wc_get_price_thousand_separator();
$decimals           = wc_get_price_decimals();
$price_format       = get_woocommerce_price_format();
$negative           = $sugg_price < 0;
// @codingStandardsIgnoreStart
// $price           = apply_filters( 'raw_woocommerce_price', floatval( $negative ? $sugg_price * -1 : $sugg_price ) );
// $price           = apply_filters( 'formatted_woocommerce_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );
// @codingStandardsIgnoreEnd


$price = wc_format_localized_price( $sugg_price );

if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $decimals > 0 ) {
	$price = wc_trim_zeros( $price );
}

if ( ywcnp_product_has_subscription( $product ) ) {

	$price_time_option = YWSBS_Subscription_Helper()->get_subscription_period_for_price( $product );
	$span_subscription = '<span class="ywcnp_subscription_period"> / ' . $price_time_option . '</span>';

}

$price_format = get_woocommerce_price_format();
$currency     = get_woocommerce_currency_symbol();


$input_number = sprintf( '<input type="text" id="ywcnp_suggest_price_single" name="ywcnp_amount" class="ywcnp_sugg_price short wc_input_price" value="%1$s"  data-suggest_price="%1$s">', $price );

?>

<div id="ywcnp_form_name_your_price" style="margin:10px 0px;<?php echo esc_attr( $show_form ); ?>">
	<?php
	/**
	 * DO_ACTION: ywcnp_before_suggest_price_single
	 *
	 * hook before printing the suggest price field.
	 *
	 */
	do_action( 'ywcnp_before_suggest_price_single' );
	?>
	<p class="ywcnp_suggest_price_single">
		<?php
		$sugg_label_text = get_option( 'ywcnp_name_your_price_label' );
		?>
		<label for="ywcnp_suggest_price_single"><?php echo esc_html( $sugg_label_text ); ?></label>
		<?php echo sprintf( $price_format, '<span class="ywcnp_currency">' . $currency . '</span>', $input_number ); //phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php echo $span_subscription; //phpcs:ignore WordPress.Security.EscapeOutput ?>
		<input type="hidden" name="ywcnp_min" value="<?php echo esc_attr( $min_price ); ?>" />
		<input type="hidden" name="ywcnp_max" value="<?php echo esc_attr( $max_price ); ?>" />

		<input type="hidden" name="ywcnp_currency" value="<?php echo esc_attr( get_woocommerce_currency() ); ?>" />
	</p>
	<p class="ywcnp_sugg_label" style="display:<?php echo empty( $sugg_price ) ? 'none' : 'block'; ?>;">
		<?php echo ywcnp_get_suggest_price_html( $product_id ); //phpcs:ignore WordPress.Security.EscapeOutput ?>
	</p>
	<p class="ywcnp_min_label" style="display:<?php echo empty( $min_price ) ? 'none' : 'block'; ?>;">
		<?php echo ywcnp_get_min_price_html( $product_id );//phpcs:ignore WordPress.Security.EscapeOutput ?>
	</p>

	<p class="ywcnp_max_label" style="display:<?php echo empty( $max_price ) ? 'none' : 'block'; ?>;">
		<?php echo ywcnp_get_max_price_html( $product_id ); //phpcs:ignore WordPress.Security.EscapeOutput ?>
	</p>
	<?php
	/**
	 * DO_ACTION: ywcnp_after_suggest_price_single
	 *
	 * hook after printed the suggest price field.
	 *
	 * @param
	 */
	do_action( 'ywcnp_after_suggest_price_single' );
	?>
</div>

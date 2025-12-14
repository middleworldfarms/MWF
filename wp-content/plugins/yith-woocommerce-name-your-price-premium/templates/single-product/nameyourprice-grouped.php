<?php
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 *  @package YITH WooCommerce Name Your Price Premium
 */

$is_nameyourprice = $product->get_meta( '_is_nameyourprice' );
$product_id       = $product->get_id();

if ( $is_nameyourprice ) :

	$sugg_price = ywcnp_get_suggest_price( $product_id );
	$min_price  = ywcnp_get_min_price( $product_id );
	$max_price  = ywcnp_get_max_price( $product_id );

	$suggest_label   = is_numeric( $sugg_price ) ? ywcnp_get_suggest_price_html( $product_id ) : '';
	$min_price_label = is_numeric( $min_price ) ? ywcnp_get_min_price_html( $product_id ) : '';
	$max_price_label = is_numeric( $max_price ) ? ywcnp_get_max_price_html( $product_id ) : '';

	?>

	<td class="grouped_name_your_price">
		<input type="text" class="ywcnp_amount" name="ywcnp_amount[<?php echo esc_attr( $product_id ); ?>]"
			value="<?php echo esc_attr( $sugg_price ); ?>" data-suggest_price="<?php echo esc_attr( $sugg_price ); ?>"/>

		<div class="groupedprice"><?php echo esc_html( $min_price_label ); ?></div>
		<div class="groupedprice"><?php echo esc_html( $max_price_label ); ?></div>
		<?php
		$availability = $product->get_availability();
		if ( $availability ) {
			$availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>';
			echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $product ); //phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</td>
	<?php
endif;

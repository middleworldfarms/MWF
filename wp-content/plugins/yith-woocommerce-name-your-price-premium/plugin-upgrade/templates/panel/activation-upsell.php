<?php
/**
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @author  YITH
 * @package YITH/PluginUpgrade
 * @var array $upsell_products An array of upsell products
 */

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

?>

<section class="yith-licences-panel-upsell">

	<h2><?php echo esc_html__( 'Suggested tools to improve UX, increase conversions, and loyalize customers', 'yith-plugin-upgrade-fw' ); ?></h2>

	<div class="yith-licences-upsell-wrapper">
		<?php foreach ( $upsell_products as $product ) : ?>
			<div class="yith-licences-upsell-product">
				<div class="yith-licences-upsell-product-title">
					<img src="<?php echo esc_url( $product['image'] ); ?>" width="50" alt="" />
					<?php echo esc_html( $product['name'] ); ?>
				</div>
				<div class="yith-licences-upsell-product-description">
					<?php echo wp_kses_post( $product['description'] ); ?>
				</div>
				<div class="yith-licences-upsell-product-action">
					<a href="<?php echo esc_url( $product['permalink'] ); ?>" class="button-get-it" target="_blank">
						<?php echo esc_html__( 'Get it', 'yith-plugin-upgrade-fw' ); ?>
						<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"></path>
						</svg>
					</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
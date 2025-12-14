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
 * @var array $activated_licences An array of activated licences.
 * @var array $licences_to_activate An array of licences to activate.
 * @var boolean $have_expired_licences True if there are expired licences, false otherwise.
 * @var string $licence_check_status_url The licence check status URL.
 */

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

?>

<div id="yith-licences-panel-container">

	<div class="yith-licences-panel-header">
		<div class="yith-licences-panel-header-logo">
			<img src="<?php echo esc_url( YITH_PLUGIN_UPGRADE_URL ); ?>/assets/images/logo-yith.svg" width="100px" alt="YITH"/>
			<div class="yith-licences-panel-header-tagline">
				#1 Independent Seller of
				<br><span>WooCommerce Plugins</span></div>
		</div>
	</div>

	<section class="yith-licences-panel-list">

		<h2><?php echo esc_html__( 'Your licenses', 'yith-plugin-upgrade-fw' ); ?></h2>

		<p>
			<?php echo esc_html__( 'Only with an active license you’ll be able to keep your shop secure and get regular updates and support.', 'yith-plugin-upgrade-fw' ); ?>
			<br>
			<?php
			printf(
				esc_html__( '%1$sClick here%3$s to learn where to find the email and the license key of your plugins and %2$sread this article%3$s if you have any issue with the license activation.', 'yith-plugin-upgrade-fw' ),
				'<a href="#" class="yith-licence-where-find-these">',
				'<a href="//support.yithemes.com/hc/en-us/articles/360012568594-License-activation-issues" target="_blank">',
				'</a>'
			);
			?>
		</p>

		<?php if ( $have_expired_licences ) : ?>

		<div class="yith-licences-update-status">
			<img src="<?php echo esc_url( YITH_PLUGIN_UPGRADE_URL ); ?>/assets/images/alert-rounded.svg" width="25px" alt=""/>
			<span>
				<strong><?php esc_html_e( 'Some of your licenses have expired.', 'yith-plugin-upgrade-fw' ); ?></strong>
				<?php esc_html_e( 'If you have already renewed your subscription, click the button to update your license information.', 'yith-plugin-upgrade-fw' ); ?>
			</span>
			<span class="licence-update-status">
				<a href="<?php echo esc_url( $licence_check_status_url ); ?>" class="button-licence" title="<?php esc_attr_e( 'Update licence information', 'yith-plugin-upgrade-fw' ); ?>">
					<?php esc_html_e( 'Update', 'yith-plugin-upgrade-fw' ); ?>
				</a>
			</span>
		</div>

		<?php endif; ?>

		<div class="yith-licences-list">

			<!-- Activation licences list header -->
			<div class="yith-licence-header" <?php echo empty( $activated_licences ) ? 'style="display:none;"' : ''; ?>>
				<span class="yith-licence-product">
					<?php echo esc_html__( 'Plugin:', 'yith-plugin-upgrade-fw' ); ?>
				</span>
				<span class="yith-licence-email">
					<?php echo esc_html__( 'Email associated with license:', 'yith-plugin-upgrade-fw' ); ?>
				</span>
				<span class="yith-licence-key">
					<?php echo esc_html__( 'License key:', 'yith-plugin-upgrade-fw' ); ?>
				</span>
				<span class="yith-licence-activation-remaining">
					<?php echo esc_html__( 'Licenses used:', 'yith-plugin-upgrade-fw' ); ?>
				</span>
				<span class="yith-licence-expire-on">
					<?php echo esc_html__( 'Expire on:', 'yith-plugin-upgrade-fw' ); ?>
				</span>
				<span class="yith-licence-action"></span>
			</div>

			<?php
			foreach ( $activated_licences as $init => $licence ) :
				include 'activation-row.php';
			endforeach;
			?>

			<?php
			foreach ( $licences_to_activate as $init => $licence ) :
				include 'activation-form.php';
			endforeach;
			?>
		</div>
	</section>

	<?php
	if ( ! empty( $upsell_products ) ) :
		include 'activation-upsell.php';
	endif;
	?>
</div>
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
 * @var string                      $init    The plugin product init string.
 * @var Licence $licence The licence object.
 */

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

use YITH\PluginUpgrade\Admin\Utils;
use YITH\PluginUpgrade\Licence;

?>

<div class="yith-licence-activation" data-product="<?php echo esc_attr( $init ); ?>">
	<span class="yith-licence-product" data-label="<?php echo esc_attr__( 'Plugin:', 'yith-plugin-upgrade-fw' ); ?>">
		<?php echo esc_html( Utils::display_product_name( $init ) ); ?>
	</span>
	<span class="yith-licence-email" data-label="<?php echo esc_attr__( 'Email associated with license:', 'yith-plugin-upgrade-fw' ); ?>">
		<?php echo esc_html( Utils::display_activation_licence_email( $licence->email ) ); ?>
	</span>
	<span class="yith-licence-key" data-label="<?php echo esc_attr__( 'License key:', 'yith-plugin-upgrade-fw' ); ?>">
		<?php echo esc_html( sprintf( '%s-****-****-****-************', substr( $licence->licence_key, 0, 8 ) ) ); ?>
	</span>
	<span class="yith-licence-activation-remaining" data-label="<?php echo esc_attr__( 'Licenses used:', 'yith-plugin-upgrade-fw' ); ?>">
		<?php
		printf(
		/* translators: %1$1s: Number of activations for the licence. %2$2s: The activations number limit */
			esc_html__( '%1$1s out of %2$2s', 'yith-plugin-upgrade-fw' ),
			( $licence->activation_limit - $licence->activation_remaining ),
			$licence->activation_limit
		);
		?>
		</span>
	<span class="yith-licence-expire-on" data-label="<?php echo esc_attr__( 'Expire on:', 'yith-plugin-upgrade-fw' ); ?>">
		<?php
		if ( $licence->is_banned() ) :
			printf( '<span class="banned">%s</span>', esc_html_x( 'Banned', 'Licence status', 'yith-plugin-upgrade-fw' ) );
		elseif ( $licence->is_expired() ) :
			printf( '<span class="expired">%s</span>', esc_html_x( 'Expired', 'Licence status', 'yith-plugin-upgrade-fw' ) );
		else :
			printf( '<time datetime="%s">%s</time>', esc_attr( gmdate( 'Y-m-d', $licence->licence_expires ) ), esc_html( gmdate( 'M j, Y', $licence->licence_expires ) ) );
		endif;
		?>
	</span>
	<span class="yith-licence-action">
		<span class="yith-plugin-fw__action-button yith-plugin-fw__action-button--delete-action">
			<i class="yith-plugin-fw__action-button__icon yith-icon yith-icon-trash"></i>
		</span>
	</span>
</div>

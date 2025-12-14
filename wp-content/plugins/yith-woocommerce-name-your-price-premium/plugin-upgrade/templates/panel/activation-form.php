<?php
/**
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @author YITH
 * @package YITH/PluginUpgrade
 * @var string $init The plugin product init string.
 * @var Licence $licence The licence object.
 */

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

use YITH\PluginUpgrade\Admin\Utils;
use YITH\PluginUpgrade\Licence;

?>

<form class="yith-licence-activation-form">
	<span class="yith-licence-product">
		<img src="<?php echo esc_url( YITH_PLUGIN_UPGRADE_URL ); ?>/assets/images/alert.svg" width="25px" alt=""/>
		<span>
			<?php echo esc_html( Utils::display_product_name( $init ) ); ?>
			<br><span class="yith-licence-activation-alert"><?php echo esc_html__( 'Please activate your license', 'yith-plugin-upgrade-fw' ); ?></span>
		</span>
	</span>
	<span class="yith-licence-email">
		<input type="text" id="yith-licence-email-<?php echo esc_attr( $licence->product_id ); ?>" autocomplete="off" name="email" placeholder="<?php echo esc_html_x( 'Enter e-mail address', 'Placeholder', 'yith-plugin-upgrade-fw' ); ?>" value="" />
	</span>
	<span class="yith-licence-key">
		<input type="text" id="yith-licence-key-<?php echo esc_attr( $licence->product_id ); ?>" autocomplete="off" name="licence_key" maxlength="36" placeholder="<?php echo esc_html_x( 'Enter license key', 'Placeholder', 'yith-plugin-upgrade-fw' ); ?>" value=""/>
	</span>
	<span class="yith-licence-submit">
		<input type="hidden" name="product_init" value="<?php echo esc_attr( $init ); ?>"/>
		<input type="submit" class="button-licence licence-activation" value="<?php echo esc_attr_x( 'Activate', 'Button Label', 'yith-plugin-upgrade-fw' ); ?>" disabled="disabled">
	</span>
</form>

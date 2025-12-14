<?php
/**
 * This file belongs to the YIT Plugin Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @author  YITH
 * @package YITH/PluginUpgrade
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.


if ( ! function_exists( 'yith_plugin_upgrade_get_home_url' ) ) {
	/**
	 * Get the home url without protocol
	 *
	 * @since  5.0.0
	 * @return string The home url.
	 * @deprecated
	 */
	function yith_plugin_upgrade_get_home_url(): string {
		_deprecated_function( __FUNCTION__, '5.2.0', 'Use instead \YITH\PluginUpgrade\Utils::get_home_url' );
		return \YITH\PluginUpgrade\Utils::get_home_url();
	}
}

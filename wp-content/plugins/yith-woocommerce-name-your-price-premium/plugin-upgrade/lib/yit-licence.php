<?php
/**
 * This file belongs to the YIT Plugin Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @author YITH
 * @package YITH/PluginUpgrade
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Startup module by including loading class.
 * This is done to be backward compatible with Plugin-fw
 */
require_once dirname( __DIR__ ) . '/includes/loader.php';

// Overwrite old functions to grant backward compatibility.
if ( ! function_exists( 'YITH_Plugin_Licence' ) ) {
	/**
	 * Main instance of plugin
	 *
	 * @since  5.0.0
	 * @return \YITH\PluginUpgrade\Licences
	 */
	function YITH_Plugin_Licence() { // phpcs:ignore
		return \YITH\PluginUpgrade\Licences::instance();
	}
}

if ( ! function_exists( 'YITH_Plugin_Upgrade' ) ) {
	/**
	 * Main instance of plugin
	 *
	 * @since  5.0.0
	 * @return \YITH\PluginUpgrade\Upgrade
	 */
	function YITH_Plugin_Upgrade() { // phpcs:ignore
		return \YITH\PluginUpgrade\Upgrade::instance();
	}
}

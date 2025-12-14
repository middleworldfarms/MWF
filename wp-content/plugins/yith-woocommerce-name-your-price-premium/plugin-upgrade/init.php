<?php
/**
 * Framework Name: YITH Licence & Upgrade Framework
 * Version: 5.3.1
 * Author: YITHEMES
 * Text Domain: yith-plugin-upgrade-fw
 * Domain Path: /languages/
 *
 * @author YITH
 * @version 5.3.1
 * @package YITH/PluginUpgrade
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'yith_maybe_load_plugin_upgrade' ) ) {
	function yith_maybe_load_plugin_upgrade( $plugin_path ) {
		global $plugin_upgrade_fw_data;

		$default_headers = array(
			'Version' => 'Version',
		);

		$plugin_path  = trailingslashit( $plugin_path );
		$upgrade_path = $plugin_path . 'plugin-upgrade/init.php';

		if ( ! file_exists( $upgrade_path ) ) {
			return;
		}

		$upgrade_headers = get_file_data( $plugin_path . 'plugin-upgrade/init.php', $default_headers );
		$upgrade_version = isset( $upgrade_headers['Version'] ) ? $upgrade_headers['Version'] : '';

		if ( empty( $upgrade_version ) ) {
			return;
		}

		// Be backward compatible with old framework versions.
		if ( empty( $plugin_upgrade_fw_data ) || version_compare( key( $plugin_upgrade_fw_data ), $upgrade_version, '<' ) ) {
			$file                   = trailingslashit( dirname( $upgrade_path ) ) . 'lib/yit-licence.php';
			$plugin_upgrade_fw_data = array( $upgrade_version => array( $file ) );
		}
	}
}

add_action(
	'plugins_loaded',
	function () {
		global $plugin_upgrade_fw_data;

		if ( class_exists( '\YITH\PluginUpgrade\Loader', false ) || empty( $plugin_upgrade_fw_data ) ) {
			return;
		}

		foreach ( $plugin_upgrade_fw_data as $files ) {
			foreach ( $files as $file ) {
				if ( ! file_exists( $file ) ) {
					continue;
				}

				require_once $file;
			}
		}
	},
	10
);

<?php

defined( 'ABSPATH' ) || exit;

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    GPLVault_Updater
 * @subpackage GPLVault_Updater/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    GPLVault_Updater
 * @subpackage GPLVault_Updater/includes
 * @author     GPL Vault <support@gplvault.com>
 */
class GPLVault_Updater_Activator {

	/**
	 * Run this method during plugin activation
	 *
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( ! class_exists( 'GPLVault_Settings_Manager', false ) ) {
			require_once GPLVault()->includes_path( '/settings/class-gplvault-settings-manager.php' );
		}

		if ( ! function_exists( 'gv_api_manager' ) ) {
			require_once GPLVault()->includes_path( '/gplvault-functions.php' );
		}

		if ( ! wp_next_scheduled( 'gplvault_clean_up_logs' ) ) {
			wp_schedule_event( time() + 300, 'daily', 'gplvault_clean_up_logs' );
		}

		$settings_manager = GPLVault_Settings_Manager::instance();

		if ( $settings_manager ) {
			$settings_manager->set_initial();
		}

		// Schedule the weekly full catalog refresh
		if ( function_exists( 'GPLVault' ) && method_exists( GPLVault(), 'schedule_catalog_refresh' ) ) {
			GPLVault()->schedule_catalog_refresh();
		}
	}
}

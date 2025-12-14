<?php

defined( 'ABSPATH' ) || exit;

/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 *
 * @package    GPLVault_Updater
 * @subpackage GPLVault_Updater/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    GPLVault_Updater
 * @subpackage GPLVault_Updater/includes
 * @author     GPL Vault <support@gplvault.com>
 */
class GPLVault_Updater_Deactivator {

	/**
	 * Run this method during plugin activation
	 *
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		foreach ( array(
			'gplvault_six_hours_cron',
			'gplvault_two_hourly_cron',
			'gplvault_licence_check',
			'gplvault_status_check',
			'gplvault_fetch_client_schema',
			'gplvault_clean_up_logs',
		) as $cron_action ) {
			if ( wp_next_scheduled( $cron_action ) ) {
				wp_clear_scheduled_hook( $cron_action );
			}
		}

		if ( ! class_exists( 'GPLVault_Settings_Manager', false ) ) {
			require_once GPLVault()->includes_path( '/settings/class-gplvault-settings-manager.php' );
		}

		if ( ! function_exists( 'gv_util' ) ) {
			require_once GPLVault()->includes_path( '/gplvault-functions.php' );
		}

		gv_util()->cleanup();

		// Clear the scheduled weekly full catalog refresh
		if ( function_exists( 'GPLVault' ) && method_exists( GPLVault(), 'clear_scheduled_catalog_refresh' ) ) {
			GPLVault()->clear_scheduled_catalog_refresh();
		}
	}
}

<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once 'gplvault-updater.php';

/**
 * @var GPLVault_API_Manager $apiManager
 */

if ( ! class_exists( 'GPLVault_Settings_Manager', false ) ) {
	require_once GPLVault()->includes_path( '/settings/class-gplvault-settings-manager.php' );
}

if ( ! function_exists( 'gv_util' ) ) {
	require_once GPLVault()->includes_path( '/gplvault-functions.php' );
}

gv_util()->cleanup();
gv_settings_manager()->remove_initial();

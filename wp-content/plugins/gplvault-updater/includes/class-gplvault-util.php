<?php

defined( 'ABSPATH' ) || exit;

class GPLVault_Util {
	protected static $singleton = null;
	/**
	 * @var GPLVault_Settings_Manager $settings
	 */
	protected $settings;

	public static function instance() {
		if ( is_null( self::$singleton ) ) {
			self::$singleton = new self();
		}

		return self::$singleton;
	}

	private function __construct() {
		$this->settings = GPLVault_Settings_Manager::instance();
	}


	public static function is_gplvault_area() {
		$pages = array(
			GPLVault_Admin::SLUG_PLUGINS,
			GPLVault_Admin::SLUG_THEME,
			GPLVault_Admin::SLUG_SETTINGS,
			GPLVault_Admin::SLUG_LOGS,
			GPLVault_Admin::SLUG_SYSTEM,
			GPLVault_Admin::SLUG_BLACKLIST,
		);
		$p_now = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return in_array( $p_now, $pages, true );
	}

	public function clear_cron() {
		foreach ( array(
			'gplvault_six_hours_cron',
			'gplvault_two_hourly_cron',
			'gplvault_fetch_client_schema',
			'gplvault_licence_check',
			'gplvault_status_check',
		) as $cron_action ) {
			if ( wp_next_scheduled( $cron_action ) ) {
				wp_clear_scheduled_hook( $cron_action );
			}
		}
	}

	public function clear_options() {
		gv_settings_manager()->deactivation();
	}

	public function cleanup() {
		$this->clear_cron();
	}

	public function inactive_status_notice() {
		include GV_UPDATER_STATIC_PATH . 'notices/notice-inactive.php';
	}

	public static function single_event( $hook_suffix, $schedule = 0, $args = array(), $wp_error = false ) {
		if ( empty( $hook_suffix ) ) {
			/* translators: %s: Hook Suffix for single event */
			return new WP_Error( 'gv_error_cron_hook', sprintf( __( 'Could not create single event for %s', 'gplvault' ), $hook_suffix ) );
		}
		$hook = 'gplvault_' . trim( $hook_suffix, '_' );
		if ( wp_next_scheduled( $hook ) ) {
			wp_clear_scheduled_hook( $hook );
		}

		// CRON schedule is set to run after 1 hour from now if no schedule is provided
		$cron_schedule = 0 === $schedule ? HOUR_IN_SECONDS : (int) $schedule;

		return wp_schedule_single_event( time() + $cron_schedule, $hook, $args, $wp_error );
	}

	public static function clear_single_event( $hook, $args = array(), $wp_error = false ) {
		return wp_clear_scheduled_hook( $hook, $args, $wp_error );
	}
}

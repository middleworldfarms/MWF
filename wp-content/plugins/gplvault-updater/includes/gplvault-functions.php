<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'gv_event_loader' ) ) {
	/**
	 * @return GPLVault_Hooks
	 */
	function gv_event_loader() {
		if ( ! class_exists( 'GPLVault_Hooks', false ) ) {
			require_once GPLVault()->includes_path( '/class-gplvault-hooks.php' );
		}

		return GPLVault_Hooks::instance();
	}
}

/**
 * @return GPLVault_Settings_Manager
 */
function gv_settings_manager() {
	if ( ! class_exists( 'GPLVault_Settings_Manager', false ) ) {
		require_once GPLVault()->includes_path( '/settings/class-gplvault-settings-manager.php' );
	}

	return GPLVault_Settings_Manager::instance();
}

/**
 * @return GPLVault_API_Manager
 */
function gv_api_manager( $singleton = false ) {
	if ( ! class_exists( 'GPLVault_API_Manager', false ) ) {
		require_once GPLVault()->includes_path( '/api/class-gplvault-api-manager.php' );
	}

	return GPLVault_API_Manager::instance( $singleton );
}

if ( ! function_exists( 'gv_util' ) ) :
	function gv_util() {
		if ( ! class_exists( 'GPLVault_Util', false ) ) {
			require_once GPLVault()->includes_path( '/api/class-gplvault-util.php' );
		}

		return GPLVault_Util::instance();
	}
endif;

if ( ! function_exists( 'gv_doing_it_wrong' ) ) :
	function gv_doing_it_wrong( $function, $message, $version ) {
		// @codingStandardsIgnoreStart
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

		if ( wp_doing_ajax() || gv_is_rest_request() ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
	}
endif;

if (! function_exists( 'gv_is_rest_request') ) :
	function gv_is_rest_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return apply_filters( 'gplvault_is_rest_api_request', $is_rest_api_request );
	}
endif;

if (! function_exists('gv_create_files')) :
	function gv_create_files() {
		$files = array(
			array(
				'base'    => GV_UPDATER_LOG_DIR,
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
			array(
				'base'    => GV_UPDATER_LOG_DIR,
				'file'    => 'index.html',
				'content' => '',
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
					fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
				}
			}
		}
	}
endif;
if (! function_exists('gv_new_logger')):
	function gv_new_logger($filename = 'gplvault-log') {
		if (! class_exists('GPLVault_Psr_Logger', false)) {
			require_once trailingslashit(GV_UPDATER_PATH) . 'includes/logger/load.php';
		}
		if (gv_is_logging_disabled()) {
			return new GPLVault_Null_Logger();
		}

		if (defined('GPLVAULT_DISABLE_LOG') && true === constant('GPLVAULT_DISABLE_LOG')) {
			return new GPLVault_Null_Logger();
		}
		$filename = trailingslashit(GV_UPDATER_LOG_DIR) . gv_log_filename($filename);
		try {
			$handler = new GPLVault_Psr_Log_Handler($filename);
			return new GPLVault_Psr_Logger($handler);
		} catch (Exception $exception) {
			error_log(sprintf(
				'[ERROR] Failed to initiate logging handler class. %s',
				$exception->getMessage()));
			return new GPLVault_Null_Logger();
		}
	}
endif;

if (!function_exists('gv_is_logging_disabled')):
	function gv_is_logging_disabled(): bool
	{
		return defined('GPLVAULT_LOGGING_DISABLED')
			&& true === constant('GPLVAULT_LOGGING_DISABLED');
	}
endif;


if (! function_exists('gv_log_filename')):
	function gv_log_filename($base_name = 'gplvault-log', $date_suffix = null) {
		$date_suffix = null !== $date_suffix ? $date_suffix : gmdate('Y-m-d');
		$hash_suffix = gv_hash("{$base_name}{$date_suffix}");
		return sanitize_file_name( implode( '-', array( $base_name, $date_suffix, $hash_suffix ) ) . '.log' );
	}
endif;

if (! function_exists( 'gv_logger')) :
	/**
	 * @return GPLVault_Logger_Interface
	 */
	function gv_logger() {
		static $logger = null;

		$class = apply_filters( 'gplvault_logging_class', 'GPLVault_Logger' );

		if ( null !== $logger && is_string( $class ) && is_a( $logger, $class ) ) {
			return $logger;
		}

		$implements = class_implements( $class );

		if ( is_array( $implements ) && in_array( 'GPLVault_Logger_Interface', $implements, true ) ) {
			$logger = is_object( $class ) ? $class : new $class();
		} else {
			gv_doing_it_wrong(
				__FUNCTION__,
				sprintf(
				/* translators: 1: class name 2: gplvault_logging_class 3: GPLVault_Logger_Interface */
					__( 'The class %1$s provided by %2$s filter must implement %3$s.', 'gplvault' ),
					'<code>' . esc_html( is_object( $class ) ? get_class( $class ) : $class ) . '</code>',
					'<code>gplvault_logging_class</code>',
					'<code>GPLVault_Logger_Interface</code>'
				),
				'2.1.0'
			);

			$logger = is_a( $logger, 'GPLVault_Logger' ) ? $logger : new GPLVault_Logger();
		}

		return $logger;
	}
endif;

if ( ! function_exists( 'gv_log') ) :
	function gv_log($type, $message, $context = array()) {
		$logger = gv_logger();
		$type = strtolower($type);

		if (gv_settings_manager()->is_logging_disabled()) {
			return;
		}

		if(method_exists($logger, $type)) {
			$context['source'] = isset($context['source']) ? $context['source'] : 'gv-debug';
			$logger->{$type}($message, $context);
		}
	}
endif;

if (! function_exists('gv_debug')) :
	function gv_debug($message) {
		$logger = gv_logger();

		if (gv_settings_manager()->is_logging_disabled()) {
			return;
		}

		$logger->debug($message, array('source' => 'debug-log'));
	}
endif;

if (! function_exists('gv_api_debug')) :
	function gv_api_debug($message) {
		$logger = gv_logger();

		if (gv_settings_manager()->is_logging_disabled()) {
			return;
		}

		$logger->debug($message . PHP_EOL, array('source' => 'api-debug-log'));
	}
endif;

if (! function_exists('gv_schema_debug')) :
	function gv_schema_debug($message) {
		$logger = gv_logger();

		if (gv_settings_manager()->is_logging_disabled()) {
			return;
		}

		$logger->debug($message . PHP_EOL, array('source' => 'schema-debug-log'));
	}
endif;

if (! function_exists('gv_settings_debug')) :
	function gv_settings_debug($message) {
		$logger = gv_logger();

		if (gv_settings_manager()->is_logging_disabled()) {
			return;
		}

		$logger->debug($message, array('source' => 'settings-debug-log'));
	}
endif;


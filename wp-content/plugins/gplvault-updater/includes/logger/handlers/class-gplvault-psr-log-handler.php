<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class GPLVault_Psr_Log_Handler implements GPLVault_Psr_Log_Handler_Interface {

	/**
	 * @var string
	 */
	private $filename;

	public function __construct( $filename ) {
		$dir = dirname( $filename );
		if ( ! file_exists( $dir ) ) {
			$status = wp_mkdir_p( $dir );
			if ( false === $status && ! is_dir( $dir ) ) {
				throw new UnexpectedValueException( sprintf( 'Unable to create directory "%s". The directory does not exist and could not be created.', esc_html( $dir ) ) );
			}
		}
		$this->filename = $filename;
	}

	public function handle( $vars ) {
		$output = self::DEFAULT_FORMAT;
		foreach ( $vars as $var => $value ) {
			$output = str_replace( '%' . $var . '%', $value, $output );
		}
		file_put_contents( $this->filename, $output . PHP_EOL, FILE_APPEND ); // @phpcs:ignore
	}

	/**
	 * @return array
	 */
	public static function get_log_files() {
		$files  = @scandir( GV_UPDATER_LOG_DIR ); // @phpcs:ignore
		$result = array();

		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $value ) {
				if ( ! in_array( $value, array( '.', '..' ), true ) ) {
					if ( ! is_dir( $value ) && strstr( $value, '.log' ) ) {
						$result[ sanitize_title( $value ) ] = $value;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param string $handle
	 * @return bool
	 */
	public static function remove( $handle ) {
		$removed = false;
		$logs    = self::get_log_files();
		$handle  = sanitize_title( $handle );

		if ( isset( $logs[ $handle ] ) && $logs[ $handle ] ) {
			$file = realpath( trailingslashit( GV_UPDATER_LOG_DIR ) . $logs[ $handle ] );
			if ( 0 === stripos( $file, realpath( trailingslashit( GV_UPDATER_LOG_DIR ) ) ) && is_file( $file ) && is_writable( $file ) ) { // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_is_writable, WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
				$removed = @unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
			}
		}

		return $removed;
	}

	/**
	 * @param string $handle
	 * @return void
	 */
	public static function download( $handle ) {
		$logs   = self::get_log_files();
		$handle = sanitize_title( $handle );

		if ( isset( $logs[ $handle ] ) && $logs[ $handle ] ) {
			$file = realpath( trailingslashit( GV_UPDATER_LOG_DIR ) . $logs[ $handle ] );
			if ( 0 === stripos( $file, realpath( trailingslashit( GV_UPDATER_LOG_DIR ) ) ) && is_file( $file ) && is_readable( $file ) ) { // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_is_writable
				header( 'Content-Description: File Transfer' );
				header( 'Content-Disposition: attachment; filename=' . basename( $file ) );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate' );
				header( 'Pragma: public' );
				header( 'Content-Length: ' . filesize( $file ) );
				header( 'Content-Type: text/plain' );
				readfile( $file ); // @phpcs:ignore
			}
		}
	}
}

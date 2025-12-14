<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @author  YITH
 * @package YITH/PluginUpgrade
 */

declare( strict_types = 1 );

namespace YITH\PluginUpgrade;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

if ( ! class_exists( 'YITH\PluginUpgrade\Debug' ) ) :
	/**
	 * Static class collecting debug utils methods
	 *
	 * @since 5.0.0
	 * @package YITH/PluginUpgrade
	 */
	class Debug {

		/**
		 * Check if debug is active
		 *
		 * @since 5.0.0
		 * @return boolean
		 */
		public static function is_enabled(): bool {
			return defined( 'YIT_LICENCE_DEBUG' ) && \YIT_LICENCE_DEBUG;
		}

		/**
		 * Check if log is enabled
		 *
		 * @since 5.0.0
		 * @return bool
		 */
		public static function is_log_enabled() {
			return self::is_enabled() || ( isset( $_REQUEST['yith-license-debug'] ) && 'true' === sanitize_text_field( wp_unslash( $_REQUEST['yith-license-debug'] ) ) ); // phpcs:ignore
		}

		/**
		 * Add a log entry
		 *
		 * @since 5.0.0
		 * @param string $entry The entry to log.
		 * @return void
		 */
		public static function log( string $entry ) {
			if ( ! self::is_log_enabled() ) {
				return;
			}

			$file   = self::get_log_filepath();
			$stream = @fopen( $file, 'a' ); // phpcs:ignore

			if ( $stream ) {
				@fwrite( $stream, self::format_entry( $entry ) . PHP_EOL ); // phpcs:ignore
				@fclose( $stream ); // phpcs:ignore

				// Schedule delete file if possible.
				if ( function_exists( 'as_schedule_single_action' ) && ! as_next_scheduled_action( 'yith_plugin_upgrade_clear_log', array( 'file' => $file ) ) ) {
					as_schedule_single_action( strtotime( '+ 1 month' ), 'yith_plugin_upgrade_clear_log', array( 'file' => $file ) );
				}
			}
		}

		/**
		 * Delete given log file
		 *
		 * @since 5.0.0
		 * @param string $file The log file to delete.
		 * @return void
		 */
		public static function delete_log( string $file ) {
			file_exists( $file ) && wp_delete_file( $file );
		}

		/**
		 * Get the log filename
		 *
		 * @since 5.0.0
		 * @return string
		 */
		protected static function get_log_filename(): string {
			return sanitize_file_name( implode( '-', array( 'yith-licence', wp_date( 'Y-m-d', time() ) ) ) . '.log' );
		}

		/**
		 * Get the log file path
		 *
		 * @since 5.0.0
		 * @return string
		 */
		protected static function get_log_filepath(): string {
			// Use wc-logs or create e dedicated path.
			$path = defined( 'WC_LOG_DIR' ) ? \WC_LOG_DIR : self::get_log_path();
			return $path . self::get_log_filename();
		}

		/**
		 * Format a log entry
		 *
		 * @since 5.0.0
		 * @param string $entry The entry to format.
		 * @return string
		 */
		protected static function format_entry( string $entry ): string {
			$time = date_i18n( 'm-d-Y @ H:i:s' );
			return "$time - $entry";
		}

		/**
		 * Get base log path
		 *
		 * @since 5.0.0
		 * @return string
		 */
		protected static function get_log_path(): string {
			$upload_dir = wp_upload_dir( null, false );
			$path       = $upload_dir['basedir'] . '/yith-plugin-upgrade/';

			if ( ! file_exists( $path ) && wp_mkdir_p( $path ) ) {
				$files = array(
					'index.html' => '',
					'.htaccess'  => 'deny from all',
				);

				foreach ( $files as $filename => $content ) {
					$file = trailingslashit( $path ) . $filename;
					if ( file_exists( $file ) ) {
						continue;
					}

					$stream = @fopen( $file, 'wb' ); // phpcs:ignore
					if ( $stream && $content ) {
						@fwrite( $stream, $content ); // phpcs:ignore
						@fclose( $stream ); // phpcs:ignore
					}
				}
			}

			return $path;
		}
	}

endif;

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

namespace YITH\PluginUpgrade\Legacy;

use WP_Error;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

if ( ! class_exists( 'YITH\PluginUpgrade\Legacy\Upgrade' ) ) :
	/**
	 * Legacy upgrade plugin class.
	 * Handle download/update package
	 *
	 * @since 5.0.0
	 */
	abstract class Upgrade {

		/**
		 * XML notifier update
		 *
		 * @var string
		 */
		protected $remote_url = 'https://update.yithemes.com/plugin-xml.php';

		/**
		 * The api server url
		 *
		 * @var string
		 */
		protected $package_url = 'https://licence.yithemes.com/api/download/';

		/**
		 * The registered plugins
		 *
		 * @var array
		 */
		protected $plugins = array();

		/**
		 * Current plugin upgrading
		 *
		 * @var string
		 */
		protected $plugin_upgrading = '';

		/**
		 * Retrieve the remote url with query string args
		 *
		 * @since 5.0.0
		 * @param array $plugin_info The plugin info array.
		 * @return string the remote url
		 * @deprecated
		 */
		public function get_remote_url( array $plugin_info ): string {
			$plugin_init = $this->get_init_from_slug( $plugin_info['slug'] );
			return $plugin_init ? $this->get_xml_url( $plugin_init ) : '';
		}

		/**
		 * Get plugin init from slug
		 *
		 * @since 5.0.0
		 * @param string $slug The plugin slug.
		 * @return string
		 */
		protected function get_init_from_slug( string $slug ): string {
			$plugin_init = '';
			foreach ( $this->plugins as $init => $plugin ) {
				if ( $slug === $plugin['slug'] ) {
					$plugin_init = $init;
					break;
				}
			}

			return $plugin_init;
		}

		/**
		 * Retrieve the temp filename
		 *
		 * @since    5.0.0
		 * @param string $url     The package url.
		 * @param array  $body    The post data fields.
		 * @param int    $timeout Execution timeout (default: 300).
		 * @return string|WP_Error The temp filename
		 * @see      wp-admin/includes/class-wp-upgrader.php
		 * @deprecated
		 */
		protected function download_url( string $url, array $body, int $timeout = 300 ) {
			// WARNING: The file is not automatically deleted, The script must unlink() the file.
			if ( ! $url ) {
				return new WP_Error( 'http_no_url', esc_html__( 'Invalid URL Provided.', 'yith-plugin-upgrade-fw' ) );
			}

			$tmpfname = wp_tempnam( $url );
			$args     = array(
				'timeout'  => $timeout,
				'stream'   => true,
				'filename' => $tmpfname,
				'body'     => $body,
			);

			if ( ! $tmpfname ) {
				return new WP_Error( 'http_no_file', esc_html__( 'Could not create Temporary file.', 'yith-plugin-upgrade-fw' ) );
			}

			$response = wp_safe_remote_get( $url, $args );

			if ( is_wp_error( $response ) ) {
				wp_delete_file( $tmpfname );

				return $response;
			}

			$response_code = intval( wp_remote_retrieve_response_code( $response ) );
			// Firstly we check if yithemes gives a 404 error. In this case the upgrade won't check on backup system.
			if ( 404 === $response_code ) {
				wp_delete_file( $tmpfname );

				return new WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) );

			} elseif ( 200 !== $response_code ) {
				// If the error code is not 404 but neither a 200 then the upgrade will check on backup system.
				$body = array_merge(
					array(
						'wc-api'  => 'download-api',
						'request' => 'download',
					),
					$body
				);
				$url  = add_query_arg( $body, 'https://casper.yithemes.com' );
				unset( $args['body'] );

				$response = wp_safe_remote_get( $url, $args );

				if ( is_wp_error( $response ) || 200 !== intval( wp_remote_retrieve_response_code( $response ) ) ) {
					// If errors persists also on backup system then we throw an error.
					wp_delete_file( $tmpfname );

					return new WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) );
				}
			}

			$content_md5 = wp_remote_retrieve_header( $response, 'content-md5' );

			if ( $content_md5 ) {
				$md5_check = verify_file_md5( $tmpfname, $content_md5 );
				if ( is_wp_error( $md5_check ) ) {
					wp_delete_file( $tmpfname );

					return $md5_check;
				}
			}

			return $tmpfname;
		}

		/**
		 * Retrieve the remote url with query string args
		 *
		 * @since 5.0.0
		 * @param string $plugin_init The plugin init.
		 * @return string the remote url
		 */
		abstract protected function get_xml_url( string $plugin_init ): string;
	}
endif;

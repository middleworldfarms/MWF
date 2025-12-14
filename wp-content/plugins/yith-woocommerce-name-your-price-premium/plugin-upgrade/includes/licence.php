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

use Exception;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

if ( ! class_exists( 'YITH\PluginUpgrade\Licence' ) ) :
	/**
	 * Base Licence class
	 * All classes that wants to handle actions for a specific product will implement abstract part of this class
	 * Instances of this class will be handled by YITH\PluginUpgrade\Licences.
	 *
	 * @since 5.0.0
	 * @package YITH/PluginUpgrade
	 */
	abstract class Licence {

		/**
		 * ID of product licence
		 *
		 * @since 5.0.0
		 * @var string
		 */
		protected $id;

		/**
		 * Type of product licence
		 *
		 * @since 5.0.0
		 * @var string
		 */
		protected $type;

		/**
		 * Product licence data
		 *
		 * @since 5.0.0
		 * @var array
		 */
		protected $data = array();

		/**
		 * Product licence data schema
		 *
		 * @since 5.0.0
		 * @var array
		 */
		protected $schema = array();

		/**
		 * Class construct
		 *
		 * @since 5.0.0
		 * @param string $id   The plugin licence id.
		 * @param array  $data (Optional) The licence data array. Default empty array.
		 * @return void
		 */
		public function __construct( string $id, array $data = array() ) {
			$this->id   = $id;
			$this->data = $this->parse_data( $data );
		}

		/**
		 * Magic GET method
		 *
		 * @since 5.0.0
		 * @param string $key The data key to retrieve.
		 * @return mixed The key related value, empty string if not found.
		 */
		public function __get( string $key ) {
			return $this->data[ $key ] ?? '';
		}

		/**
		 * Get the licence product ID
		 *
		 * @since 5.0.0
		 * @return string
		 */
		public function get_id(): string {
			return $this->id;
		}

		/**
		 * Get the licence product data
		 *
		 * @since 5.0.0
		 * @return array
		 */
		public function get_data(): array {
			return $this->data;
		}

		/**
		 * Get the licence type
		 *
		 * @since 5.0.0
		 * @return string
		 */
		public function get_type(): string {
			return $this->type;
		}

		/**
		 * Check if licence is activated
		 *
		 * @since 5.0.0
		 * @return boolean
		 */
		public function is_activated(): bool {
			return false;
		}

		/**
		 * Check if licence is banned
		 *
		 * @since 5.0.0
		 * @return boolean
		 */
		public function is_banned(): bool {
			return false;
		}

		/**
		 * Check if licence is expired
		 *
		 * @since 5.0.0
		 * @return boolean
		 */
		public function is_expired(): bool {
			return false;
		}

		/**
		 * Parse licence data with schema
		 *
		 * @since 5.0.0
		 * @param array $data The data to parse.
		 * @return array
		 */
		protected function parse_data( array $data ): array {
			$tmp = array_intersect_key( $data, $this->schema );
			return array_merge( $this->schema, $tmp );
		}

		/**
		 * Returns url to call when performing an API call
		 *
		 * @param string $endpoint Endpoint to call on the remote server.
		 * @param bool   $alt      Whether to use alternative url.
		 * @return string API url to call.
		 */
		protected function get_api_url( string $endpoint = '', bool $alt = false ): string {
			$api_url = ! $alt ? 'https://licence.yithemes.com/api/%request%' : 'https://casper.yithemes.com/wc-api/software-api/';
			if ( Debug::is_enabled() ) {
				$api_url = defined( 'YIT_LICENCE_DEBUG_LOCALHOST' ) ? \YIT_LICENCE_DEBUG_LOCALHOST : 'https://staging-licenceyithemes-staging.kinsta.cloud/api/%request%';
			}

			return str_replace( '%request%', $endpoint, $api_url );
		}

		/**
		 * Returns url to call when performing an API download call
		 *
		 * @param string $endpoint Endpoint to call on the remote server.
		 * @param bool   $alt      Whether to use alternative url.
		 * @return string API url to call.
		 */
		protected function get_download_api_url( string $endpoint = '', bool $alt = false ): string {
			$api_url = ! $alt ? 'https://licence.yithemes.com/api/%request%' : 'https://casper.yithemes.com/wc-api/download-api/';
			if ( Debug::is_enabled() ) {
				$api_url = defined( 'YIT_LICENCE_DEBUG_LOCALHOST' ) ? \YIT_LICENCE_DEBUG_LOCALHOST : 'https://staging-licenceyithemes-staging.kinsta.cloud/api/%request%';
			}

			return str_replace( '%request%', $endpoint, $api_url );
		}

		/**
		 * Performs API request against the server
		 *
		 * @param string $endpoint Endpoint to call.
		 * @param array  $body     Array of body parameters (on GET requests, they will become query string).
		 * @param string $method   Method to use for API call.
		 * @param bool   $alt      Optionally use alternative url.
		 *
		 * @return mixed Body of the response coming from remote server.
		 * @throws Exception When an error occurs during API call.
		 */
		protected function do_request( string $endpoint, array $body = array(), string $method = 'POST', bool $alt = false ) {
			$request_args = apply_filters(
				'yith_plugin_fw_do_request_args',
				array(
					'method'  => $method,
					'timeout' => 30,
					'body'    => 'GET' === $method ? $body : wp_json_encode( $body ),
				)
			);

			add_filter( 'block_local_requests', '__return_false' );

			$url      = $this->get_api_url( $endpoint, $alt );
			$response = wp_remote_request( $url, $request_args );

			Debug::log( $url );
			Debug::log( print_r( $request_args, true ) ); // phpcs:ignore
			Debug::log( print_r( $response, true ) ); // phpcs:ignore

			// If response is not valid and request is not the alternative one, call casper!
			if ( ! $alt && ( is_wp_error( $response ) || ! $this->is_valid_response( $response ) ) ) {
				return $this->do_request( $endpoint, $body, 'GET', true ); // do casper request.
			}

			return $response;
		}

		/**
		 * Checks whether the API response is valid
		 *
		 * @since 5.0.0
		 * @param array $response Remote request response.
		 * @return bool Whether response is valid or not.
		 */
		protected function is_valid_response( array $response ): bool {
			return isset( $response['response'] ) && 200 === (int) $response['response']['code'];
		}

		/**
		 * Activate a plugin licence
		 *
		 * @since 5.0.0
		 * @return string|bool Licence activation key, or status of the activation.
		 * @throws Exception When something fails on the activation process.
		 */
		abstract public function activate(): bool;

		/**
		 * Deactivate a plugin licence
		 *
		 * @since 5.0.0
		 * @return boolean
		 * @throws Exception When something fails on the deactivation process.
		 */
		abstract public function deactivate(): bool;

		/**
		 * Check a plugin licence status
		 *
		 * @return boolean True if active, false otherwise
		 */
		abstract public function check(): bool;

		/**
		 * Returns url to download the plugin
		 *
		 * @since 5.0.0
		 * @param bool $alt Whether to use alternative url.
		 * @return string API url to call.
		 */
		abstract public function get_download_url( bool $alt = false ): string;
	}
endif;

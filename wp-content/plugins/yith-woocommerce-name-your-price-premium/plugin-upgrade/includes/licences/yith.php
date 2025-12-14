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

namespace YITH\PluginUpgrade\Licences;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

use Exception;
use YITH\PluginUpgrade\Licence;
use YITH\PluginUpgrade\Utils;
use const HOUR_IN_SECONDS;

if ( ! class_exists( 'YITH\PluginUpgrade\Licences\YITH' ) ) :
	/**
	 * YITH Licence class
	 * This class implements methods for YITH licence handling
	 *
	 * @since 5.0.0
	 */
	class YITH extends Licence {

		/**
		 * Type of product licence
		 *
		 * @since 5.0.0
		 * @var string
		 */
		protected $type = 'yith';

		/**
		 * Default licence data
		 *
		 * @since 5.0.0
		 * @var array
		 */
		protected $schema = array(
			'email'                => '',
			'licence_key'          => '',
			'licence_expires'      => '',
			'licence_next_check'   => 0,
			'banned'               => false,
			'activated'            => false,
			'activation_limit'     => 1,
			'activation_remaining' => 0,
			'is_membership'        => false,
			'product_id'           => '',
			'secret_key'           => '',
		);

		/**
		 * Activate a plugin licence
		 *
		 * @since 5.0.0
		 * @param array $args Array of arguments used to activate a licence.
		 *
		 * @return bool Status of the activation.
		 * @throws Exception When something fails on the activation process.
		 */
		public function activate( array $args = array() ): bool {
			$args = wp_parse_args(
				array_merge( $this->data, $args ),
				array(
					'email'       => '',
					'licence_key' => '',
					'product_id'  => '',
					'secret_key'  => '',
					'instance'    => Utils::get_home_url(),
					'request'     => 'activation',
				)
			);

			if ( ! $args['email'] || ! $args['licence_key'] || ! $args['secret_key'] ) {
				throw new Exception( 'invalid_activation' );
			}

			$response = $this->do_request( 'activation', $args );

			if ( is_wp_error( $response ) ) {
				throw new Exception( esc_html( $response->get_error_message() ) );
			}

			$body = @json_decode( $response['body'], true ); // phpcs:ignore

			if ( ! $body ) {
				throw new Exception( 'invalid_response' );
			}

			$activated = isset( $body['activated'] ) && $body['activated'];
			$code      = ! empty( $body['code'] ) ? (int) $body['code'] : 100;

			if ( ! $activated ) {
				throw new Exception( esc_html( $this->get_error_message( $code ) ) );
			}

			$this->data = $this->parse_data(
				array_merge(
					$this->data,
					array(
						'activated'            => true,
						'banned'               => false,
						'email'                => urldecode( $args['email'] ),
						'licence_key'          => $args['licence_key'],
						'licence_expires'      => $body['licence_expires'],
						'activation_limit'     => $body['activation_limit'],
						'activation_remaining' => $body['activation_remaining'],
						'is_membership'        => isset( $body['is_membership'] ) && ! ! $body['is_membership'],
					)
				)
			);

			return true;
		}

		/**
		 * Deactivate a plugin licence
		 *
		 * @since 5.0.0
		 * @param array $args  Optional array of parameters for the operation.
		 * @param bool  $force Whether to force deactivation.
		 * @return boolean
		 * @throws Exception When something fails on the activation process.
		 */
		public function deactivate( array $args = array(), bool $force = false ): bool {
			$args = wp_parse_args(
				array_merge( $this->data, $args ),
				array(
					'email'       => '',
					'licence_key' => '',
					'product_id'  => '',
					'secret_key'  => '',
					'instance'    => Utils::get_home_url(),
					'request'     => 'deactivation',
				)
			);

			if ( ! $args['email'] || ! $args['licence_key'] || ! $args['secret_key'] ) {
				throw new Exception( 'invalid_deactivation' );
			}

			$response = $this->do_request( 'deactivation', $args );

			if ( is_wp_error( $response ) ) {
				throw new Exception( esc_html( $response->get_error_message() ) );
			}

			$body = @json_decode( $response['body'], true ); // phpcs:ignore

			if ( ! $body ) {
				throw new Exception( 'invalid_response' );
			}

			$activated = isset( $body['activated'] ) && $body['activated'];
			$code      = ! empty( $body['code'] ) ? (int) $body['code'] : 100;

			// 100 ok, 106 banned, 107 expired.
			if ( ( in_array( $code, array( 100, 106, 107 ), true ) && ! $activated ) || $force ) {
				$this->data = array_merge(
					$this->data,
					array(
						'activated'            => false,
						'banned'               => false,
						'email'                => '',
						'licence_key'          => '',
						'licence_expires'      => '',
						'activation_limit'     => '',
						'activation_remaining' => '',
						'is_membership'        => false,
					)
				);

			}

			return ! $activated || $force;
		}

		/**
		 * Check a plugin licence status
		 *
		 * @param array $args  Optional array of parameters for the operation.
		 * @param bool  $force Whether to force deactivation.
		 *
		 * @return boolean True if active, false otherwise
		 * @throws Exception When something fails on the activation process.
		 */
		public function check( array $args = array(), bool $force = false ): bool {
			$args = wp_parse_args(
				array_merge( $this->data, $args ),
				array(
					'email'       => '',
					'licence_key' => '',
					'product_id'  => '',
					'secret_key'  => '',
					'instance'    => Utils::get_home_url(),
					'request'     => 'check',
				)
			);

			if ( ! $this->is_check_needed() && ! $force ) {
				return true;
			}

			if ( ! $args['email'] || ! $args['licence_key'] || ! $args['secret_key'] ) {
				throw new Exception( 'invalid_check' );
			}

			$response = $this->do_request( 'check', $args, 'GET' );
			if ( is_wp_error( $response ) ) {
				throw new Exception( esc_html( $response->get_error_message() ) );
			}

			$body = @json_decode( $response['body'], true ); // phpcs:ignore
			if ( ! $body ) {
				throw new Exception( 'invalid_response' );
			}

			$activated = isset( $body['activated'] ) && $body['activated'];
			$code      = ! empty( $body['code'] ) ? (int) $body['code'] : 100;

			if ( 100 === $code && $activated ) {
				$this->data = array_merge(
					$this->data,
					array(
						'activated'            => true,
						'banned'               => false,
						'email'                => urldecode( $args['email'] ),
						'licence_key'          => $args['licence_key'],
						'licence_expires'      => $body['licence_expires'],
						'activation_limit'     => $body['activation_limit'],
						'activation_remaining' => $body['activation_remaining'],
						'is_membership'        => isset( $body['is_membership'] ) && ! ! $body['is_membership'],
					)
				);
			} elseif ( 100 === $code && ! $activated ) {
				$this->data = array_merge(
					$this->data,
					array(
						'activated'            => false,
						'banned'               => false,
						'email'                => '',
						'licence_key'          => '',
						'licence_expires'      => '',
						'activation_limit'     => 1,
						'activation_remaining' => 0,
						'is_membership'        => false,
					)
				);
			} elseif ( 106 === $code ) { // Licence is expired.
				$this->data = array_merge(
					$this->data,
					array(
						'activated'       => false,
						'banned'          => false,
						'licence_expires' => $body['licence_expires'],
					)
				);
			} elseif ( 107 === $code ) { // Licence is banned.
				$this->data = array_merge(
					$this->data,
					array(
						'activated' => false,
						'banned'    => true,
					)
				);
			}

			$this->data['licence_next_check'] = time() + ( 6 * HOUR_IN_SECONDS );
			if ( ! $activated ) {
				throw new Exception( esc_html( $this->get_error_message( $code ) ) );
			}

			return true;
		}

		/**
		 * Check if licence is activated
		 *
		 * @since 5.0.0
		 * @return boolean
		 */
		public function is_activated(): bool {
			try {
				$this->check();
				return $this->activated;
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Check if licence is banned
		 *
		 * @since 5.0.0
		 * @return boolean
		 */
		public function is_banned(): bool {
			try {
				$this->check();
			} catch ( Exception $e ) {
				// no action required.
			} finally {
				return $this->banned;
			}
		}

		/**
		 * Check if licence is expired
		 *
		 * @since 5.0.0
		 * @return boolean
		 */
		public function is_expired(): bool {
			try {
				$this->check();
			} catch ( Exception $e ) {
				// no action required.
			} finally {
				return $this->licence_expires < time();
			}
		}

		/**
		 * Returns url to download the plugin
		 *
		 * @since 5.0.0
		 * @param bool $alt Whether to use alternative url.
		 * @return string API url to call.
		 */
		public function get_download_url( bool $alt = false ): string {
			$url = parent::get_download_api_url( 'download', $alt );
			return add_query_arg(
				array(
					'email'       => $this->email,
					'licence_key' => $this->licence_key,
					'product_id'  => $this->product_id,
					'secret_key'  => $this->secret_key,
					'instance'    => Utils::get_home_url(),
				),
				$url
			);
		}

		/**
		 * Checks if system needs to perform a new check operation against server.
		 *
		 * @return bool
		 */
		protected function is_check_needed(): bool {
			return empty( $this->data['licence_next_check'] ) || $this->data['licence_next_check'] < time();
		}

		/**
		 * Get the licence information
		 *
		 * @since  1.0
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @param int $code The error code.
		 * @return string The error code message.
		 */
		protected function get_response_code_message( int $code ): string {
			$messages = array(
				100 => __( 'Invalid Request', 'yith-plugin-upgrade-fw' ),
				101 => __( 'Matching license key not found. Be sure to use the email used for the order in YITH and the correct license serial number.', 'yith-plugin-upgrade-fw' ),
				102 => __( 'Software has been deactivated', 'yith-plugin-upgrade-fw' ),
				103 => __( 'Maximum number of license activations reached', 'yith-plugin-upgrade-fw' ),
				104 => __( 'Invalid instance ID', 'yith-plugin-upgrade-fw' ),
				105 => __( 'Invalid security key', 'yith-plugin-upgrade-fw' ),
				106 => __( 'License key has expired', 'yith-plugin-upgrade-fw' ),
				107 => __( 'License key has been revoked', 'yith-plugin-upgrade-fw' ),
				108 => __( 'This product is not included in your YITH Club Subscription Plan', 'yith-plugin-upgrade-fw' ),
				200 => sprintf( '<strong>%s</strong>! %s', __( 'Great', 'yith-plugin-upgrade-fw' ), __( 'License successfully activated', 'yith-plugin-upgrade-fw' ) ),
				999 => '999', // Local use only.
			);

			return $messages[ $code ] ?? '';
		}

		/**
		 * Get the licence information
		 *
		 * @since  4.3.0
		 * @author Francesco Licandro
		 * @param int $code The error code.
		 * @return string The error code message.
		 */
		public function get_error_message( int $code ): string {
			return __( 'Error', 'yith-plugin-upgrade-fw' ) . ': ' . $this->get_response_code_message( $code );
		}
	}
endif;

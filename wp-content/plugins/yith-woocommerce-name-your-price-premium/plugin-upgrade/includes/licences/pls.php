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
use const MINUTE_IN_SECONDS;

if ( ! class_exists( 'YITH\PluginUpgrade\Licences\PLS' ) ) :
	/**
	 * PLS System Licence class
	 * This class implements methods for PLS licence system
	 *
	 * @since 5.0.0
	 */
	class PLS extends Licence {

		/**
		 * Type of product licence
		 *
		 * @since 5.0.0
		 * @var string
		 */
		protected $type = 'pls';

		/**
		 * Max attempts value
		 *
		 * @since 5.0.0
		 * @const integer
		 */
		const MAX_ATTEMPTS = 5;

		/**
		 * Default PLS licence data
		 *
		 * @since 5.0.0
		 * @var array
		 */
		protected $schema = array(
			'product_id'  => '',
			'licence_key' => '',
			'attempts'    => 0,
		);

		/**
		 * Constructor
		 *
		 * @since 5.0.0
		 * @param string $plugin_init The plugin licence id.
		 * @param array  $data        The licence data array.
		 * @return void
		 */
		public function __construct( string $plugin_init, array $data ) {
			parent::__construct( $plugin_init, $data );

			$this->data['licence_key'] = $this->get_licence();
			if ( ! empty( $this->data['licence_key'] ) ) {
				$this->maybe_activate();

				add_action( 'yith_pls_retry_licence_activation_' . $this->data['product_id'], array( $this, 'maybe_activate' ) );
			}
		}

		/**
		 * Try to activate a specific plugin against PLS system
		 *
		 * @sinxe 5.0.0
		 * @return void
		 */
		public function maybe_activate() {

			if ( $this->is_activated() ) {
				return;
			}

			$hook_name      = 'yith_pls_retry_licence_activation_' . $this->data['product_id'];
			$transient_name = 'yith_pls_is_activating_licence_' . $this->data['product_id'];
			$is_executing   = ! ! get_transient( $transient_name );

			if ( $is_executing || ( function_exists( 'as_next_scheduled_action' ) && as_next_scheduled_action( $hook_name ) ) || $this->data['attempts'] >= self::MAX_ATTEMPTS ) {
				return;
			}

			try {
				set_transient( $transient_name, true );
				$this->activate();

				$this->data['attempts'] = 0; // If activation process is completed, reset attempts.
			} catch ( Exception $e ) {
				if ( function_exists( 'as_schedule_single_action' ) ) {
					as_schedule_single_action( time() + 10 * MINUTE_IN_SECONDS, $hook_name );
				}

				++$this->data['attempts'];
			} finally {
				delete_transient( $transient_name );
			}
		}

		/**
		 * Activate a plugin licence
		 *
		 * @since 5.0.0
		 * @return bool Licence activation key.
		 * @throws Exception When something fails on the activation process.
		 */
		public function activate(): bool {

			$res = \NewfoldLabs\WP\PLS\PLS::activate(
				$this->data['product_id'],
				$this->data['licence_key'],
				array(
					'instance' => Utils::get_home_url(),
					'email'    => get_bloginfo( 'admin_email' ),
				)
			);

			if ( is_wp_error( $res ) ) {
				throw new Exception( esc_html( $res->get_error_message() ) );
			}

			return $res;
		}

		/**
		 * Deactivate a plugin licence
		 *
		 * @since 5.0.0
		 * @return boolean
		 * @throws Exception When something fails on the deactivation process.
		 */
		public function deactivate(): bool {
			$res = \NewfoldLabs\WP\PLS\PLS::deactivate( $this->data['product_id'] );

			if ( is_wp_error( $res ) ) {
				throw new Exception( esc_html( $res->get_error_message() ) );
			}

			return $res;
		}

		/**
		 * Returns url to download the plugin
		 *
		 * @since 5.0.0
		 * @param bool $alt Whether to use alternative url.
		 * @return string API url to call.
		 */
		public function get_download_url( bool $alt = false ): string {
			$url = parent::get_download_api_url( 'pls/download', $alt );
			return add_query_arg(
				array(
					'licence_key'    => $this->data['licence_key'],
					'software_id'    => $this->data['product_id'],
					'activation_key' => rawurlencode( \NewfoldLabs\WP\PLS\PLS::get_activation_key( $this->data['licence_key'] ) ),
				),
				$url
			);
		}

		/**
		 * Check a plugin license status
		 *
		 * @return boolean True if active, false otherwise
		 */
		public function check(): bool {
			$active = \NewfoldLabs\WP\PLS\PLS::check( $this->product_id );
			return $active && ! is_wp_error( $active );
		}

		/**
		 * Check if licence is activated.
		 * Alias for check.
		 *
		 * @since 5.0.0
		 * @return boolean
		 */
		public function is_activated(): bool {
			return $this->check();
		}

		/**
		 * Returns PLS licence id registered for current software
		 *
		 * @since 5.0.0
		 * @return string PLS licence id for current plugin, if any.
		 */
		public function get_licence(): string {
			$licence = \NewfoldLabs\WP\PLS\PLS::get_license_id( $this->product_id );
			return ! is_wp_error( $licence ) ? $licence : '';
		}
	}
endif;

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

declare( strict_types=1 );

namespace YITH\PluginUpgrade;

use NewfoldLabs\WP\PLS\PLS;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

if ( ! class_exists( 'YITH\PluginUpgrade\Services' ) ) :
	/**
	 * Services class.
	 *
	 * @since   5.0.8
	 * @package YITH/PluginUpgrade
	 */
	class Services {

		/**
		 * The single instance of the class
		 *
		 * @since 5.1.0
		 * @var Services|null
		 */
		protected static $instance = null;

		/**
		 * Services class instance
		 *
		 * @return Services Main instance
		 * @since  5.1.0
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Class constructor
		 *
		 * @return void
		 * @since 5.1.0
		 */
		protected function __construct() {}

		/**
		 * Get the tokens stored inside the options
		 *
		 * @return array
		 * @since  5.1.0
		 */
		protected function get_tokens() {
			return get_option( 'yith_services_tokens', array() );
		}

		/**
		 * Get the plugin token stored
		 *
		 * @param string $slug  Plugin slug.
		 * @param string $plugin_init Plugin init.
		 * @return string|bool
		 * @since  5.1.0
		 * @throws  \Exception Error on get token.
		 */
		public function get_token( string $slug, string $plugin_init ) {

			$licence = $this->get_licence_info( $plugin_init );

			if ( is_null( $licence ) || ! $licence->is_activated() ) {
				throw new \Exception( __( 'Impossible to proceed with token request. Licence is not active', 'yith-plugin-upgrade-fw' ) );
			}

			$tokens = $this->get_tokens();

			if ( $tokens && isset( $tokens[ $slug ] ) && $tokens[ $slug ]['exp'] > current_time( 'timestamp' ) ) { // phpcs:ignore
				return $tokens[ $slug ]['token'];
			}

			$token = $this->get_new_token( $licence );
			if ( ! empty( $token ) ) {
				$this->set_token( $token['token'], $slug );
			}

			return $token['token'];
		}

		/**
		 * Return the valid args to validate the licence
		 *
		 * @param Licence $licence The licence object.
		 * @return array
		 */
		protected function get_auth_args( Licence $licence ) {

			$args = array(
				'licenceKey' => $licence->licence_key,
				'productID'  => $licence->product_id,
				'instance'   => Utils::get_home_url(),
			);

			if ( 'yith' === $licence->get_type() ) {
				$additional_args = array(
					'email'       => $licence->email,
					'secretKey'   => $licence->secret_key,
					'licenceType' => 'yith',
				);
			} else {
				$additional_args = array(
					'licenceType'   => 'pls',
					'licenceKey'    => $licence->licence_key,
					'activationKey' => PLS::get_activation_key( $licence->licence_key ),
					'environment'   => Debug::is_enabled() ? 'staging' : 'production',
				);
			}

			return array_merge( $args, $additional_args );
		}
		/**
		 * Get a new token for the plugin
		 *
		 * @param  Licence $licence  Licence object.
		 * @return array|bool
		 * @since  5.1.0
		 * @throws  \Exception Error on get new token.
		 */
		public function get_new_token( Licence $licence ) {
			$token = array();

			$body_args = $this->get_auth_args( $licence );
			$args      = array(
				'body' => wp_json_encode(
					$body_args
				),
			);

			try {
				if ( ! class_exists( 'YITH_External_Services' ) ) {
					throw new \Exception( __( 'YITH_External_Services not found', 'yith-plugin-upgrade-fw' ) );
				}

				$token = \YITH_External_Services::get_instance()->auth( $args );
			} catch ( \Exception $e ) {
				throw new \Exception( __( 'Error: Unable to get a valid token. Reason:' . $e->getMessage(), 'yith-plugin-upgrade-fw' ) );
			}

			return $token;
		}


		/**
		 * Save the token inside tokens options
		 *
		 * @param   array  $token  Token.
		 * @param   string $slug   Plugin slug.
		 * @return void
		 * @since   5.1.0
		 */
		public function set_token( $token, $slug ) {
			$tokens          = $this->get_tokens();
			$tokens[ $slug ] = $token;
			update_option( 'yith_services_tokens', $tokens );
		}

		/**
		 * Get the licence information of the plugin
		 *
		 * @param string $plugin_init  Plugin init.
		 * @return Licence|null The licence requested
		 */
		protected function get_licence_info( string $plugin_init ) {
			if ( class_exists( 'YITH\PluginUpgrade\Licences' ) ) {
				$licences = Licences::instance();

				if ( is_callable( array( $licences, 'get_licence' ) ) ) {
					return $licences->get_single_licence( $plugin_init );
				}
			}

			return null;
		}
	}

endif;

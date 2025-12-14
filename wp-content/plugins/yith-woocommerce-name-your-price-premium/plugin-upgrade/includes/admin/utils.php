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

namespace YITH\PluginUpgrade\Admin;

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

if ( ! class_exists( 'YITH\PluginUpgrade\Admin\Utils' ) ) :
	/**
	 * A collection of utils methods
	 *
	 * @since 5.0.0
	 */
	class Utils {

		/**
		 * Get the licence product name to display
		 *
		 * @since 5.0.0
		 * @param string $plugin_init The plugin init.
		 * @return string the product name
		 */
		public static function display_product_name( string $plugin_init ): string {
			$plugins = get_plugins();
			if ( ! isset( $plugins[ $plugin_init ] ) ) {
				return $plugin_init;
			}

			return str_replace(
				array(
					'for WooCommerce',
					'for WordPress',
					'WooCommerce',
					'Premium',
					'Theme',
					'WordPress',
					'Plugin',
				),
				'',
				$plugins[ $plugin_init ]['Name']
			);
		}

		/**
		 * Return the email to display in activation row
		 *
		 * @since 5.0.0
		 * @param string $email The licence email to format.
		 * @return string The licence email formatted for activation row
		 */
		public static function display_activation_licence_email( string $email ): string {

			$split              = explode( '@', $email );
			$len                = strlen( $split[0] );
			$email_start_length = 0;

			if ( $len > 3 ) {
				$email_start_length = 3;
			} elseif ( $len > 2 ) {
				$email_start_length = 2;
			} elseif ( $len > 1 ) {
				$email_start_length = 1;
			}

			$email_start      = substr( $split[0], 0, $email_start_length );
			$email_anonymized = str_repeat( '*', ( $len - $email_start_length ) );

			return $email_start . $email_anonymized . '@' . $split[1];
		}
	}
endif;

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

defined( 'ABSPATH' ) || exit;  // Exit if accessed directly.

if ( ! class_exists( 'YITH\PluginUpgrade\Utils' ) ) :
	/**
	 * Collection of utils methods.
	 *
	 * @since   5.2.0
	 * @package YITH/PluginUpgrade
	 */
	class Utils {

		/**
		 * Get the home url without protocol
		 *
		 * @since  5.2.0
		 * @return string The home url.
		 */
		public static function get_home_url(): string {

			add_filter( 'wpml_get_home_url', array( __CLASS__, 'filter_wpml_home_url' ), PHP_INT_MAX, 2 );
			add_filter( 'trp_home_url', array( __CLASS__, 'filter_translatepress_home_url' ), PHP_INT_MAX, 2 );

			$home_url = home_url();

			remove_filter( 'wpml_get_home_url', array( __CLASS__, 'filter_wpml_home_url' ), PHP_INT_MAX );
			remove_filter( 'trp_home_url', array( __CLASS__, 'filter_translatepress_home_url' ), PHP_INT_MAX );

			$schemes = array( 'https://', 'http://', 'www.' );

			foreach ( $schemes as $scheme ) {
				$home_url = str_replace( $scheme, '', $home_url );
			}

			if ( false !== strpos( $home_url, '?' ) ) {
				list( $base, $query ) = explode( '?', $home_url, 2 );
				$home_url             = $base;
			}

			return untrailingslashit( $home_url );
		}

		/**
		 * Prevent WPML filter home url.
		 *
		 * @since 5.2.0
		 * @param string $wpml_home_url Filtered WPML home url.
		 * @param string $original_home_url Original home url.
		 * @return string
		 */
		public static function filter_wpml_home_url( $wpml_home_url, $original_home_url ) {
			return $original_home_url;
		}

		/**
		 * Prevent TranslatePress filter home url.
		 *
		 * @since 5.2.0
		 * @param string $translatepress_home_url Filtered TranslatePress home url.
		 * @param string $abs_home_url Absolute home url.
		 * @return string
		 */
		public static function filter_translatepress_home_url( $translatepress_home_url, $abs_home_url ) {
			return $abs_home_url;
		}
	}

endif;

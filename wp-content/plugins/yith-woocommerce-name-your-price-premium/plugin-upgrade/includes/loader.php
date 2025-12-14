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

if ( ! class_exists( 'YITH\PluginUpgrade\Loader', false ) ) :
	/**
	 * Loader module class
	 *
	 * @since 5.0.0
	 * @package YITH/PluginUpgrade
	 */
	class Loader {

		/**
		 * Init class
		 *
		 * @since 5.0.0
		 */
		public static function init() {
			self::define_constant();

			// Load legacy function file to grant backward compatibility.
			include_once YITH_PLUGIN_UPGRADE_PATH . '/includes/legacy/functions.php';
			// Register module autoloader.
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( __CLASS__, 'autoload' ) );

			// Define class aliases for backward compatibility.
			self::define_class_aliases();

			// Load text-domain.
			self::load_textdomain();

			// Common hooks.
			add_action( 'yith_plugin_upgrade_clear_log', 'YITH\PluginUpgrade\Debug::delete_log' );
		}

		/**
		 * Autoload requested file
		 *
		 * @since 5.0.0
		 * @param string $classname The class name to load.
		 * @return void
		 */
		public static function autoload( string $classname ) {
			if ( false === strpos( $classname, 'YITH\PluginUpgrade' ) ) {
				return;
			}

			$class_path = str_replace( array( 'YITH\PluginUpgrade', '\\' ), array( '', '/' ), $classname );
			$file       = YITH_PLUGIN_UPGRADE_PATH . '/includes' . strtolower( $class_path ) . '.php';

			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}

		/**
		 * Define constant
		 *
		 * @since 5.0.0
		 */
		protected static function define_constant() {
			defined( 'YITH_PLUGIN_UPGRADE_PATH' ) || define( 'YITH_PLUGIN_UPGRADE_PATH', dirname( __DIR__ ) );
			defined( 'YITH_PLUGIN_UPGRADE_URL' ) || define( 'YITH_PLUGIN_UPGRADE_URL', untrailingslashit( plugin_dir_url( __DIR__ ) ) );
			defined( 'YITH_PLUGIN_UPGRADE_VERSION' ) || define( 'YITH_PLUGIN_UPGRADE_VERSION', self::get_version() );
		}

		/**
		 * Get module version
		 *
		 * @since 5.0.0
		 * @return string
		 */
		protected static function get_version(): string {
			$plugin_version = get_file_data( YITH_PLUGIN_UPGRADE_PATH . '/init.php', array( 'version' ) );
			return ! empty( $plugin_version ) ? $plugin_version[0] : '1.0.0';
		}

		/**
		 * Define class aliases
		 *
		 * @since 5.0.0
		 */
		protected static function define_class_aliases() {
			class_alias( 'YITH\PluginUpgrade\Licences', 'YITH_Plugin_Licence' );
			class_alias( 'YITH\PluginUpgrade\Upgrade', 'YITH_Plugin_Upgrade' );
		}

		/**
		 * Load module text-domain
		 *
		 * @since 5.0.0
		 * @return void
		 */
		protected static function load_textdomain() {
			load_textdomain( 'yith-plugin-upgrade-fw', YITH_PLUGIN_UPGRADE_PATH . '/languages/yith-plugin-upgrade-fw-' . apply_filters( 'plugin_locale', get_locale(), 'yith-plugin-upgrade-fw' ) . '.mo' );
		}
	}

endif;

Loader::init();

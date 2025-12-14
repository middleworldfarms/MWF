<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Name Your Price Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_WC_Name_Your_Price_Compatibility' ) ) {

	/**
	 * YITH_WC_Name_Your_Price_Compatibility
	 */
	class YITH_WC_Name_Your_Price_Compatibility {


		/**
		 * Single instance of the class
		 *
		 * @var YITH_WC_Name_Your_Price_Compatibility static instance
		 */
		protected static $instance;
		/**
		 * __construct function
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->include_compatibility_files();
		}

		/**
		 * Return single instance
		 *
		 * @since 1.0.0
		 * @return YITH_WC_Name_Your_Price_Compatibility
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Include compatibility files
		 *
		 * @access public
		 * @since  1.0.0
		 */
		private function include_compatibility_files() {
			$compatibility_dir = YWCNP_INC . 'classes/compatibility/';

			$files = array(
				$compatibility_dir . 'class.yith-wc-name-your-price-multivendor-compatibility.php',
				$compatibility_dir . 'class.yith-category-rule-vendor-table.php',
				$compatibility_dir . 'class.yith-wc-name-your-price-save-for-later-compatibility.php',
				$compatibility_dir . 'class.yith-wc-name-your-price-yith-wcmcs.php',

			);

			/**WooCommerce MultiLingual*/
			if ( class_exists( 'woocommerce_wpml' ) ) {
				require_once 'class.yith-wc-name-your-price-wcml.php';
			}

			if ( defined( 'YITH_YWRAQ_PREMIUM' ) && version_compare( YITH_YWRAQ_VERSION, '2.0.13', '>=' ) ) {
				require_once 'class.yith-wc-name-your-price-raq.php';
			}

			foreach ( $files as $file ) {
				file_exists( $file ) && require_once $file;
			}

		}

		/**
		 * Check if user has YITH Multivendor Premium plugin
		 *
		 * @since  1.0
		 * @return bool
		 */
		public static function has_multivendor_plugin() {
			return defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM && defined( 'YITH_WPV_VERSION' ) && version_compare( YITH_WPV_VERSION, apply_filters( 'yith_wcpsc_multivendor_min_version', '1.7.1' ), '>=' );
		}
	}
}
/**
 * YITH_Name_Your_Price_Compatibility
 *
 * @return YITH_WC_Name_Your_Price_Compatibility
 */
function YITH_Name_Your_Price_Compatibility() { // phpcs:ignore WordPress.NamingConventions
	return YITH_WC_Name_Your_Price_Compatibility::get_instance();
}

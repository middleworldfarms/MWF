<?php
/**
 * Plugin Name: YITH WooCommerce Name Your Price Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-name-your-price/
 * Description: <code><strong>YITH WooCommerce Name Your Price Premium</strong></code> allows your users to choose how much they want to pay for your products and offers the possibility to set a minimum and a maximum price that your customer can choose on the product page. <a href ="https://yithemes.com">Get more plugins for your e-commerce shop on <strong>YITH</strong></a>
 * Version: 1.45.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-name-your-price
 * Domain Path: /languages/
 * WC requires at least: 9.7
 * WC tested up to: 9.9
 * Requires Plugins: woocommerce
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Name Your Price Premium
 * @version 1.45.0
 */

/*
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * Yith_wc_name_your_price_premium_install_woocommerce_admin_notice
 *
 * @return void
 */
function yith_wc_name_your_price_premium_install_woocommerce_admin_notice() {   ?>
	<div class="error">
		<p><?php esc_html_e( 'YITH WooCommerce Name Your Price Premium is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-name-your-price' ); ?></p>
	</div>
	<?php
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );

if ( ! defined( 'YWCNP_VERSION' ) ) {
	define( 'YWCNP_VERSION', '1.45.0' );
}

if ( ! defined( 'YWCNP_PREMIUM' ) ) {
	define( 'YWCNP_PREMIUM', '1' );
}

if ( ! defined( 'YWCNP_INIT' ) ) {
	define( 'YWCNP_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YWCNP_FILE' ) ) {
	define( 'YWCNP_FILE', __FILE__ );
}

if ( ! defined( 'YWCNP_DIR' ) ) {
	define( 'YWCNP_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YWCNP_URL' ) ) {
	define( 'YWCNP_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YWCNP_ASSETS_URL' ) ) {
	define( 'YWCNP_ASSETS_URL', YWCNP_URL . 'assets/' );
}

if ( ! defined( 'YWCNP_ASSETS_PATH' ) ) {
	define( 'YWCNP_ASSETS_PATH', YWCNP_DIR . 'assets/' );
}

if ( ! defined( 'YWCNP_TEMPLATE_PATH' ) ) {
	define( 'YWCNP_TEMPLATE_PATH', YWCNP_DIR . 'templates/' );
}

if ( ! defined( 'YWCNP_INC' ) ) {
	define( 'YWCNP_INC', YWCNP_DIR . 'includes/' );
}
if ( ! defined( 'YWCNP_SLUG' ) ) {
	define( 'YWCNP_SLUG', 'yith-woocommerce-name-your-price' );
}

if ( ! defined( 'YWCNP_SECRET_KEY' ) ) {

	define( 'YWCNP_SECRET_KEY', '' );
}

// Plugin Framework Loader.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
}

if ( ! function_exists( 'yith_name_your_price_premium_init' ) ) {
	/**
	 * Unique access to instance of YITH_Name_Your_Price class
	 *
	 * @since 1.0.0
	 */
	function yith_name_your_price_premium_init() {
		
		if ( function_exists( 'yith_plugin_fw_load_plugin_textdomain' ) ) {
			yith_plugin_fw_load_plugin_textdomain( 'yith-woocommerce-name-your-price', dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		// Load required classes and functions !

		require_once YWCNP_INC . 'functions.yith-name-your-price.php';
		require_once YWCNP_INC . 'functions.yith-name-your-price-premium.php';
		require_once YWCNP_INC . 'class.yith-custom-table.php';
		require_once YWCNP_TEMPLATE_PATH . 'admin/ywcnp-category-rules.php';
		require_once YWCNP_INC . 'classes/class.yith-wc-name-your-price-admin.php';
		require_once YWCNP_INC . 'classes/compatibility/class.yith-wc-name-your-price-compatibility.php';
		require_once YWCNP_INC . 'classes/class.yith-wc-name-your-price-premium-admin.php';
		require_once YWCNP_INC . 'classes/class.yith-wc-name-your-price-frontend.php';
		require_once YWCNP_INC . 'classes/class.yith-wc-name-your-price-premium-frontend.php';
		require_once YWCNP_INC . 'classes/class.yith-wc-name-your-price.php';
		require_once YWCNP_INC . 'classes/class.yith-wc-name-your-price-premium.php';

		if ( ! empty( $GLOBALS['woocommerce-aelia-currencyswitcher'] ) ) {
			require_once YWCNP_INC . 'classes/compatibility/class.yith-wc-aeliacs-module.php';
		}

		global $YWC_Name_Your_Price; // phpcs:ignore WordPress.NamingConventions
		$YWC_Name_Your_Price = YITH_WooCommerce_Name_Your_Price_Premium::get_instance(); // phpcs:ignore WordPress.NamingConventions

	}
}

add_action( 'ywcnp_premium_init', 'yith_name_your_price_premium_init' );

if ( ! function_exists( 'yith_name_your_price_premium_install' ) ) {

	/**
	 * Yith_name_your_price_premium_install
	 *
	 * @return void
	 */
	function yith_name_your_price_premium_install() {

		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_wc_name_your_price_premium_install_woocommerce_admin_notice' );
		} else {
			/**
			 * DO_ACTION: ywcnp_premium_init
			 *
			 * Hook plugin init.
			 *
			 */
			add_action( 'before_woocommerce_init', 'yith_name_your_price_add_support_hpos_system' );
			do_action( 'ywcnp_premium_init' );
		}
	}
}

add_action( 'plugins_loaded', 'yith_name_your_price_premium_install', 11 );

if ( ! function_exists( 'yith_name_your_price_add_support_hpos_system' ) ) {
    function yith_name_your_price_add_support_hpos_system() {
	    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YWCNP_INIT );
	    }
    }
}

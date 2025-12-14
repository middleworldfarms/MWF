<?php
/**
 * Main class for Cross Promotion Banners.
 *
 * @version 1.0.1
 */

if ( ! defined('ABSPATH') ) {
    exit;
}

if ( version_compare( WBTE_UIEW_CROSS_PROMO_BANNER_VERSION, get_option( 'wbfte_promotion_banner_version', WBTE_UIEW_CROSS_PROMO_BANNER_VERSION ), '==' ) && ! class_exists( 'Wbte_Cross_Promotion_Banners' ) ) {

	class Wbte_Cross_Promotion_Banners {


		public function __construct() {

			/**
			 * Class includes helper functions for pklist invoice cta banner
			 */
			if ( ! get_option( 'wt_hide_invoice_cta_banner' ) ) {
				require_once plugin_dir_path(__FILE__) . 'class-wt-pklist-cta-banner.php';
			}

			/**
			 * Class includes helper functions for smart coupon cta banner
			 */
			if ( ! get_option( 'wt_hide_smart_coupon_cta_banner' ) ) {
				require_once plugin_dir_path(__FILE__) . 'class-wt-smart-coupon-cta-banner.php';
			}

			/**
			 * Class includes helper functions for pklist invoice cta banner
			 */
			if ( ! get_option( 'wt_hide_product_ie_cta_banner' ) ) {
				require_once plugin_dir_path(__FILE__) . 'class-wt-p-iew-cta-banner.php';
			}
				
		}

		public static function get_banner_version() {
			return WBTE_UIEW_CROSS_PROMO_BANNER_VERSION;
		}
	}

	new Wbte_Cross_Promotion_Banners();
}
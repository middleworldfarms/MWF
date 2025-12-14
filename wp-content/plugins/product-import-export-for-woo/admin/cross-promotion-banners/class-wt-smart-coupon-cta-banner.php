<?php
/**
 * Class Wt_Smart_Coupon_Cta_Banner
 *
 * This class is responsible for displaying the CTA banner on the coupon edit page.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (!class_exists('Wt_Smart_Coupon_Cta_Banner')) {
    class Wt_Smart_Coupon_Cta_Banner {
        /**
         * Is BFCM season.
         *
         * @var bool
         */
        private static $is_bfcm_season = false;
        /**
         * Constructor.
         */
        public function __construct() { 
            // Check if premium plugin is active
            if (!in_array('wt-smart-coupon-pro/wt-smart-coupon-pro.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                self::$is_bfcm_season = method_exists( 'Wt_Import_Export_For_Woo_Basic', 'is_bfcm_season' ) && Wt_Import_Export_For_Woo_Basic::is_bfcm_season();

                add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
                add_action('add_meta_boxes', array($this, 'add_meta_box'));
                add_action('wp_ajax_wt_dismiss_smart_coupon_cta_banner', array($this, 'dismiss_banner'));
            }
        }

        /**
         * Enqueue required scripts and styles.
         */
        public function enqueue_scripts($hook) { 
           if (!in_array($hook, array('post.php', 'post-new.php')) || get_post_type() !== 'shop_coupon') {
                return;
            }

            wp_enqueue_style( 
                'wt-wbte-cta-banner',
                plugin_dir_url(__FILE__) . 'assets/css/wbte-cross-promotion-banners.css',
                array(),
                Wbte_Cross_Promotion_Banners::get_banner_version(),
            );

            wp_enqueue_script(
                'wt-wbte-cta-banner',
                plugin_dir_url(__FILE__) . 'assets/js/wbte-cross-promotion-banners.js',
                array('jquery'),
                Wbte_Cross_Promotion_Banners::get_banner_version(),
                true
            );

            // Localize script with AJAX data
            wp_localize_script('wt-wbte-cta-banner', 'wt_smart_coupon_cta_banner_ajax', array(
                'ajax_url' => esc_url( admin_url('admin-ajax.php') ),
                'nonce' => wp_create_nonce('wt_dismiss_smart_coupon_cta_banner_nonce'),
                'action' => 'wt_dismiss_smart_coupon_cta_banner'
            ));
        }

        /**
         * Add the meta box to the coupon edit screen
         */
        public function add_meta_box() {
            if ( !defined( 'WT_SMART_COUPON_DISPLAY_BANNER' ) ){
                add_meta_box(
                    'wbte_coupon_import_export_pro',
                    self::$is_bfcm_season ? ' ' : __( 'Smart Coupons for WooCommerce Pro', 'product-import-export-for-woo' ),
                    array($this, 'render_banner'),
                    'shop_coupon',
                    'side',
                    'low'
                );
                define( 'WT_SMART_COUPON_DISPLAY_BANNER', true );
            }
        }

        /**
         * Render the banner HTML.
         */
        public function render_banner() {
            // Check if banner should be hidden based on option
            $hide_banner = get_option('wt_hide_smart_coupon_cta_banner', false);
            
            $plugin_url = 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_cross_promotion&utm_medium=marketing_coupons_tab&utm_campaign=Smart_coupons';
            $wt_admin_img_path = plugin_dir_url( __FILE__ ) . 'assets/images';
            
            if ($hide_banner) {
                echo '<style>#wbte_coupon_import_export_pro { display: none !important; }</style>';
                return;
            }
            ?>
            <style type="text/css">
				<?php
				if ( self::$is_bfcm_season ) {
					?>
                     #wbte_coupon_import_export_pro .postbox-header{  height: 66px; background: url( <?php echo esc_url( plugin_dir_url(__FILE__ ) . 'assets/images/bfcm-doc-settings-coupon.svg' ); ?> ) no-repeat 18px 0 #FFFBD5; }
					.wbte-cta-banner-features_head_div{ height: 80px; border-bottom: 1px solid #c3c4c7; display: flex; align-items: center; padding-left: 15px; justify-content: center; }
					.wbte-cta-banner-features_head_div img{ width: 50px; }
					.wbte-cta-banner-features_head_div h2{ font-weight: 600 !important; font-size: 13px !important; }
					<?php
				} else {
					echo '#wbte_coupon_import_export_pro .postbox-header{  height:80px; background:url(' . esc_url( $wt_admin_img_path . '/smart-coupon.svg' ) . ') no-repeat 18px 18px #fff; padding-left:65px; margin-bottom:18px; background-size: 45px 45px; }';
				}
				?>
			</style>

            <div class="wbte-cta-banner">
                <div class="wbte-cta-content">

                    <?php
                    if ( self::$is_bfcm_season ) {
                        ?>
                        <div class="wbte-cta-banner-features_head_div">
                            <img src="<?php echo esc_url( $wt_admin_img_path . '/smart-coupon.svg' ); ?>" alt="<?php esc_attr_e( 'upgrade box icon', 'product-import-export-for-woo' ); ?>">
                            <h2><?php esc_html_e( 'Create better coupon campaigns with advanced WooCommerce coupon features', 'product-import-export-for-woo' ); ?></h2>
                        </div>
                        <?php
                    }
                    ?>

                    <div class="wt-cta-features-header">
						<h2 style="font-size: 13px; font-weight: 700; color: #4750CB;"><?php esc_html_e( 'Smart Coupons for WooCommerce Pro', 'product-import-export-for-woo' ); ?></h2>
					</div>

                    <ul class="wbte-cta-features">
                        <li><?php esc_html_e('Auto-apply coupons', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Create attractive Buy X Get Y (BOGO) offers', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Create product quantity/subtotal based discounts', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Offer store credits and gift cards', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Set up smart giveaway campaigns', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Set advanced coupon rules and conditions', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Bulk generate coupons', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Shipping, purchase history, and payment method-based coupons', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Sign up coupons', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Cart abandonment coupons', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Create day-specific deals', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Display coupon banners and widgets', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Import coupons', 'product-import-export-for-woo'); ?></li>
                    </ul>

                    <div class="wbte-cta-footer">
                        <div class="wbte-cta-footer-links">
                            <a href="#" class="wbte-cta-toggle" data-show-text="<?php esc_attr_e('View all premium features', 'product-import-export-for-woo'); ?>" data-hide-text="<?php esc_attr_e('Show less', 'product-import-export-for-woo'); ?>"><?php esc_html_e('View all premium features', 'product-import-export-for-woo'); ?></a>
                            <a href="<?php echo esc_url($plugin_url); ?>" class="wbte-cta-button" target="_blank"><img src="<?php echo esc_url($wt_admin_img_path . '/promote_crown.png');?>" style="width: 15.01px; height: 10.08px; margin-right: 8px;"><?php esc_html_e('Get the plugin', 'product-import-export-for-woo'); ?></a>
                        </div>
                        <a href="#" class="wbte-cta-dismiss" style="display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none;"><?php esc_html_e('Dismiss', 'product-import-export-for-woo'); ?></a>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Handle the dismiss action via AJAX
         */
        public function dismiss_banner() {
            // Check if nonce exists
            if ( ! isset($_POST['nonce']) ) {
                wp_send_json_error(esc_html__('Missing nonce', 'product-import-export-for-woo'));
            }

            // Verify nonce for security
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wt_dismiss_smart_coupon_cta_banner_nonce' ) ) {
                wp_send_json_error(esc_html__('Invalid nonce', 'product-import-export-for-woo'));
            }

            // Check if user has permission
            if (!current_user_can('manage_options')) {
                wp_send_json_error(esc_html__('Insufficient permissions', 'product-import-export-for-woo'));
            }

            // Update the option to hide the banner
            update_option('wt_hide_smart_coupon_cta_banner', true);

            wp_send_json_success('Banner dismissed successfully');
        }
    }

    new Wt_Smart_Coupon_Cta_Banner();
} 
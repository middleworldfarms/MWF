<?php
/**
 * Class Wt_P_IEW_Cta_Banner
 *
 * This class is responsible for displaying the CTA banner on the product edit page.
 */

if ( ! defined('ABSPATH') ) {
    exit;
}


if ( ! class_exists('Wt_P_IEW_Cta_Banner') ) {
    class Wt_P_IEW_Cta_Banner {

         /**
		 * Is BFCM season.
		 * @var bool
		 */
		private static $is_bfcm_season = false;

        /**
         * Constructor.
         */
        public function __construct() {  
            // Check if premium plugin is active
            if (!in_array('wt-import-export-for-woo-product/wt-import-export-for-woo-product.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                self::$is_bfcm_season = method_exists( 'Wt_Import_Export_For_Woo_Basic', 'is_bfcm_season' ) && Wt_Import_Export_For_Woo_Basic::is_bfcm_season();

                add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
                add_action('add_meta_boxes', array($this, 'add_meta_box'));
                add_action('wp_ajax_wt_dismiss_product_ie_cta_banner', array($this, 'dismiss_banner'));
            }
        }
        /**
         * Enqueue required scripts and styles.
         */
        public function enqueue_scripts($hook) {
            if (!in_array($hook, array('post.php', 'post-new.php')) || get_post_type() !== 'product') {
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
            wp_localize_script('wt-wbte-cta-banner', 'wt_product_ie_cta_banner_ajax', array(
                'ajax_url' => esc_url( admin_url('admin-ajax.php') ),
                'nonce' => wp_create_nonce('wt_dismiss_product_ie_cta_banner_nonce'),
                'action' => 'wt_dismiss_product_ie_cta_banner'
            ));
        }

        /**
         * Add the meta box to the product edit screen
         */
        public function add_meta_box() {

            $limit          = 50;
            $limit_exceeded = false;
            $counts         = (array) wp_count_posts( 'product' ); // cached by WP
            $total_products = 0;
            
            foreach ( $counts as $status => $count ) {
                if ( 'trash' === $status ) {
                    continue;
                }
            
                $total_products += (int) $count;
            
                if ( $total_products >= $limit ) {
                    $limit_exceeded = true;
                    break;                    // stop looping
                }
            }

            // Show banner if there are 50 or more products
            if ( !defined( 'WT_PRODUCT_IMPORT_EXPORT_DISPLAY_BANNER' ) && $limit_exceeded ) {
                add_meta_box(
                    'wbte_product_import_export_pro',
                    self::$is_bfcm_season ? ' ' : __( 'Product Import Export for WooCommerce', 'product-import-export-for-woo' ),
                    array($this, 'render_banner'),
                    'product',
                    'side',
                    'low'
                );
                define( 'WT_PRODUCT_IMPORT_EXPORT_DISPLAY_BANNER', true );
            }
        }

        /**
         * Render the banner HTML.
         */
        public function render_banner() {
            // Check if banner should be hidden based on option
            $hide_banner = get_option('wt_hide_product_ie_cta_banner', false);
            
            $plugin_url = 'https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_cross_promotion&utm_medium=add_new_product_tab&utm_campaign=Product_import_export';
            $wt_admin_img_path = plugin_dir_url( __FILE__ ) . 'assets/images';
            
            if ( $hide_banner ) {
                echo '<style>#wbte_product_import_export_pro { display: none !important; }</style>';
                return;
            }
            ?>

            <style type="text/css">
				<?php
				if ( self::$is_bfcm_season ) {
					?>
                     #wbte_product_import_export_pro .postbox-header{  height: 66px; background: url( <?php echo esc_url( plugin_dir_url(__FILE__ ) . 'assets/images/bfcm-doc-settings-coupon.svg' ); ?> ) no-repeat 18px 0 #FFFBD5; }
					.wbte-cta-banner-features_head_div{ height: 80px; border-bottom: 1px solid #c3c4c7; display: flex; align-items: center; padding-left: 15px; justify-content: center; }
					.wbte-cta-banner-features_head_div img{ width: 50px; }
					.wbte-cta-banner-features_head_div h2{ font-weight: 600 !important; font-size: 13px !important; }
					<?php
				} else {
					echo '#wbte_product_import_export_pro .postbox-header{  height:80px; background:url(' . esc_url( $wt_admin_img_path . '/product-ie.svg' ) . ') no-repeat 18px 18px #fff; padding-left:65px; margin-bottom:18px; background-size: 45px 45px; }';
				}
				?>
			</style>
            
            <div class="wbte-cta-banner">
                <div class="wbte-cta-content">
                    <?php
                    if ( self::$is_bfcm_season ) {
                        ?>
                        <div class="wbte-cta-banner-features_head_div">
                            <img src="<?php echo esc_url( $wt_admin_img_path . '/product-ie.svg' ); ?>" alt="<?php esc_attr_e( 'upgrade box icon', 'product-import-export-for-woo' ); ?>">
                            <h2><?php esc_html_e( 'Product Import Export for WooCommerce', 'product-import-export-for-woo' ); ?></h2>
                        </div>
                        <?php
                    }
                    ?>

                    <ul class="wbte-cta-features">
                        <li><?php esc_html_e('Import, export, or update WooCommerce products', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Supports all types of products (Simple, variable, subscription grouped, and external)', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Multiple file formats - CSV, XML, Excel, and TSV', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Advanced filters and customizations for better control', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Bulk update WooCommerce product data', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Import via FTP/SFTP and URL', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Schedule automated import & export', 'product-import-export-for-woo'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Export and Import custom fields and third-party plugin fields', 'product-import-export-for-woo'); ?></li>
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
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wt_dismiss_product_ie_cta_banner_nonce' ) ) {
                wp_send_json_error(esc_html__('Invalid nonce', 'product-import-export-for-woo'));
            }

            // Check if user has permission
            if (!current_user_can('manage_options')) {
                wp_send_json_error(esc_html__('Insufficient permissions', 'product-import-export-for-woo'));
            }

            // Update the option to hide the banner
            update_option('wt_hide_product_ie_cta_banner', true);

            wp_send_json_success('Banner dismissed successfully');
        }
    }

    new Wt_P_IEW_Cta_Banner();
}

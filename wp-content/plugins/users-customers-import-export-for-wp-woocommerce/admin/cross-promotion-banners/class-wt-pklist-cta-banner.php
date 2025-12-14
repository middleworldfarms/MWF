<?php
/**
 * Class Wt_Invoice_Cta_Banner
 *
 * This class is responsible for displaying the CTA banner on the order edit page.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (!class_exists('Wt_Invoice_Cta_Banner')) {
    class Wt_Invoice_Cta_Banner {

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
            if (!in_array('wt-woocommerce-invoice-addon/wt-woocommerce-invoice-addon.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                self::$is_bfcm_season = method_exists( 'Wt_Import_Export_For_Woo_Basic', 'is_bfcm_season' ) && Wt_Import_Export_For_Woo_Basic::is_bfcm_season();

                add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
                add_action('add_meta_boxes', array($this, 'add_meta_box'));
                add_action('wp_ajax_wt_dismiss_invoice_cta_banner', array($this, 'dismiss_banner'));
            }
        }
        /**
         * Enqueue required scripts and styles.
         */
        public function enqueue_scripts($hook) {

            $currentScreen = get_current_screen();
                        
            // Check if current screen is allowed
            if ($currentScreen->id !== 'woocommerce_page_wc-orders' && $currentScreen->id !== 'shop_order') {
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
            wp_localize_script('wt-wbte-cta-banner', 'wt_invoice_cta_banner_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wt_dismiss_invoice_cta_banner_nonce'),
                'action' => 'wt_dismiss_invoice_cta_banner'
            ));
        }

        /**
         * Add the meta box to the product edit screen
         */
        public function add_meta_box() {
            if( !defined( 'WT_PDF_INVOICE_PLUGIN_DISPLAY_BANNER' ) ){
                add_meta_box(
                    'wbte_pdf_invoice_pro',
                    self::$is_bfcm_season ? ' ' : __( 'WooCommerce PDF Invoices, Packing Slips and Credit Notes', 'users-customers-import-export-for-wp-woocommerce' ),
                    array($this, 'render_banner'),
                    array('woocommerce_page_wc-orders', 'shop_order'),
                    'side',
                    'low'
                );
                define( 'WT_PDF_INVOICE_PLUGIN_DISPLAY_BANNER', true );
            }
        }

        /**
         * Render the banner HTML.
         */
        public function render_banner() {
            // Check if banner should be hidden based on option
            $hide_banner = get_option('wt_hide_invoice_cta_banner', false);
            
            $plugin_url = 'https://www.webtoffee.com/product/woocommerce-pdf-invoices-packing-slips/?utm_source=free_plugin_cross_promotion&utm_medium=add_new_order_sidebar&utm_campaign=PDF_invoice';
            $wt_admin_img_path = plugin_dir_url( __FILE__ ) . 'assets/images';
            
            if ($hide_banner) {
                echo '<style>#wbte_pdf_invoice_pro { display: none !important; }</style>';
                return;
            }
            ?>

            <style type="text/css">
                <?php
                if ( self::$is_bfcm_season ) {
                    ?>
                    #wbte_pdf_invoice_pro .postbox-header{  height: 66px; background: url( <?php echo esc_url( plugin_dir_url(__FILE__ ) . 'assets/images/bfcm-doc-settings-coupon.svg' ); ?> ) no-repeat 18px 0 #FFFBD5; }
                    .wbte-cta-banner-features_head_div{ height: 80px; border-bottom: 1px solid #c3c4c7; display: flex; align-items: center; padding-left: 15px; justify-content: center; }
                    .wbte-cta-banner-features_head_div img{ width: 50px; }
                    .wbte-cta-banner-features_head_div h2{ font-weight: 600 !important; font-size: 13px !important; }
                    <?php
                } else {
                    echo '#wbte_pdf_invoice_pro .postbox-header{  height:80px; background:url(' . esc_url( $wt_admin_img_path . '/pdf_invoice.svg' ) . ') no-repeat 18px 18px #fff; padding-left:65px; margin-bottom:18px; background-size: 45px 45px; }';
                }
                ?>
            </style>

            <div class="wbte-cta-banner">
                <div class="wbte-cta-content">

                    <?php
                    if ( self::$is_bfcm_season ) {
                        ?>
                        <div class="wbte-cta-banner-features_head_div">
                            <img src="<?php echo esc_url( $wt_admin_img_path . '/pdf_invoice.svg' ); ?>" alt="<?php esc_attr_e( 'upgrade box icon', 'users-customers-import-export-for-wp-woocommerce' ); ?>">
                            <h2><?php esc_html_e( 'WooCommerce PDF Invoices, Packing Slips and Credit Notes', 'users-customers-import-export-for-wp-woocommerce' ); ?></h2>
                        </div>
                        <?php
                    }
                    ?>

                    <ul class="wbte-cta-features">
                        <li><?php esc_html_e('Automatically generate PDF invoices, packing slips, and credit notes', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                        <li><?php esc_html_e('Use ready-made, customizable templates to match your brand', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                        <li><?php esc_html_e('Print or download invoices individually or in bulk', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                        <li><?php esc_html_e('Set custom invoice numbering for better organization', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Customize documents fully with visual or code editors', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Include VAT, GST, ABN, and other tax details', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Add "Pay Now" link on invoices', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                        <li class="hidden-feature"><?php esc_html_e('Add custom fields to any order document with ease', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                    </ul>

                    <div class="wbte-cta-footer">
                        <div class="wbte-cta-footer-links">
                            <a href="#" class="wbte-cta-toggle" data-show-text="<?php esc_attr_e('View all premium features', 'users-customers-import-export-for-wp-woocommerce'); ?>" data-hide-text="<?php esc_attr_e('Show less', 'users-customers-import-export-for-wp-woocommerce'); ?>"><?php esc_html_e('View all premium features', 'users-customers-import-export-for-wp-woocommerce'); ?></a>
                            <a href="<?php echo esc_url($plugin_url); ?>" class="wbte-cta-button" target="_blank"><img src="<?php echo esc_url($wt_admin_img_path . '/promote_crown.png');?>" style="width: 15.01px; height: 10.08px; margin-right: 8px;"><?php esc_html_e('Get the plugin', 'users-customers-import-export-for-wp-woocommerce'); ?></a>
                        </div>
                        <a href="#" class="wbte-cta-dismiss" style="display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none;"><?php esc_html_e('Dismiss', 'users-customers-import-export-for-wp-woocommerce'); ?></a>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Handle the dismiss action via AJAX
         */
        public function dismiss_banner() {
            // Verify nonce for security
            $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
            if (! wp_verify_nonce($nonce, 'wt_dismiss_invoice_cta_banner_nonce')) {
                wp_send_json_error('Invalid nonce');
            }

            // Check if user has permission
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Insufficient permissions');
            }

            // Update the option to hide the banner
            update_option('wt_hide_invoice_cta_banner', true);

            wp_send_json_success('Banner dismissed successfully');
        }
    }

    new Wt_Invoice_Cta_Banner();
}

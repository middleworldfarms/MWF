<?php
/**
 * EMA Banner
 * 
 * @since 2.6.7
 *
 * @package  Users_Customers_Import_Export_For_WP_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Wbte_Ema_Banner' ) ) {

    class Wbte_Ema_Banner {
        public $module_id               = '';
        public static $module_id_static = '';
        public $module_base             = 'wbte_ema_banner';

        /**
         * The single instance of the class
         *
         * @var self
         */
        private static $instance = null;

        /**
         * The dismiss option name in WP Options table
         *
         * @var string
         */
        private $analytics_page_dismiss_option = 'wbte_ema_banner_analytics_page_dismiss';

        /**
         * Constructor
         * @since 2.6.7
         */
        public function __construct() {
            update_option($this->analytics_page_dismiss_option, false);
            $this->module_id        = $this->module_base;
            self::$module_id_static = $this->module_id;

            if ( ! in_array( 'decorator-woocommerce-email-customizer/decorator.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
                add_action('admin_footer', array($this, 'ema_inject_analytics_script'));
                add_action('wp_ajax_wbte_ema_banner_analytics_page_dismiss', array($this, 'wbte_ema_banner_analytics_page_dismiss_banner'));
            }
        }

        /**
         * Ensures only one instance is loaded or can be loaded.
         *
         * @since 2.6.7
         * @return self
         */
        public static function get_instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Enqueue banner styles
         * 
         * @since 2.6.7
         */
        public function enqueue_styles() {
            if ( ! $this->ema_should_display_banner() ) {
                return;
            }

            wp_enqueue_style('wt-u-iew-ema-banner',plugin_dir_url(__FILE__) . 'assets/css/wbte-ema-banner.css',array(),WT_U_IEW_VERSION);
            wp_enqueue_script('wt-u-iew-ema-banner',plugin_dir_url(__FILE__) . 'assets/js/wbte-ema-banner.js',array('jquery'),WT_U_IEW_VERSION,true);

            wp_localize_script('wt-u-iew-ema-banner', 'wbte_ema_banner_params', array(
                'ajaxurl' => esc_url(admin_url('admin-ajax.php')),
                'nonce' => wp_create_nonce('wbte_ema_banner_nonce'),
            ));
        }

        /**
         * Check if we should display the banner
         * 
         * @since 2.6.7
         * @return boolean
         */
        private function ema_should_display_banner() {
            $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
            // Only consider showing on Analytics Overview page
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check of current page
            if ( ! $screen || 'woocommerce_page_wc-admin' !== $screen->id || ! isset( $_GET['path'] ) || '/analytics/overview' !== $_GET['path'] ) {
                return false;
            }

            return ! get_option( $this->analytics_page_dismiss_option ) && ! defined( 'WBTE_EMA_ANALYTICS_BANNER' );
        }

        /**
         * Ajax handler to dismiss the EMA banner
         * 
         * @since 2.6.7
         */
        public function wbte_ema_banner_analytics_page_dismiss_banner() {
            check_ajax_referer( 'wbte_ema_banner_nonce', 'nonce' );
            update_option( $this->analytics_page_dismiss_option, true );
            wp_send_json_success();
        }

        /**
         * Inject analytics script in admin footer
         * 
         * @since 2.6.7
         */
        public function ema_inject_analytics_script() {
            $screen = get_current_screen();
            
            // Only inject on analytics page
            if ( ! $screen || 'woocommerce_page_wc-admin' !== $screen->id || ! isset( $_GET['path'] ) || '/analytics/overview' !== $_GET['path'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
                return;
            }

            ob_start();

            if ( ! $this->ema_should_display_banner() ) {
                return;
            }

            define( 'WBTE_EMA_ANALYTICS_BANNER', true );
            
            $sale_link = 'https://www.webtoffee.com/ecommerce-marketing-automation/?utm_source=free_plugin_analytics_overview_tab&utm_medium=users_import_export_free&utm_campaign=EMA' ;

            ?>
            
                <div class="wbte_ema_banner_analytics_page">	
                    <div class="wbte_ema_box">						
                        <div class="wbte_ema_text">
                            <img src="<?php echo esc_url( WT_U_IEW_PLUGIN_URL . 'admin/banner/assets/images/idea_bulb_purple.svg' ); ?>" style="">
                            <span class="wbte_ema_title"><?php esc_html_e( 'Did you know?', 'users-customers-import-export-for-wp-woocommerce' ); ?></span>
                            <?php esc_html_e( 'You can boost your store revenue and recover lost sales with automated email campaigns, cart recovery, and upsell popups using the WebToffee Marketing Automation App.','users-customers-import-export-for-wp-woocommerce' ); ?>
                        </div>
                        <div class="wbte_ema_actions">
                            <a href="<?php echo esc_url( $sale_link ); ?>" class="btn-primary" target="_blank"><?php esc_html_e( 'Sign Up for Free', 'users-customers-import-export-for-wp-woocommerce' ); ?></a>
                            <button type="button" class="notice-dismiss wbte_ema_banner_analytics_page_dismiss">
                                <span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'users-customers-import-export-for-wp-woocommerce' ); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
                
            <?php
            $output = ob_get_clean();
            
            if ( empty( trim( $output ) ) ) {
                return;
            }
            ?>
            <script type="text/javascript">
                // Wait for DOM to be fully loaded and give extra time for dynamic content
                setTimeout(function() {
                    var ema_output = document.createElement('div');
                    ema_output.innerHTML = <?php echo wp_json_encode( wp_kses_post( $output ) ); ?>;
    
                    // Find the header element
                    var header = document.querySelector('.woocommerce-layout__header');
                    if ( header && header.parentNode ) {
                        // Insert after the header
                        header.parentNode.insertBefore(ema_output, header.nextSibling);
                    } 
                }, 1000); // 1 second delay
            </script>
            <?php
        }
    }

    /**
     * Initialize the EMA banner
     * 
     * @since 2.6.7
     */
    add_action('admin_init', array('Wbte_Ema_Banner', 'get_instance'));

}

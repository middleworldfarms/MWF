<?php

/**
 * Product CTA Banner for Default Import/Export Pages
 *  
 *
 * @package  Product_Import_Export_CTA_Banner
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WT_P_IEW_CTA_Banner_Default_Page')) {
    class WT_P_IEW_CTA_Banner_Default_Page
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            add_action('admin_footer', array($this, 'product_cta_banner_in_default_page'));
            add_action('wp_ajax_wt_p_iew_dismiss_cta_banner_default_page', array($this, 'dismiss_product_cta_banner'));
        }

        /**
         * Display the import/export banner notice in the default product import/export page
         *
         * @since 2.5.6
         * @return void
         */
        public function product_cta_banner_in_default_page() {
            // Check if we're on the product importer or exporter page
            $screen = get_current_screen();
            if ( ! $screen ) {
                return;
            }

            $is_import_page = 'product_page_product_importer' === $screen->id;
            $is_export_page = 'product_page_product_exporter' === $screen->id;

            if ( ! $is_import_page && ! $is_export_page ) {
                return;
            }

            // Check if banner is dismissed in any page.
            $banner_dismissed = get_option( 'wt_p_iew_product_cta_banner_default_page_dismissed', false );
            if ( $banner_dismissed && ! defined( 'WT_PRODUCT_IMPORT_EXPORT_DISPLAY_BANNER_DEFAULT_PAGE' ) ) {
                return;
            }
            
            define( 'WT_PRODUCT_IMPORT_EXPORT_DISPLAY_BANNER_DEFAULT_PAGE', true );

            // Prepare translatable strings
            $link = 'https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin&utm_medium=woo_export_products_tab&utm_campaign=Product_import_export';
            $text = __( 'Product Import Export Plugin for WooCommerce.', 'product-import-export-for-woo' );
            $did_you_know = __( 'Did You Know?', 'product-import-export-for-woo' );
            $get_text = __( 'Get', 'product-import-export-for-woo' );

            // Set appropriate message based on page type
            if ( $is_import_page ) {
                $main_text = __( 'Go beyond basic CSV imports. Effortlessly import products from CSV, XML, or Excel, make bulk edits, and schedule imports on your terms with the Product Import plugin.', 'product-import-export-for-woo' );
                $target_selector = '.wc-progress-form-content.woocommerce-importer';
            } else {
                $main_text = __( 'Export only the products you need. Filter by type, category, tags, stock status, and more using advanced filters to keep your store organized with the Product Export plugin.', 'product-import-export-for-woo' );
                $target_selector = '.woocommerce-exporter';
            }

            ?>
            <script>
            jQuery( document ).ready( function( $ ) {
                // Wait for the form to be ready
                var $target = $( '<?php echo esc_js( $target_selector ); ?>' );
                if ( $target.length > 0 ) {
                    // Create banner
                    var banner = `
                        <div id="wt-product-cta-banner" style="
                            box-sizing: border-box;
                            position: relative;
                            height: 108px;
                            margin: 30px auto;
                            background: #FFF2FB;
                            border: 0.5px solid #B452F6;
                            border-radius: 7px;
                            display: flex;
                            align-items: center;
                            padding: 0 20px;
                        ">
                            <!-- Close Button -->
                            <button type="button" class="wt-product-cta-close" style="
                                position: absolute;
                                top: 4px;
                                right: 8px;
                                background: none;
                                border: none;
                                font-size: 18px;
                                color: #B452F6;
                                cursor: pointer;
                                padding: 0;
                                width: 20px;
                                height: 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                line-height: 1;
                            " title="<?php esc_attr_e( 'Close', 'product-import-export-for-woo' ); ?>">×</button>
                            
                            <!-- Text Content -->
                            <div style="
                                flex: 1;
                                font-family: 'Roboto', sans-serif;
                                text-align: left;
                                display: flex;
                                align-items: flex-start;
                                gap: 4px;
                            ">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0; margin-top: 2px;">
                                    <path d="M7.83362 13.1544H5.51155C5.27651 13.1544 5.08592 13.3437 5.08592 13.5772C5.08592 13.8107 5.27651 14 5.51155 14H7.83362C8.06867 14 8.25926 13.8107 8.25926 13.5772C8.25926 13.3437 8.06867 13.1544 7.83362 13.1544ZM6.67259 1.67342C6.90763 1.67342 7.09822 1.48409 7.09822 1.2506V0.422819C7.09822 0.189329 6.90763 0 6.67259 0C6.43754 0 6.24695 0.189329 6.24695 0.422819V1.2506C6.24695 1.48409 6.43754 1.67342 6.67259 1.67342ZM12.7331 5.54785H11.8998C11.6648 5.54785 11.4742 5.73718 11.4742 5.97067C11.4742 6.20416 11.6648 6.39349 11.8998 6.39349H12.7331C12.9682 6.39349 13.1588 6.20416 13.1588 5.97067C13.1588 5.73718 12.9687 5.54785 12.7331 5.54785ZM1.44486 5.54785H0.612035C0.376991 5.54785 0.186401 5.73718 0.186401 5.97067C0.186401 6.20416 0.376991 6.39349 0.612035 6.39349H1.44533C1.68038 6.39349 1.87097 6.20416 1.87097 5.97067C1.87097 5.73718 1.6799 5.54785 1.44486 5.54785ZM3.01166 2.93201C3.0949 3.0147 3.20367 3.05604 3.31292 3.05604C3.42216 3.05604 3.53094 3.0147 3.61417 2.93201C3.78064 2.76711 3.78064 2.49933 3.61417 2.33443L3.02443 1.74859C2.85796 1.58322 2.58839 1.58322 2.4224 1.74859C2.25593 1.91349 2.25593 2.18128 2.4224 2.34617L3.01166 2.93201ZM10.3207 1.74859L9.73148 2.33396C9.56501 2.49886 9.56501 2.76664 9.73148 2.93154C9.81471 3.01423 9.92348 3.05557 10.0327 3.05557C10.142 3.05557 10.2508 3.01423 10.334 2.93154L10.9233 2.34617C11.0897 2.18128 11.0897 1.91349 10.9233 1.74859C10.7568 1.58369 10.4867 1.58369 10.3207 1.74859ZM6.67259 2.97946C4.5326 2.97617 2.79175 4.70503 2.79175 6.83463C2.79175 7.85973 3.19658 8.79228 3.85868 9.48054C4.29802 9.93765 4.5709 10.5272 4.5709 11.1591V11.5195C4.5709 11.7788 4.78277 11.9893 5.04383 11.9893H6.67259H8.30135C8.5624 11.9893 8.77427 11.7788 8.77427 11.5195V11.1591C8.77427 10.5272 9.04668 9.93765 9.4865 9.48054C10.1481 8.79228 10.5534 7.8602 10.5534 6.83463C10.5534 4.70503 8.81258 2.9757 6.67259 2.97946ZM6.29188 5.32658C5.73667 5.46376 5.29022 5.9096 5.15402 6.46255C5.10626 6.65564 4.93222 6.7853 4.74116 6.7853C4.70758 6.7853 4.67306 6.78107 4.63948 6.77309C4.41105 6.71765 4.27154 6.48886 4.32735 6.26195C4.54111 5.39423 5.21503 4.72148 6.08616 4.50584C6.31411 4.4504 6.54584 4.58711 6.60212 4.81403C6.65887 5.04094 6.51983 5.2702 6.29188 5.32658Z" fill="#7927AF"/>
                                </svg>
                                <div style="
                                    flex: 1;
                                    font-style: normal;
                                    font-size: 12px;
                                    line-height: 160%;
                                    max-width: 676px;
                                ">
                                    <span style="
                                        font-weight: 900;
                                        font-size: 13px;
                                        font-weight: 700;
                                        color: #7927AF;
                                    "><?php echo esc_html( $did_you_know ); ?></span> 
                                    <?php echo esc_html( $main_text ); ?>
                                    <br> <?php echo esc_html( $get_text ); ?> <a href="<?php echo esc_url( $link ); ?>" target="_blank" style="text-decoration: underline;"><?php echo esc_html( $text ); ?></a>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Insert at the END of the form
                    $target.after( banner );
                    
                    // Add close functionality
                    $(document).on('click', '.wt-product-cta-close', function(e) {
                        e.preventDefault();
                        
                        // Hide the banner
                        $('#wt-product-cta-banner').fadeOut(300, function() {
                            $(this).remove();
                        });
                        
                        // Save dismissal state via AJAX
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'wt_p_iew_dismiss_cta_banner_default_page',
                                nonce: '<?php echo esc_js(wp_create_nonce( 'wt_p_iew_dismiss_cta_banner_default_page' )); ?>'
                            },
                            dataType: 'json',
                            success: function(response) {
                                // Banner dismissed successfully
                            }
                        });
                    });
                }
            } );
            </script>
            <?php
        }

        /**
         * AJAX handler to dismiss the product CTA banner
         */
        public function dismiss_product_cta_banner()
        {
            // Check if this is a POST request
            if (!isset($_POST['nonce'])) {
                wp_die(esc_html__('Missing nonce', 'product-import-export-for-woo'));
            }

            // Verify nonce
            if ( ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wt_p_iew_dismiss_cta_banner_default_page') ) {
                wp_die(esc_html__('Security check failed', 'product-import-export-for-woo'));
            }

            // Check if user has permission
            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('Insufficient permissions', 'product-import-export-for-woo'));
            }

            // Update the dismissal option
            update_option('wt_p_iew_product_cta_banner_default_page_dismissed', true);
            
            // Send success response
            wp_send_json_success(esc_html__('Banner dismissed successfully', 'product-import-export-for-woo'));
        }
    }

    // Initialize the class
    new WT_P_IEW_CTA_Banner_Default_Page(); 
}


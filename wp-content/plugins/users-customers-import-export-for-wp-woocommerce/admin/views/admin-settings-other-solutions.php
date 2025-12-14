<?php
if ( ! defined( 'WPINC' ) ) {die;
}
?>
<style>
.wt_card_margin {margin-bottom: 0.0rem;width : 31%;height : 300px;float: left;margin: 10px 150px 20px 15px;}
.card {margin: 10px 10px 20px 10px;padding-left:px;border: 0;box-shadow: 0px 0px 10px 0px rgba(82, 63, 105, 0.1);-webkit-box-shadow: 0px 0px 10px 0px rgba(82, 63, 105, 0.1);-moz-box-shadow: 0px 0px 10px 0px rgba(82, 63, 105, 0.1);-ms-box-shadow: 0px 0px 10px 0px rgba(82, 63, 105, 0.1);}
.card {height: 360px;position: relative;display: flex;flex-direction: column;min-width: 0;word-wrap: break-word;background-color: #ffffff;background-clip: border-box;border: 1px solid #e6e4e9;border-radius: 8px;}
.wt_heading_1{text-align:center;font-style: normal;font-weight: bold;font-size: 82px;}
.wt_heading_2{text-align:center;font-style: normal;font-weight: normal;font-size: 17px;}
.wt_widget{padding-left:-100px;}
.wt_widget .wt_widget_title_wrapper {display: flex;}
.wt_widget .wt_buttons {display: flex;}
.wt_widget_column_1 img {width: 60px;height: 60px;}
.wt_widget_column_1{padding-top:18px;}
.wt_widget_title_wrapper .wt_widget_column_2{align:top;}
.wt_widget_column_2{font-size: 15px;text-align: top;padding-left:10px; width:100%;height:100px;}
.wt_widget_column_3{text-align:left;vertical-align: text-top;position: relative;height:170px;}
.wt_installed_button{padding-left:10px;width: 100%;}
.wt_free_button{padding-left:10px;}
.wt_free_btn_a{}
.wt_get_premium_btn {text-align:center;padding: 6px 1px 0px 1px;height:25px; width:100%; background: linear-gradient(90.67deg, #2608DF -34.86%, #3284FF 115.74%);box-shadow: 0px 4px 13px rgb(46 80 242 / 39%);border-radius: 5px;display: inline-block;font-style: normal;font-size: 12px;line-height: 18px;color: #FFFFFF;text-decoration: none;}
.wt_get_premium_btn:hover {box-shadow: 0px 3px 13px rgb(46 80 242 / 50%);text-decoration: none;transform: translateY(2px);transition: all .2s ease;color: #FFFFFF;}
.wt_installed_btn{height:30px;width:100%; border-style: solid;border-color: #2A2EEA;border-radius: 5px;color: #2A2EEA;}
.wt_free_btn{height:30px;width:109px; border-style: solid;border-color: #2A2EEA;border-radius: 5px;color: #2A2EEA;cursor: pointer;}
.wt_free_button.full_width {width: 100%;}
.wt_free_btn.full_width {width: 100%;}
</style>
<div class="wt-iew-tab-content" data-id="<?php echo esc_attr($target_id);?>">
    <div class="wt_row"> 
        <div clas="wt_headings">
            <h1 class="wt_heading_1"><?php esc_html_e('More Plugins To Make Your Store Stand Out', 'users-customers-import-export-for-wp-woocommerce'); ?></h1>
            <h2 class="wt_heading_2"><?php esc_html_e('Check out our other plugins that are perfectly suited for WooCommerce store needs.', 'users-customers-import-export-for-wp-woocommerce'); ?></h2> 
        </div>
    <div class="wt_column">
<?php 

/* image location for the logos */
$wt_admin_img_path = WT_U_IEW_PLUGIN_URL . 'assets/images/other_solutions';

/* Plugin lists array */
$plugins=array(
    'accessibility-checker' => array(
        'title'         => __('Accessibility Tool Kit: WP Accessibility for WCAG, Section 508, ADA, EAA Compliance', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Build an accessible WordPress site that works for everyone. Scan for accessibility issues, get fix recommendations, and ensure WCAG compliance, all without writing code. Inclusive web design made simple.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'accessibility-checker.png',
        'premium_url'   => '',
        'basic_url'     => 'https://wordpress.org/plugins/accessibility-plus/',
        'pro_plugin'    => '', // No pro plugin available
        'basic_plugin'  => 'accessibility-plus/accessibility.php',
    ),
    'product_feed_sync' => array(
        'title'         => __('WebToffee WooCommerce Product Feed & Sync Manager', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Generate WooCommerce product feeds for Google Merchant Center and Facebook Business Manager. Use the Facebook catalog sync manager to sync WooCommerce products with Facebook and Instagram shops.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'product-feed-sync.png',
        'premium_url'   => 'https://www.webtoffee.com/product/product-catalog-sync-for-facebook/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=WooCommerce_Product_Feed',
        'basic_url'     => 'https://wordpress.org/plugins/webtoffee-product-feed/',
        'pro_plugin'    => 'webtoffee-product-feed-pro/webtoffee-product-feed-pro.php',
        'basic_plugin'  => 'webtoffee-product-feed/webtoffee-product-feed.php',
    ),
    'request_quote' => array(
        'title'         => __('WebToffee Woocommerce Request a Quote', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Configure a fully optimized WooCommerce quote request set up in your store. Allow customers to request quotes and store managers to respond to them. Hide product prices, set up email alerts, and more.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'request-quote.png',
        'premium_url'   => 'https://www.webtoffee.com/product/woocommerce-request-a-quote/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=Request_Quote',
        'basic_url'     => '',
        'pro_plugin'    => 'wt-woo-request-quote/wt-woo-request-quote.php',
        'basic_plugin'  => '',
    ),
    'giftcards_plugin' => array(
        'title'         => __('WebToffee WooCommerce Gift Cards', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Create and manage advanced gift cards for WooCommerce stores. Enable your customers to buy, redeem, and share gift cards from your store.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'giftcards_plugin.png',
        'premium_url'   => 'https://www.webtoffee.com/product/woocommerce-gift-cards/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=WooCommerce_Gift_Cards',
        'basic_url'     => 'https://wordpress.org/plugins/wt-gift-cards-woocommerce/',
        'pro_plugin'    => 'wt-woocommerce-gift-cards/wt-woocommerce-gift-cards.php',
        'basic_plugin'  => 'wt-gift-cards-woocommerce/wt-gift-cards-woocommerce.php', 
    ),
    'fbt_plugins' => array(
        'title'         => __('Frequently Bought Together for WooCommerce', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Boost the visibility of the products by displaying them as ‘Frequently bought together’ items in your store. You may also set up discounts for Frequently Bought Together bundles with this plugin.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'fbt_plugin.png',
        'premium_url'   => 'https://www.webtoffee.com/product/woocommerce-frequently-bought-together/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=Frequently_Bought_Together',
        'basic_url'     => '',
        'pro_plugin'    => 'wt-frequently-bought-together/wt-frequently-bought-together.php',
        'basic_plugin'  => '', 
    ),
    'gdpr_cookie_consent_plugin' => array(
        'title'         => __('GDPR Cookie Consent Plugin (CCPA Ready)', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('The plugin helps you get compliant with GDPR, CCPA, and other major cookie laws. You can create and manage cookie consent banners, scan website cookies, and generate cookie policies with this plugin.','users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'gdpr-cookie-concent-plugin.png',
        'premium_url'   => 'https://www.webtoffee.com/product/gdpr-cookie-consent/?utm_source=other_solution_page&utm_medium=_free_plugin_&utm_campaign=GDPR',
        'basic_url'     => 'https://wordpress.org/plugins/cookie-law-info/',
        'pro_plugin'    => 'webtoffee-gdpr-cookie-consent/cookie-law-info.php',
        'basic_plugin'  => 'cookie-law-info/cookie-law-info.php', 
    ),
    'product_import_export_plugin' => array(
        'title'         => __('Product Import Export Plugin For WooCommerce', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Seamlessly import/export your WooCommerce products including simple, variable, custom products and subscriptions. You may also import and export product images, tags, categories, reviews, and ratings.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'product-import-export-plugin.png',
        'premium_url'   => 'https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=Product_Import_Export',
        'basic_url'     => 'https://wordpress.org/plugins/product-import-export-for-woo/',
        'pro_plugin'    => 'wt-import-export-for-woo-product/wt-import-export-for-woo-product.php',
        'basic_plugin'  => 'product-import-export-for-woo/product-import-export-for-woo.php',
    ),
    'customers_import_export_plugin' => array(
        'title'         => __('WordPress Users & WooCommerce Customers Import Export', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Easily import and export your WordPress users and WooCommerce customers using the Import Export plugin for WooCommerce. The plugin supports the use of CSV, XML, TSV, XLS, and XLSX file formats.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'user-import-export-plugin.png',
        'premium_url'   => 'https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=User_Import_Export',
        'basic_url'     => 'https://wordpress.org/plugins/users-customers-import-export-for-wp-woocommerce/',
        'pro_plugin'    => 'wt-import-export-for-woo-user/wt-import-export-for-woo-user.php',
        'basic_plugin'  => 'users-customers-import-export-for-wp-woocommerce/users-customers-import-export-for-wp-woocommerce.php',
    ),
    'order_import_export_plugin' => array(
        'title'         => __('Order, Coupon, Subscription Export Import for WooCommerce', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Export and Import your WooCommerce orders, subscriptions, and discount coupons using a single Import Export plugin. You may customize the export and import files with advanced filters and settings.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'order-import-export-plugin.png',
        'premium_url'   => 'https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=Order_Import_Export',
        'basic_url'     => 'https://wordpress.org/plugins/order-import-export-for-woocommerce/',
        'pro_plugin'    => 'wt-import-export-for-woo-order/wt-import-export-for-woo-order.php',
        'basic_plugin'  => 'order-import-export-for-woocommerce/order-import-export-for-woocommerce.php',
    ),
    'import_export_suit' => array(
        'title'         => __('Import Export Suite for WooCommerce', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('An all-in-one plugin to import and export WooCommerce store data. You can import and export products, product reviews, orders, customers, discount coupons, and subscriptions using this single plugin.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'suite-1-plugin.png',
        'premium_url'   => 'https://www.webtoffee.com/product/woocommerce-import-export-suite/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=Import_Export_Suite',
        'basic_url'     => '',
        'pro_plugin'    => array(
            'product'   => 'wt-import-export-for-woo-product/wt-import-export-for-woo-product.php',
            'user'      => 'wt-import-export-for-woo-user/wt-import-export-for-woo-user.php',
            'order'     => 'wt-import-export-for-woo-order/wt-import-export-for-woo-order.php',
            ),
        'basic_plugin'  => '', 
    ),
    'smart_coupons_plugin' => array(
        'title'         => __('Smart Coupons for WooCommerce', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Create coupons to offer discounts and free products to your customers with Smart Coupons for WooCommerce. You can set up BOGO coupons, giveaways, gift cards, store credits, and more with this plugin.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'smart-coupons-plugin.png',
        'premium_url'   => 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=smart_coupons',
        'basic_url'     => 'https://wordpress.org/plugins/wt-smart-coupons-for-woocommerce/',
        'pro_plugin'    => 'wt-smart-coupon-pro/wt-smart-coupon-pro.php',
        'basic_plugin'  => 'wt-smart-coupons-for-woocommerce/wt-smart-coupon.php',
    ),
    'url_coupons_plugin' => array(
        'title'         => __('URL Coupons for WooCommerce', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Generate custom URLs and QR codes for every discount coupon in your WooCommerce store. These unique coupons are easy to share and can even be set to add new products to the cart upon application.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'url-coupons-plugin.png',
        'premium_url'   => 'https://www.webtoffee.com/product/url-coupons-for-woocommerce/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=URL_Coupons',
        'basic_url'     => '',
        'pro_plugin'    => 'wt-woocommerce-gift-cards/wt-woocommerce-gift-cards.php',
        'basic_plugin'  => '', 
    ),
    'sequential_order_plugin' => array(
        'title' => __('Sequential Order Numbers for WooCommerce', 'users-customers-import-export-for-wp-woocommerce'),
        'description' => __('Number your WooCommerce orders in a custom, sequential & manageable format. The Sequential Order Number plugin lets your orders follow a custom & unique numbering sequence suitable for your business.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url' => 'Sequential-order-number-plugin.png',
        'premium_url' => 'https://www.webtoffee.com/product/woocommerce-sequential-order-numbers/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=Sequential_Order_Numbers',
        'basic_url' => 'https://wordpress.org/plugins/wt-woocommerce-sequential-order-numbers/',
        'pro_plugin' => 'wt-woocommerce-sequential-order-numbers-pro/wt-advanced-order-number-pro.php',
        'basic_plugin' => 'wt-woocommerce-sequential-order-numbers/wt-advanced-order-number.php',
    ),
	'wt_ipc_addon' => array(
        'title'         => __('WooCommerce PDF Invoices, Packing Slips and Credit Notes', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Easily generate & print fully customized PDF Invoices, Packing Slips, and Credit Notes for your orders. Automatically send the documents to the recipients by attaching them to the order status emails.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'wt_ipc_logo.png',
        'premium_url'   => 'https://www.webtoffee.com/product/woocommerce-pdf-invoices-packing-slips/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=PDF_invoice',
        'basic_url'     => '',
        'pro_plugin'    => 'wt-woocommerce-invoice-addon/wt-woocommerce-invoice-addon.php',
        'basic_plugin'  => '',
    ),
    'wt_sdd_addon' => array(
        'title'         => __('WooCommerce Shipping Labels, Dispatch Labels and Delivery Notes', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Automatically generate WooCommerce Shipping Labels, Dispatch Labels, and Delivery Notes with custom settings and layouts. Customize the label sizes and add extra product or order fields as required.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'wt_sdd_logo.png',
        'premium_url'   => 'https://www.webtoffee.com/product/woocommerce-shipping-labels-delivery-notes/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=Shipping_Label',
        'basic_url'     => '',
        'pro_plugin'    => 'wt-woocommerce-shippinglabel-addon/wt-woocommerce-shippinglabel-addon.php',
        'basic_plugin'  => '',
    ),
    'wt_pl_addon' => array(
        'title'         => __('WooCommerce Picklists', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Customize, generate and print WooCommerce picklists for all orders on your store and automatically attach them to the order status emails. Add product variation data and other fields to the document.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'wt_pl_logo.png',
        'premium_url'   => 'https://www.webtoffee.com/product/woocommerce-picklist/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=Picklist',
        'basic_url'     => '',
        'pro_plugin'    => 'wt-woocommerce-picklist-addon/wt-woocommerce-picklist-addon.php',
        'basic_plugin'  => '',
    ),
    'wt_pi_addon' => array(
        'title'         => __('WooCommerce Proforma Invoices', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Automate the generation of WooCommerce proforma invoices when new orders are placed and send them to your customers via order emails. Customize the layout and content of the invoice as per your needs.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'wt_pi_logo.png',
        'premium_url'   => 'https://www.webtoffee.com/product/woocommerce-proforma-invoice/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=Proforma_Invoice',
        'basic_url'     => '',
        'pro_plugin'    => 'wt-woocommerce-proforma-addon/wt-woocommerce-proforma-addon.php',
        'basic_plugin'  => '',
    ),
    'wt_al_addon' => array(
        'title'         => __('WooCommerce Address Labels', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Generate address labels for all orders in your store and easily print them in bulk. Customize the label layout and create labels of different types (shipping, billing, return, from address) with ease.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'wt_al_logo.png',
        'premium_url'   => 'https://www.webtoffee.com/product/woocommerce-address-label/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=Address_Label',
        'basic_url'     => '',
        'pro_plugin'    => 'wt-woocommerce-addresslabel-addon/wt-woocommerce-addresslabel-addon.php',
        'basic_plugin'  => '',
    ),	
    'backup_and_migration_plugin' => array(
        'title' => __('WordPress Backup and Migration', 'users-customers-import-export-for-wp-woocommerce'),
        'description' => __('A complete WordPress backup and migration plugin to easily back up and migrate your WordPress website and database. This fast and flexible backup solution makes creating and restoring backups easy.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url' => 'WordPress-backup-and-migration-plugin.png',
        'premium_url' => '',
        'basic_url' => 'https://wordpress.org/plugins/wp-migration-duplicator/',
        'pro_plugin' => 'wp-migration-duplicator-pro/wp-migration-duplicator-pro.php',
        'basic_plugin' => 'wp-migration-duplicator/wp-migration-duplicator.php',
    ),
    'product_recommendations' => array(
        'title'         => __('WooCommerce Product Recommendations', 'users-customers-import-export-for-wp-woocommerce'),
        'description'   => __('Generate Intelligent Product Recommendations For Your WooCommerce Store. Offer WooCommerce smart product recommendations to your customers & maximize the average cart value.', 'users-customers-import-export-for-wp-woocommerce'),
        'image_url'     => 'product-recommendation.png',
        'premium_url'   => 'https://www.webtoffee.com/product/woocommerce-product-recommendations/?utm_source=other_solution_page&utm_medium=free_plugin&utm_campaign=Product_Recommendations',
        'basic_url'     => '',
        'pro_plugin'    => 'wt-woocommerce-product-recommendations/wt-woocommerce-product-recommendations.php',
        'basic_plugin'  => '', 
    ),    
);

    foreach ($plugins as $key => $value)
    {   
        if(isset($value['pro_plugin'])){
            if(is_array($value['pro_plugin']) && isset($value['pro_plugin']['product']) && isset($value['pro_plugin']['user']) && isset($value['pro_plugin']['order']))
            {
                if(is_plugin_active($value['pro_plugin']['product']) && is_plugin_active($value['pro_plugin']['user']) && is_plugin_active($value['pro_plugin']['order'])){
                    continue;
                }
            }
            else
            {
                if(is_plugin_active($value['pro_plugin']))
                {
                    continue;
                }
            }
        }
?>
        <div class="card wt_card_margin">
            <div class="wt_widget">
                <div class="wt_widget_title_wrapper">
                    <div class="wt_widget_column_1">
                        <img src="<?php echo esc_url($wt_admin_img_path . '/' . $value['image_url']);?>">
                    </div>
                    <div class="wt_widget_column_2">
                        <h4 class="card-title">
                            <?php echo esc_html($value['title']); ?>
                        </h4>
                    </div>
                </div>
                <div class="wt_widget_column_3">
                    <p class="">
                        <?php echo esc_html($value['description']); ?>
                    </p>
                </div> 
                <div class="wt_buttons">
                <?php
                if ( isset( $value['premium_url'] ) && ! empty( $value['premium_url'] ) ) {
                ?>
                    <div class="wt_premium_button" style="width: 100%;">
                        <a href="<?php echo esc_url($value['premium_url']); ?>" class="wt_get_premium_btn" target="_blank"><img src="<?php echo esc_url($wt_admin_img_path . '/promote_crown.png');?>" style="width: 10px;height: 10px;"><?php  esc_html_e(' Get Premium','users-customers-import-export-for-wp-woocommerce'); ?></a>
                    </div> 
<?php           }  
                    if(is_plugin_active($value['basic_plugin']))
		            { 
?>
                    <div class="wt_installed_button">
                        <button class="wt_installed_btn">
                            <?php esc_html_e('Installed','users-customers-import-export-for-wp-woocommerce'); ?>
                        </button>
                    </div>
<?php               
                    }elseif(isset($value['basic_plugin']) && "" !== $value['basic_plugin'] && !is_plugin_active($value['basic_plugin'])
                    && isset($value['basic_url']) && "" !== $value['basic_url'] 
                    && ( empty($value['pro_plugin'] ) || ! is_plugin_active( $value['pro_plugin'] ) ) )
		            { 
?>
                    <div class="wt_free_button<?php echo (empty($value['premium_url'])) ? ' full_width' : ''; ?>">
                             <a class="wt_free_btn_a" href="<?php echo esc_url($value['basic_url']); ?>" target="_blank">
                                <button class="wt_free_btn<?php echo (empty($value['premium_url'])) ? ' full_width' : ''; ?>">
                                    <?php esc_html_e('Get Free Plugin', 'users-customers-import-export-for-wp-woocommerce'); ?>
                                </button>
                            </a>
                    </div>

              <?php } ?>
              

                </div>
            </div>
        </div>

<?php } ?>

        </div>
    </div>
</div>

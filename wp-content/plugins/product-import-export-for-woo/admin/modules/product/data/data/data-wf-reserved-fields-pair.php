<?php
if (!defined('WPINC')) {
    exit;
}

$wc_weight_unit = get_option('woocommerce_weight_unit');
$wc_dimension_unit = get_option('woocommerce_dimension_unit');
// Reserved column names
$post_columns =  array(
                'post_title' => array('title'=>'Product name','description'=>'Product Title. ie Name of the product', 'field_type' => 'alternates', 'similar_fields' => array('Name')),
                'post_name' => array('title'=>'Product permalink','description'=>'Unique part of the product URL', 'field_type' => 'alternates', 'similar_fields' => array('slug')),
                'ID' => array('title'=>'ID','description'=>'Product ID'),
                'post_parent' => array('title'=>'Parent ID','description'=>'Parent Product ID , if you are importing variation Product'),
                'post_status' => array('title'=>'Status','description'=>'Product Status ( published , draft ...)', 'field_type' => 'alternates', 'similar_fields' => array('Published')),
                'post_content' => array('title'=>'Description','description'=>'Description about the Product', 'field_type' => 'alternates', 'similar_fields' => array('Description')),
                'post_excerpt' => array('title'=>'Short description','description'=>'Short description about the Product', 'field_type' => 'alternates', 'similar_fields' => array('Short description')),
                'post_date' => array('title'=>'Post date','description'=>'Product posted date', 'type' => 'date'),
                'post_password' => array('title'=>'Post password','description'=>'To Protect a post with password'),
                'post_author' => array('title'=>'Product author','description'=>'Product Author ( 1 - Admin )'),
                'menu_order' => array('title'=>'Menu order','description'=>'If menu enabled , menu order'),
                'comment_status' => array('title'=>'Comment status','description'=>'Comment Status ( Open or Closed comments for this prodcut)', 'field_type' => 'alternates', 'similar_fields' => array('Allow customer reviews?')),
                //'post_date_gmt' => array('title'=>'Post Date GMT','description'=>'Tooltip data Status'),
                                
                'sku' => array('title'=>'SKU','description'=>'Product SKU - This will unique and Product identifier'),
                'parent_sku' => array('title'=>'Parent SKU','description'=>'Parent Product SKU , if you are importing variation Product'),                
				'children' => array('title'=>'Child product ID','description'=>'Linked Products id if you are importing Grouped products'),
                'downloadable' => array('title'=>'Type: Downloadable','description'=>'Is Product is downloadable eg:- Book'),
                'virtual' => array('title'=>'Type: Virtual','description'=>'Is Product is virtual'),
                'visibility' => array('title'=>'Visibility: Visibility','description'=>'Visibility status ( hidden or visible)', 'field_type' => 'alternates', 'similar_fields' => array('Visibility in catalog')),               
                'purchase_note' => array('title'=>'Purchase note','description'=>'Purchase note', 'field_type' => 'alternates', 'similar_fields' => array('Purchase note')),
                'stock' => array('title'=>'Inventory: Stock','description'=>'Stock quantity', 'field_type' => 'alternates', 'similar_fields' => array('quantity', 'qty')),
                'stock_status' => array('title'=>'Inventory: Stock status','description'=>'InStock or OutofStock', 'field_type' => 'alternates', 'similar_fields' => array('In stock?')),
                'backorders' => array('title'=>'Inventory: Backorders','description'=>'Backorders', 'field_type' => 'alternates', 'similar_fields' => array('Backorders allowed?')),
                'sold_individually' => array('title'=>'Inventory: Sold individually','description'=>'Sold individually', 'field_type' => 'alternates', 'similar_fields' => array('Sold individually?')),
                'low_stock_amount' => array('title'=>'Inventory: Low stock amount','description'=>'Low stock amount', 'field_type' => 'alternates', 'similar_fields' => array('Low stock amount')),
                'manage_stock' => array('title'=>'Inventory: Manage stock','description'=>'yes to enable no to disable'),
                'sale_price' => array('title'=>'Price: sale price','description'=>'Sale Price', 'field_type' => 'alternates', 'similar_fields' => array('Sale price')),
                'regular_price' => array('title'=>'Price: regular price','description'=>'Regular Price', 'field_type' => 'alternates', 'similar_fields' => array('Regular price')),
                'sale_price_dates_from' => array('title'=>'Sale price dates: From','description'=>'Sale Price Dates effect from', 'type' => 'date', 'field_type' => 'alternates', 'similar_fields' => array('Date sale price starts')),
                'sale_price_dates_to' => array('title'=>'Sale price dates: To','description'=>'Sale Price Dates effect to', 'type' => 'date', 'field_type' => 'alternates', 'similar_fields' => array('Date sale price ends')),
                'weight' => array('title'=>'Dimensions: Weight','description'=>'Wight of product in LB , OZ , KG as of your woocommerce Unit', 'field_type' => 'alternates', 'similar_fields' => array("Weight ($wc_weight_unit)")),
                'length' => array('title'=>'Dimensions: Length','description'=>'Length', 'field_type' => 'alternates', 'similar_fields' => array("Length ($wc_dimension_unit)")),
                'width' => array('title'=>'Dimensions: Width','description'=>'Width', 'field_type' => 'alternates', 'similar_fields' => array("Width ($wc_dimension_unit)")),
                'height' => array('title'=>'Dimensions: Height','description'=>'Height', 'field_type' => 'alternates', 'similar_fields' => array("Height ($wc_dimension_unit)")),
                'tax_status' => array('title'=>'Tax: Tax status','description'=>'Taxable product or not', 'field_type' => 'alternates', 'similar_fields' => array('Tax status')),
                'tax_class' => array('title'=>'Tax: Tax class','description'=>'Tax class ( eg:- reduced rate)', 'field_type' => 'alternates', 'similar_fields' => array('Tax class')),
                'upsell_ids' => array('title'=>'Related products: Upsell IDs','description'=>'Upsell Product ids', 'field_type' => 'alternates', 'similar_fields' => array('Upsells')),
                'crosssell_ids' => array('title'=>'Related products: Crosssell IDs','description'=>'Crosssell Product ids', 'field_type' => 'alternates', 'similar_fields' => array('Cross-sells')),
                'file_paths' => array('title'=>'Downloads: File paths (WC 2.0.x)','description'=>'File Paths'),
                'downloadable_files' => array('title'=>'Downloads: Downloadable files (WC 2.1.x)','description'=>'Downloadable Files'),
                'download_limit' => array('title'=>'Downloads: Download limit','description'=>'Download Limit', 'field_type' => 'alternates', 'similar_fields' => array('Download limit')),
                'download_expiry' => array('title'=>'Downloads: Download expiry','description'=>'Download Expiry', 'field_type' => 'alternates', 'similar_fields' => array('Download expiry days')),
                'product_url' => array('title'=>'External: Product URL','description'=>'Product URL if the Product is external', 'field_type' => 'alternates', 'similar_fields' => array('External URL')),
                'button_text' => array('title'=>'External: Button text','description'=>'Buy button text for Product , if the Product is external', 'field_type' => 'alternates', 'similar_fields' => array('Button text')),
                'images' => array('title'=>'Images/Gallery','description'=>'Image URLs seperated with &#124;'),
                'product_page_url' => array('title'=>'Product page URL','description'=>'Product Page URL'),
                'meta:total_sales' => array('title'=>'meta:total_sales','description'=>'Total sales for the Product'),
                'meta:_global_unique_id' => array('title'=>'GTIN, UPC, EAN, or ISBN','description'=>'Global unique ID'),
//                'tax:product_type' => array('title'=>'Product Type','description'=>'( eg:- simple , variable)'),
//                'tax:product_cat' => array('title'=>'Product Categories','description'=>'Product related categories'),
//                'tax:product_tag' => array('title'=>'Product Tags','description'=>'Product related tags'),
//                'tax:product_shipping_class' => array('title'=>'Product Shipping Class','description'=>'Allow you to group similar products for shipping'),
//                'tax:product_visibility' => array('title'=>'Product Visibility: Featured','description'=>'Featured Product'),

    
);

if (class_exists('WPSEO_Options')) {
    /* Yoast is active */

    $post_columns['meta:_yoast_wpseo_focuskw'] = array('title' => 'meta:_yoast_wpseo_focuskw', 'description' => 'yoast SEO');
    $post_columns['meta:_yoast_wpseo_canonical'] = array('title' => 'meta:_yoast_wpseo_canonical', 'description' => 'yoast SEO');
    $post_columns['meta:_yoast_wpseo_bctitle'] = array('title' => 'meta:_yoast_wpseo_bctitle', 'description' => 'yoast SEO');
    $post_columns['meta:_yoast_wpseo_meta-robots-adv'] = array('title' => 'meta:_yoast_wpseo_meta-robots-adv', 'description' => 'yoast SEO');
    $post_columns['meta:_yoast_wpseo_is_cornerstone'] = array('title' => 'meta:_yoast_wpseo_is_cornerstone', 'description' => 'yoast SEO');
    $post_columns['meta:_yoast_wpseo_metadesc'] = array('title' => 'meta:_yoast_wpseo_metadesc', 'description' => 'yoast SEO');
    $post_columns['meta:_yoast_wpseo_linkdex'] = array('title' => 'meta:_yoast_wpseo_linkdex', 'description' => 'yoast SEO');
    $post_columns['meta:_yoast_wpseo_estimated-reading-time-minutes'] = array('title' => 'meta:yoast_wpseo_estimated-reading-time-minutes', 'description' => 'yoast SEO');
    $post_columns['meta:_yoast_wpseo_content_score'] = array('title' => 'meta:_yoast_wpseo_focuskw', 'description' => 'yoast SEO');
    $post_columns['meta:_yoast_wpseo_title'] = array('title' => 'meta:_yoast_wpseo_title', 'description' => 'yoast SEO');
    $post_columns['meta:_yoast_wpseo_metadesc'] = array('title' => 'meta:_yoast_wpseo_metadesc', 'description' => 'yoast SEO');
    $post_columns['meta:_yoast_wpseo_metakeywords'] = array('title' => 'meta:_yoast_wpseo_metakeywords', 'description' => 'yoast SEO');
}

if (function_exists( 'aioseo' )) {
        
    /* All in One SEO is active */
    
    $post_columns['meta:_aioseo_title'] = array('title' => 'meta:_aioseo_title', 'description' => 'All in One SEO');
    $post_columns['meta:_aioseo_description'] = array('title' => 'meta:_aioseo_description', 'description' => 'All in One SEO');
    $post_columns['meta:_aioseo_keywords'] = array('title' => 'meta:_aioseo_keywords', 'description' => 'All in One SEO');
    $post_columns['meta:_aioseo_og_title'] = array('title' => 'meta:_aioseo_og_title', 'description' => 'All in One SEO');
    $post_columns['meta:_aioseo_og_description'] = array('title' => 'meta:_aioseo_og_description', 'description' => 'All in One SEO');
    $post_columns['meta:_aioseo_twitter_title'] = array('title' => 'meta:_aioseo_twitter_title', 'description' => 'All in One SEO');
    $post_columns['meta:_aioseo_og_article_tags'] = array('title' => 'meta:_aioseo_og_article_tags', 'description' => 'All in One SEO');
    $post_columns['meta:_aioseo_twitter_description'] = array('title' => 'meta:_aioseo_twitter_description', 'description' => 'All in One SEO');
}

if (class_exists('WC_COG_Loader')) {
	$post_columns['meta:_wc_cog_cost'] = array('title' => 'meta:_wc_cog_cost', 'description' => 'Cost of Goods');
}

if (apply_filters('wpml_setting', false, 'setup_complete')) {

    $post_columns['wpml:language_code'] = array('title'=>'wpml:language_code','description'=>'WPML language code');
    $post_columns['wpml:original_product_id'] = array('title'=>'wpml:original_product_id','description'=>'WPML Original Product ID');
    $post_columns['wpml:original_product_sku'] = array('title'=>'wpml:original_product_sku','description'=>'WPML Original Product SKU');
}

if ( function_exists( 'wt_feed_basic_check_for_woocommerce' ) || function_exists( 'wt_feed_check_for_woocommerce' ) ) {
   
    /* product feed or feed pro is active */   

    $post_columns['meta:_wt_feed_brand'] = array('title'=>'_wt_feed_brand','description'=>'_wt_feed_brand');
    $post_columns['meta:_wt_feed_condition'] = array('title'=>'_wt_feed_condition','description'=>'meta:_wt_feed_condition');
    $post_columns['meta:_wt_feed_gtin'] = array('title'=>'_wt_feed_gtin','description'=>'_wt_feed_brand');
    $post_columns['meta:_wt_feed_mpn'] = array('title'=>'_wt_feed_mpn','description'=>'_wt_feed_mpn');
    $post_columns['meta:_wt_feed_agegroup'] = array('title'=>'_wt_feed_agegroup','description'=>'_wt_feed_agegroup');
    $post_columns['meta:_wt_feed_gender'] = array('title'=>'_wt_feed_gender','description'=>'_wt_feed_gender');
    $post_columns['meta:_wt_feed_size'] = array('title'=>'_wt_feed_size','description'=>'_wt_feed_size');
    $post_columns['meta:_wt_feed_color'] = array('title'=>'_wt_feed_color','description'=>'_wt_feed_color');
    $post_columns['meta:_wt_feed_material'] = array('title'=>'_wt_feed_material','description'=>'_wt_feed_material');
    $post_columns['meta:_wt_feed_pattern'] = array('title'=>'_wt_feed_pattern','description'=>'_wt_feed_pattern');
    $post_columns['meta:_wt_feed_unit_pricing_measure'] = array('title'=>'_wt_feed_unit_pricing_measure','description'=>'_wt_feed_unit_pricing_measure');
    $post_columns['meta:_wt_feed_unit_pricing_base_measure'] = array('title'=>'_wt_feed_unit_pricing_base_measure','description'=>'_wt_feed_unit_pricing_base_measure');
    $post_columns['meta:_wt_feed_energy_efficiency_class'] = array('title'=>'_wt_feed_energy_efficiency_class','description'=>'_wt_feed_energy_efficiency_class');
    $post_columns['meta:_wt_feed_min_energy_efficiency_class'] = array('title'=>'_wt_feed_min_energy_effciency_class','description'=>'_wt_feed_min_energy_effciency_class');
    $post_columns['meta:_wt_feed_max_energy_efficiency_class'] = array('title'=>'_wt_feed_max_energy_effciency_class','description'=>'_wt_feed_max_energy_effciency_class');
    $post_columns['meta:_wt_feed_glpi_pickup_method'] = array('title'=>'_wt_feed_glpi_pickup_method','description'=>'_wt_feed_glpi_pickup_method');
    $post_columns['meta:_wt_feed_glpi_pickup_sla'] = array('title'=>'_wt_feed_glpi_pickup_sla','description'=>'_wt_feed_glpi_pickup_sla');
    $post_columns['meta:_wt_feed_custom_label_0'] = array('title'=>'_wt_feed_custom_label_0','description'=>'_wt_feed_custom_label_0');
    $post_columns['meta:_wt_feed_custom_label_1'] = array('title'=>'_wt_feed_custom_label_1','description'=>'_wt_feed_custom_label_1');
    $post_columns['meta:_wt_feed_custom_label_2'] = array('title'=>'_wt_feed_custom_label_2','description'=>'_wt_feed_custom_label_2');
    $post_columns['meta:_wt_feed_custom_label_3'] = array('title'=>'_wt_feed_custom_label_3','description'=>'_wt_feed_custom_label_3');
    $post_columns['meta:_wt_feed_custom_label_4'] = array('title'=>'_wt_feed_custom_label_4','description'=>'_wt_feed_custom_label_4');
    $post_columns['meta:_wt_google_google_product_category'] = array('title'=>'_wt_google_google_product_category','description'=>'_wt_google_google_product_category');
    $post_columns['meta:_wt_facebook_fb_product_category'] = array('title'=>'_wt_facebook_fb_product_category','description'=>'_wt_facebook_fb_product_category');
}

if (function_exists('wt_feed_check_for_woocommerce')) {

    /* product feed pro is active */

    $post_columns['meta:_wt_feed_han'] = array('title'=>'_wt_feed_han','description'=>'_wt_feed_han');
    $post_columns['meta:_wt_feed_ean'] = array('title'=>'_wt_feed_ean','description'=>'_wt_feed_ean');

}


return apply_filters('woocommerce_csv_product_import_reserved_fields_pair', $post_columns);
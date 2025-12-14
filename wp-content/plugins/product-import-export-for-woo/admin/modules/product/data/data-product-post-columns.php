<?php

if (!defined('WPINC')) {
    exit;
}

if (function_exists('wc_get_filename_from_url')) {
    $file_path_header = 'downloadable_files';
} else {
    $file_path_header = 'file_paths';
}


$post_columns = array(
    'post_title' => 'Product name',
    'post_name' => 'Product slug',
    'post_parent' => 'Parent ID',
    'ID' => 'ID',
    'post_content' => 'Description',
    'post_excerpt' => 'Short description',    
    'post_status' => 'Status',
    'post_password' => 'Post password',
    'menu_order' => 'Menu order',
    'post_date' => 'Post date',
    'post_author' => 'Post author',
    'comment_status' => 'Comment status',
    // Meta

    'sku' => 'SKU',
    'parent_sku' => 'Parent SKU',
    'children' => 'Children', //For Grouped products
    'downloadable' => 'Downloadable',
    'virtual' => 'Virtual',
    'stock' => 'Stock',
    'regular_price' => 'Regular price',
    'sale_price' => 'Sale price',
    'weight' => 'Weight',
    'length' => 'Length',
    'width' => 'Width',
    'height' => 'Height',
    'tax_class' => 'Tax class',
    'visibility' => 'Visibility',
    'stock_status' => 'Stock status',
    'backorders' => 'Backorders',
    'sold_individually' => 'Sold individually',
    'low_stock_amount' => 'Low stock amount',
    'manage_stock' => 'Manage stock',
    'tax_status' => 'Tax status',
    'upsell_ids' => 'Upsell IDs',
    'crosssell_ids' => 'Crosssell IDs',
    'purchase_note' => 'Purchase note',
    'sale_price_dates_from' => 'Sale price dates from',
    'sale_price_dates_to' => 'Sale price dates_to',
    // Downloadable products
    'download_limit' => 'Download limit',
    'download_expiry' => 'Download expiry',    
    // Virtual products
    'product_url' => 'Product URL',
    'button_text' => 'Button text',



    'images' => 'Images (featured and gallery)',
    "$file_path_header" => 'Downloadable file paths',
    'product_page_url' => 'Product page URL',
	'meta:total_sales' => 'Total sales count',
    'meta:_global_unique_id' => 'GTIN, UPC, EAN, or ISBN',
    //'taxonomies' => 'Taxonomies (cat/tags/shipping-class)',
    //'meta' => 'Meta (custom fields)',
    //'attributes' => 'Attributes',
);

if(class_exists('WPSEO_Options')){
    /* Yoast is active */

    $post_columns['meta:_yoast_wpseo_focuskw'] = 'meta:_yoast_wpseo_focuskw';
    $post_columns['meta:_yoast_wpseo_canonical'] = 'meta:_yoast_wpseo_canonical';
    $post_columns['meta:_yoast_wpseo_bctitle'] = 'meta:_yoast_wpseo_bctitle';
    $post_columns['meta:_yoast_wpseo_meta-robots-adv'] = 'meta:_yoast_wpseo_meta-robots-adv';
    $post_columns['meta:_yoast_wpseo_is_cornerstone'] ='meta:_yoast_wpseo_is_cornerstone';
    $post_columns['meta:_yoast_wpseo_metadesc'] = 'meta:_yoast_wpseo_metadesc';
    $post_columns['meta:_yoast_wpseo_linkdex'] = 'meta:_yoast_wpseo_linkdex';
    $post_columns['meta:_yoast_wpseo_estimated-reading-time-minutes'] = 'meta:yoast_wpseo_estimated-reading-time-minutes';
    $post_columns['meta:_yoast_wpseo_content_score'] = 'meta:_yoast_wpseo_focuskw';
    $post_columns['meta:_yoast_wpseo_title'] = 'meta:_yoast_wpseo_title';
    $post_columns['meta:_yoast_wpseo_metadesc'] = 'meta:_yoast_wpseo_metadesc';
    $post_columns['meta:_yoast_wpseo_metakeywords'] = 'meta:_yoast_wpseo_metakeywords';
    
}

if (function_exists( 'aioseo' )) {
    /* All in One SEO is active */

    $post_columns['meta:_aioseo_title'] = 'meta:_aioseo_title';
    $post_columns['meta:_aioseo_description'] =  'meta:_aioseo_description';
    $post_columns['meta:_aioseo_keywords'] =  'meta:_aioseo_keywords';
    $post_columns['meta:_aioseo_og_title'] =  'meta:_aioseo_og_title';
    $post_columns['meta:_aioseo_og_description'] =  'meta:_aioseo_og_description';
    $post_columns['meta:_aioseo_twitter_title'] =  'meta:_aioseo_twitter_title';
    $post_columns['meta:_aioseo_og_article_tags'] =  'meta:_aioseo_og_article_tags';
    $post_columns['meta:_aioseo_twitter_description'] =  'meta:_aioseo_twitter_description';
}

if (class_exists('WC_COG_Loader')) {
	$post_columns['meta:_wc_cog_cost'] = 'meta:_wc_cog_cost';
}

if (apply_filters('wpml_setting', false, 'setup_complete')) {

    $post_columns['wpml:language_code'] = 'wpml:language_code';
    $post_columns['wpml:original_product_id'] = 'wpml:original_product_id';
    $post_columns['wpml:original_product_sku'] = 'wpml:original_product_sku';
}

if ( function_exists( 'wt_feed_basic_check_for_woocommerce' ) || function_exists('wt_feed_check_for_woocommerce') ) {
   
    /* product feed or feed pro is active */   

    $post_columns['meta:_wt_feed_brand'] = 'meta:_wt_feed_brand';
    $post_columns['meta:_wt_feed_condition'] = 'meta:_wt_feed_condition';
    $post_columns['meta:_wt_feed_gtin'] = 'meta:_wt_feed_gtin';
    $post_columns['meta:_wt_feed_mpn'] = 'meta:_wt_feed_mpn';
    $post_columns['meta:_wt_feed_agegroup'] = 'meta:_wt_feed_agegroup';
    $post_columns['meta:_wt_feed_gender'] = 'meta:_wt_feed_gender';
    $post_columns['meta:_wt_feed_size'] = 'meta:_wt_feed_size';
    $post_columns['meta:_wt_feed_color'] = 'meta:_wt_feed_color';
    $post_columns['meta:_wt_feed_material'] = 'meta:_wt_feed_material';
    $post_columns['meta:_wt_feed_pattern'] = 'meta:_wt_feed_pattern';
    $post_columns['meta:_wt_feed_unit_pricing_measure'] = 'meta:_wt_feed_unit_pricing_measure';
    $post_columns['meta:_wt_feed_unit_pricing_base_measure'] = 'meta:_wt_feed_unit_pricing_base_measure';
    $post_columns['meta:_wt_feed_energy_efficiency_class'] = 'meta:_wt_feed_energy_efficiency_class';
    $post_columns['meta:_wt_feed_min_energy_efficiency_class'] = 'meta:_wt_feed_min_energy_efficiency_class';
    $post_columns['meta:_wt_feed_max_energy_efficiency_class'] = 'meta:_wt_feed_max_energy_efficiency_class';
    $post_columns['meta:_wt_feed_glpi_pickup_method'] = 'meta:_wt_feed_glpi_pickup_method';
    $post_columns['meta:_wt_feed_glpi_pickup_sla'] = 'meta:_wt_feed_glpi_pickup_sla';
    $post_columns['meta:_wt_feed_custom_label_0'] = 'meta:_wt_feed_custom_label_0';
    $post_columns['meta:_wt_feed_custom_label_1'] = 'meta:_wt_feed_custom_label_1';
    $post_columns['meta:_wt_feed_custom_label_2'] = 'meta:_wt_feed_custom_label_2';
    $post_columns['meta:_wt_feed_custom_label_3'] = 'meta:_wt_feed_custom_label_3';
    $post_columns['meta:_wt_feed_custom_label_4'] = 'meta:_wt_feed_custom_label_4';
    $post_columns['meta:_wt_google_google_product_category'] = 'meta:_wt_google_google_product_category';
    $post_columns['meta:_wt_facebook_fb_product_category'] = 'meta:_wt_facebook_fb_product_category';
}

if (function_exists('wt_feed_check_for_woocommerce')) {

    /* product feed pro is active */

    $post_columns['meta:_wt_feed_han'] = '_wt_feed_han';
    $post_columns['meta:_wt_feed_ean'] = '_wt_feed_ean';

}

return apply_filters('wt_iew_product_post_columns',$post_columns);
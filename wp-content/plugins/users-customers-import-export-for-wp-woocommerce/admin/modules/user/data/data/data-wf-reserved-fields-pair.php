<?php
if (!defined('ABSPATH')) {
    exit;
}

$columns = array(
    'ID' => array('title'=>'ID','description'=>'Customer/User ID'),
    'customer_id' => array('title'=>'Customer ID','description'=>'Customer ID'),
    'user_login' => array('title'=>'User Login','description'=>'User Login'),
    'user_pass' => array('title'=>'user_pass','description'=>'user_pass'),
    'user_nicename' => array('title'=>'user_nicename','description'=>'user_nicename'),
    'user_email' => array('title'=>'user_email','description'=>'user_email'),
    'user_url' => array('title'=>'user_url','description'=>'user_url'),
    'user_registered' => array('title'=>'user_registered','description'=>'user_registered'),
    'display_name' => array('title'=>'display_name','description'=>'display_name'),
    'first_name' => array('title'=>'first_name','description'=>'first_name'),
    'last_name' => array('title'=>'last_name','description'=>'last_name'),
    'user_status' => array('title'=>'user_status','description'=>'user_status'),
    'roles' => array('title'=>'roles','description'=>'roles'),
);

// default meta
$columns['nickname'] = array('title'=>'nickname','description'=>'');
$columns['first_name'] = array('title'=>'first_name','description'=>'');
$columns['last_name'] = array('title'=>'last_name','description'=>'');
$columns['description'] = array('title'=>'description','description'=>'');
$columns['rich_editing'] = array('title'=>'rich_editing','description'=>'');
$columns['syntax_highlighting'] = array('title'=>'syntax_highlighting','description'=>'');
$columns['admin_color'] = array('title'=>'admin_color','description'=>'');
$columns['use_ssl'] = array('title'=>'use_ssl','description'=>'');
$columns['show_admin_bar_front'] = array('title'=>'show_admin_bar_front','description'=>'');
$columns['locale'] = array('title'=>'locale','description'=>'');
$columns['wp_user_level'] = array('title'=>'wp_user_level','description'=>'');
$columns['dismissed_wp_pointers'] = array('title'=>'dismissed_wp_pointers','description'=>'');
$columns['show_welcome_panel'] = array('title'=>'show_welcome_panel','description'=>'');
$columns['session_tokens'] = array('title'=>'session_tokens','description'=>'');
$columns['last_update'] = array('title'=>'last_update','description'=>'');
$columns['is_guest_user'] = array('title'=>'is_guest_user','description'=>'');


if ( class_exists( 'WooCommerce' ) ):
    $columns['orders'] = array('title'=>'orders','description'=>'');    
    $columns['total_spent'] = array('title'=>'total_spent','description'=>'');    
    $columns['aov'] = array('title'=>'aov','description'=>'');	
    $columns['billing_first_name'] = array('title'=>'Billing first name','description'=>'');
    $columns['billing_last_name'] = array('title'=>'Billing last name','description'=>'');
    $columns['billing_company'] = array('title'=>'Billing company','description'=>'');
    $columns['billing_email'] = array('title'=>'Billing email','description'=>'');
    $columns['billing_phone'] = array('title'=>'Billing phone','description'=>'');
    $columns['billing_address_1'] = array('title'=>'Billing address 1','description'=>'');
    $columns['billing_address_2'] = array('title'=>'Billing address 2','description'=>'');
    $columns['billing_postcode'] = array('title'=>'Billing postcode','description'=>'');
    $columns['billing_city'] = array('title'=>'Billing city','description'=>'');
    $columns['billing_state'] = array('title'=>'Billing state','description'=>'');
    $columns['billing_country'] = array('title'=>'Billing country','description'=>'');
    $columns['shipping_first_name'] = array('title'=>'Shipping first name','description'=>'');
    $columns['shipping_last_name'] = array('title'=>'Shipping last name','description'=>'');
    $columns['shipping_company'] = array('title'=>'Shipping company','description'=>'');
    $columns['shipping_phone'] = array('title'=>'Shipping phone','description'=>'');
    $columns['shipping_address_1'] = array('title'=>'Shipping address 1','description'=>'');
    $columns['shipping_address_2'] = array('title'=>'Shipping address 2','description'=>'');
    $columns['shipping_postcode'] = array('title'=>'Shipping postcode','description'=>'');
    $columns['shipping_city'] = array('title'=>'Shipping city','description'=>'');
    $columns['shipping_state'] = array('title'=>'Shipping state','description'=>'');
    $columns['shipping_country'] = array('title'=>'Shipping country','description'=>'');
    $columns['wc_last_active'] =array('title'=>'Wc last active','description'=>'');
endif;




//global $wpdb;
//
//$meta_keys = $wpdb->get_col("SELECT distinct(meta_key) FROM $wpdb->usermeta WHERE meta_key NOT IN ('wp_capabilities')");
//
//foreach ($meta_keys as $meta_key) {
//    if (empty($columns[$meta_key])) {
//        $columns['meta:'.$meta_key] = array('title'=>'meta:'.$meta_key,'description'=>'');
//    }
//}
return apply_filters('hf_csv_customer_import_columns', $columns);
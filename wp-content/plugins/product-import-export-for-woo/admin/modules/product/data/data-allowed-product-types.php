<?php
if (!defined('WPINC')) {
    exit;
}

$allowed_product_types = array(
    'simple' => __('Simple product', 'product-import-export-for-woo'),
    'grouped' => __('Grouped product', 'product-import-export-for-woo'),
    'external' => __('External/Affiliate product', 'product-import-export-for-woo'),
);

return apply_filters('wt_iew_allowed_product_types', $allowed_product_types);
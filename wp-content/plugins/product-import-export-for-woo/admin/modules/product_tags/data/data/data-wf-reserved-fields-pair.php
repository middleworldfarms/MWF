<?php

if (!defined('WPINC')) {
    exit;
}
// Reserved column names
return apply_filters('woocommerce_csv_tags_import_reserved_fields_pair', array(
    'term_id' => array('title' => 'Tag term ID', 'description' => 'Tag term ID'),
    'name' => array('title' => 'Name', 'description' => 'Name of the tag'),
    'slug' => array('title' => 'Slug', 'description' => 'Slug of the tag'),
    'description' => array('title' => 'Description', 'description' => 'Description of the tag'),
        ));

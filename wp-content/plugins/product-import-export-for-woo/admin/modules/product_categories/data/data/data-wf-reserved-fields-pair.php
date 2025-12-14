<?php

if (!defined('WPINC')) {
    exit;
}
// Reserved column names
return apply_filters('woocommerce_csv_taxonomies_import_reserved_fields_pair', array(
    'term_id' => array('title' => 'Taxonomy term ID', 'description' => 'Taxonomy term ID'),
    'name' => array('title' => 'Name', 'description' => 'Name of the taxonomy'),
    'slug' => array('title' => 'Slug', 'description' => 'Slug of the taxonomy'),
    'description' => array('title' => 'Description', 'description' => 'Description of the taxonomy'),
    'display_type' => array('title' => 'Display type', 'description' => 'Display type of the taxonomy'),
    'parent' => array('title' => 'Parent ID', 'description' => 'Parent ID'),
    'thumbnail' => array('title' => 'Thumbnail', 'description' => 'Thumbnail'),
        ));

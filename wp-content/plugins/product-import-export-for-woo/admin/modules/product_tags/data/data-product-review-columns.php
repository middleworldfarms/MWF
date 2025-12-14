<?php

if (!defined('WPINC')) {
    exit;
}

return apply_filters('wt_tags_csv_product_post_columns', array(
    'term_id' => 'term_id',
    'name' => 'name',
    'slug' => 'slug',
    'description' => 'description',
        ));

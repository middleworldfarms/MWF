<?php

if (!defined('WPINC')) {
    exit;
}

class Wt_Import_Export_For_Woo_Basic_Product_Review_Export {

    public $parent_module = null;

    public function __construct($parent_object) {

        $this->parent_module = $parent_object;
    }

    public function prepare_header() {

        $export_columns = $this->parent_module->get_selected_column_names();

        return apply_filters('wt_alter_product_reviews_export_csv_columns', $export_columns);
    }

    /**
     * Prepare data that will be exported.
     */
    public function prepare_data_to_export($form_data, $batch_offset) {        
        if (!function_exists('get_current_screen')) {
            require_once(ABSPATH . 'wp-admin/includes/screen.php');
        }

        $export_reply = !empty($form_data['filter_form_data']['reply']) ? '1' : '';
        $stars = !empty($form_data['filter_form_data']['wt_iew_stars']) ? $form_data['filter_form_data']['wt_iew_stars'] : '';
        $owner = !empty($form_data['filter_form_data']['wt_iew_owner']) ? $form_data['filter_form_data']['wt_iew_owner'] : '';
        $products = !empty($form_data['filter_form_data']['wt_iew_product']) ? $form_data['filter_form_data']['wt_iew_product'] : '';
        $pr_rev_date_from = !empty($form_data['filter_form_data']['wt_iew_date_from']) ? $form_data['filter_form_data']['wt_iew_date_from'] : gmdate('Y-m-d 00:00', 0);
        $pr_rev_date_to = !empty($form_data['filter_form_data']['wt_iew_date_to']) ? $form_data['filter_form_data']['wt_iew_date_to'] : gmdate('Y-m-d 23:59', current_time('timestamp'));
        $pr_rev_status = !empty($form_data['filter_form_data']['wt_iew_status']) ? $form_data['filter_form_data']['wt_iew_status'] : '';
        $sortcolumn = !empty($form_data['filter_form_data']['wt_iew_sort_columns']) ? $form_data['filter_form_data']['wt_iew_sort_columns'] : 'comment_ID';
        $export_sort_order = !empty($form_data['filter_form_data']['wt_iew_order_by']) ? $form_data['filter_form_data']['wt_iew_order_by'] : 'ASC';
        
        $export_limit = !empty($form_data['filter_form_data']['wt_iew_limit']) ? intval($form_data['filter_form_data']['wt_iew_limit']) : 999999999; //user limit
        $current_offset = !empty($form_data['filter_form_data']['wt_iew_offset']) ? intval($form_data['filter_form_data']['wt_iew_offset']) : 0; //user offset
        $batch_count = !empty($form_data['advanced_form_data']['wt_iew_batch_count']) ? $form_data['advanced_form_data']['wt_iew_batch_count'] : Wt_Import_Export_For_Woo_Basic_Common_Helper::get_advanced_settings('default_export_batch');

        
        $real_offset = ($current_offset + $batch_offset);

        if($batch_count<=$export_limit)
        {
            if(($batch_offset+$batch_count)>$export_limit) //last offset
            {
                $limit=$export_limit-$batch_offset;
            }else
            {
                $limit=$batch_count;
            }
        }else
        {
            $limit=$export_limit;
        }

        $data_array = array();
        if ($batch_offset < $export_limit)
        {

            $args = array(
                'orderby' => $sortcolumn,
                'order' => $export_sort_order,
                'post_type' => 'product',                
                'date_query' => array(
                    array(
                        'before' => $pr_rev_date_to,
                        'after' => $pr_rev_date_from,
                        'inclusive' => true,
                    ),
                ),
            );

			if($pr_rev_status) {
                $status_mapping = array(
                    'Approved' => 'approve',
                    'Pending' => 'hold',
                    'Spam' => 'spam', 
                    'Trash' => 'trash'
                );

                $args['status'] = is_array($pr_rev_status) 
                    ? array_map(function($status) use ($status_mapping) {
                        return isset($status_mapping[$status]) ? $status_mapping[$status] : $status;
                    }, $pr_rev_status)
                    : (isset($status_mapping[$pr_rev_status]) ? $status_mapping[$pr_rev_status] : $pr_rev_status);

            }
            
//            if($export_reply == ''){ 
//                $args['hierarchical'] = 'threaded'; // threaded  flat
//            }
            
//        if (!empty($selected_pr_rev_ids)) {
//            $args['comment__in'] = $selected_pr_rev_ids;
//        }
            if (!empty($products)) {
                $args['post__in'] = implode(',', $products);
            }            
            
            if (!empty($stars)) {
                $args['meta_query'][] = array('key' => 'rating', 'value' => $stars);
            }


            if (!empty($owner)) {
                if ($owner == 'verified') {
                    $args['author__not_in'] =  array(0);
                }
                if ($owner == 'non-verified') {
                    $args['user_id'] =  0;
                }
            }
                        
            $args = apply_filters('product_reviews_csv_product_export_args', $args);
            $args['offset'] = $real_offset;
            $args['number'] = $limit;  

			$args['hierarchical'] = 'threaded';
			
            $comments_query = new WP_Comment_Query;
            $comments = $comments_query->query($args);

            foreach ($comments as $comment) {
                $data_array[] = $this->hf_import_to_csv($comment, $comments);
                
                if ($export_reply === '1') {
                    $sub_reply = get_comments(array('parent' => $comment->comment_ID));
                    if (!empty($sub_reply)) {
                        foreach ($sub_reply as $reply) {
                            $data_array[] = $this->hf_import_to_csv($reply,$sub_reply);
                        }
                    }
                }
            }
            /**
            *   taking total records
            */
            $total_records=0;
            if($batch_offset==0) //first batch
            {
                $total_item_args=$args;
                $total_item_args['number']=$export_limit; //user given limit
                $total_item_args['offset']=$current_offset; //user given offset
                $comments_query = new WP_Comment_Query;
                $items_found = $comments_query->query($total_item_args);
                $total_records = count($items_found); 
            }

            $return['total'] = $total_records;
            $return['data'] = $data_array;

            if ( 0 === $batch_offset && 0 === $total_records ) {
                $return['no_post'] = __( 'Nothing to export under the selected criteria. Please check and try adjusting the filters.', 'product-import-export-for-woo');
            }

            return $return;
        }
      
    }
     
        public function hf_import_to_csv($comment, $comments) {
        $row = array();
        
        $csv_columns = $this->parent_module->get_selected_column_names();
        $found_review_meta = $this->parent_module->wt_get_found_product_meta();
        
        $comment_ID = $comment->comment_ID;

        $comment->meta = new stdClass;
        $comment->meta->rating = get_comment_meta($comment_ID, 'rating', true);
        $comment->meta->verified = get_comment_meta($comment_ID, 'verified', true);
        $comment->meta->title = get_comment_meta($comment_ID, 'title', true);
        
        if (!empty($found_review_meta)) {
            foreach ($found_review_meta as $comment_metas) {
                $comment->meta->{$comment_metas} = get_comment_meta($comment_ID, $comment_metas, true);
            }
        }

        foreach ($csv_columns as $column => $value) {

                if ($column === 'comment_alter_id') {
                    $row[$column] = $comment_ID;
                    continue;
                }

                if ('meta' == $column) {
                    foreach ($found_review_meta as $commentmeta) {
                        if (isset($comment->meta->$commentmeta)) {
                            $row[$column] = ($comment->meta->$commentmeta);
                        } else {
                            $row[$column] = '';
                        }
                    }
                    continue;
                }
                if (isset($comment->meta->$column)) {
                    $row[$column] = ($comment->meta->$column);
                    continue;
                }

                if (isset($comment->$column) && !is_array($comment->$column)) {
                    if ($column === 'comment_post_ID') {
                        $temp_product_id = sanitize_text_field($comment->$column);
                        // continue;
                    }
                    if ($column === 'user_id') {
                        if (($comment->user_id == 0)) {
                            $user_details = get_user_by('email', $comment->comment_author_email);
                            $row[$column] = is_object($user_details) ? $user_details->ID : 0;
                            continue;
                        } else {
                            $row[$column] = $comment->$column;
                            continue;
                        }
                    }

                    $row[$column] = ($comment->$column);
                    continue;
                }
                if ($column == 'product_title' && !empty($temp_product_id)) {
                    $temp_product_object = ( isset($temp_product_id) && version_compare( WC()->version, '3.0', '>=' ) ) ? wc_get_product($temp_product_id) : get_product($temp_product_id);
                    $row[$column] = $temp_product_object->get_title();
                    continue;
                }

                if ($column === 'product_SKU' && !empty($temp_product_id)) {
                    $row[$column] = (string) get_post_meta($temp_product_id, '_sku', true);
                    continue;
                }
            
        }
        
        $row = apply_filters('wt_alter_product_reviews_export_csv_data', $row, $comment->ID,$csv_columns);
        return $row;

    }

}

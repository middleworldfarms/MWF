<?php

if (!defined('WPINC')) {
    exit;
}

class Wt_Import_Export_For_Woo_Basic_Tags_Export {

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


        $sortcolumn = !empty($form_data['filter_form_data']['wt_iew_sort_columns']) ? $form_data['filter_form_data']['wt_iew_sort_columns'] : 'id';
        $export_sort_order = !empty($form_data['filter_form_data']['wt_iew_order_by']) ? $form_data['filter_form_data']['wt_iew_order_by'] : 'ASC';
        $taxonomy_type =  'product_tag';
        
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

            $args = array('taxonomy' => $taxonomy_type, 'orderby' => $sortcolumn, 'order'=> $export_sort_order, 'hide_empty' => 0, 'offset' => $real_offset, 'number' => $limit);

            $terms = get_terms( $args );

            foreach ($terms as $term) {
                $data_array[] = $this->hf_import_to_csv($term, $terms, $taxonomy_type);
                

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
                $all_terms = get_terms( $total_item_args );
                $total_records = is_array($all_terms) ? count($all_terms) : 0; 
            }

            $return['total'] = $total_records;
            $return['data'] = $data_array;

            if ( 0 === $batch_offset && 0 === $total_records ) {
                $return['no_post'] = __( 'Nothing to export under the selected criteria. Please check and try adjusting the filters.', 'product-import-export-for-woo' );
            }

            return $return;
        }
      
    }
     
        public function hf_import_to_csv($term, $terms, $taxonomy_type) {
        $row = array();

        $csv_columns = $this->parent_module->get_selected_column_names();

        foreach ($csv_columns as $column => $value) {

            if ($column === 'term_id') {
                $row[$column] = $term->term_id;
                continue;
            }
            if ($column === 'name') {
                $row[$column] = htmlspecialchars_decode($term->name);
                continue;
            }
            if ($column === 'slug') {
                $row[$column] = rawurldecode($term->slug);
                continue;
            }
            if ($column === 'description') {
                $row[$column] = $term->description;
                continue;
            }
        }

        $row = apply_filters('wt_alter_product_tags_export_csv_data', $row, $term->term_id, $csv_columns);
        return $row;

    }

}

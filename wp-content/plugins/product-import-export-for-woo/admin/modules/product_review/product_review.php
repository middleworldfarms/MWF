<?php
/**
 * Product section of the plugin
 *
 * @link           
 *
 * @package  Wt_Import_Export_For_Woo 
 */
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('Wt_Import_Export_For_Woo_Basic_Product_Review')){
class Wt_Import_Export_For_Woo_Basic_Product_Review {

    public $module_id = '';
    public static $module_id_static = '';
    public $module_base = 'product_review';
    public $module_name = 'Product review Import Export for WooCommerce';
    public $min_base_version= '1.0.0'; /* Minimum `Import export plugin` required to run this add on plugin */

    private $all_meta_keys = array();
    private $found_product_meta = array();
    private $selected_column_names = null;

    public function __construct()
    {
        /**
        *   Checking the minimum required version of `Import export plugin` plugin available
        */
        if(!Wt_Import_Export_For_Woo_Basic_Common_Helper::check_base_version($this->module_base, $this->module_name, $this->min_base_version))
        {
            return;
        }
        if(!function_exists('is_plugin_active'))
        {
            include_once(ABSPATH.'wp-admin/includes/plugin.php');
        }
        if ( ! class_exists( 'WooCommerce' ) )
        {
            return;
        }
        
        $this->module_id = Wt_Import_Export_For_Woo_basic::get_module_id($this->module_base);

        self::$module_id_static = $this->module_id;
                       
        add_filter('wt_iew_exporter_post_types_basic', array($this, 'wt_iew_exporter_post_types'), 10, 1);
        add_filter('wt_iew_importer_post_types_basic', array($this, 'wt_iew_exporter_post_types'), 10, 1);

        add_filter('wt_iew_exporter_alter_filter_fields_basic', array($this, 'exporter_alter_filter_fields'), 10, 3);
        
        add_filter('wt_iew_exporter_alter_mapping_fields_basic', array($this, 'exporter_alter_mapping_fields'), 10, 3);        
        add_filter('wt_iew_importer_alter_mapping_fields_basic', array($this, 'get_importer_post_columns'), 10, 3);
        
        add_filter('wt_iew_exporter_alter_advanced_fields_basic', array($this, 'exporter_alter_advanced_fields'), 10, 3);
        add_filter('wt_iew_importer_alter_advanced_fields_basic', array($this, 'importer_alter_advanced_fields'), 10, 3);        
        
        add_filter('wt_iew_exporter_alter_meta_mapping_fields_basic', array($this, 'exporter_alter_meta_mapping_fields'), 10, 3);
        add_filter('wt_iew_importer_alter_meta_mapping_fields_basic', array($this, 'importer_alter_meta_mapping_fields'), 10, 3);

        add_filter('wt_iew_exporter_alter_mapping_enabled_fields_basic', array($this, 'exporter_alter_mapping_enabled_fields'), 10, 3);
        add_filter('wt_iew_importer_alter_mapping_enabled_fields_basic', array($this, 'exporter_alter_mapping_enabled_fields'), 10, 3);

        add_filter('wt_iew_exporter_do_export_basic', array($this, 'exporter_do_export'), 10, 7);
        add_filter('wt_iew_importer_do_import_basic', array($this, 'importer_do_import'), 10, 8);

        add_filter('wt_iew_importer_steps_basic', array($this, 'importer_steps'), 10, 2);
    }


    /**
    *   Altering advanced step description
    */
    public function importer_steps($steps, $base)
    {
        if($this->module_base==$base)
        {
            $steps['advanced']['description']=__('Use advanced options from below to decide updates to existing reviews, batch import count. You can also save the template file for future imports.', 'product-import-export-for-woo');
        }
        return $steps;
    }
    
    public function importer_do_import($import_data, $base, $step, $form_data, $selected_template_data, $method_import, $batch_offset, $is_last_batch) {        
        if ($this->module_base != $base) {
            return $import_data;
        }
        
        if(0 == $batch_offset){                        
            $memory = size_format(self::wt_let_to_num(ini_get('memory_limit')));
            $wp_memory = size_format(self::wt_let_to_num(WP_MEMORY_LIMIT));                      
            Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->module_base, 'import', '---[ New import started at '.gmdate('Y-m-d H:i:s').' ] PHP Memory: ' . $memory . ', WP Memory: ' . $wp_memory);
        }
        
        include plugin_dir_path(__FILE__) . 'import/import.php';
        $import = new Wt_Import_Export_For_Woo_Basic_Product_Review_Import($this);
        
        $response = $import->prepare_data_to_import($import_data,$form_data,$batch_offset,$is_last_batch);
        
        if($is_last_batch){
            Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->module_base, 'import', '---[ Import ended at '.gmdate('Y-m-d H:i:s').']---');
        }
        
        return $response;
    }
    
    public static function wt_let_to_num( $size ) {
            $l   = substr( $size, -1 );
            $ret = (int) substr( $size, 0, -1 );
            switch ( strtoupper( $l ) ) {
                    case 'P':
                            $ret *= 1024;
                            // No break.
                    case 'T':
                            $ret *= 1024;
                            // No break.
                    case 'G':
                            $ret *= 1024;
                            // No break.
                    case 'M':
                            $ret *= 1024;
                            // No break.
                    case 'K':
                            $ret *= 1024;
                            // No break.
            }
            return $ret;
    }

    public function exporter_do_export($export_data, $base, $step, $form_data, $selected_template_data, $method_export, $batch_offset) {
        if ($this->module_base != $base) {
            return $export_data;
        }
        
        
        switch ($method_export) {
            case 'quick':
                $this->set_export_columns_for_quick_export($form_data);  
                break;

            case 'template':
            case 'new':
                $this->set_selected_column_names($form_data);
                break;
            
            default:
                break;
        }

        include plugin_dir_path(__FILE__) . 'export/export.php';
        $export = new Wt_Import_Export_For_Woo_Basic_Product_Review_Export($this);

        $header_row = $export->prepare_header();

        $data_row = $export->prepare_data_to_export($form_data, $batch_offset);

        $export_data = array(
            'head_data' => $header_row,
            'body_data' => $data_row['data'],
        );
        
        if(isset($data_row['total']) && !empty($data_row['total'])){
            $export_data['total'] = $data_row['total'];
        }

        if ( isset( $data_row['no_post'] ) ) {
            $export_data['no_post'] = $data_row['no_post'];
        }

        return $export_data;
    }

    /**
     * Adding current post type to export list
     *
     */
    public function wt_iew_exporter_post_types($arr) {
		$arr['order'] = __('Order', 'product-import-export-for-woo');
		$arr['coupon'] = __('Coupon', 'product-import-export-for-woo');
		$arr['product'] = __('Product', 'product-import-export-for-woo');
		$arr['product_review'] = __('Product Review', 'product-import-export-for-woo');
		$arr['product_categories'] = __('Product Categories', 'product-import-export-for-woo');
		$arr['product_tags'] = __('Product Tags', 'product-import-export-for-woo');
		$arr['user'] = __('User/Customer', 'product-import-export-for-woo');
		$arr['subscription'] = __('Subscription', 'product-import-export-for-woo');
        return $arr;
    }
    
    
    
    /*
     * Setting default export columns for quick export
     */
    
    public function set_export_columns_for_quick_export($form_data) {

        $post_columns = self::get_product_review_post_columns();

        $this->selected_column_names = array_combine(array_keys($post_columns), array_keys($post_columns));
        
        if (isset($form_data['method_export_form_data']['mapping_enabled_fields']) && !empty($form_data['method_export_form_data']['mapping_enabled_fields'])) {
            foreach ($form_data['method_export_form_data']['mapping_enabled_fields'] as $value) {
                $additional_quick_export_fields[$value] = array('fields' => array());
            }

            $export_additional_columns = $this->exporter_alter_meta_mapping_fields($additional_quick_export_fields, $this->module_base, array());
            foreach ($export_additional_columns as $value) {
                $this->selected_column_names = array_merge($this->selected_column_names, $value['fields']);
            }
        }
    }
    

    public static function get_product_review_statuses() {
       $comment_statuses = array('approve' => 'Approved', 'hold' => 'Pending', 'spam' => 'Spam', 'trash' => 'Trash');
        return apply_filters('wt_iew_allowed_product_review_statuses', $comment_statuses);
    }

    public static function get_product_review_sort_columns() {
        $sort_columns = array('comment_ID', 'comment_parent', 'comment_post_ID',
         'comment_date', 'comment_date_gmt', 'comment_author_email',
         'comment_type', 'comment_agent', 'comment_approved',
         'comment_author', 'comment_content', 'user_id');
        return apply_filters('wt_iew_allowed_product_review_sort_columns', array_combine($sort_columns, $sort_columns));
    }

    public static function get_product_review_post_columns() {
        return include plugin_dir_path(__FILE__) . 'data/data-product-review-columns.php';
    }
    
    public function get_importer_post_columns($fields, $base, $step_page_form_data) {
        if ($base != $this->module_base) {
            return $fields;
        }
        $colunm = include plugin_dir_path(__FILE__) . 'data/data/data-wf-reserved-fields-pair.php';
//        $colunm = array_map(function($vl){ return array('title'=>$vl, 'description'=>$vl); }, $arr); 
        return $colunm;
    }

    public function exporter_alter_mapping_enabled_fields($mapping_enabled_fields, $base, $form_data_mapping_enabled_fields) {        
        if ($base != $this->module_base) {
            return $mapping_enabled_fields;
        }
        /*
            $mapping_enabled_fields = array();
            $mapping_enabled_fields['meta'] = array(__('Meta (custom fields)'), 0);
         * 
         */
        
        return $mapping_enabled_fields;
    }

    public function exporter_alter_meta_mapping_fields($fields, $base, $step_page_form_data) {
        if ($base != $this->module_base) {
            return $fields;
        }

        /*
        foreach ($fields as $key => $value) {
            switch ($key) {
                case 'meta':
                    $meta_attributes = array();
                    $found_product_meta = $this->wt_get_found_product_meta();
         
                    foreach ($found_product_meta as $product_meta) {
                        $fields[$key]['fields']['meta:' . $product_meta] = 'meta:' . $product_meta;
                    }
                    break;
                
                default:
                    break;
            }
        }
         * 
         */

        return $fields;
    }
    
    public function importer_alter_meta_mapping_fields($fields, $base, $step_page_form_data) {
        if ($base != $this->module_base) {
            return $fields;
        }
        $fields=$this->exporter_alter_meta_mapping_fields($fields, $base, $step_page_form_data);
        $out=array();
        foreach ($fields as $key => $value) 
        {
            $value['fields']=array_map(function($vl){ return array('title'=>$vl, 'description'=>$vl); }, $value['fields']);
            $out[$key]=$value;
        }
        return $out;
    }

    public function wt_get_found_product_meta() {

        if (!empty($this->found_product_meta)) {
            return $this->found_product_meta;
        }

        // Loop products and load meta data
        $found_product_meta = array();
        // Some of the values may not be usable (e.g. arrays of arrays) but the worse
        // that can happen is we get an empty column.

        $all_meta_keys = $this->wt_get_all_meta_keys();

        $csv_columns = self::get_product_review_post_columns();
        
        foreach ($all_meta_keys as $meta) {

            if (!$meta || (substr((string) $meta, 0, 1) == '_') || in_array($meta, array_keys($csv_columns)) || in_array('meta:' . $meta, array_keys($csv_columns)))
                continue;

            $found_product_meta[] = $meta;
        }

        $found_product_meta = array_diff($found_product_meta, array_keys($csv_columns));

        $this->found_product_meta = $found_product_meta;
        return $this->found_product_meta;
    }

    

    public function wt_get_all_meta_keys() {

        if (!empty($this->all_meta_keys)) {
            return $this->all_meta_keys;
        }

        $all_meta_pkeys = self::get_all_metakeys();

        $this->all_meta_keys = $all_meta_pkeys;

        return $this->all_meta_keys;
    }

    /**
     * Get a list of all the meta keys for a post type. This includes all public, private,
     * used, no-longer used etc. They will be sorted once fetched.
     */
    public static function get_all_metakeys() {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $meta = $wpdb->get_col(
        "SELECT DISTINCT cm.meta_key
        FROM {$wpdb->commentmeta} AS cm
        LEFT JOIN {$wpdb->comments} AS c ON c.comment_ID = cm.comment_id
        WHERE c.comment_type = 'review'"  
        );
        sort($meta);
        return $meta;
    }

    public function set_selected_column_names($full_form_data) {

        if (is_null($this->selected_column_names)) {
            if (isset($full_form_data['mapping_form_data']['mapping_selected_fields']) && !empty($full_form_data['mapping_form_data']['mapping_selected_fields'])) {
                $this->selected_column_names = $full_form_data['mapping_form_data']['mapping_selected_fields'];
            }
            if (isset($full_form_data['meta_step_form_data']['mapping_selected_fields']) && !empty($full_form_data['meta_step_form_data']['mapping_selected_fields'])) {
                $export_additional_columns = $full_form_data['meta_step_form_data']['mapping_selected_fields'];
                foreach ($export_additional_columns as $value) {
                    $this->selected_column_names = array_merge($this->selected_column_names, $value);
                }
            }
        }

        return $full_form_data;
    }

    public function get_selected_column_names() {
        return $this->selected_column_names;
    }

    public function exporter_alter_mapping_fields($fields, $base, $mapping_form_data) {
        if ($base != $this->module_base) {
            return $fields;
        }

        $fields = self::get_product_review_post_columns();
        return $fields;
    }


    /**
     *  Customize the items in filter export page
     */
    public function exporter_alter_filter_fields($fields, $base, $filter_form_data) {
        if ($this->module_base != $base) {
            return $fields;
        }

        /* altering help text of default fields */
        $fields['limit']['label']=__('Total number of reviews to export', 'product-import-export-for-woo'); 
		$fields['limit']['help_text']=__('Exports specified number of reviews. e.g. Entering 500 with a skip count of 10 will export reviews from 11th to 510th position.', 'product-import-export-for-woo');
        $fields['offset']['label']=__('Skip first <i>n</i> reviews', 'product-import-export-for-woo');
		$fields['offset']['help_text']=__('Skips specified number of reviews from the beginning of the database. e.g. Enter 10 to skip first 10 reviews from export.', 'product-import-export-for-woo');

        $fields['date_from'] = array(
            'label' => __('Date From', 'product-import-export-for-woo'),
            'placeholder' => __('Date', 'product-import-export-for-woo'),
            'field_name' => 'date_from',
            'sele_vals' => '',
            'help_text' => __('Date on which the review was received. Export products reviews received on and after the specified date.', 'product-import-export-for-woo'),
            'type' => 'text',
            'css_class' => 'wt_iew_datepicker',
//            'type' => 'field_html',
//            'field_html' => '<input type="text" name="date_from" placeholder="'.__('From date').'" class="wt_iew_datepicker input-text" />',
        );
        
        $fields['date_to'] = array(
            'label' => __('Date To', 'product-import-export-for-woo'),
            'placeholder' => __('Date', 'product-import-export-for-woo'),
            'field_name' => 'date_to',
            'sele_vals' => '',
            'help_text' => __('Date on which the review was received. Export products reviews received upto the specified date.', 'product-import-export-for-woo'),
            'type' => 'text',
            'css_class' => 'wt_iew_datepicker',
//            'type' => 'field_html',
//            'field_html' => '<input type="text" name="date_to" placeholder="'. __('To date').'" class="wt_iew_datepicker input-text" />',
        );
        
               
        $fields['product'] = array(
            'label' => __('Products', 'product-import-export-for-woo'),
            'placeholder' => __('All product', 'product-import-export-for-woo'),
            'field_name' => 'product',
            'sele_vals' => array(),
            'help_text' => __('Input the product name to export respective reviews.', 'product-import-export-for-woo'),
            'type' => 'multi_select',
            'css_class' => 'wc-product-search',
            'validation_rule' => array('type'=>'text_arr')
        );
        
        $fields['stars'] = array(
            'label' => __('Stars', 'product-import-export-for-woo'),
            'placeholder' => __('All', 'product-import-export-for-woo'),
            'field_name' => 'stars',
            'sele_vals' => array(1=>1,2=>2,3=>3,4=>4,5=>5),
            'help_text' => __('Export reviews of a specific star rating.', 'product-import-export-for-woo'),
            'type' => 'multi_select',
            'css_class' => 'wc-enhanced-select',
            'validation_rule' => array('type'=>'text_arr')
        );

        $fields['owner'] = array(
            'label' => __('Customer/Guest Review', 'product-import-export-for-woo'),
            'placeholder' => __('All', 'product-import-export-for-woo'),
            'field_name' => 'owner',
            'sele_vals' => array(''=>'All Reviews','verified'=>'Customer','non-verified'=>'Guest'),
            'help_text' => __('Export reviews by customer or guest or both.', 'product-import-export-for-woo'),
            'type' => 'select',
            'css_class' => '',
            'validation_rule' => array('type'=>'text_arr')
        );



        $fields['reply'] = array(
            'label' => __('Review with replies', 'product-import-export-for-woo'),
//            'placeholder' => __('Any tag'),
            'field_name' => 'reply',
            'sele_vals' => '',
            'help_text' => __('Enable to include the replies along with the respective reviews.', 'product-import-export-for-woo'),
            'type' => 'field_html',
            'field_html' => '<input type="checkbox" name="reply" value="1" id="v_replycolumn" class="input-text" />',
            'css_class' => '',
        );

        $fields['status'] = array(
            'label' => __('Status', 'product-import-export-for-woo'),
            'placeholder' => __('All status', 'product-import-export-for-woo'),
            'field_name' => 'status',
            'sele_vals' => self::get_product_review_statuses(),
            'help_text' => __('Export reviews by specific post status.', 'product-import-export-for-woo'),
            'type' => 'multi_select',
            'css_class' => 'wc-enhanced-select',
            'validation_rule' => array('type'=>'text_arr')
        );

        $sort_columns = self::get_product_review_sort_columns();
        $fields['sort_columns'] = array(
            'label' => __('Sort Columns', 'product-import-export-for-woo'),
            'placeholder' => __('comment_ID', 'product-import-export-for-woo'),
            'field_name' => 'sort_columns',
            'sele_vals' => $sort_columns,
            'help_text' => __('Sort the exported data based on the selected column in the order specified. Defaulted to ascending order.', 'product-import-export-for-woo'),
            'type' => 'select',
        );
        
        $fields['order_by'] = array(
                'label' => __('Sort By', 'product-import-export-for-woo'),
                'placeholder' => __('ASC', 'product-import-export-for-woo'),
                'field_name' => 'order_by',
                'sele_vals' => array('ASC' => 'Ascending', 'DESC' => 'Descending'),
                'help_text' => __('Defaulted to Ascending. Applicable to above selected columns in the order specified.', 'product-import-export-for-woo'),
                'type' => 'select',
                'css_class' => '',
            );

        return $fields;
    }
    
    
    public function exporter_alter_advanced_fields($fields, $base, $advanced_form_data) {
        if ($this->module_base != $base) {
            return $fields;
        }
        unset($fields['export_shortcode_tohtml']);
        
        return $fields;
    }
    
    public function importer_alter_advanced_fields($fields, $base, $advanced_form_data) {
        if ($this->module_base != $base) {
            return $fields;
        }        
        $out = array();
        
        $out['skip_new'] = array(
            'label' => __("Update Only", 'product-import-export-for-woo'),
            'type' => 'radio',
            'radio_fields' => array(
                '1' => __('Yes', 'product-import-export-for-woo'),
                '0' => __('No', 'product-import-export-for-woo')
            ),
            'value' => '0',
            'field_name' => 'skip_new',
            'help_text_conditional'=>array(
                array(
                    // translators: %1$s and %2$s are HTML bold tags
                    'help_text'=> sprintf(__('The store is updated with the data from the input file only for matching/existing records from the file. In case of a conflict with %1$sIDs of other existing post types%2$s the reviews will not be imported.', 'product-import-export-for-woo'), '<b>', '</b>'),
                    'condition'=>array(
                        array('field'=>'wt_iew_skip_new', 'value'=>1)
                    )
                ),
                array(
                    'help_text'=> __('The entire data from the input file is processed for an update or insert as the case maybe.', 'product-import-export-for-woo'),
                    'condition'=>array(
                        array('field'=>'wt_iew_skip_new', 'value'=>0)
                    )
                )
            ),
            'form_toggler'=>array(
                'type'=>'parent',
                'target'=>'wt_iew_skip_new',
            )
        ); 
        
        $out['merge'] = array(
            'label' => __("If the review exists in the store", 'product-import-export-for-woo'),
            'type' => 'radio',
            'radio_fields' => array(                
                '0' => __('Skip', 'product-import-export-for-woo'),
                '1' => __('Update', 'product-import-export-for-woo')
            ),
            'value' => '0',
            'field_name' => 'merge',
            'help_text' => __('Reviews are matched by their IDs.', 'product-import-export-for-woo'),
            'help_text_conditional'=>array(
                array(
                    'help_text'=> __('Retains the order in the store as is and skips the matching order from the input file.', 'product-import-export-for-woo'),
                    'condition'=>array(
                        array('field'=>'wt_iew_merge', 'value'=>0)
                    )
                ),
                array(
                    'help_text'=> __('Update order as per data from the input file', 'product-import-export-for-woo'),
                    'condition'=>array(
                        array('field'=>'wt_iew_merge', 'value'=>1)
                    )
                )
            ),
            'form_toggler'=>array(
                'type'=>'parent',
                'target'=>'wt_iew_found_action'
            )
        );
        
        $out['merge_empty_cells'] = array(
            'label' => __("Update even if empty values", 'product-import-export-for-woo'),
            'type' => 'radio',
            'radio_fields' => array(
                '1' => __('Yes', 'product-import-export-for-woo'),
                '0' => __('No', 'product-import-export-for-woo')
            ),
            'value' => '0',
            'field_name' => 'merge_empty_cells',
            'help_text' => __('Updates the order data respectively even if some of the columns in the input file contains empty value.', 'product-import-export-for-woo'),
            'form_toggler'=>array(
                'type'=>'child',
                'id'=>'wt_iew_found_action',
                'val'=>'1',
            )
        );
        
        $out['use_sku'] = array(
            'label' => __("Associate product reviews by SKU", 'product-import-export-for-woo'),
            'type' => 'radio',
            'radio_fields' => array(
                '1' => __('Yes', 'product-import-export-for-woo'),
                '0' => __('No', 'product-import-export-for-woo')
            ),
            'value' => '0',
            'field_name' => 'use_sku',
            'help_text_conditional'=>array(
                array(
                    'help_text'=> __('Link the products reviews being imported with the respective products by their SKU.', 'product-import-export-for-woo'),
                    'condition'=>array(
                        array('field'=>'wt_iew_use_sku', 'value'=>1)
                    )
                ),
                array(
                    // translators: %1$s and %2$s are HTML bold tags
                    'help_text'=> sprintf(__(' Link the products reviews being imported with the respective products by their product ID. In case of a conflict with %1$sIDs of other existing post types%2$s the reviews will not be imported.', 'product-import-export-for-woo'), '<b>', '</b>'),
                    'condition'=>array(
                        array('field'=>'wt_iew_use_sku', 'value'=>0)
                    )
                )
            ),
        );
        
        foreach ($fields as $fieldk => $fieldv) {
            $out[$fieldk] = $fieldv;
        }
        return $out;
    }
    
    public function get_item_by_id($id) {
        $post['edit_url']=get_edit_comment_link($id);
        $post['title'] = get_comment_excerpt($id);
        return $post; 
    }
	public static function get_item_link_by_id($id) {
        $post['edit_url']=get_edit_comment_link($id);
        $post['title'] = get_comment_excerpt($id);
        return $post; 
    }	
}
}
new Wt_Import_Export_For_Woo_Basic_Product_Review();

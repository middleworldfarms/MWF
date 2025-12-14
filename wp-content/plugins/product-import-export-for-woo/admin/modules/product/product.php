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

if(!class_exists('Wt_Import_Export_For_Woo_Basic_Product')){

#[AllowDynamicProperties]
class Wt_Import_Export_For_Woo_Basic_Product {

    public $module_id = '';
    public static $module_id_static = '';
    public $module_base = 'product';
    public $module_name = 'Product Import Export for WooCommerce';
    public $min_base_version= '1.0.0'; /* Minimum `Import export plugin` required to run this add on plugin */

    private $importer = null;
    private $exporter = null;
    private $product_categories = null;
    private $product_tags = null;
    private $product_brands = null;
    private $product_taxonomies = array();
    private $all_meta_keys = array();
    private $product_attributes = array();
    private $exclude_hidden_meta_columns = array();
    private $found_product_meta = array();
    private $found_product_hidden_meta = array();
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

        $this->module_id = Wt_Import_Export_For_Woo_Basic::get_module_id($this->module_base);
        self::$module_id_static = $this->module_id;

        add_filter('wt_iew_exporter_post_types_basic', array($this, 'wt_iew_exporter_post_types_basic'), 10, 1);
        add_filter('wt_iew_importer_post_types_basic', array($this, 'wt_iew_exporter_post_types_basic'), 10, 1);

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
		
        add_action('admin_footer-edit.php', array($this, 'wt_add_products_bulk_actions'));
        add_action('load-edit.php', array($this, 'wt_process_products_bulk_actions'));  
        
    }
    
	
	
        /**
     * Product list page bulk export action add to action list
     * 
     */
    public function wt_add_products_bulk_actions() {
        global $post_type, $post_status;

        if ( 'product' === $post_type && 'trash' !== $post_status  && !is_plugin_active( 'wt-import-export-for-woo/wt-import-export-for-woo.php' ) ) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    var $downloadProducts = $('<option>').val('wt_ier_download_products').text('<?php esc_html_e('Export to CSV', 'product-import-export-for-woo') ?>');
                    $('select[name^="action"]').append($downloadProducts);
                });
            </script>
            <?php
        }
    }
    
    
    /**
     * Product page bulk export action
     * 
     */
    public function wt_process_products_bulk_actions() {
        global $typenow;
        if ($typenow == 'product') {
            // get the action list
            $wp_list_table = _get_list_table('WP_Posts_List_Table');
            $action = $wp_list_table->current_action();
            if (!in_array($action, array('wt_ier_download_products'))) {
                return;
            }
            // security check
            check_admin_referer('bulk-posts');

            if (isset($_REQUEST['post'])) {
                $prod_ids = array_map('absint', $_REQUEST['post']);
            }
            if (empty($prod_ids)) {
                return;
            }

            if ($action == 'wt_ier_download_products') {
                include_once( 'export/class-wt-prodimpexpcsv-basic-exporter.php' );

                Wt_Import_Export_For_Woo_Basic_Product_Bulk_Export::do_export('product', $prod_ids);
            }
        }
    }

	
	
	
    /**
    *   Altering advanced step description
    */
    public function importer_steps($steps, $base)
    {
        if($this->module_base==$base)
        {
            $steps['advanced']['description']=__('Use advanced options from below to decide updates to existing products, batch import count. You can also save the template file for future imports.', 'product-import-export-for-woo');
        }
        return $steps;
    }

    public function importer_do_import($import_data, $base, $step, $form_data, $selected_template_data, $method_import, $batch_offset, $is_last_batch) {        
        if ($this->module_base != $base) {
            return $import_data;
        }
            
        if(0 == $batch_offset){                        
            $memory = size_format(wt_let_to_num_basic(ini_get('memory_limit')));
            $wp_memory = size_format(wt_let_to_num_basic(WP_MEMORY_LIMIT));                      
            Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->module_base, 'import', '---[ New import started at '.gmdate('Y-m-d H:i:s').' ] PHP Memory: ' . $memory . ', WP Memory: ' . $wp_memory);
        }
        
        include plugin_dir_path(__FILE__) . 'import/import.php';
        $import = new Wt_Import_Export_For_Woo_Basic_Product_Import($this);
        
        $response = $import->prepare_data_to_import($import_data,$form_data,$batch_offset,$is_last_batch);
        
        if($is_last_batch){
            Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->module_base, 'import', '---[ Import ended at '.gmdate('Y-m-d H:i:s').']---');
        }
        return $response;
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
        $export = new Wt_Import_Export_For_Woo_Basic_Product_Export($this);

        $header_row = $export->prepare_header();

        $data_row = $export->prepare_data_to_export($form_data, $batch_offset,$step);
        
		$export_data = array(
			'head_data' => $header_row,
			'body_data' => $data_row['data'],
			'total' => $data_row['total'],
		); 
		
		if(isset($data_row['no_post'])){
			$export_data['no_post'] = $data_row['no_post'];
		}

        
        return $export_data;
    }
        
    /**
     * Adding current post type to export list
     *
     */
    public function wt_iew_exporter_post_types_basic($arr) {
        
        if(is_plugin_active('product-import-export-for-woo/product-import-export-for-woo.php')){
            $arr['product'] = __('Product', 'product-import-export-for-woo');
            $arr['product_review'] = __('Product Review', 'product-import-export-for-woo');
            $arr['product_categories'] = __('Product Categories', 'product-import-export-for-woo');
            $arr['product_tags'] = __('Product Tags', 'product-import-export-for-woo');
        }
        if(is_plugin_active('order-import-export-for-woocommerce/order-import-export-for-woocommerce.php')){
            $arr['order'] = __('Order', 'product-import-export-for-woo');
		    $arr['coupon'] = __('Coupon', 'product-import-export-for-woo');
        }
        if(is_plugin_active('users-customers-import-export-for-wp-woocommerce/users-customers-import-export-for-wp-woocommerce.php'))
        {
            $arr['user'] = __('User/Customer', 'product-import-export-for-woo'); 
        }
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

    /**
     * Add/Remove steps in export section.
     * @param array $steps array of built in steps
     * @param string $base product, order etc
     * @return array $steps 
     */
    public function wt_iew_exporter_steps($steps, $base) {
        if ($base == $this->module_base) {
            foreach ($steps as $stepk => $stepv) {
                $out[$stepk] = $stepv;
                if ($stepk == 'filter') {
                    /*
                      $out['product']=array(
                      'title'=>'Product',
                      'description'=>'',
                      );
                     */
                }
            }
        } else {
            $out = $steps;
        }
        return $out;
    }
    
    
    /*
     * Setting default export columns for quick export
     */
    
    public function set_export_columns_for_quick_export($form_data) {

        $post_columns = self::get_product_post_columns();

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


    /**
     * Get product categories
     * @return array $categories 
     */
    private function get_product_categories() {
        if (!is_null($this->product_categories)) {
            return $this->product_categories;
        }
        $out = array();
        $product_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false
        ));
        if ( ! is_wp_error( $product_categories ) ) {
            $version = get_bloginfo('version');
            foreach ( $product_categories as $category ) {
                if ( $version < '4.8' ) {
                    $out[$category->slug] = $category->name;
                } else {
                    // 2.5.5 - Use a safer approach to get parent list
                    $parent_list = $this->get_safe_term_parents_list( $category->term_id, 'product_cat' );
                    if ( ! empty( $parent_list ) ) {
                        $out[$category->slug] = $parent_list;
                    } else {
                        continue;
                    }
                }
            }
        }
        $this->product_categories = $out;
        return $out;
    }

     /**
     * Get product brands
     * @return array $brands 
     */
    private function get_product_brands() {
        if ( ! is_null( $this->product_brands ) ) {
            return $this->product_brands;
        }
        $out = array();
        
        // Default brand taxonomy
        $default_taxonomy = array('product_brand');
        
        // Get additional brand taxonomies from filter
        $additional_taxonomies = apply_filters('wt_iew_product_brand_taxonomies', array());
        
        // Combine default with additional taxonomies
        $brand_taxonomies = array_merge($default_taxonomy, $additional_taxonomies);

        $version = get_bloginfo('version');
        
        // Process each taxonomy with safety checks
        foreach ($brand_taxonomies as $taxonomy) {
            // Validate taxonomy name
            if ( empty( $taxonomy ) || ! is_string( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }
            
            try {
                $brands = get_terms($taxonomy);
                if ( is_wp_error( $brands ) || empty( $brands ) || ! is_array( $brands ) ) {
                    continue;
                }
                
                foreach ( $brands as $brand ) {
                    // Skip invalid or incomplete brand terms
                    if ( ! is_object( $brand ) || ! isset( $brand->term_id ) || empty( $brand->slug ) || empty( $brand->name ) ) {
                        continue;
                    }
                    
                    // Prefix with taxonomy name to avoid conflicts between taxonomies
                    $key = $taxonomy . ':' . $brand->slug;
                    
                    // Show hierarchy like categories if WordPress version is 4.8 or higher
                    if ( version_compare( $version, '4.8', '<' ) ) {
                        // WordPress version is less than 4.8 - show simple name
                        $out[$key] = $brand->name;
                    } else {
                        // WordPress version is 4.8 or higher - show hierarchy
                        $parents_list = $this->get_safe_term_parents_list( $brand->term_id, $taxonomy );
                        if ( ! empty( $parents_list ) ) {
                            $out[$key] = $parents_list;
                        } else {
                            $out[$key] = $brand->name;
                        }
                    }
                }
            } catch (Exception $e) {
                // Log error for debugging but continue processing other taxonomies
                continue;
            } catch (Error $e) {
                // Catch fatal errors
                continue;
            }
        }
        
        $this->product_brands = $out;
        return $out;
    }

    /**
     * Safely get term parents list without using get_term_parents_list which can cause WP_Error issues
     * @param int $term_id Term ID
     * @param string $taxonomy Taxonomy name
     * @return string Parent list or empty string on failure
     * 
     * @since 2.5.5
     */
    private function get_safe_term_parents_list($term_id, $taxonomy) {
        try {
            // Validate inputs
            if ( empty( $term_id ) || ! is_numeric( $term_id ) || empty( $taxonomy ) || ! is_string( $taxonomy ) ) {
                return '';
            }
            
            // Check if taxonomy exists
            if ( ! taxonomy_exists( $taxonomy ) ) {
                return '';
            }
            
            // Get the term with safety checks
            $term = get_term($term_id, $taxonomy);
            if ( is_wp_error( $term ) || ! $term  || empty( $term->name ) ) {
                return '';
            }

            // Get ancestors with safety checks
            $parents = get_ancestors($term_id, $taxonomy, 'taxonomy');
            if ( is_wp_error( $parents ) || empty( $parents ) || ! is_array( $parents ) ) {
                // No parents, return just the term name
                return $term->name;
            }

            // Validate parents array contains only numeric IDs
            $valid_parents = array();
            foreach ( $parents as $parent_id ) {
                if ( ! empty( $parent_id ) && is_numeric( $parent_id ) && $parent_id > 0 ) {
                    $valid_parents[] = intval($parent_id);
                }
            }
            
            if ( empty( $valid_parents ) ) {
                return $term->name;
            }

            // Add current term to the beginning
            array_unshift( $valid_parents, intval( $term_id ) );
            
            $parent_names = array();
            $processed_ids = array(); // Prevent duplicate processing
            
            foreach ( array_reverse( $valid_parents ) as $parent_id ) {
                // Prevent processing the same ID twice
                if ( in_array( $parent_id, $processed_ids ) ) {
                    continue;
                }
                $processed_ids[] = $parent_id;
                
                // Get parent term with safety checks
                $parent = get_term($parent_id, $taxonomy);
                if ( ! is_wp_error( $parent ) && $parent && ! empty( $parent->name ) ) {
                    $parent_names[] = $parent->name;
                }
            }

            // Return hierarchy or just the term name if no valid parents found
            if ( empty( $parent_names ) ) {
                return $term->name;
            }
            
            return implode( ' -> ', $parent_names );
        } catch (Exception $e) {
            return '';
        } catch (Error $e) {
            return '';
        }
    }

    private function get_product_tags() {
        if (!is_null($this->product_tags)) {
            return $this->product_tags;
        }
        $out = array();
        $product_tags = get_terms('product_tag');
        if (!is_wp_error($product_tags)) {
            foreach ($product_tags as $tag) {
                $out[$tag->slug] = $tag->name;
            }
        }
        $this->product_tags = $out;
        return $out;
    }

    public static function get_product_types() {
        return include plugin_dir_path(__FILE__) . 'data/data-allowed-product-types.php';
        /*
        $product_types = array();
        foreach ( wc_get_product_types() as $value => $label ) {
            $product_types[esc_attr( $value )] = esc_html( $label );
        }
        
        return array_merge($product_types, array('variation' => 'Product variations'));
         * 
         */
    }

    public static function get_product_statuses() {
        $product_statuses = array('publish', 'private', 'draft', 'pending', 'future');
        return apply_filters('wt_iew_allowed_product_statuses', array_combine($product_statuses, $product_statuses));
    }

    public static function get_product_sort_columns() {    
//        $sort_columns = array('post_parent', 'ID', 'post_author', 'post_date', 'post_title', 'post_name', 'post_modified', 'menu_order', 'post_modified_gmt', 'rand', 'comment_count');
        $sort_columns = array('ID'=>'ID', 'name'=>'post_title', 'type'=>'Product Type', 'date'=>'post_date', 'modified'=>'post_modified');
        return apply_filters('wt_iew_allowed_product_sort_columns', $sort_columns);
    }

    public static function get_product_post_columns() {
        return include plugin_dir_path(__FILE__) . 'data/data-product-post-columns.php';
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
        if ($base == $this->module_base) {
            $mapping_enabled_fields = array();
            $mapping_enabled_fields['taxonomies'] = array(__('Taxonomies (categories/tags/shipping-class)', 'product-import-export-for-woo'), 1);

            $mapping_enabled_fields['attributes'] = array(__('Attributes', 'product-import-export-for-woo'), 1);
            // $mapping_enabled_fields['meta'] = array(__('Meta (custom fields)'), 0);
            $mapping_enabled_fields['hidden_meta'] = array(__('Hidden meta', 'product-import-export-for-woo'), 0);

        }
        return $mapping_enabled_fields;
    }

        /**
     * Get upgrade banner HTML for premium features
     */
    public function get_upgrade_banner_html() {
        $icon_url = plugins_url('admin/wt-ds/icons/icons/right-arrow-3.svg', __FILE__);

        return '<div id="product-type-notice" style="margin-top: 10px; display: block; width: 850px; height: auto; top: 210px; left: 117px;">
    <div class="notice notice-warning" style="width: 92.5%; max-width: 810px; margin-left: 0px; display: inline-flex; padding: 16px 18px 16px 26px; justify-content: flex-end; align-items: center; border-radius: 8px; border: 1px solid #F5F9FF; background-color: #F5F9FF; box-sizing: border-box;">
        <div style="display: flex; flex: 1 1 0; flex-direction: column; justify-content: flex-start; align-items: flex-start; width: 100%;">
            <!-- Title -->
            <div style="padding-bottom: 10px; align-self: stretch; color: #2A3646; font-size: 14px; font-family: Inter; font-weight: 600; line-height: 16px; word-wrap: break-word;">
                ' . __('Upgrade to premium 💎', 'product-import-export-for-woo') . '
            </div>

            <!-- Description -->
            <div style="width: 100%; max-width: 679px; padding-bottom: 10px;">
                <span style="color: #2A3646; font-size: 13px; font-family: Inter; font-weight: 400; ">' . __('We\'ve detected hidden WooCommerce metadata & custom product fields in your store. Unlock full access to export them seamlessly.', 'product-import-export-for-woo') . '</span>
            </div>

            <!-- Button -->
                <a href="https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin&utm_medium=export_hidden_meta_tab&utm_campaign=Product_import_export" target="_blank" style="
                    width: auto;
                    height: 18px;
                font-family:  \'Inter\', sans-serif;
                    font-weight: 600;
                    font-size: 12px;
                    line-height: 100%;
                    color: #2B28E9;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 4px;
                    gap: 5px;
                    text-decoration: none;
                    margin-top: 0px;
                ">
                    ' . __('Upgrade now', 'product-import-export-for-woo') . ' <span style="font-size: 14px;">→</span>
                </a>
            </div>
        </div>
    </div>
';
    }

    
    public function exporter_alter_meta_mapping_fields($fields, $base, $step_page_form_data) {
        if ($base != $this->module_base) {
            return $fields;
        }
        
        // Check if premium plugin is active
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $is_premium_active = is_plugin_active( 'wt-import-export-for-woo-product/wt-import-export-for-woo-product.php' );
        
        foreach ($fields as $key => $value) {
            switch ($key) {
                case 'taxonomies':
                    $product_taxonomies = $this->wt_get_product_taxonomies();
                    foreach ($product_taxonomies as $taxonomy) {
                        if (strstr($taxonomy->name, 'pa_'))
                            continue; // Skip attributes                        
                        $fields[$key]['fields']['tax:' . $taxonomy->name] = 'tax:' . $taxonomy->name;
                    }
                    break;
                    


                case 'attributes':
                    $found_attributes = $this->wt_get_product_attributes();
                    
                    if(!empty($meta_attributes)){  // adding meta attributes
                        foreach ($meta_attributes as $attribute_value) {
                            $fields[$key]['fields']['meta:' . $attribute_value] = 'meta:' . $attribute_value;
                        }
                    }
                    
                    foreach ($found_attributes as $attribute) {
                        $fields[$key]['fields']['attribute:' . $attribute] = 'attribute:' . $attribute;
                        $fields[$key]['fields']['attribute_data:' . $attribute] = 'attribute_data:' . $attribute;                        
                    }
                    
                    break;


                case 'hidden_meta':
                    // Check if premium plugin is active and hidden meta exists
                    if (!$is_premium_active && $this->has_hidden_meta_keys()) {
                        // Set banner HTML instead of fields
                        $fields[$key]['banner_html'] = $this->get_upgrade_banner_html();
                        $fields[$key]['fields'] = array(); // Clear fields to show banner instead
                    } else {
                        // Populate hidden meta fields normally
                        $found_product_hidden_meta = $this->wt_get_found_product_hidden_meta();
                        foreach ($found_product_hidden_meta as $meta) {
                            $fields[$key]['fields']['meta:' . $meta] = 'meta:' . $meta;
                        }
                    }
                    break;

                default:
                    break;
            }
        }

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
            $value['fields'] = array_map(function($vl){ 
				$meta_mapping_temp = array('title'=>$vl, 'description'=>$vl);

				// For fileds other than default fields, the alternates slect firlds cannot be set as of now
				// Its called after loading the default fields so need to load head again in backend to set from similar array
				// Here user alternate field as single value. ( For defaults, its array )
				if( 'tax:product_type' === $vl){
							$meta_mapping_temp['field_type'] = 'alternates';
							$meta_mapping_temp['similar_fields'] = 'Type';
				}
				if( 'tax:product_tag' === $vl){
							$meta_mapping_temp['field_type'] = 'alternates';
							$meta_mapping_temp['similar_fields'] = 'Tags';
				}
				if( 'tax:product_cat' === $vl){
							$meta_mapping_temp['field_type'] = 'alternates';
							$meta_mapping_temp['similar_fields'] = 'Categories';
				}
				if( 'tax:product_shipping_class' === $vl){
							$meta_mapping_temp['field_type'] = 'alternates';
							$meta_mapping_temp['similar_fields'] = 'Shipping class';
				}					
				
				return $meta_mapping_temp; }, $value['fields']);
            $out[$key]=$value;
        }
        return $out;
    }
    
    public function wt_get_product_taxonomies() {

        if (!empty($this->product_taxonomies)) {
            return $this->product_taxonomies;
        }
        $product_ptaxonomies = get_object_taxonomies('product', 'name');
        $product_vtaxonomies = get_object_taxonomies('product_variation', 'name');
        $product_taxonomies = array_merge($product_ptaxonomies, $product_vtaxonomies);

        $this->product_taxonomies = $product_taxonomies;
        return $this->product_taxonomies;
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
        $csv_columns = self::get_product_post_columns();
        $exclude_hidden_meta_columns = $this->wt_get_exclude_hidden_meta_columns();
        foreach ($all_meta_keys as $meta) {

            if (!$meta || (substr((string) $meta, 0, 1) == '_') || in_array($meta, $exclude_hidden_meta_columns) || in_array($meta, array_keys($csv_columns)) || in_array('meta:' . $meta, array_keys($csv_columns)))
                continue;

            $found_product_meta[] = $meta;
        }

        $found_product_meta = array_diff($found_product_meta, array_keys($csv_columns));

        $this->found_product_meta = $found_product_meta;
        return $this->found_product_meta;
    }

        /**
     * Check if any hidden meta keys exist (for existence check only)
     * More efficient than wt_get_found_hidden_meta() when only checking existence
     */
    public function has_hidden_meta_keys() {
        if (isset($this->has_hidden_meta_keys)) {
            return $this->has_hidden_meta_keys;
        }

        global $wpdb;
        $csv_columns = self::get_product_post_columns();
        
        // Use a more efficient query to check for existence of hidden meta keys in products
        $product_params = array_merge(array('product', '_%'), array_keys($csv_columns));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $product_result = $wpdb->get_var( $wpdb->prepare(
            "SELECT 1 FROM {$wpdb->postmeta} AS pm
             LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
             WHERE p.post_type = %s
             AND p.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
             AND pm.meta_key LIKE %s
             AND pm.meta_key NOT IN (" . implode(',', array_fill(0, count($csv_columns), '%s')) . ")
             LIMIT 1",
            $product_params
        ));

        
        if ($product_result !== null) {
            $this->has_hidden_meta_keys = true;
            return true;
        }

        // Check for hidden meta in product variations
        $variation_params = array_merge( array('product_variation', '_%'), array_keys($csv_columns) );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $variation_result = $wpdb->get_var( $wpdb->prepare(
            "SELECT 1 FROM {$wpdb->postmeta} AS pm
             LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
             WHERE p.post_type = %s
             AND p.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
             AND pm.meta_key LIKE %s
             AND pm.meta_key NOT IN (" . implode(',', array_fill(0, count($csv_columns), '%s')) . ")
             LIMIT 1",
            $variation_params
        ));

        
        $this->has_hidden_meta_keys = $variation_result !== null;
        return $this->has_hidden_meta_keys;
    }

    public function wt_get_found_product_hidden_meta() {

        if (!empty($this->found_product_hidden_meta)) {
            return $this->found_product_hidden_meta;
        }

        // Loop products and load meta data
        $found_product_meta = array();
        // Some of the values may not be usable (e.g. arrays of arrays) but the worse
        // that can happen is we get an empty column.

        $all_meta_keys = $this->wt_get_all_meta_keys();
        $csv_columns = self::get_product_post_columns();//$this->get_selected_column_names();
        $exclude_hidden_meta_columns = $this->wt_get_exclude_hidden_meta_columns();
        foreach ($all_meta_keys as $meta) {

            if (!$meta || (substr((string) $meta, 0, 1) != '_') || in_array($meta, $exclude_hidden_meta_columns) || in_array($meta, array_keys($csv_columns)) || in_array('meta:' . $meta, array_keys($csv_columns)))
                continue;

            $found_product_meta[] = $meta;
        }

        $found_product_meta = array_diff($found_product_meta, array_keys($csv_columns));

        $this->found_product_hidden_meta = $found_product_meta;
        return $this->found_product_hidden_meta;
    }

    public function wt_get_exclude_hidden_meta_columns() {

        if (!empty($this->exclude_hidden_meta_columns)) {
            return $this->exclude_hidden_meta_columns;
        }

        $exclude_hidden_meta_columns = include( plugin_dir_path(__FILE__) . 'data/data-wf-hidden-meta-columns.php' );

        $this->exclude_hidden_meta_columns = $exclude_hidden_meta_columns;
        return $this->exclude_hidden_meta_columns;
    }

    public function wt_get_all_meta_keys() {

        if (!empty($this->all_meta_keys)) {
            return $this->all_meta_keys;
        }

        $all_meta_pkeys = self::get_all_metakeys('product');
        $all_meta_vkeys = self::get_all_metakeys('product_variation');
        $all_meta_keys = array_merge($all_meta_pkeys, $all_meta_vkeys);
        $all_meta_keys = array_unique($all_meta_keys);

        $this->all_meta_keys = $all_meta_keys;
        return $this->all_meta_keys;
    }

    /**
     * Get a list of all the meta keys for a post type. This includes all public, private,
     * used, no-longer used etc. They will be sorted once fetched.
     */
    public static function get_all_metakeys($post_type = 'product') {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $meta = $wpdb->get_col($wpdb->prepare(
                        "SELECT DISTINCT pm.meta_key
            FROM {$wpdb->postmeta} AS pm
            LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND p.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )", $post_type
        ));

        sort($meta);

        return $meta;
    }

    public function set_selected_column_names($full_form_data) {   
        if (is_null($this->selected_column_names)) {
			$this->selected_column_names = array();
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

    public function wt_get_product_attributes() {
        if (!empty($this->product_attributes)) {
            return $this->product_attributes;
        }
        $found_pattributes = self::get_all_product_attributes('product');
        $found_vattributes = self::get_all_product_attributes('product_variation');
        $found_attributes = array_merge($found_pattributes, $found_vattributes);
        $found_attributes = array_unique($found_attributes);
        $found_attributes = array_map('rawurldecode', $found_attributes);
        $this->product_attributes = $found_attributes;
        return $this->product_attributes;
    }

    /**
     * Get a list of all the product attributes for a post type.
     * These require a bit more digging into the values.
     */
    public static function get_all_product_attributes($post_type = 'product') {

        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_col($wpdb->prepare(
                        "SELECT DISTINCT pm.meta_value
            FROM {$wpdb->postmeta} AS pm
            LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND p.post_status IN ( 'publish', 'pending', 'private', 'draft' )
            AND pm.meta_key = '_product_attributes'", $post_type
        ));

        // Go through each result, and look at the attribute keys within them.
        $result = array();

        if (!empty($results)) {
            foreach ($results as $_product_attributes) { 
                $attributes = Wt_Import_Export_For_Woo_Basic_Common_Helper::wt_unserialize_safe($_product_attributes);
                if (!empty($attributes) && is_array($attributes)) {
                    foreach ($attributes as $key => $attribute) {
                        if (!$key) {
                            continue;
                        }
                        if (!strstr($key, 'pa_')) {
                            if (empty($attribute['name'])) {
                                continue;
                            }
                            $key = $attribute['name'];
                        }

                        $result[$key] = $key;
                    }
                }
            }
        }

        sort($result);

        return $result;
    }

    public function exporter_alter_mapping_fields($fields, $base, $mapping_form_data) {
        if ($base == $this->module_base) {
            $fields = self::get_product_post_columns();
        }
        return $fields;
    }

    public function exporter_alter_advanced_fields($fields, $base, $advanced_form_data) {
        if ($this->module_base != $base) {
            return $fields;
        }
        $out = array();
		$out['header_empty_row'] = array(
			'tr_html' => '<tr id="header_empty_row"><th></th><td></td></tr>'
		);
        foreach ($fields as $fieldk => $fieldv) {
            $out[$fieldk] = $fieldv;
        }

		

        return $out;
    }
    
    public function importer_alter_advanced_fields($fields, $base, $advanced_form_data) {
        if ($this->module_base != $base) {
            return $fields;
        }
        $out = array(); 
        $out['header_empty_row'] = array(
			'tr_html' => '<tr id="header_empty_row"><th></th><td></td></tr>'
		);
        $out['skip_new'] = array(
            'label' => __("Skip import of new products", 'product-import-export-for-woo'),
            'type' => 'radio',
            'radio_fields' => array(
                '0' => __('No', 'product-import-export-for-woo'),
				'1' => __('Yes', 'product-import-export-for-woo')
            ),
            'value' => '0',
			'merge_right' => true,
            'field_name' => 'skip_new',
            'help_text_conditional'=>array(
                array(
                    'help_text'=> __('This option will not import the new products from the input file.', 'product-import-export-for-woo'),
                    'condition'=>array(
                        array('field'=>'wt_iew_skip_new', 'value'=>1)
                    )
                ),
                array(
                    'help_text'=> __('This option will import the new products from the input file.', 'product-import-export-for-woo'),
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
        
        $out['merge_with'] = array(
            'label' => __("Match products by their", 'product-import-export-for-woo'),
            'type' => 'radio',
            'radio_fields' => array(
                'id' => __('ID', 'product-import-export-for-woo'),
                'sku' => __('SKU', 'product-import-export-for-woo'),             
            ),
            'value' => 'id',
			'merge_right' => true,
            'field_name' => 'merge_with',
            //'help_text' => __('The products are either looked up based on their ID or SKU as per the selection.'),
            'help_text_conditional'=>array(
                array(
                    'help_text'=> __('To look up the product on the basis of ID.', 'product-import-export-for-woo'),
                    'condition'=>array(
                        array('field'=>'wt_iew_merge_with', 'value'=>'id'),
                    )
                ),
                array(
                    'help_text'=> __('To look up the product on the basis of SKU.<br/><br/><b>Note:</b> If the ID of a product in the input file is different from that of the product ID in site, then match products by SKU. If in case, the product has no SKU, it will be imported as a new item even if the file contains the correct ID.', 'product-import-export-for-woo'),
                    'condition'=>array(
                        array('field'=>'wt_iew_merge_with', 'value'=>'sku'),
                    )
                )
            )
        );
        
        $out['found_action_merge'] = array(
            'label' => __("If product exists in the store", 'product-import-export-for-woo'),
            'type' => 'radio',
            'radio_fields' => array(
                'skip' => __('Skip', 'product-import-export-for-woo'),
                'update' => __('Update', 'product-import-export-for-woo'),                
            ),
            'value' => 'skip',
            'field_name' => 'found_action',
            'help_text_conditional'=>array(
                array(
                    'help_text'=> __('This option will not update the existing products.', 'product-import-export-for-woo'),
                    'condition'=>array(
                        array('field'=>'wt_iew_found_action', 'value'=>'skip')
                    )
                ),
                array(
                    'help_text'=> __('This option will update the existing products as per the data from the input file.', 'product-import-export-for-woo'),
                    'condition'=>array(
                        array('field'=>'wt_iew_found_action', 'value'=>'update')
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
            'help_text' => __('Updates the product data respectively even if some of the columns in the input file contains empty value.', 'product-import-export-for-woo'),
            'form_toggler'=>array(
                'type'=>'child',
                'id'=>'wt_iew_found_action',
                'val'=>'update',
            )
        );

        
       
        foreach ($fields as $fieldk => $fieldv) {
            $out[$fieldk] = $fieldv;
        }
        return $out;
    }

    /**
     *  Customize the items in filter export page
     */
    public function exporter_alter_filter_fields($fields, $base, $filter_form_data) {
        if ($this->module_base != $base) {
            return $fields;
        }

        /* altering help text of default fields */
	$fields['limit']['label']=__('Total number of products to export', 'product-import-export-for-woo'); 
	$fields['limit']['help_text']=__('Exports specified number of products. e.g. Entering 500 with a skip count of 10 will export products from 11th to 510th position.', 'product-import-export-for-woo');
	$fields['offset']['label']=__('Skip first <i>n</i> products', 'product-import-export-for-woo');
	$fields['offset']['help_text']=__('Skips specified number of products from the beginning of the database. e.g. Enter 10 to skip first 10 products from export.', 'product-import-export-for-woo');

        
        $fields['product'] = array(
            'label' => __( 'Products', 'product-import-export-for-woo' ),
            'placeholder' => __( 'All products', 'product-import-export-for-woo' ),
            'attr' => array('data-exclude_type' => 'variable,variation'),
            'field_name' => 'product',
            'sele_vals' => array(),
            'help_text' => __( 'Export specific products. Key in the product names to export multiple products.', 'product-import-export-for-woo' ),
            'type' => 'multi_select',
            'css_class' => 'wc-product-search',
            'validation_rule' => array('type'=>'text_arr')
        );
        $fields['stock_status'] = array(
            'label' => __('Stock status', 'product-import-export-for-woo'),
            'placeholder' => __('All status', 'product-import-export-for-woo'),
            'field_name' => 'stock_status',
            'sele_vals' => array( '' => __( 'All status', 'product-import-export-for-woo' ), 'instock' => __( 'In Stock', 'product-import-export-for-woo' ), 'outofstock' => __( 'Out of Stock', 'product-import-export-for-woo' ), 'onbackorder' => __( 'On backorder', 'product-import-export-for-woo' ) ),
            'help_text' => __( 'Export products based on stock status.', 'product-import-export-for-woo' ),
            'type' => 'select',
            'validation_rule' => array('type'=>'text_arr')
        );        
        $fields['exclude_product'] = array(
            'label' => __( 'Exclude products', 'product-import-export-for-woo' ),
            'placeholder' => __( 'Exclude products', 'product-import-export-for-woo' ),
            'attr' => array('data-exclude_type' => 'variable,variation'),
            'field_name' => 'exclude_product',
            'sele_vals' => array(),
            'help_text' => __( 'Use this if you need to exclude a specific or multiple products from your export list.', 'product-import-export-for-woo' ),
            'type' => 'multi_select',
            'css_class' => 'wc-product-search',
            'validation_rule' => array('type'=>'text_arr')
        );

        $fields['product_categories'] = array(
            'label' => __( 'Product categories', 'product-import-export-for-woo' ),
            'placeholder' => __( 'Any category', 'product-import-export-for-woo' ),
            'field_name' => 'product_categories',
            'sele_vals' => $this->get_product_categories(),
            'help_text' => __( 'Export products belonging to a particular or from multiple categories. Just select the respective categories.', 'product-import-export-for-woo' ),
            'type' => 'multi_select',
            'css_class' => 'wc-enhanced-select',
            'validation_rule' => array('type'=>'sanitize_title_with_dashes_arr')
        );

        $fields['product_tags'] = array(
            'label' => __( 'Product tags', 'product-import-export-for-woo' ),
            'placeholder' => __( 'Any tag', 'product-import-export-for-woo' ),
            'field_name' => 'product_tags',
            'sele_vals' => $this->get_product_tags(),
            'help_text' => __( 'Enter the product tags to export only the respective products that have been tagged accordingly.', 'product-import-export-for-woo' ),
            'type' => 'multi_select',
            'css_class' => 'wc-enhanced-select',
            'validation_rule' => array('type'=>'sanitize_title_with_dashes_arr')
        );

        $fields['product_status'] = array(
            'label' => __( 'Product status', 'product-import-export-for-woo' ),
            'placeholder' => __( 'Any status', 'product-import-export-for-woo' ),
            'field_name' => 'product_status',
            'sele_vals' => self::get_product_statuses(),
            'help_text' => __( 'Filter products by their status.', 'product-import-export-for-woo' ),
            'type' => 'multi_select',
            'css_class' => 'wc-enhanced-select',
            'validation_rule' => array('type'=>'text_arr')
        );

        $fields['product_brand'] = array(
            'label' => __( 'Product brand', 'product-import-export-for-woo' ),
            'placeholder' => __( 'Any brand', 'product-import-export-for-woo' ),
            'field_name' => 'product_brand',
            'sele_vals' => $this->get_product_brands(),
            'help_text' => __( 'Enter the product brands to export only the respective products that have been branded accordingly.', 'product-import-export-for-woo' ),
            'type' => 'multi_select',
            'css_class' => 'wc-enhanced-select',
            'validation_rule' => array('type'=>'text_arr')
        );

 

        return $fields;
    }

    /**
     * Get File name by url
     * @param string $file_url URL of the file.
     * @return string the base name of the given URL (File name).
     */
    public static function xa_wc_get_filename_from_url($file_url) {
        $parts = parse_url($file_url);
        if (isset($parts['path'])) {
            return basename($parts['path']);
        }
    }

    /**
     * Get info like language code, parent product ID etc by product id.
     * @param int Product ID.
     * @return array/false.
     */
    public static function wt_get_wpml_original_post_language_info($element_id) {
        $get_language_args = array('element_id' => $element_id, 'element_type' => 'post_product');
        $original_post_language_info = apply_filters('wpml_element_language_details', null, $get_language_args);
        return $original_post_language_info;
    }

    public static function wt_get_product_id_by_sku($sku) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $post_exists_sku = $wpdb->get_var($wpdb->prepare("
	    		SELECT $wpdb->posts.ID
	    		FROM $wpdb->posts
	    		LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
	    		WHERE $wpdb->posts.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
	    		AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = %s
	    		", $sku));
        if ($post_exists_sku) {
            return $post_exists_sku;
        }
        return false;
    }

    /**
     * To strip the specific string from the array key as well as value.
     * @param array $array.
     * @param string $data.
     * @return array.
     */
    public static function wt_array_walk($array, $data) {
        $new_array = array();
        foreach ($array as $key => $value) {
            $new_array[str_replace($data, '', $key)] = str_replace($data, '', $value);
        }
        return $new_array;
    }
    
    public function get_item_by_id($id) {
        $post['edit_url']=get_edit_post_link($id);
        $post['title'] = get_the_title($id);
        return $post; 
    }
	    
	public static function get_item_link_by_id($id) {
        $post['edit_url']=get_edit_post_link($id);
        $post['title'] = get_the_title($id);
        return $post; 
    }	

}
}

new Wt_Import_Export_For_Woo_Basic_Product();

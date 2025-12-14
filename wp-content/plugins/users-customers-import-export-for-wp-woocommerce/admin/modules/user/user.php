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

if(!class_exists('Wt_Import_Export_For_Woo_basic_User')){
    
#[AllowDynamicProperties]
class Wt_Import_Export_For_Woo_basic_User {

    public $module_id = '';
    public static $module_id_static = '';
    public $module_base = 'user';
    public $module_name = 'User Import Export for WordPress/WooCommerce';
    public $min_base_version= '1.0.0'; /* Minimum `Import export plugin` required to run this add on plugin */

    private $all_meta_keys = array();
    private $found_meta = array();
    private $found_hidden_meta = array();
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


        $this->module_id = Wt_Import_Export_For_Woo_Basic::get_module_id($this->module_base);
        
        self::$module_id_static = $this->module_id;
        
        add_filter('wt_iew_exporter_post_types_basic', array($this, 'wt_iew_exporter_post_types'), 10, 1);
        add_filter('wt_iew_importer_post_types_basic', array($this, 'wt_iew_exporter_post_types'), 10, 1);

        add_filter('wt_iew_exporter_alter_filter_fields_basic', array($this, 'exporter_alter_filter_fields'), 10, 3);
        
        add_filter('wt_iew_exporter_alter_mapping_fields_basic', array($this, 'exporter_alter_mapping_fields'), 10, 3);        
        add_filter('wt_iew_importer_alter_mapping_fields_basic', array($this, 'get_importer_post_columns'), 10, 3);  
        
		add_filter('wt_iew_exporter_alter_advanced_fields_basic', array($this, 'exporter_alter_advanced_fields'), 10, 3);		
        add_filter('wt_iew_importer_alter_advanced_fields_basic', array($this, 'importer_alter_advanced_fields'), 10, 3);

        add_filter('wt_iew_exporter_alter_meta_mapping_fields_basic', array($this, 'exporter_alter_meta_mapping_fields'), 10, 3);

        add_filter('wt_iew_exporter_alter_mapping_enabled_fields_basic', array($this, 'exporter_alter_mapping_enabled_fields'), 10, 3);
        add_filter('wt_iew_importer_alter_mapping_enabled_fields_basic', array($this, 'exporter_alter_mapping_enabled_fields'), 10, 3);

        add_filter('wt_iew_exporter_do_export_basic', array($this, 'exporter_do_export'), 10, 7);
        add_filter('wt_iew_importer_do_import_basic', array($this, 'importer_do_import'), 10, 8);

        add_filter('wt_iew_importer_steps_basic', array($this, 'importer_steps'), 10, 2);  
		
		add_action('admin_footer', array($this, 'wt_add_user_bulk_actions'));
        add_action('load-users.php', array($this, 'wt_process_user_bulk_actions'));  
        
    }

	
	
     public function wt_add_user_bulk_actions() {
        
        global $post_type, $post_status;

        $screen = get_current_screen();

        if ( "users" === $screen->id && !is_plugin_active( 'wt-import-export-for-woo/wt-import-export-for-woo.php' ) ){  // Only add to users.php page

        ?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				var $downloadUsers = $('<option>').val('wt_ier_download_users').text('<?php esc_html_e('Export to CSV', 'users-customers-import-export-for-wp-woocommerce') ?>');
				$('select[name^="action"]').append($downloadUsers);
			});
		</script>
        <?php
		}
    }

    /**
     * user page bulk export action
     * 
     */
    public function wt_process_user_bulk_actions() {                
        global $typenow;

        $wp_list_table = _get_list_table('WP_Posts_List_Table');
        $action = $wp_list_table->current_action();
        if (!in_array($action, array('wt_ier_download_users'), true)) {
            return;
        }
        // security check
       
        check_admin_referer('bulk-users');
        
        if (isset($_REQUEST['users'])) {
            $user_ids = array_map('absint', wp_unslash($_REQUEST['users']));
        }
        if (empty($user_ids)) {
            return;
        }

        if ('wt_ier_download_users' === $action) {
            include_once( 'export/class-wt-customerimpexpcsv-basic-exporter.php' );
            Wt_Import_Export_For_Woo_Basic_User_Bulk_Export::do_export($user_ids);
        }
       
    }
    
    /**
    *   Altering advanced step description
    */
    public function importer_steps($steps, $base)
    {
        if($this->module_base === $base)
        {
            $steps['advanced']['description']=__('Use advanced options from below to retain user passwords or not, set batch import count. You can also save the template file for future imports.', 'users-customers-import-export-for-wp-woocommerce');
        }
        return $steps;
    }
    
    public function importer_do_import($import_data, $base, $step, $form_data, $selected_template_data, $method_import, $batch_offset, $is_last_batch) {                
        if ($this->module_base !== $base) {
            return $import_data;
        }
                        
        if(0 == $batch_offset){    
            // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_ini_get
            $memory = size_format(wt_let_to_num_basic(ini_get('memory_limit'))); // @codingStandardsIgnoreLine.
            $wp_memory = size_format(wt_let_to_num_basic(WP_MEMORY_LIMIT));                      
            Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->module_base, 'import', '---[ New import started at '.gmdate('Y-m-d H:i:s').' ] PHP Memory: ' . $memory . ', WP Memory: ' . $wp_memory);
        }
        
        include plugin_dir_path(__FILE__) . 'import/import.php';
        $import = new Wt_Import_Export_For_Woo_basic_User_Import($this);
        
        $response = $import->prepare_data_to_import($import_data,$form_data, $batch_offset, $is_last_batch);
         
        if($is_last_batch){
            Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->module_base, 'import', '---[ Import ended at '.gmdate('Y-m-d H:i:s').']---');
        }
        
        return $response;
    }

    public function exporter_do_export($export_data, $base, $step, $form_data, $selected_template_data, $method_export, $batch_offset) {
        if ($this->module_base !== $base) {
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
        $export = new Wt_Import_Export_For_Woo_basic_User_Export($this);

        $header_row = $export->prepare_header();

        $data_row = $export->prepare_data_to_export($form_data, $batch_offset);

        $export_data = array(
            'head_data' => $header_row,
            'body_data' => $data_row['data'],
        );
        
        if(isset($data_row['total']) && !empty($data_row['total'])){
            $export_data['total'] = $data_row['total'];
        }
        if(isset($data_row['no_post'])){
            $export_data['no_post'] = $data_row['no_post'];
        }

        return $export_data;
    }
    
    /*
     * Setting default export columns for quick export
     */
    
    public function set_export_columns_for_quick_export($form_data) {

        $post_columns = self::get_user_post_columns();

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
     * Adding current post type to export list
     *
     */
    public function wt_iew_exporter_post_types($arr) {
		
		$arr['user'] = __('Users', 'users-customers-import-export-for-wp-woocommerce');
		if (class_exists('woocommerce')) {
			if(is_plugin_active('users-customers-import-export-for-wp-woocommerce/users-customers-import-export-for-wp-woocommerce.php'))
            {
                $arr['user'] = __('User/Customer', 'users-customers-import-export-for-wp-woocommerce'); 
            }
            if(is_plugin_active('product-import-export-for-woo/product-import-export-for-woo.php')){
                $arr['product'] = __('Product', 'users-customers-import-export-for-wp-woocommerce');
                $arr['product_review'] = __('Product Review', 'users-customers-import-export-for-wp-woocommerce');
                $arr['product_categories'] = __('Product Categories', 'users-customers-import-export-for-wp-woocommerce');
                $arr['product_tags'] = __('Product Tags', 'users-customers-import-export-for-wp-woocommerce');
            }
            if(is_plugin_active('order-import-export-for-woocommerce/order-import-export-for-woocommerce.php')){
                $arr['order'] = __('Order', 'users-customers-import-export-for-wp-woocommerce');
                $arr['coupon'] = __('Coupon', 'users-customers-import-export-for-wp-woocommerce');
            }
            
			$arr['order'] = __('Order', 'users-customers-import-export-for-wp-woocommerce');
			$arr['coupon'] = __('Coupon', 'users-customers-import-export-for-wp-woocommerce');
			$arr['product'] = __('Product', 'users-customers-import-export-for-wp-woocommerce');
			$arr['product_review'] = __('Product Review', 'users-customers-import-export-for-wp-woocommerce');
			$arr['product_categories'] = __('Product Categories', 'users-customers-import-export-for-wp-woocommerce');
			$arr['product_tags'] = __('Product Tags', 'users-customers-import-export-for-wp-woocommerce');
			$arr['user'] = __('User/Customer', 'users-customers-import-export-for-wp-woocommerce');
			$arr['subscription'] = __('Subscription', 'users-customers-import-export-for-wp-woocommerce');
		}
		return $arr;
    }
    
    public static function get_user_sort_columns() {
        $sort_columns = array('ID'=>'ID', 'user_registered'=>'user_registered','user_email'=> 'user_email', 'user_login'=>'user_login', 'user_nicename'=>'user_nicename','user_url'=>'user_url');
        return apply_filters('wt_iew_export_user_sort_columns', $sort_columns);
    }
    
    public static function get_user_roles() {
        global $wp_roles;                                
        $roles = array();
        foreach ( $wp_roles->role_names as $role => $name ) {
            $roles[esc_attr( $role )] = esc_html( $name );
        }
        return apply_filters('wt_iew_export_user_roles', $roles);
    }


    public static function get_user_post_columns() {
        return include plugin_dir_path(__FILE__) . 'data/data-user-columns.php';
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
            $mapping_enabled_fields['hidden_meta'] = array(__('Hidden meta', 'users-customers-import-export-for-wp-woocommerce'), 0);

            if ( ! function_exists( 'is_plugin_active' ) ) {
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }

            // Check if premium plugin is active.
            if ( ! is_plugin_active( 'wt-import-export-for-woo-user/wt-import-export-for-woo-user.php' ) ) {
                if ( $this->has_hidden_meta_keys() ) {
                    $mapping_enabled_fields['hidden_meta']['banner_html'] = $this->get_upgrade_banner_html();
                }
            }
        }
        return $mapping_enabled_fields;
    }

    /**
     * Get upgrade banner HTML for premium features
     */
    public function get_upgrade_banner_html() {
        $icon_url = plugins_url('admin/wt-ds/icons/icons/right-arrow-3.svg', __FILE__);

        return '<div id="user-type-notice" style="margin-top: 10px; display: block; width: 840px; height: auto; top: 210px; left: 117px;">
    <div class="notice notice-warning" style="width: 93%; max-width: 810px; margin-left: 0px;margin-top: 20px;display: inline-flex; padding: 16px 18px 16px 26px; justify-content: flex-end; align-items: center; border-radius: 8px; border: 1px solid #F5F9FF; background-color: #F5F9FF; box-sizing: border-box;">
        <div style="display: flex; flex: 1 1 0; flex-direction: column; justify-content: flex-start; align-items: flex-start; width: 100%;">
            <!-- Title -->
            <div style="padding-bottom: 10px; align-self: stretch; color: #2A3646; font-size: 14px; font-family: Inter; font-weight: 600; line-height: 16px; word-wrap: break-word;">
                ' . __('Upgrade to premium 💎', 'users-customers-import-export-for-wp-woocommerce') . '
            </div>

            <!-- Description -->
            <div style="width:780;height: 19;angle: 0 deg;opacity: 1;top: 48px;left: 27px;padding-bottom: 10px;">
                <span style="color: #2A3646; font-size: 13px; font-family: Inter; font-weight: 400; ">
                        ' . __('We\'ve detected hidden WooCommerce metadata & custom user fields in your store. Unlock full access to export them seamlessly.', 'users-customers-import-export-for-wp-woocommerce') . '
                    </span>
                </div>

            <!-- Button -->
                <a href="https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/?utm_source=free_plugin&utm_medium=export_hidden_meta_tab&utm_campaign=User_Import_Export" target="_blank" style="
                    width: auto;
                    height: 18px;
                font-family:  Inter;
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
                    ' . __('Upgrade now', 'users-customers-import-export-for-wp-woocommerce') . ' <span style="font-size: 14px;">→</span>
                </a>
            </div>
        </div>
    </div>
';
    }

    public function exporter_alter_meta_mapping_fields($fields, $base, $step_page_form_data) {
        if ($base !== $this->module_base) {
            return $fields;
        }

        foreach ($fields as $key => $value) {
            switch ($key) {
                default:
                    break;
            }
        }

        return $fields;
    }
    
    
    public function importer_alter_meta_mapping_fields($fields, $base, $step_page_form_data) {
        if ($base !== $this->module_base) {
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


    public function wt_get_found_meta() {

        if (!empty($this->found_meta)) {
            return $this->found_meta;
        }

        // Loop products and load meta data
        $found_meta = array();
        // Some of the values may not be usable (e.g. arrays of arrays) but the worse
        // that can happen is we get an empty column.

        $all_meta_keys = $this->wt_get_all_meta_keys();

        $csv_columns = self::get_user_post_columns();
        
        foreach ($all_meta_keys as $meta) {

            if (!$meta || (substr((string) $meta, 0, 1) == '_') || in_array($meta, array_keys($csv_columns)) || in_array('meta:' . $meta, array_keys($csv_columns)))
                continue;

            $found_meta[] = $meta;
        }

        $found_meta = array_diff($found_meta, array_keys($csv_columns));

        $this->found_meta = $found_meta;
        return $this->found_meta;
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
        $result = $wpdb->get_col($wpdb->prepare("SELECT distinct(meta_key) FROM $wpdb->usermeta WHERE meta_key NOT IN (%s)", $wpdb->prefix . 'capabilities'));
        return apply_filters('wt_alter_user_meta_data', $result);
    }
    
   
    /**
     * Check if any hidden meta keys exist (for existence check only)
     */
    public function has_hidden_meta_keys() {
        if (isset($this->has_hidden_meta_keys)) {
            return $this->has_hidden_meta_keys;
        }

        global $wpdb;
        $csv_columns = self::get_user_post_columns();
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        // @codingStandardsIgnoreStart
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT 1 FROM $wpdb->usermeta 
             WHERE meta_key LIKE %s 
             AND meta_key NOT IN (%s)
             AND meta_key NOT IN (" . implode(',', array_fill(0, count($csv_columns), '%s')) . ")
             LIMIT 1",
            array_merge(array('_%', $wpdb->prefix . 'capabilities'), array_keys($csv_columns))
        ));
        // @codingStandardsIgnoreEnd

        $this->has_hidden_meta_keys = ( null !== $result );
        return $this->has_hidden_meta_keys;
    }
    
    public function wt_get_found_hidden_meta() {

        if (!empty($this->found_hidden_meta)) {
            return $this->found_hidden_meta;
        }

        // Loop products and load meta data
        $found_hidden_meta = array();
        // Some of the values may not be usable (e.g. arrays of arrays) but the worse
        // that can happen is we get an empty column.
                
        $all_meta_keys = $this->wt_get_all_meta_keys();
        $csv_columns = self::get_user_post_columns();
        foreach ($all_meta_keys as $meta) {

            if (!$meta || (substr((string) $meta, 0, 1) != '_') || in_array($meta, array_keys($csv_columns)) || in_array('meta:' . $meta, array_keys($csv_columns)))
                continue;

            $found_hidden_meta[] = $meta;
        }

        $found_hidden_meta = array_diff($found_hidden_meta, array_keys($csv_columns));

        $this->found_hidden_meta = $found_hidden_meta;
        return $this->found_hidden_meta;
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

        $fields = self::get_user_post_columns();
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

	$fields['limit']['label']=__('Total number of users to export', 'users-customers-import-export-for-wp-woocommerce'); 
	$fields['limit']['help_text']=__('Exports specified number of users. e.g. Entering 500 with a skip count of 10 will export users from 11th to 510th position.', 'users-customers-import-export-for-wp-woocommerce');
	$fields['offset']['label']=__('Skip first <i>n</i> users', 'users-customers-import-export-for-wp-woocommerce');
	$fields['offset']['help_text']=__('Skips specified number of users from the beginning of the database. e.g. Enter 10 to skip first 10 users from export.', 'users-customers-import-export-for-wp-woocommerce');

        
        $fields['email'] = array(
            'label' => __('User email', 'users-customers-import-export-for-wp-woocommerce'),
            'placeholder' => __('All users', 'users-customers-import-export-for-wp-woocommerce'),
            'field_name' => 'email',
            'sele_vals' => '',
			'type' => 'multi_select',			
            'help_text' => __('Input the user name or email to specify the users.', 'users-customers-import-export-for-wp-woocommerce'),   
			'css_class' => 'wc-enhanced-select wt-user-search',
            'validation_rule' => array('type'=>'text_arr')
        );        
        if ( class_exists( 'WooCommerce' ) )
        {
            $fields['email']['help_text']=__('Input the customer name or email to specify the customers.', 'users-customers-import-export-for-wp-woocommerce');
            $fields['email']['css_class']='wc-customer-search';
        }
        
        $fields['roles'] = array(
            'label' => __('User role', 'users-customers-import-export-for-wp-woocommerce'),
            'placeholder' => __('Any role', 'users-customers-import-export-for-wp-woocommerce'),
            'field_name' => 'roles',
            'sele_vals' => self::get_user_roles(),
            'help_text' => __('Input specific roles to export information pertaining to all users with the respective role/s.', 'users-customers-import-export-for-wp-woocommerce'),
            'type' => 'multi_select',
            'css_class' => 'wc-enhanced-select',
            'validation_rule' => array('type'=>'text_arr')
        );
        
        $fields['date_from'] = array(
            'label' => __('Registered date from', 'users-customers-import-export-for-wp-woocommerce'),
            'placeholder' => __('Date from', 'users-customers-import-export-for-wp-woocommerce'),
            'field_name' => 'date_from',
            'sele_vals' => '',
            'help_text' => __('Date on which the user registered. Export users registered on and after the specified date.', 'users-customers-import-export-for-wp-woocommerce'),
            'type' => 'text',
            'css_class' => 'wt_iew_datepicker',
        );
        
        $fields['date_to'] = array(
            'label' => __('Registered date to', 'users-customers-import-export-for-wp-woocommerce'),
            'placeholder' => __('Date to', 'users-customers-import-export-for-wp-woocommerce'),
            'field_name' => 'date_to',
            'sele_vals' => '',
            'help_text' => __('Export users registered upto the specified date.', 'users-customers-import-export-for-wp-woocommerce'),
            'type' => 'text',
            'css_class' => 'wt_iew_datepicker',
        );
        
        $fields['sort_columns'] = array(
                'label' => __('Sort columns', 'users-customers-import-export-for-wp-woocommerce'),
                'placeholder' => 'user_login',
                'field_name' => 'sort_columns',
                'sele_vals' => self::get_user_sort_columns(),
                'help_text' => __('Sort the exported data based on the selected columns in order specified. Defaulted to ascending order.', 'users-customers-import-export-for-wp-woocommerce'),
                'type' => 'multi_select',
                'css_class' => 'wc-enhanced-select',
                'validation_rule' => array('type'=>'text_arr')
            );

        $fields['order_by'] = array(
            'label' => __('Sort by', 'users-customers-import-export-for-wp-woocommerce'),
            'placeholder' => __('ASC', 'users-customers-import-export-for-wp-woocommerce'),
            'field_name' => 'order_by',
            'sele_vals' => array('ASC' => __('Ascending', 'users-customers-import-export-for-wp-woocommerce'), 'DESC' => __('Descending', 'users-customers-import-export-for-wp-woocommerce')),
            'help_text' => __('Defaulted to Ascending. Applicable to above selected columns in the order specified.', 'users-customers-import-export-for-wp-woocommerce'),
            'type' => 'select',
        );

        return $fields;
    }
    
    
    public function exporter_alter_advanced_fields($fields, $base, $advanced_form_data) {
        if ($this->module_base != $base) {
            return $fields;
        }
        unset($fields['export_shortcode_tohtml']);
        $out = array();
		$out['header_empty_row'] = array(
			'tr_html' => '<tr id="header_empty_row"><th></th><td></td></tr>'
		);
		if ( class_exists( 'woocommerce' ) ){
        $out['export_guest_user'] = array(
            'label' => __('Export guest customers', 'users-customers-import-export-for-wp-woocommerce'),
            'type' => 'checkbox',
			'merge_right' => true,
			'checkbox_fields' => array( 1 => __( 'Enable', 'users-customers-import-export-for-wp-woocommerce' ) ),
            'value' => 0,
            'field_name' => 'export_guest_user',
            'help_text' => __('Enabling this option will include all guest customers in the export.', 'users-customers-import-export-for-wp-woocommerce'),
			'tip_description' => __( 'Guest users here means the users who checked out from the site without creating an account, provided that the store settings allow guest user checkout. These user details won’t be available in the wp_user table from where our plugin export the user data. Thus we are extracting minimal data like address, email, etc, and whatever data that we can get from the woocommerce order details. Obviously, for these users, the columns like password, username, user created date, etc will be empty.', 'users-customers-import-export-for-wp-woocommerce'),
        );
		}
        
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

        $out['found_action_merge'] = array(
            'label' => __("If User exists", 'users-customers-import-export-for-wp-woocommerce'),
            'type' => 'radio',
            'radio_fields' => array(
                'skip' => __('Skip', 'users-customers-import-export-for-wp-woocommerce'),                                
                'update' => __('Update', 'users-customers-import-export-for-wp-woocommerce'),
//                'import' => __('Import as new item'),                
            ),
            'value' => 'skip',
			'merge_right' => true,
            'field_name' => 'found_action',
            'help_text_conditional'=>array(
                array(
                    'help_text'=> __('Retains the user in the store as is and skips the matching user from the input file.', 'users-customers-import-export-for-wp-woocommerce'),
                    'condition'=>array(
                        array('field'=>'wt_iew_found_action', 'value'=>'skip')
                    )
                ),
                array(
                    'help_text'=> __('Update user as per data from the input file', 'users-customers-import-export-for-wp-woocommerce'),
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
         
        $out['skip_guest_user'] = array(
            'label' => __("Skip Guest User", 'users-customers-import-export-for-wp-woocommerce'),
            'type' => 'checkbox',
			'merge_right' => true,
			'checkbox_fields' => array( 1 => __( 'Enable', 'users-customers-import-export-for-wp-woocommerce' ) ),
            'value' => 1,
            'field_name' => 'skip_guest_user',
            'help_text' => __('Exclude guest users from being imported as registered customers.', 'users-customers-import-export-for-wp-woocommerce'),
        );
        foreach ($fields as $fieldk => $fieldv) {
            $out[$fieldk] = $fieldv;
        }
        return $out;
    }
    
    public function get_item_by_id($id) {
		$post = array();
        $post['edit_url'] = get_edit_user_link($id);
        $user_info = get_userdata($id);
        if($user_info){
			$post['title'] = $user_info->user_login;   
		}
        return $post; 
    }
    public static function get_item_link_by_id($id) {
		$post = array();
        $post['edit_url'] = get_edit_user_link($id);
        $user_info = get_userdata($id);
        if($user_info){
			$post['title'] = $user_info->user_login;   
		}
        return $post; 
    }	
    
}
}

new Wt_Import_Export_For_Woo_basic_User();

<?php

if (!defined('WPINC')) {
    exit;
}

use Automattic\WooCommerce\Admin\ReportsSync;

if(!class_exists('Wt_Import_Export_For_Woo_basic_User_Import')){
class Wt_Import_Export_For_Woo_basic_User_Import {

    public $parent_module = null;
    public $parsed_data = array();
    public $user_all_fields = array();
    public $user_base_fields = array();
    public $user_meta_fields = array();
    public $current_user = array();
    public $skip_guest_user = false;
         
    
    var $merge;
    
    public $merge_with = 'id';
    public $found_action = 'skip';
    public $id_conflict = 'skip';
    
    public $is_user_exist = false;
    
    // Results
    var $import_results = array();
    
    
    var $row;
    var $post_defaults;		// Default post data
    var $postmeta_defaults;		// default post meta
    var $postmeta_allowed;		// post meta validation

    public function __construct($parent_object) {

        $this->current_user = get_current_user_id();
        $this->parent_module = $parent_object;
        $this->user_all_fields = include plugin_dir_path( __FILE__).'../data/data-user-columns.php';
        $this->user_base_fields  = array_slice($this->user_all_fields, 0, 13);
        $this->user_meta_fields = array_slice($this->user_all_fields, 13);  
        
        
    }
    
    public function hf_log_data_change($content = 'review-csv-import', $data = '') {
        Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', $data);
    }
    
    public function prepare_data_to_import($import_data,$form_data, $batch_offset, $is_last_batch){

        $this->merge_with = !empty($form_data['advanced_form_data']['wt_iew_merge_with']) ? $form_data['advanced_form_data']['wt_iew_merge_with'] : 'email'; 
        $this->found_action = !empty($form_data['advanced_form_data']['wt_iew_found_action']) ? $form_data['advanced_form_data']['wt_iew_found_action'] : 'skip'; 
        $this->skip_guest_user = !empty($form_data['advanced_form_data']['wt_iew_skip_guest_user']) ? $form_data['advanced_form_data']['wt_iew_skip_guest_user'] : false;
        wp_defer_term_counting(true);
        wp_defer_comment_counting(true);
        wp_suspend_cache_invalidation(true);
        
        Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Preparing for import.");
        
        $success = 0;
        $failed = 0;
        foreach ($import_data as $key => $data) {
           $row = $batch_offset+$key+1;
           
           Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Parsing item.");                      
           $parsed_data = $this->parse_users($data); 
           
           if (!is_wp_error($parsed_data)){
               Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Processing item.");
               
                $result = $this->process_users($parsed_data,$data);
                
                if(!is_wp_error($result)){     
                    $msg = 'User imported successfully.';                                                       
                    if($this->is_user_exist){
                        $msg = 'User updated successfully.';
                    }
                    $this->import_results[$row] = array('row'=>$row, 'message'=>$msg, 'status'=>true, 'status_msg' => __( 'Success', 'users-customers-import-export-for-wp-woocommerce' ), 'post_id'=>$result['id'], 'post_link' => Wt_Import_Export_For_Woo_basic_User::get_item_link_by_id($result['id'])); 
                    Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - ".$msg);
                    $success++;                     
                }else{
                   $this->import_results[$row] = array('row'=>$row, 'message'=>$result->get_error_message(), 'status'=>false, 'status_msg' => __( 'Failed/Skipped', 'users-customers-import-export-for-wp-woocommerce' ), 'post_id'=>'', 'post_link' => array( 'title' => __( 'Untitled', 'users-customers-import-export-for-wp-woocommerce' ), 'edit_url' => false ) );
                   Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Processing failed. Reason: ".$result->get_error_message());
                   $failed++;
                }  
           }else{
               $this->import_results[$row] = array('row'=>$row, 'message'=>$parsed_data->get_error_message(), 'status'=>false, 'status_msg' => __( 'Failed/Skipped', 'users-customers-import-export-for-wp-woocommerce' ), 'post_id'=>'', 'post_link' => array( 'title' => __( 'Untitled', 'users-customers-import-export-for-wp-woocommerce' ), 'edit_url' => false ) );
               Wt_Import_Export_For_Woo_Basic_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Parsing failed. Reason: ".$parsed_data->get_error_message());
                $failed++; 
           }           
            unset($data, $parsed_data);            
        }
        wp_suspend_cache_invalidation(false);
        wp_defer_term_counting(false);
        wp_defer_comment_counting(false);
        
        $import_response=array(
                'total_success'=>$success,
                'total_failed'=>$failed,
                'log_data'=>$this->import_results,
            );
        
		if($is_last_batch){
			/**
			* Regenerate data for reports section.
			*
			* @param int|bool $days Number of days to import.
			* @param bool     $skip_existing Skip existing records.
			* @return string
			*/
			if ( class_exists( 'WooCommerce' ) && class_exists( "Automattic\WooCommerce\Admin\ReportsSync" ) ) {
				$days = false; // Initiate now.
				$skip_existing = 1;
				ReportsSync::regenerate_report_data( $days, $skip_existing );
			}

		}
		
        return $import_response;  
                             
    }
    
    /**
     * Parse users
     * @param  array  $item
     * @return array
     */     
    public function parse_users( $data ) {
       
        try{
            $data = apply_filters('wt_user_importer_pre_parse_data', $data); 
            if( $this->skip_guest_user &&  !empty($data['mapping_fields']['is_guest_user'])){
                $email = '';
                if(isset($data['mapping_fields']['user_email']) &&!empty($data['mapping_fields']['user_email'])){
                    $email =  'Email : ' . $data['mapping_fields']['user_email'];
                }elseif(isset($data['mapping_fields']['billing_email']) &&!empty($data['mapping_fields']['billing_email'])){
                    $email =  'Email : ' . $data['mapping_fields']['billing_email'];
                }
                throw new Exception(sprintf('Guest user skipped %s', $email));
            }
            $item = $data['mapping_fields'];
            foreach ($data['meta_mapping_fields'] as $value) {
                $item = array_merge($item,$value);            
            }
            global $wpdb;
            
            $this->is_user_exist = false;
            $this->merge = false; 
            
            $id = $user_id = isset($item['ID']) && !empty($item['ID']) ? absint($item['ID']) : 0;         
            $id_found_with_id = '';
            if($id && 'id' == $this->merge_with){                                 
                $id_found_with_id = get_userdata($id);
                if ($id_found_with_id) {
                    $this->is_user_exist = true;
                    $user_id = $id;
                }          
            } 

            $email = isset($item['user_email']) && '' != $item['user_email'] ? trim($item['user_email']) : '';
            $id_found_with_email = '';
            if(!empty($email) && 'email' == $this->merge_with){   

                if (is_email($email) && false !== email_exists($email)) {
                    $id_found_with_email = email_exists($email);
                    $this->is_user_exist = true;
                    $user_id = $id_found_with_email;
                }
                                 
            }
            
            $username = isset($item['user_login']) && '' != $item['user_login'] ? trim($item['user_login']) : '';
            $id_found_with_username = '';
            if(!empty($username) && 'username' == $this->merge_with){  
                
                if (false !== username_exists($username)) {
                    $id_found_with_username = username_exists($username);
                    $this->is_user_exist = true;
                    $user_id = $id_found_with_username;
                    

                }
                                  
            }
            
            if($this->is_user_exist){                
                if('skip' == $this->found_action){
                    if($id && $id_found_with_id ){
                        throw new Exception(sprintf('User with same ID already exists. ID: %d',$id ));
                    }elseif($email && $id_found_with_email ){
                        throw new Exception(sprintf('User with same Email already exists. Email: %s',$email ));
                    }elseif($username && $id_found_with_username ){
                        throw new Exception(sprintf('User with same Username already exists. Username: %s',$username ));
                    }else{
                        throw new Exception('User already exists.');
                    }                 
                }elseif('update' == $this->found_action){
                    $this->merge = true;                     
                }
            }else{
                $this->merge = false;
            }

//            if(!$this->is_user_exist && $this->skip_new){
//                throw new Exception('Skipping new item' );
//            } 
             
            if (!$this->is_user_exist) {
                    $create_user_without_email = apply_filters('wt_create_user_without_email', FALSE);  // create user without email address
                    if (empty($email) && $create_user_without_email === FALSE) {
                        $this->hf_log_data_change('user-csv-import', __('> skipped: cannot insert user without email.', 'users-customers-import-export-for-wp-woocommerce'));
                        unset($item);
                        return new WP_Error('parse-error', __('> skipped: cannot insert user without email.', 'users-customers-import-export-for-wp-woocommerce'));
                    } elseif (!is_email($email) && $create_user_without_email === FALSE) {
                        // translators: %s is the email address
                        $this->hf_log_data_change('user-csv-import', sprintf(__('> skipped: Email is not valid. %s', 'users-customers-import-export-for-wp-woocommerce'), $item['user_email']));
                        unset($item);
                        return new WP_Error('parse-error', __('>  skipped: Email is not valid.', 'users-customers-import-export-for-wp-woocommerce'));
                    }
            }

            $user_meta = $user_details = array();


            
            $user_details['ID'] = $user_id;
                                    
            foreach ($this->user_base_fields as $key => $value) {
                if(in_array($key, array('ID'))){  // ID alreay set
                    continue;
                }                
                if(isset($item[$value]))
                    $user_details[$key] = trim($item[$value]);
                //$user_details[$key] = isset( $item[$value] ) ? $item[$value] : "" ;
            }

            foreach ($this->user_meta_fields as $key => $value){
                if($key == 'wc_last_active' || $key == 'last_update'){
                    $date = isset( $item[$key] ) ? trim($item[$key]) : "" ;
                    $item[$key] = strtotime($date);
                }
                $user_meta[] = array( 'key' => $key, 'value' => isset( $item[$key] ) ? trim($item[$key]) : "" );
            }
         
            // the $user_details array will now contain the necessary name-value pairs for the wp_users table, and also any meta data in the 'usermeta' array
            $parsed_details = array();

            $parsed_details['user_details'] = $user_details;
            $parsed_details['user_meta'] = $user_meta;
            return $parsed_details;
        } catch (Exception $e) {
            return new WP_Error('woocommerce_product_importer_error', $e->getMessage(), array('status' => $e->getCode()));
        }
    }
        
        
    /**
     * Create new posts based on import information
     */
    private function process_users($post, $parsed_item) {
        try{            
            global $wpdb;

            $this->hf_log_data_change('user-csv-import', __('Processing users.', 'users-customers-import-export-for-wp-woocommerce'));

            $user_id = !empty($post['user_details']['ID']) && $post['user_details']['ID'] ? $post['user_details']['ID'] : 0;  
            
            //  user exists when importing &  merge not ticked
            $new_added = false;

            if ($user_id && $this->merge) {
                $current_user = $this->current_user;
                if ($current_user == $user_id) {
                    // translators: %s is the user ID.
                    $this->hf_log_data_change('user-csv-import', sprintf(__('> &#8220;%s&#8221; This user is currently logged in hence we cannot update.', 'users-customers-import-export-for-wp-woocommerce'), $user_id), true);
                    unset($post);
                    // translators: %s is the user ID.
                    return new WP_Error( 'parse-error',sprintf(__('> &#8220;%s&#8221; This user is currently logged in hence we cannot update.', 'users-customers-import-export-for-wp-woocommerce'), $user_id));
                }
                $user = get_userdata($user_id);
                $roles = $user->roles;
                $only_update_admin_with_admin = apply_filters('wt_ier_update_admin_only_by_admin_user', true);
                if(in_array('administrator', $roles) && $only_update_admin_with_admin ){
                    $current_user = get_userdata($current_user);
                    $current_roles = $current_user->roles;
                    if(!in_array('administrator', $current_roles)){
                        // translators: %s is the user ID.
                        return new WP_Error( 'parse-error',sprintf(__('> &#8220;%s&#8221; Only a user with an Administrator role has the capability to update a user with an Administrator role.', 'users-customers-import-export-for-wp-woocommerce'), $user_id)); 
                    }
                }

                $user_id = $this->hf_update_customer($user_id, $post);
            } else {

                $user_id = $this->hf_create_customer($post);
                $new_added = true;
                if (is_wp_error($user_id)) {
                    // translators: %s is the error message.
                    $this->hf_log_data_change('user-csv-import', sprintf(__('> Error inserting : %s', 'users-customers-import-export-for-wp-woocommerce'), $user_id->get_error_message()), true);
                    //$skipped++;
                    unset($post);
                    // translators: %s is the error message.
                    return new WP_Error( 'parse-error',sprintf(__('> Error inserting: %s', 'users-customers-import-export-for-wp-woocommerce'), $user_id->get_error_message()));
                } elseif (empty($user_id)) {
                    
                    //$skipped++;
                    unset($post);
                    return new WP_Error( 'parse-error',__('An error occurred with the customer information provided.', 'users-customers-import-export-for-wp-woocommerce'));
                }
            }

            if ($this->merge && !$new_added) {
                // translators: %s is the user ID.
                $out_msg = sprintf(__('User updated successfully. ID:%s', 'users-customers-import-export-for-wp-woocommerce'), $user_id);
            } else {
                // translators: %s is the user ID.
                $out_msg = sprintf(__('User imported successfully. ID:%s', 'users-customers-import-export-for-wp-woocommerce'), $user_id);
            }
          
            $this->hf_log_data_change('user-csv-import', sprintf('> &#8220;%s&#8221;', $user_id) . $out_msg, true);

            // translators: %s is the user ID.
            $this->hf_log_data_change('user-csv-import', sprintf(__('> Finished importing user %s', 'users-customers-import-export-for-wp-woocommerce'), $user_id ));
            
            do_action('wt_customer_csv_import_data', $parsed_item, $user_id);
            unset($post);
            return $result =  array(
                'id' => $user_id,
                'updated' => $this->merge,
            );
        } catch (Exception $e) {
            return new WP_Error('woocommerce_product_importer_error', $e->getMessage(), array('status' => $e->getCode()));
        }
    }

    public function hf_create_customer($data) { 
        $customer_email = (!empty($data['user_details']['user_email']) ) ? $data['user_details']['user_email'] : '';
        $username = (!empty($data['user_details']['user_login']) ) ? $data['user_details']['user_login'] : '';
        $customer_id = (!empty($data['user_details']['customer_id']) ) ? $data['user_details']['customer_id'] : '';
        $insertion_id = (!empty($data['user_details']['ID']) ) ? $data['user_details']['ID'] : '';
        $merge_empty_cells = apply_filters('wt_user_import_empty_csv_column', false); 
        
		if('email' === $this->merge_with){
			$insertion_id = '';
		}
               
        if (!empty($data['user_details']['user_pass'])) {
            // Store the raw password (from CSV) into a variable for processing
            $raw_pass = $data['user_details']['user_pass'];
            // Flag to indicate whether the password was system-generated
            $password_generated = false;
        
            // Initialize a flag to identify already hashed passwords
            $is_hashed_password = false;

            // Check if it's a native WordPress hash format ($P$... typically 34 characters)
            if (strlen($raw_pass) === 34 && strpos($raw_pass, '$P$') === 0) {
                $is_hashed_password = true;
            }
        
            // Check if it's a bcrypt hash (exported via plugin, possibly with $wp$ prefix)
            // Accepts patterns like: $2y$, $2a$, $2b$, with or without a $wp prefix
            if (preg_match('/^(?:\$wp)?\$2[aby]\$.{56}$/', $raw_pass)) {
                $is_hashed_password = true;
            }
        
            $password = $is_hashed_password ? $raw_pass : wp_hash_password($raw_pass);
        } else {
            $password = wp_generate_password(12, true);
            $password_generated = true;            
        }
        $found_customer = false;
        $create_user_without_email = apply_filters('wt_create_user_without_email', FALSE);   // create user without email address
        if (is_email($customer_email) || $create_user_without_email === TRUE) {
            $maybe_username = $username;
            // Not in test mode, create a user account for this email
            if (empty($username)) {
                $maybe_username = explode('@', $customer_email);
                $maybe_username = sanitize_user($maybe_username[0]);
            }
            $counter = 1;
            $username = $maybe_username;
            while (username_exists($username)) {
                $username = $maybe_username . $counter;
                $counter++;
            }                        
              
            if (!$this->is_user_exist && $insertion_id) {
                global $wpdb;
                $insertion_id = (int) $insertion_id;
                $username = sanitize_user($username);
                $customer_email = sanitize_email($customer_email);
                if($password_generated){
                    $password = wp_hash_password($password);
                }
                // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $result = $wpdb->insert($wpdb->users, array('ID' => $insertion_id, 'user_login' => $username, 'user_email' => $customer_email, 'user_pass' => $password));
                // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                if ($result) {
                    $found_customer = $insertion_id;
                }
                
            }
            if(!$found_customer) { 
                $found_customer = wp_create_user($username, $password, $customer_email);
            }
            $user_data = array('ID' => $found_customer, 'user_login' => $username, 'user_email' => $customer_email);
            if (!$password_generated) {
                $user_data['user_pass'] = $password;
            }
                        
            if (!is_wp_error($found_customer)) {
                wp_insert_user($user_data);
                $wp_user_object = new WP_User($found_customer);
                if ( ! function_exists( 'get_editable_roles' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/user.php';
                }
                $roles = get_editable_roles();
                $new_roles_str = isset($data['user_details']['roles']) ? $data['user_details']['roles'] : '';

                if(empty($new_roles_str)){
                    $new_roles_str = get_option('default_role');
                }

                $new_roles = array_map('trim', explode(',', $new_roles_str));
                $new_roles = array_intersect($new_roles, array_keys($roles));
                $roles_to_remove = array();
                $user_roles = array_intersect(array_values($wp_user_object->roles), array_keys($roles));
                if (!$new_roles) {
                    // If there are no roles, delete all of the user's roles
                    $roles_to_remove = $user_roles;
                } else {
                    $roles_to_remove = array_diff($user_roles, $new_roles);
                }
                array_push($roles_to_remove, 'subscriber');
                if (!empty($new_roles)) {
                    foreach ($roles_to_remove as $_role) {
                        $wp_user_object->remove_role($_role);   //remove the default role before adding new roles
                    }
                    // Make sure that we don't call $wp_user_object->add_role() any more than it's necessary
                    $_new_roles = array_diff($new_roles, array_intersect(array_values($wp_user_object->roles), array_keys($roles)));
                    foreach ($_new_roles as $_role) {
                        $wp_user_object->add_role($_role);
                    }
                }
                $meta_array = array();
                foreach ($data['user_meta'] as $meta) {
                    $meta_array[$meta['key']] = $meta['value'];
                }

                $user_nicename = (!empty($data['user_details']['user_nicename'])) ? $data['user_details']['user_nicename'] : '';
                $website = (!empty($data['user_details']['user_url'])) ? $data['user_details']['user_url'] : '';
                $user_registered = (!empty($data['user_details']['user_registered'])) ? $data['user_details']['user_registered'] : '';
                $display_name = (!empty($data['user_details']['display_name'])) ? $data['user_details']['display_name'] : '';
                $first_name = (!empty($data['user_details']['first_name'])) ? $data['user_details']['first_name'] : '';
                $last_name = (!empty($data['user_details']['last_name'])) ? $data['user_details']['last_name'] : '';
                $user_status = (!empty($data['user_details']['user_status'])) ? $data['user_details']['user_status'] : '';
                wp_update_user(array(
                    'ID' => $found_customer,
                    'user_nicename' => $user_nicename,
                    'user_url' => $website,
                    'user_registered' => $user_registered,
                    'display_name' => $display_name,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'user_status' => $user_status,
                    )
                );

                //unset($this->user_meta_fields['role']);
                // update user meta data
                global $wpdb;
                foreach ($this->user_meta_fields as $key => $meta) {
                    if (($key == $wpdb->prefix.'capabilities') || (in_array($key, $this->user_base_fields))) {                        continue;
                    }
                    $key = trim(str_replace('meta:', '', $key));
//                    $meta = trim(str_replace('meta:', '', $meta));
                    $meta_value = (!empty($meta_array[$key]) ) ? $meta_array[$key] : '';
                    if ($key == $wpdb->prefix.'user_level' && $meta_value == ''){
                        $meta_value = 0;
                    }
                    if ('session_tokens' == $key) {
                        if (!empty($meta_array[$key]) ) {
                            $session_json = base64_decode($meta_array[$key]);
                            $meta_value = json_decode($session_json, true);
                        }
                    }
                    if (empty($meta_value) && !$merge_empty_cells) {
                        continue;
                    }
                    update_user_meta($found_customer, $key, $meta_value);
                }

            }
        } else {
            $found_customer = new WP_Error('hf_invalid_customer', sprintf(__('User could not be created without Email.', 'users-customers-import-export-for-wp-woocommerce'), $customer_id));
        }
        return apply_filters('xa_user_impexp_alter_user_meta', $found_customer, $this->user_meta_fields, $meta_array);
    }

    public function hf_update_customer($found_customer, $data) { 
        $meta_array = array();
        if ($found_customer) {
            $wp_user_object = new WP_User($found_customer);
            if ( ! function_exists( 'get_editable_roles' ) ) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
            }                                    
            $roles = get_editable_roles();
			$new_roles_str = isset($data['user_details']['roles']) ? $data['user_details']['roles'] : '';
            $new_roles = array_map('trim', explode(',', $new_roles_str));
            $new_roles = array_intersect($new_roles, array_keys($roles));
            $roles_to_remove = array();
            $user_roles = array_intersect(array_values($wp_user_object->roles), array_keys($roles));
            $merge_empty_cells = apply_filters('wt_user_import_empty_csv_column', false); 
            if (!$new_roles) {
                // If there are no roles, delete all of the user's roles
                $roles_to_remove = $user_roles;
            } else {
                $roles_to_remove = array_diff($user_roles, $new_roles);
            }
            if (!empty($new_roles)) {
                foreach ($roles_to_remove as $_role) {
                    $wp_user_object->remove_role($_role);   //remove the default role before adding new roles
                }
                // Make sure that we don't call $wp_user_object->add_role() any more than it's necessary
                $_new_roles = array_diff($new_roles, array_intersect(array_values($wp_user_object->roles), array_keys($roles)));
                foreach ($_new_roles as $_role) {
                    $wp_user_object->add_role($_role);
                }
            }

            foreach ($data['user_meta'] as $meta) {
                $meta_array[$meta['key']] = $meta['value'];
            }
            // update user meta data
            global $wpdb;
            foreach ($this->user_meta_fields as $key => $meta) {
                if (($key == $wpdb->prefix.'capabilities') || (in_array($key, $this->user_base_fields))) {                    continue;
                }
                $key = trim(str_replace('meta:', '', $key));
//                    $meta = trim(str_replace('meta:', '', $meta));
                $meta_value = (!empty($meta_array[$key]) ) ? $meta_array[$key] : '';
                if ($key == $wpdb->prefix.'user_level' && $meta_value == ''){
                    $meta_value = 0;
                }
                if (empty($meta_value) && !$merge_empty_cells) {
                    continue;
                }
                update_user_meta($found_customer, $key, $meta_value);
            }

            $user_data = array(
                'ID' => $found_customer
            );
            if (isset($data['user_details']['user_nicename'])) {
                $user_data['user_nicename'] = $data['user_details']['user_nicename'];
            }

			$email_updated = false;
            //added when implement merge with user id use for update email
            if (isset($data['user_details']['user_email']) && !empty($data['user_details']['user_email'])) {
                if ($wp_user_object->user_email != $data['user_details']['user_email']) {
                    $email_updated = true;
                    $customer_email = $data['user_details']['user_email'];
                }                
                $user_data['user_email'] = $data['user_details']['user_email'];
            }
            if (isset($data['user_details']['user_url'])) {
                $user_data['user_url'] = $data['user_details']['user_url'];
            }
            if (isset($data['user_details']['user_registered'])) {
                $user_data['user_registered'] = $data['user_details']['user_registered'];
            }
            if (isset($data['user_details']['user_pass'])) {
                $user_data['user_pass'] = str_replace("'", "\'",$data['user_details']['user_pass']);                
            }
            if (isset($data['user_details']['display_name'])) {
                $user_data['display_name'] = $data['user_details']['display_name'];
            }
            if (isset($data['user_details']['first_name'])) {
                $user_data['first_name'] = $data['user_details']['first_name'];
            }
            if (isset($data['user_details']['last_name'])) {
                $user_data['last_name'] = $data['user_details']['last_name'];
            }
            if (isset($data['user_details']['user_status'])) {
                $user_data['user_status'] = $data['user_details']['user_status'];
            }
            add_filter('send_password_change_email', '__return_false'); // for preventing sending password change notification mail on by wp_update_user.
            if (sizeof($user_data) > 1) {                               
                wp_update_user($user_data);
            }
            $is_wordpress_password = false;

            // 2.6.4 - Hashed password with $wp$ prefix will also be considered as WordPress password.
            if ( strlen( $data['user_details']['user_pass'] ) === 34 && 0 === strpos( $data['user_details']['user_pass'], '$P$B' ) || 0 === strpos( $data['user_details']['user_pass'], '$wp$' ) ) {
                $is_wordpress_password = true;
            }

            if ($is_wordpress_password && isset($data['user_details']['user_pass']) && !empty($data['user_details']['user_pass'])) {
                $password = $data['user_details']['user_pass'];
                $user_data = array_merge($user_data, array('user_login' => $wp_user_object->user_login, 'user_email' => ( $email_updated ) ? $customer_email : $wp_user_object->user_email, 'user_url' =>$wp_user_object->user_url));                
                $user_data['user_pass'] = $password; 
                $user_default_meta = array('nickname','first_name','last_name','display_name','description','rich_editing','syntax_highlighting','comment_shortcuts','admin_color','use_ssl','show_admin_bar_front','locale');
                foreach ($user_default_meta as $meta_key => $meta) {
                    $user_data[$meta] = $wp_user_object->$meta;
                }
                wp_insert_user($user_data);
            }

        } else {
            $found_customer = new WP_Error('hf_invalid_customer', sprintf(__('User could not be found with given Email or username.', 'users-customers-import-export-for-wp-woocommerce'), $customer_email));
        }        
        return apply_filters('xa_user_impexp_alter_user_meta', $found_customer, $this->user_meta_fields, $meta_array);
    }
}
}

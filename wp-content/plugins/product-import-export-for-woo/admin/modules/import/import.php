<?php
/**
 * Import section of the plugin
 *
 * @link 
 *
 * @package  Wt_Import_Export_For_Woo
 */
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('Wt_Import_Export_For_Woo_Basic_Import')){
class Wt_Import_Export_For_Woo_Basic_Import
{	
	public $module_id='';
	public static $module_id_static='';
	public $module_base='import';
	
	public static $import_dir=WP_CONTENT_DIR.'/webtoffee_import';
	public static $import_dir_name='/webtoffee_import';
	public $steps=array();
	public $allowed_import_file_type=array();
	public $max_import_file_size=10;  //in MB

	private $to_import_id='';
	private $to_import='';
	private $rerun_id=0;
	public $import_method='';
	public $import_methods=array();
	public $selected_template=0;
	public $default_batch_count=0; /* configure this value in `advanced_setting_fields` method */
	public $selected_template_data=array();
	public $default_import_method=''; /* configure this value in `advanced_setting_fields` method */
	public $form_data=array();
	public $temp_import_file='';
	private $to_process='';
        public $allowed_import_file_type_mime=array();
        public $step_need_validation_filter=array();     
		public $allowed_mime_types = array();

		private $skip_from_evaluation_array = array();
    	private $decimal_columns = array();

	public function __construct()
	{
		$this->module_id=Wt_Import_Export_For_Woo_Basic::get_module_id($this->module_base);
		self::$module_id_static=$this->module_id;

		$this->max_import_file_size=(int)wp_max_upload_size()/1000000; //in MB
		
		/* allowed file types */
		$this->allowed_import_file_type=array(
			'csv'=>__('CSV', 'product-import-export-for-woo'),
		);
		$this->allowed_import_file_type_mime=array(
			'csv'=>'text/csv',
		);
		$this->allowed_mime_types = array(
			'text/csv',
			'application/vnd.ms-excel'
		);

		/* default step list */
		$this->steps=array(
			'post_type'=>array(
				'title'=>__('Select a post type', 'product-import-export-for-woo'),
				'description'=>__('Use a CSV file to import data. As a first step, select the post type.', 'product-import-export-for-woo'),
			),
			'method_import'=>array(
				'title'=>__('Select import method', 'product-import-export-for-woo'),
				'description'=>__('Choose from the options below to continue with your import: quick import, based on a pre-saved template or a new import with advanced options.', 'product-import-export-for-woo'),
			), 
			'mapping'=>array(
				'title'=>__('Map import columns', 'product-import-export-for-woo'),
				'description'=>__('Map the standard columns with your CSV column names.', 'product-import-export-for-woo'),
			),
			'advanced'=>array(
				'title'=>__('Advanced options/Batch import', 'product-import-export-for-woo'),
				'description'=>__('Use options from below to decide on the delimiter options, updates to existing products, batch import count or schedule an import. You can also save the template file for future imports.', 'product-import-export-for-woo'),
			),
		);

		$this->import_methods=array(
			'quick'=>array('title'=>__('Quick import', 'product-import-export-for-woo'), 'description'=> __('Use this option primarily if you exported the input file using the same plugin.', 'product-import-export-for-woo')),
			'template'=>array('title'=>__('Pre-saved template', 'product-import-export-for-woo'), 'description'=> __('Retains the filter parameters and column specifications as per the chosen template and imports data accordingly.', 'product-import-export-for-woo')),
			'new'=>array('title'=>__('Advanced Import', 'product-import-export-for-woo'), 'description'=> __('Imports data after a detailed process of filtration, column selection and advanced options. The configured settings can be saved as a template for future imports.', 'product-import-export-for-woo')),
		);
		
		$this->step_need_validation_filter=array('method_import', 'mapping', 'advanced');

		/* advanced plugin settings */
		add_filter('wt_iew_advanced_setting_fields_basic', array($this, 'advanced_setting_fields'));
		
		/* setting default values this method must be below of advanced setting filter */
		$this->get_defaults();

		/* main ajax hook. The callback function will decide which is to execute. */
		add_action('wp_ajax_iew_import_ajax_basic', array($this, 'ajax_main'), 11);

		/* Admin menu for import */
		add_filter('wt_iew_admin_menu_basic', array($this, 'add_admin_pages'), 10, 1);
	
	}

	public function get_defaults()
	{	
		$this->default_import_method= Wt_Import_Export_For_Woo_Basic_Common_Helper::get_advanced_settings('default_import_method');
		$this->default_batch_count=Wt_Import_Export_For_Woo_Basic_Common_Helper::get_advanced_settings('default_import_batch');
	}

	/**
	*	Fields for advanced settings
	*
	*/
	public function advanced_setting_fields($fields)
	{
		
                $fields['maximum_execution_time'] = array(
                        'label' => __("Maximum execution time", 'product-import-export-for-woo'),
                        'type' => 'number',
                        'value' => ini_get('max_execution_time'), /* Default max_execution_time settings value */
                        'field_name' => 'maximum_execution_time',
                        'field_group' => 'advanced_field',
                        'help_text' => __('The maximum execution time, in seconds(eg:- 300, 600, 1800, 3600). If set to zero, no time limit is imposed. Increasing this will reduce the chance of export/import timeouts.', 'product-import-export-for-woo'),
                        'validation_rule' => array('type' => 'int'),
                );            
                $fields['enable_import_log']=array(
						'label'=>__("Generate Import log", 'product-import-export-for-woo'),
						'type' => 'checkbox',
						'checkbox_fields' => array( 1 => __( 'Enable', 'product-import-export-for-woo' ) ),
						'value' =>1,
						'field_name'=>'enable_import_log',
						'field_group'=>'advanced_field',			
						// translators: %1$s is opening link tag, %2$s is closing link tag
						'help_text'=>sprintf(__('You can view the logs in the %1$sImport Logs%2$s section or in History > View logs.', 'product-import-export-for-woo'), '<a href="' . admin_url('admin.php?page=wt_import_export_for_woo_basic_history_log') . '"><b>', '</b></a>'),
						'validation_rule'=>array('type'=>'absint'),
				);
		$import_methods=array_map(function($vl){ return $vl['title']; }, $this->import_methods);
		$fields['default_import_method']=array(
			'label'=>__("Default Import method", 'product-import-export-for-woo'),
			'type'=>'select',
			'sele_vals'=>$import_methods,
            'value' =>'new',
			'field_name'=>'default_import_method',
			'field_group'=>'advanced_field',			
			'help_text'=>__('Select the default method of import.', 'product-import-export-for-woo'),
		);
		$fields['default_import_batch']=array(
			'label'=>__("Default Import batch count", 'product-import-export-for-woo'),
			'type'=>'number',
            'value' =>10, /* If altering then please also change batch count field help text section */
			'field_name'=>'default_import_batch',
			'help_text'=>__('Provide the default count for the records to be imported in a batch.', 'product-import-export-for-woo'),
			'validation_rule'=>array('type'=>'absint'),
			'attr' => array('min' => 1, 'max' => 50),
		);
                

                
		return $fields;
	}

	/**
	*	Fields for Import advanced step
	*/
	public function get_advanced_screen_fields($advanced_form_data)
	{
		$advanced_screen_fields=array(
			
			'batch_count'=>array(
				'label'=>__("Import in batches of", 'product-import-export-for-woo'),
				'type'=>'text',
				'value'=>$this->default_batch_count,
				'field_name'=>'batch_count',
				// translators: %d is the default number of records (10)
				'help_text'=>sprintf(__('The number of records that the server will process for every iteration within the configured timeout interval. If the import fails you can lower this number accordingly and try again. Defaulted to %d records.', 'product-import-export-for-woo'), 10),
				'validation_rule'=>array('type'=>'absint'),
			)
		);

		/* taking advanced fields from post type modules */
		$advanced_screen_fields=apply_filters('wt_iew_importer_alter_advanced_fields_basic', $advanced_screen_fields, $this->to_import, $advanced_form_data);
		return $advanced_screen_fields;
	}

	/**
	*	Fields for Import method step
	*/
	public function get_method_import_screen_fields($method_import_form_data)
	{
		$file_from_arr=array(
			'local'=>__('Local', 'product-import-export-for-woo'),
		);

		/* taking available remote adapters */
		$remote_adapter_names=array();
		$remote_adapter_names=apply_filters('wt_iew_importer_remote_adapter_names_basic', $remote_adapter_names);
		if($remote_adapter_names && is_array($remote_adapter_names))
		{
			foreach($remote_adapter_names as $remote_adapter_key => $remote_adapter_vl)
			{
				$file_from_arr[$remote_adapter_key]=$remote_adapter_vl;
			}
		}

		//prepare file from field type based on remote type adapters
		$file_from_field_arr=array(
			'label'=>__("Choose file for Import", 'product-import-export-for-woo').' [<a href"#" target="_blank" id="sample-csv-file">'. __('Sample CSV', 'product-import-export-for-woo').'</a>]',
			'type'=>'select',
			'tr_class'=>'wt-iew-import-method-options wt-iew-import-method-options-quick wt-iew-import-method-options-new wt-iew-import-method-options-template',
			'sele_vals'=>$file_from_arr,
			'field_name'=>'file_from',
			'default_value'=>'local',
			'form_toggler'=>array(
				'type'=>'parent',
				'target'=>'wt_iew_file_from'
			)
		);


		$method_import_screen_fields=array(
			'file_from'=>$file_from_field_arr,
			'local_file'=>array(
				'label'=>__("Select a file", 'product-import-export-for-woo'),
				'type'=>'dropzone',
				'merge_left'=>true,
				'merge_right'=>true,
				'tr_id'=>'local_file_tr',
				'tr_class'=>$file_from_field_arr['tr_class'], //add tr class from parent.Because we need to toggle the tr when parent tr toggles.
				'field_name'=>'local_file',
				'html_id'=>'local_file',
				'form_toggler'=>array(
					'type'=>'child',
					'id'=>'wt_iew_file_from',
					'val'=>'local',
				),
			),

		);

		/* taking import_method fields from other modules */
		$method_import_screen_fields=apply_filters('wt_iew_importer_alter_method_import_fields_basic', $method_import_screen_fields, $this->to_import, $method_import_form_data);


		$method_import_screen_fields['delimiter']=array(
			'label'=>__("Delimiter", 'product-import-export-for-woo'),
			'type'=>'select',
			'value'=>",",
			'css_class'=>"wt_iew_delimiter_preset",
			'tr_id'=>'delimiter_tr',
			'tr_class'=>$file_from_field_arr['tr_class'], //add tr class from parent.Because we need to toggle the tr when parent tr toggles.
			'field_name'=>'delimiter_preset',
			'sele_vals'=>Wt_Iew_IE_Basic_Helper::_get_csv_delimiters(),
			'help_text'=>__('The character used to separate columns in the CSV file. Takes comma (,) by default.', 'product-import-export-for-woo'),
			'validation_rule'=>array('type'=>'skip'),
			'after_form_field'=>'<input type="text" class="wt_iew_custom_delimiter" name="wt_iew_delimiter" value="," />',//Always pass safe data.
		);



		return $method_import_screen_fields;
	}

	/**
	* Adding admin menus
	*/
	public function add_admin_pages($menus)
	{
		$first = array_slice($menus, 0, 3, true);
    	$last=array_slice($menus, 3, (count($menus)-1), true);
     	
		$menu=array(
			$this->module_base=>array(
				'submenu',
				WT_IEW_PLUGIN_ID_BASIC,
				__('Import', 'product-import-export-for-woo'),
				__('Import', 'product-import-export-for-woo'),
				apply_filters('wt_import_export_allowed_capability', 'import'),
				$this->module_id,
				array($this, 'admin_settings_page')
			)
		);

		$menus=array_merge($first, $menu, $last);
		return $menus;
	}

	/**
	* 	Import page
	*/
	public function admin_settings_page()
	{
		/**
		*	Check it is a rerun call
		*/
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This is for rerun functionality, nonce verification is handled in the main ajax handler
		$requested_rerun_id=(isset($_GET['wt_iew_rerun']) ? absint($_GET['wt_iew_rerun']) : 0);
		$this->_process_rerun($requested_rerun_id);
		
		if($this->rerun_id>0) /* this is a rerun request. Then validate the file */
		{
			$response=$this->download_remote_file($this->form_data);
			if($response['response']) /* temp file created. */
			{
				$this->temp_import_file=$response['file_name'];

				/* delete temp files other than the current temp file of same rerun id, if exists */
				$file_path=$this->get_file_path();
   				$temp_files = glob($file_path.'/rerun_'.$this->rerun_id.'_*');
   				if(count($temp_files)>1) /* Other than the current temp file */
   				{
   					foreach($temp_files as $key => $temp_file)
   					{
   						if(basename($temp_file)!=$this->temp_import_file)
   						{
   							@unlink($temp_file); //delete it
   						}
   					}
   				} 
   				
			}else /* unable to create temp file, then abort the rerun request */
			{
				$this->rerun_id=0;
				$this->form_data=array();
			}
		}
		$this->enqueue_assets();		
		include plugin_dir_path(__FILE__).'views/main.php';
	}


	/**
	*	Validating and Processing rerun action
	*/
	protected function _process_rerun($rerun_id)
	{
		if($rerun_id>0)
		{
			/* check the history module is available */
			$history_module_obj=Wt_Import_Export_For_Woo_Basic::load_modules('history');
			if(!is_null($history_module_obj))
			{
				/* check the history entry is for import and also has form_data */
				$history_data=$history_module_obj->get_history_entry_by_id($rerun_id);
				if($history_data && $history_data['template_type']==$this->module_base)
				{
					$form_data_raw = wp_unslash($history_data['data']);
					$form_data = is_array($form_data_raw) ? 
							array_map(function($item) {
								return is_string($item) ? json_decode($item, true) : $item;
							}, $form_data_raw) : 
							json_decode($form_data_raw, true);
					if($form_data && is_array($form_data))
					{
						$this->to_import=(isset($form_data['post_type_form_data']) && isset($form_data['post_type_form_data']['item_type']) ? $form_data['post_type_form_data']['item_type'] : '');
						if($this->to_import!="")
						{
							$this->import_method=(isset($form_data['method_import_form_data']) && isset($form_data['method_import_form_data']['method_import']) && $form_data['method_import_form_data']['method_import']!="" ?  $form_data['method_import_form_data']['method_import'] : $this->default_import_method);
							$this->rerun_id=$rerun_id;
							$this->form_data=$form_data;
							//process steps based on the import method in the history entry
							$this->get_steps();

							return true;
						}
					}
				}
			}
		}
		return false;
	}

	protected function enqueue_assets()
	{
            if(Wt_Import_Export_For_Woo_Basic_Common_Helper::wt_is_screen_allowed()){
		/* adding dropzone JS */
		wp_enqueue_script(WT_IEW_PLUGIN_ID_BASIC.'-dropzone', WT_P_IEW_PLUGIN_URL.'admin/js/dropzone.min.js', array('jquery'), WT_P_IEW_VERSION, true);

		wp_enqueue_script($this->module_id, plugin_dir_url(__FILE__).'assets/js/main.js', array('jquery', 'jquery-ui-sortable', 'jquery-ui-datepicker'), WT_P_IEW_VERSION, true);
		wp_enqueue_style('jquery-ui-datepicker');
		//wp_enqueue_media();

		wp_enqueue_style(WT_IEW_PLUGIN_ID_BASIC.'-jquery-ui', WT_P_IEW_PLUGIN_URL.'admin/css/jquery-ui.css', array(), WT_P_IEW_VERSION, 'all');
		
                /* check the history module is available */
                $history_module_obj=Wt_Import_Export_For_Woo_Basic::load_modules('history');
                if(!is_null($history_module_obj))
                {
                    wp_enqueue_script(Wt_Import_Export_For_Woo_Basic::get_module_id('history'),WT_P_IEW_PLUGIN_URL.'admin/modules/history/assets/js/main.js', array('jquery'), WT_P_IEW_VERSION, false);
                }
                
		$file_extensions=array_keys($this->allowed_import_file_type_mime);
		$file_extensions=array_map(function($vl){
			return '.'.$vl;
		}, $file_extensions);

		if ( version_compare( WC()->version, '6.7.0', '<' ) )
		{
			$review_url = admin_url('edit-comments.php');
		} else {
			$review_url= admin_url('edit.php?post_type=product&page=product-reviews');
		}

		$params=array(
			'item_type'=>'',
			'steps'=>$this->steps,
			'rerun_id'=>$this->rerun_id,
			'to_import'=>$this->to_import,
			'import_method'=>$this->import_method,
			'temp_import_file'=>$this->temp_import_file,
			'allowed_import_file_type_mime'=>$file_extensions,
			'max_import_file_size'=>$this->max_import_file_size,
			'msgs'=>array(
				'choosed_template'=>__('Choosed template:', 'product-import-export-for-woo'),
				'choose_import_method'=>__('Please select an import method.', 'product-import-export-for-woo'),
				'choose_template'=>__('Please select an import template.', 'product-import-export-for-woo'),
				'step'=>__('Step', 'product-import-export-for-woo'),
				'choose_ftp_profile'=>__('Please select an FTP profile.', 'product-import-export-for-woo'),
				'choose_import_from'=>__('Please choose import from.', 'product-import-export-for-woo'),
				'choose_a_file'=>__('Please choose an import file.', 'product-import-export-for-woo'),
				'select_an_import_template'=>__('Please select an import template.', 'product-import-export-for-woo'),
				'validating_file'=>__('Creating temp file and validating.', 'product-import-export-for-woo'),
				'processing_file'=>__('Processing input file...', 'product-import-export-for-woo'), 
				'column_not_in_the_list'=>__('This column is not present in the import list. Please tick the checkbox to include.', 'product-import-export-for-woo'),
				'uploading'=>__('Uploading...', 'product-import-export-for-woo'),
				'outdated'=>__('You are using an outdated browser. Please upgarde your browser.', 'product-import-export-for-woo'),
				'server_error'=>__('An error occured.', 'product-import-export-for-woo'),
				// translators: %s is a list of allowed file types
				'invalid_file'=>sprintf(__('Invalid file type. Only %s are allowed', 'product-import-export-for-woo'), implode(", ", array_values($this->allowed_import_file_type))),
				'drop_upload'=>__('Drop files here or click to upload', 'product-import-export-for-woo'),
				// translators: %s is a checkmark icon
				'upload_done'=>sprintf(__('%s Done.', 'product-import-export-for-woo'), '<span class="dashicons dashicons-yes-alt" style="color:#3fa847;"></span>'),
				'remove'=>__('Remove', 'product-import-export-for-woo'),
				'no_columns_mapped'=>__('No columns have been mapped. Please map the required fields to proceed with the import.', 'product-import-export-for-woo'),
			),
			'addons' => array(
				'product' => array(
					'text' => __( 'View Products', 'product-import-export-for-woo' ),
					'page_link' => admin_url('edit.php?post_type=product')
				),
				'product_categories' => array(
					'text' => __( 'View Product categories', 'product-import-export-for-woo' ),
					'page_link' => admin_url('edit-tags.php?taxonomy=product_cat&post_type=product')
				),
				'product_tags' => array(
					'text' => __( 'View Product tags', 'product-import-export-for-woo' ),
					'page_link' => admin_url('edit-tags.php?taxonomy=product_tag&post_type=product')
				),
				'product_review' => array(
					'text' => __( 'View Product reviews', 'product-import-export-for-woo' ),
					'page_link' => $review_url
					)
				),
				'order' => array(
					'text' => __( 'View Orders', 'product-import-export-for-woo' ),
					'page_link' => admin_url('edit.php?post_type=shop_order')
				),
				'coupon' => array(					
					'text' => __( 'View Coupons', 'product-import-export-for-woo' ),
					'page_link' => admin_url('edit.php?post_type=shop_coupon')
				),
				'user' => array(
					'text' => __( 'View Users', 'product-import-export-for-woo' ),
					'page_link' => admin_url('users.php')
				),
				'subscription' => array(
					'text' => __( 'View Subscriptions', 'product-import-export-for-woo' ),
					'page_link' => admin_url('edit.php?post_type=shop_subscription')
				)				
			);
		wp_localize_script($this->module_id, 'wt_iew_import_basic_params', $params);

		$this->add_select2_lib(); //adding select2 JS, It checks the availibility of woocommerce
            }
	}

	/**
	* 
	* Enqueue select2 library, if woocommerce available use that
	*/
	protected function add_select2_lib()
	{
		/* enqueue scripts */
		if(!function_exists('is_plugin_active'))
		{
			include_once(ABSPATH.'wp-admin/includes/plugin.php');
		}
		if ( class_exists( 'WooCommerce' ) )
		{ 
			wp_enqueue_script('wc-enhanced-select');
			wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url().'/assets/css/admin.css', array(), WC()->version);
		}else
		{
			wp_enqueue_style(WT_IEW_PLUGIN_ID_BASIC.'-select2', WT_P_IEW_PLUGIN_URL. 'admin/css/select2.css', array(), WT_P_IEW_VERSION, 'all' );
			wp_enqueue_script(WT_IEW_PLUGIN_ID_BASIC.'-select2', WT_P_IEW_PLUGIN_URL.'admin/js/select2.js', array('jquery'), WT_P_IEW_VERSION, false );
		}
	}

	/**
	* Get steps
	*
	*/
	public function get_steps()
	{
		if($this->import_method=='quick') /* if quick import then remove some steps */
		{
			$out=array(
				'post_type'=>$this->steps['post_type'],
				'method_import'=>$this->steps['method_import'],
				'advanced'=>$this->steps['advanced'],
			);
			$this->steps=$out;
		}
		$this->steps=apply_filters('wt_iew_importer_steps_basic', $this->steps, $this->to_import);
		return $this->steps;
	}

	/**
	* Download and save file into web server
	*
	*/
	public function download_remote_file($form_data)
	{
		$out = array(
			'response' => false,
			'file_name' => '',
			'msg' => '',
		);

		$method_import_form_data = isset($form_data['method_import_form_data']) ? $form_data['method_import_form_data'] : array();
		$file_from = isset($method_import_form_data['wt_iew_file_from']) ? Wt_Iew_Sh::sanitize_item($method_import_form_data['wt_iew_file_from']) : '';
		
		if($file_from == "") {
			return $out;
		}
		if($file_from == 'local' || $file_from == 'url')
		{
			if($file_from == 'local') {
				$file_url = isset($method_import_form_data['wt_iew_local_file']) ? Wt_Iew_Sh::sanitize_item($method_import_form_data['wt_iew_local_file'], 'url') : '';
				$local_file_path = realpath(Wt_Iew_IE_Basic_Helper::_get_local_file_path($file_url));
				$import_dir = realpath(WP_CONTENT_DIR.'/webtoffee_import');
				
				if(!$local_file_path || !$import_dir || strpos($local_file_path, $import_dir) !== 0) { /* no local file found or file is outside allowed directory */ /* no local file found */
					$file_url = '';
				}
			} else {
				$file_url = isset($method_import_form_data['wt_iew_url_file']) ? Wt_Iew_Sh::sanitize_item($method_import_form_data['wt_iew_url_file'], 'url') : '';	
			}

			if($file_url != "") { /* file URL not empty */
			
				if($this->is_extension_allowed($file_url)) { /* file type is in allowed list */ 
				
					$ext_arr = explode('.', $file_url);
					$ext = end($ext_arr);

					$file_name = $this->get_temp_file_name($ext);
					$file_dir = realpath($this->get_file_path());
					$import_dir = realpath(WP_CONTENT_DIR.'/webtoffee_import');
					$file_path = $this->get_file_path($file_name);
					
					if($file_dir && $import_dir && strpos($file_dir, $import_dir) === 0) {
						if($file_from == 'local') {
							if(@copy($local_file_path, $file_path)) {
								$out = array(
									'response' => true,
									'file_name' => $file_name,
									'msg' => '',
								);
							} else {
								$out['msg'] = __('Unable to create temp file.', 'product-import-export-for-woo');
							}
						} else {
							$file_data=$this->remote_get($file_url);
							
							if(!is_wp_error($file_data) && wp_remote_retrieve_response_code($file_data) == 200) {
								$file_data = wp_remote_retrieve_body($file_data);
								if(@file_put_contents($file_path, $file_data)) {
									$out = array(
										'response' => true,
										'file_name' => $file_name,
										'msg' => '',
									);
								} else {
									$out['msg'] = __('Unable to create temp file.', 'product-import-export-for-woo');
								}
							} else {
								$out['msg'] = __('Unable to fetch file data.', 'product-import-export-for-woo');
							}
						}						
					} else {
						$out['msg'] = __('Unable to create temp directory.', 'product-import-export-for-woo');
					}				
				} else {
					$out['msg'] = __('File type not allowed.', 'product-import-export-for-woo');
				}
			} else {
				$out['msg'] = __('File not found.', 'product-import-export-for-woo');
			}		
		} else {
			$out['response'] = true;
			$out = apply_filters('wt_iew_validate_file_basic', $out, $file_from, $method_import_form_data);
			
			if(is_array($out) && isset($out['response']) && $out['response']) { /* a form validation hook for remote modules */
			
				$remote_adapter = Wt_Import_Export_For_Woo_Basic::get_remote_adapters('import', $file_from);
				
				if(is_null($remote_adapter)) { /* adapter object not found */
					// translators: %s is the file source type (e.g., FTP, URL)
					$out['msg'] = sprintf(__('Unable to initailize %s', 'product-import-export-for-woo'), $file_from);
					$out['response'] = false;
				} else {
					/* download the file */
					$out = $remote_adapter->download($method_import_form_data, $out, $this);
				}
			}
		}
		if($out['response']!==false) {
			$file_path = realpath(self::get_file_path($out['file_name']));
			$import_dir = realpath(WP_CONTENT_DIR.'/webtoffee_import');
			
			if($file_path && $import_dir && strpos($file_path, $import_dir) === 0) {
				/**
				*	Filter to modify the import file before processing.
				*	@param 	string $file_name name of the file
				*	@param 	string $file_path path of the file
				*	@return  string $file_name name of the new altered file 
				*/
				$filtered_file_name = apply_filters('wt_iew_alter_import_file_basic', $out['file_name'], $file_path);
				
				// Validate the filtered file name/path
				$filtered_file_path = realpath(self::get_file_path($filtered_file_name));
				if($filtered_file_path && strpos($filtered_file_path, $import_dir) === 0) {
					$out['file_name'] = $filtered_file_name;
				} else {
					$out['msg'] = __('Invalid file path after filtering', 'product-import-export-for-woo');
					$out['response'] = false;
					return $out;
				}
			} else {
				$out['msg'] = __('Invalid file path', 'product-import-export-for-woo');
				$out['response'] = false;
				return $out;
			}
		}
                
		return $out;
	}

	public function remote_get($target_url)
	{
		global $wp_version;

		$def_args = array(
		    'timeout'     => 5,
		    'redirection' => 5,
		    'httpversion' => '1.0',
		    'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
		    'blocking'    => true,
		    'headers'     => array(),
		    'cookies'     => array(),
		    'body'        => null,
		    'compress'    => false,
		    'decompress'  => true,
		    'sslverify'   => false,
		    'stream'      => false,
		    'filename'    => null
		);
		return wp_remote_get($target_url, $def_args);
	}

	public function get_log_file_name($history_id)
	{
		return 'log_'.$history_id.'.log';
	}

	public function get_temp_file_name($ext)
	{
		/* adding rerun prefix is to easily identify rerun temp files */
		$rerun_prefix=($this->rerun_id>0 ? 'rerun_'.$this->rerun_id.'_' : '');
		return $rerun_prefix.'temp_'.$this->to_import.'_'.time().'.'.$ext;
	}

	/**
	* 	Get given file url.
	*	If file name is empty then URL will return
	*/
	public static function get_file_url($file_name="")
	{
		return WP_CONTENT_URL.self::$import_dir_name.'/'.$file_name;
	}

	/**
	*	Checks the file extension is in allowed list
	*	@param string File name/ URL
	*	@return boolean 
	*/
	public function is_extension_allowed($file_url)
	{
		$ext_arr=explode('.', $file_url);
		$ext=strtolower(end($ext_arr));
		if(isset($this->allowed_import_file_type[$ext])) /* file type is in allowed list */ 
		{
			return true;
		}
		return false;
	}

	/**
	*	Delete import file
	*	@param string File path/ URL
	*	@return boolean
	*/
	public function delete_import_file($file_url)
	{
		$file_path_arr=explode("/", $file_url);
		$file_name=end($file_path_arr);
		$file_path=$this->get_file_path($file_name);
		if(file_exists($file_path))
		{	
			if($this->is_extension_allowed($file_url))/* file type is in allowed list */ 
			{
				@unlink($file_path);
				return true;
			}
		}
		return false;
	}

	/**
	* 	Get given temp file path.
	*	If file name is empty then file path will return
	*/
	public static function get_file_path($file_name="")
	{
		global $wp_filesystem;
		
		// Initialize WP_Filesystem
		if (!function_exists('WP_Filesystem')) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}
		WP_Filesystem();
		
		// Create directory using WP_Filesystem
		if (!$wp_filesystem->is_dir(self::$import_dir)) {
			if (!$wp_filesystem->mkdir(self::$import_dir, FS_CHMOD_DIR)) {
				return false;
			}
		}
		
		// Ensure proper permissions and add index.php for security
		$wp_filesystem->chmod(self::$import_dir, FS_CHMOD_DIR);
		
		// Add security files to prevent directory listing and direct access
		$security_files = array(
			'index.php' => '<?php' . PHP_EOL . '// Silence is golden',
			'.htaccess' => 'deny from all'
		);
		
		foreach ($security_files as $file => $content) {
			if (!$wp_filesystem->exists(self::$import_dir . '/' . $file)) {
				$wp_filesystem->put_contents(
					self::$import_dir . '/' . $file,
					$content
				);
			}
		}
		return self::$import_dir.'/'.$file_name;
	}

	/**
	* Download and create a temp file. And create a history entry
	* @param   string   $step       the action to perform, here 'download'
	*
	* @return array 
	*/
	public function process_download($form_data, $step, $to_process, $import_id=0, $offset=0)
	{
		$out=array(
			'response'=>false,
			'new_offset'=>0,
			'import_id'=>0,
			'history_id'=>0, //same as that of import id
			'total_records'=>0,
			'finished'=>0,
			'msg'=>'',
		);
		$this->to_import=$to_process;

		if($import_id>0)
		{
			//take history data by import_id
			$import_data=Wt_Import_Export_For_Woo_Basic_History::get_history_entry_by_id($import_id);
			if(is_null($import_data)) //no record found so it may be an error
			{
				return $out;
			}else
			{
				$file_name=(isset($import_data['file_name']) ? $import_data['file_name'] : '');	
				$file_path=$this->get_file_path($file_name);
				if($file_path && file_exists($file_path))
				{
					$this->temp_import_file=$file_name;
				}else
				{
					$msg = __('Error occurred while processing the file', 'product-import-export-for-woo');
					Wt_Import_Export_For_Woo_Basic_History::record_failure($import_id, $msg);
		            $out['msg'] = $msg;
					return $out;
				}
			}
		}else
		{
			if($offset==0)
			{
				if($this->temp_import_file!="") /* its a non schedule import */
				{
					$file_path=$this->get_file_path($this->temp_import_file);
					if($file_path && file_exists($file_path))
					{
						if($this->is_extension_allowed($this->temp_import_file)) /* file type is in allowed list */ 
						{
							$import_id=Wt_Import_Export_For_Woo_Basic_History::create_history_entry('', $form_data, $to_process, 'import');
						}else
						{
							return $out;
						}
					}else
					{
						$out['msg']=__('Temp file missing.', 'product-import-export-for-woo');
						return $out;
					}
				}else /* in scheduled import need to prepare the temp file */
				{	
					$import_id=Wt_Import_Export_For_Woo_Basic_History::create_history_entry('', $form_data, $to_process, 'import');
					$response=$this->download_remote_file($form_data);

					if(!$response['response']) /* not validated successfully */
					{					
						Wt_Import_Export_For_Woo_Basic_History::record_failure($import_id, $response['msg']);
			            $out['msg']=$response['msg'];
						return $out;
					}else
					{
						$file_path=$this->get_file_path($response['file_name']);
						$this->temp_import_file=$response['file_name'];
					}
				}				
			}
		}

		/**
		* In XML import we need to convert the file into CSV before processing 
		* It may be a batched processing for larger files
		*/
		$ext_arr=explode('.', $this->temp_import_file);

			$out=$this->_set_import_file_processing_finished($file_path, $import_id);
		return $out;
	}

	/**
	*	If the file type is not CSV (Eg: XML) Then the delimiter must be ",". 
	*	Because we are converting XML to CSV
	*
	*/
	protected function _set_csv_delimiter($form_data, $import_id)
	{
		$form_data['method_import_form_data']['wt_iew_delimiter']=",";
		
		$update_data=array(
			'data'=>json_decode($form_data), //formadata
		);
		$update_data_type=array(
			'%s',
		);
		Wt_Import_Export_For_Woo_Basic_History::update_history_entry($import_id, $update_data, $update_data_type);

		return $form_data;
	}

	protected function _set_import_file_processing_finished($file_path, $import_id)
	{
		/* update total records, temp file name in history table */
		$total_records=filesize($file_path); /* in this case we cannot count number of rows */
		$update_data=array(
			'total'=>$total_records,
			'file_name'=>$this->temp_import_file,
		);
		$update_data_type=array(
			'%d',
			'%s',
		);
		Wt_Import_Export_For_Woo_Basic_History::update_history_entry($import_id, $update_data, $update_data_type);

		return array(
			'response'=>true,
			'finished'=>3,
			'import_id'=>$import_id,
			'history_id'=>$import_id, //same as that of import id
			'total_records'=>$total_records,
			'temp_import_file'=>$this->temp_import_file,
			// translators: %d is the number of processed records
			'msg'=>sprintf(__('Importing...(%d processed)', 'product-import-export-for-woo'), 0),
		);
	}


	/**
	* 	Do the import process
	*/
	public function process_action($form_data, $step, $to_process, $file_name='', $import_id=0, $offset=0)
	{
		$out=array(
			'response'=>false,
			'new_offset'=>0,
			'import_id'=>0,
			'history_id'=>0, //same as that of import id
			'total_records'=>0,
			'offset_count'=>0,
			'finished'=>0,
			'msg'=>'',
			'total_success'=>0,
			'total_failed'=>0,
		);

		$this->to_import=$to_process;
		$this->to_process=$to_process;

		/* prepare formdata, If this was not first batch */
		if($import_id>0)
		{
			//take history data by import_id
			$import_data=Wt_Import_Export_For_Woo_Basic_History::get_history_entry_by_id($import_id);
			if(is_null($import_data)) //no record found so it may be an error
			{
				return $out;
			}

			//processing form data
			$form_data_raw = wp_unslash($import_data['data']);
			$form_data = is_array($form_data_raw) ? 
					array_map(function($item) {
						return is_string($item) ? json_decode($item, true) : $item;
					}, $form_data_raw) : 
					json_decode($form_data_raw, true);		

		}
		else // No import id so it may be an error
		{
			return $out;
		}

		/* setting history_id in Log section */
		Wt_Import_Export_For_Woo_Basic_Log::$history_id=$import_id;

		$file_name=(isset($import_data['file_name']) ? $import_data['file_name'] : '');	
		$file_path=$this->get_file_path($file_name);
		if($file_path)
		{
			if(!file_exists($file_path))
			{
				$msg='Temp file missing';					
				//no need to add translation function in message
				Wt_Import_Export_For_Woo_Basic_History::record_failure($import_id, $msg);
	            $out['msg']=__('Temp file missing', 'product-import-export-for-woo');
	            return $out;
        	}
		}else
		{
			$msg='Temp file missing';					
			//no need to add translation function in message
			Wt_Import_Export_For_Woo_Basic_History::record_failure($import_id, $msg);
            $out['msg']=__('Temp file missing', 'product-import-export-for-woo');
            return $out;
		} 

		$default_batch_count=absint(apply_filters('wt_iew_importer_alter_default_batch_count_basic', $this->default_batch_count, $to_process, $form_data));
		$default_batch_count=($default_batch_count==0 ? $this->default_batch_count : $default_batch_count);

		$batch_count=$default_batch_count;
		$csv_delimiter=',';	
		$total_records=(isset($import_data['total']) ? $import_data['total'] : 0);
		$file_ext_arr=explode('.', $file_name);
		$file_type= strtolower(end($file_ext_arr));
		$file_type=(isset($this->allowed_import_file_type[$file_type]) ? $file_type : 'csv');

		if(isset($form_data['advanced_form_data']))
		{
			$batch_count=(isset($form_data['advanced_form_data']['wt_iew_batch_count']) ? $form_data['advanced_form_data']['wt_iew_batch_count'] : $batch_count);
		}
		if(isset($form_data['method_import_form_data']) && $file_type=='csv')
		{
			$csv_delimiter=(isset($form_data['method_import_form_data']['wt_iew_delimiter']) ? $form_data['method_import_form_data']['wt_iew_delimiter'] : $csv_delimiter);
			$csv_delimiter=($csv_delimiter=="" ? ',' : $csv_delimiter);
			$csv_delimiter = ( "t" === $csv_delimiter ? "\t" : $csv_delimiter );

		}		
		
		

			include_once WT_P_IEW_PLUGIN_PATH.'admin/classes/class-csvreader.php';
			$reader=new Wt_Import_Export_For_Woo_Basic_Csvreader($csv_delimiter);


		/* important: prepare deafult mapping formdata for quick import */
		$input_data=$reader->get_data_as_batch($file_path, $offset, $batch_count, $this, $form_data);
		if(empty($input_data['data_arr'])){			
			$out['msg']=__('CSV is empty', 'product-import-export-for-woo');
            return $out;
		}
				
		if(!$input_data || !is_array($input_data))
		{
			$msg = __('Unable to process the file', 'product-import-export-for-woo');					
			//no need to add translation function in message
			Wt_Import_Export_For_Woo_Basic_History::record_failure($import_id, $msg);
            $out['msg'] = $msg;
            return $out;
		}

		/* checking action is finshed */
		$is_last_offset=false;
		$new_offset=$input_data['offset']; //increase the offset
		$out['total_percent']= ceil(($new_offset/$total_records)*100);
		if($new_offset>=$total_records) //finished
		{
			$is_last_offset=true;
		}

		/**
		* 	In case of non schedule import. Offset row count. 
		*	The real internal offset is in bytes, This offset is total row processed.
		*/
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This is an internal AJAX method with nonce verification handled in the main ajax handler
		$offset_count=(isset($_POST['offset_count']) ? absint($_POST['offset_count']) : 0);

		/* giving full data */
		$form_data=apply_filters('wt_iew_import_full_form_data_basic', $form_data, $to_process, $step, $this->selected_template_data);
		
		/* in scheduled import. The import method will not available so we need to take it from formdata */
		$formdata_import_method=(isset($formdata['method_import_form_data']) && isset($formdata['method_import_form_data']['method_import']) ?  $formdata['method_import_form_data']['method_import'] : 'quick');
		$this->import_method=($this->import_method=="" ? $formdata_import_method : $this->import_method);

		/**
		*	Import response format
		*/
		$import_response=array(
			'total_success'=>$batch_count,
			'total_failed'=>0,
			'log_data'=>array(
				array('row'=>$offset_count, 'message'=>'', 'status'=>true, 'post_id'=>''),
			),
		);
					
		$import_response=apply_filters('wt_iew_importer_do_import_basic', $input_data['data_arr'], $to_process, $step, $form_data, $this->selected_template_data, $this->import_method, $offset_count, $is_last_offset); 		 		
		$out['log_data'] = $import_response['log_data'];
		/**
		*	Writing import log to file
		*/
		if(!empty($import_response) && is_array($import_response) && Wt_Import_Export_For_Woo_Basic_Common_Helper::get_advanced_settings('enable_import_log')==1)
		{
			$log_writer=new Wt_Import_Export_For_Woo_Basic_Logwriter();
			$log_file_name=$this->get_log_file_name($import_id);
			$log_file_path=$this->get_file_path($log_file_name);
			$log_data=(isset($import_response['log_data']) && is_array($import_response['log_data']) ? $import_response['log_data'] : array());
			$log_writer->write_import_log($log_data, $log_file_path);
		}


		/* updating completed offset */
		$update_data=array(
			'offset'=>$offset
		);
		$update_data_type=array(
			'%d'
		);
		Wt_Import_Export_For_Woo_Basic_History::update_history_entry($import_id, $update_data, $update_data_type);


		/* updating output parameters */
		$out['total_records']=$total_records;
		$out['import_id']=$import_id;
		$out['history_id']=$import_id;
		$out['response']=true;
		
		/* In case of non schedule import. total success, totla failed count */
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This is an internal AJAX method with nonce verification handled in the main ajax handler
		$total_success=(isset($_POST['total_success']) ? absint($_POST['total_success']) : 0);
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This is an internal AJAX method with nonce verification handled in the main ajax handler
		$total_failed=(isset($_POST['total_failed']) ? absint($_POST['total_failed']) : 0);

		$out['total_success']=(isset($import_response['total_success']) ? $import_response['total_success'] : 0)+$total_success;
		$out['total_failed']=(isset($import_response['total_failed']) ? $import_response['total_failed'] : 0)+$total_failed;

		/* updating action is finshed */	
		if($is_last_offset) //finished
		{
			/* delete the temp file */
			@unlink($file_path);

			$log_summary_msg=$this->generate_log_summary($out, $is_last_offset);

			$out['finished']=1; //finished
			$log_file_name = '';
			$log_path=Wt_Import_Export_For_Woo_Basic_Log::$log_dir;
			$log_files = glob($log_path.'/'.$out['history_id'].'_*'.'.log');  
			if(is_array($log_files) && count($log_files)>0)
			{
				$log_file_name=basename($log_files[0]);				
			}
			$out['log_file'] = $log_file_name;
			$out['msg']=$log_summary_msg;
			
			/* updating finished status */
			$update_data=array(
				'status'=>Wt_Import_Export_For_Woo_Basic_History::$status_arr['finished'],
				'status_text'=>'Finished' //translation function not needed
			);
			$update_data_type=array(
				'%d',
				'%s',
			);
			Wt_Import_Export_For_Woo_Basic_History::update_history_entry($import_id, $update_data, $update_data_type);
		}else
		{
			$rows_processed=$input_data['rows_processed'];
			$total_processed=$rows_processed+$offset_count;

			$out['offset_count']=$total_processed;
			$out['new_offset']=$new_offset;

			$log_summary_msg=$this->generate_log_summary($out, $is_last_offset);

			$out['msg']=$log_summary_msg;
		}

		return $out;
	}

	protected function generate_log_summary($data, $is_last_offset)
	{
		if($is_last_offset)
		{
			$msg='<span class="wt_iew_info_box_title">'.__('Finished', 'product-import-export-for-woo').'</span>';
                        $msg.='<span class="wt_iew_popup_close" style="line-height:10px;width:auto" onclick="wt_iew_basic_import.hide_import_info_box();">X</span>';
		}else
		{
			// translators: %d is the number of processed records
			$msg='<span class="wt_iew_info_box_title">'.sprintf(__('Importing...(%d processed)', 'product-import-export-for-woo'), $data['offset_count']).'</span>';
		}
		$msg.='<br />'.__('Total success:', 'product-import-export-for-woo').$data['total_success'].'<br />'.__('Total failed:', 'product-import-export-for-woo').$data['total_failed'];
		if($is_last_offset)
		{
			$msg.='<span class="wt_iew_info_box_finished_text" style="display:block">';
			if(Wt_Import_Export_For_Woo_Admin_Basic::module_exists('history'))
			{
				$msg.='<a class="button button-secondary wt_iew_view_log_btn" style="margin-top:10px;" data-history-id="'. $data['history_id'] .'" onclick="wt_iew_basic_import.hide_import_info_box();">'.__('View Details', 'product-import-export-for-woo').'</a></span>';
			}
		}
		return $msg;
	}

	/**
	* 	Main ajax hook to handle all import related requests
	*/
	public function ajax_main()
	{
		include_once plugin_dir_path(__FILE__).'classes/class-import-ajax.php';
		if(Wt_Iew_Sh::check_write_access(WT_IEW_PLUGIN_ID_BASIC))
		{
			/**
			*	Check it is a rerun call
			*/
			// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- Nonce already verified in the Wt_Iew_Sh::check_write_access() method
			if(!$this->_process_rerun((isset($_POST['rerun_id']) ? absint($_POST['rerun_id']) : 0)))
			{
				$this->import_method=(isset($_POST['import_method']) ? sanitize_text_field(wp_unslash($_POST['import_method'])) : '');
				$this->to_import=(isset($_POST['to_import']) ? sanitize_text_field(wp_unslash($_POST['to_import'])) : '');
				$this->selected_template=(isset($_POST['selected_template']) ? absint(wp_unslash($_POST['selected_template'])) : 0);
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- Nonce already verified in the Wt_Iew_Sh::check_write_access() method
			
			
			$this->get_steps();

			$ajax_obj=new Wt_Import_Export_For_Woo_Basic_Import_Ajax($this, $this->to_import, $this->steps, $this->import_method, $this->selected_template, $this->rerun_id);
			
			// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification handled above, sanitization handled by Wt_Iew_Sh::sanitize_item
			$import_action = Wt_Iew_Sh::sanitize_item((isset($_POST['import_action']) ? wp_unslash($_POST['import_action']) : ''), 'text');
			// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification handled above, sanitization handled by Wt_Iew_Sh::sanitize_item
			$data_type = Wt_Iew_Sh::sanitize_item((isset($_POST['data_type']) ? wp_unslash($_POST['data_type']) : ''), 'text');
			
			$allowed_ajax_actions=array('get_steps', 'validate_file', 'get_meta_mapping_fields', 'save_template', 'save_template_as', 'update_template', 'download', 'import', 'upload_import_file', 'delete_import_file');

			$out=array(
				'status'=>0,
				'msg'=>__('Error', 'product-import-export-for-woo'),
			);

			if(method_exists($ajax_obj, $import_action) && in_array($import_action, $allowed_ajax_actions))
			{
				$out=$ajax_obj->{$import_action}($out);
			}

			if($data_type=='json')
			{
				echo json_encode($out);
			}
		}
		exit();
	}

	public function process_column_val($input_file_data_row, $form_data)
	{
		$out=array(
			'mapping_fields'=>array(),
			'meta_mapping_fields'=>array()
		);

		$this->skip_from_evaluation_array = $this->get_skip_from_evaluation();
		$this->decimal_columns = $this->get_decimal_columns();  
		
		/**
		*  	Default columns
		*/
		$mapping_form_data=(isset($form_data['mapping_form_data']) ? $form_data['mapping_form_data'] : array());
		$mapping_selected_fields=(isset($mapping_form_data['mapping_selected_fields']) ? $mapping_form_data['mapping_selected_fields'] : array());		
		$mapping_fields=(isset($mapping_form_data['mapping_fields']) ? $mapping_form_data['mapping_fields'] : array());
			
		/**
		*	Input date format. 
		*	This will be taken as the global date format for all date fields in the input file.
		*	If date format is specified in the evaluation section. Then this value will be overriden.
		*/
		$method_import_form_data=(isset($form_data['method_import_form_data']) ? $form_data['method_import_form_data'] : array());
		$input_date_format=(isset($method_import_form_data['wt_iew_date_format']) ? $method_import_form_data['wt_iew_date_format'] : ''); 

		foreach ($mapping_selected_fields as $key => $value)
		{			
			$out['mapping_fields'][$key]=$this->evaluate_data($key, $value, $input_file_data_row, $mapping_fields, $input_date_format);
		}
		$mapping_form_data=$mapping_fields=$mapping_selected_fields=null;
		unset($mapping_form_data, $mapping_fields, $mapping_selected_fields);

		/**
		*  	Meta columns
		*/
		$meta_step_form_data=(isset($form_data['meta_step_form_data']) ? $form_data['meta_step_form_data'] : array());
		$mapping_selected_fields=(isset($meta_step_form_data['mapping_selected_fields']) ? $meta_step_form_data['mapping_selected_fields'] : array());
		$mapping_fields=(isset($meta_step_form_data['mapping_fields']) ? $meta_step_form_data['mapping_fields'] : array());
		foreach ($mapping_selected_fields as $meta_key => $meta_val_arr)
		{
			$out['meta_mapping_fields'][$meta_key]=array();
			$meta_fields_arr=(isset($mapping_fields[$meta_key]) ? $mapping_fields[$meta_key] : array());
			foreach ($meta_val_arr as $key => $value)
			{
				$out['meta_mapping_fields'][$meta_key][$key]=$this->evaluate_data($key, $value, $input_file_data_row, $meta_fields_arr, $input_date_format);
			}
		}
		$meta_step_form_data=$mapping_fields=$mapping_selected_fields=$input_file_data_row=$form_data=null;
		unset($meta_step_form_data, $mapping_fields, $mapping_selected_fields, $input_file_data_row, $form_data);

		return $out;
	}
	protected function evaluate_data($key, $value, $data_row, $mapping_fields, $input_date_format)
	{
		$value=$this->add_input_file_data($key, $value, $data_row, $mapping_fields, $input_date_format,true);
		$value=$this->do_arithmetic($value);
		if(!Wt_Iew_IE_Basic_Helper::wt_string_is_json($value) && !is_serialized($value)){
            $value = $this->add_input_file_data($key, $value, $data_row, $mapping_fields, $input_date_format);
        }
		$data_row=null;
		unset($data_row);
		return $value;
	}
	protected function do_arithmetic($str)
	{
		$re = '/\[([0-9()+\-*\/. ]+)\]/m';
		$matches=array();
		$find=array();
		$replace=array();
		if(preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0))
		{
			foreach ($matches as $key => $value) 
			{
				if(is_array($value) && count($value)>1)
				{
					$synatx=$this->validate_syntax($value[1]);
					if($synatx)
					{
						// phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- The input is sanitized and validated.
						$replace[]=eval('return '.$synatx.';');
					}else
					{
						$replace[]='';
					}
					$find[]=$value[0];
					unset($synatx);
				}
			}
		}
		return str_replace($find, $replace, $str);
	}
	protected function validate_syntax($val)
	{
		$open_bracket=substr_count($val, '(');
		$close_bracket=substr_count($val, ')');
		if($close_bracket!=$open_bracket)
		{
			return false; //invalid
		}

		//remove whitespaces 
		$val=str_replace(' ', '', $val);
		$re_after='/\b[\+|*|\-|\/]([^0-9\+\-\(])/m';
		$re_before='/([^0-9\+\-\)])[\+|*|\-|\/]/m';
		
		$match_after=array();
		$match_before=array();
		if(preg_match_all($re_after, $val, $match_after, PREG_SET_ORDER, 0) || preg_match_all($re_before, $val, $match_before, PREG_SET_ORDER, 0))
		{
			return false; //invalid
		}

		unset($match_after, $match_before, $re_after, $re_before);

		/* process + and - symbols */
		$val=preg_replace(array('/\+{2,}/m', '/\-{2,}/m'), array('+', '- -'), $val);

		return $val;
	}
		protected function add_input_file_data($key, $str, $data_row, $mapping_fields, $input_date_format, $skip_from_evaluation = false)
		{
			// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Required for processing large import files
			@set_time_limit(0);
			$re = '/\{([^}]+)\}/m';
			$matches = array();
			preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
			$find = array();
			$replace = array();
			foreach ($matches as $key => $value) {
				if (is_array($value) && count($value) > 1) {
					$data_key = trim($value[1]);

					/* Check for date formatting */
					$data_key_arr = explode("@", $data_key);
					$data_format = '';
					if (count($data_key_arr) == 2) /* date format field given while on import */ {
						$data_key = $data_key_arr[0]; //first value is the field key
						$data_format = $data_key_arr[1]; //second value will be the format
					}

					/* Pre-defined date field */
					if (isset($mapping_fields[$data_key]) && isset($mapping_fields[$data_key][2]) && $mapping_fields[$data_key][2] = 'date') {
						/** 
						 *	Always give preference to evaluation section
						 *	If not specified in evaluation section. Use default format
						 */
						if ($data_format == "") {
							$data_format = $input_date_format;
						}
					}

					$output_val = '';
					if (isset($data_row[$data_key])) {
						//					$output_val=sanitize_text_field($data_row[$data_key]);   sanitize_text_field stripping html content
						$output_val = ($data_row[$data_key]);
					}

					/**
					 * 	This is a date field 
					 */
					if (trim($data_format) != "" && trim($output_val) != "") {
						if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
							$date_obj = DateTime::createFromFormat($data_format, $output_val);
							if ($date_obj) {
								$output_val = $date_obj->format('Y-m-d H:i:s');
							}
						} else {
							$output_val=gmdate("Y-m-d H:i:s", strtotime(trim(str_replace('/', '-', str_replace('-', '', $output_val)))));
						}
					}

					$is_need_to_replace = false;
					if ($skip_from_evaluation) {   /* check whether skip or not */
						if (!in_array($value[1], $this->skip_from_evaluation_array)) {      /*  current item dont skip */
							$is_need_to_replace = true;
						}
					} else { /* no needed to skip, so add all items to find and replace list */
						$is_need_to_replace = true;
					}
					if ($is_need_to_replace) {
						if (in_array($value[1], $this->decimal_columns)) { /* check if it is a decimal column , if yes, format it */
							$output_val =  wt_format_decimal($output_val);
						}
						$replace[] = $output_val;
						$find[] = $value[0];
					}
					unset($data_key);
				}
			}
			$data_row = null;
			unset($data_row);
			return str_replace($find, $replace, $str);
		}
		public function get_skip_from_evaluation()
		{
			// return apply_filters('wt_iew_importer_skip_from_evaluation', array('post_title', 'description','post_content','short_description','post_excerpt','line_item_','shipping_items', 'fee_items', 'customer_note', 'meta:wc_productdata_options', 'order_items', 'shipping_method'));        
			return apply_filters('wt_iew_importer_skip_from_evaluation', array('post_title', 'description', 'post_content', 'short_description', 'post_excerpt'));
		}
		public function get_decimal_columns()
		{
			return apply_filters('wt_iew_importer_decimal_columns', array('price', 'regular_price', '_regular_price', 'sale_price', '_sale_price'));
		}
	}
}

Wt_Import_Export_For_Woo_Basic::$loaded_modules['import']=new Wt_Import_Export_For_Woo_Basic_Import();
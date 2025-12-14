<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    Wt_Import_Export_For_Woo
 * @subpackage Wt_Import_Export_For_Woo/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wt_Import_Export_For_Woo
 * @subpackage Wt_Import_Export_For_Woo/admin
 * @author     Webtoffee <info@webtoffee.com>
 */
if (!class_exists('Wt_Import_Export_For_Woo_Admin_Basic')) {
	class Wt_Import_Export_For_Woo_Admin_Basic
	{

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $plugin_name    The ID of this plugin.
		 */
		private $plugin_name;

		private $ds_loaded = false;
		private $top_header_loaded = 0;
		private $top_header_loadedoption_name = '';
		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;

		/*
	 * module list, Module folder and main file must be same as that of module name
	 * Please check the `register_modules` method for more details
	 */
		public static $modules = array(
			'history',
			'export',
			'import',
		);

		public static $existing_modules = array();

		public static $addon_modules = array();


		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param      string    $plugin_name       The name of this plugin.
		 * @param      string    $version    The version of this plugin.
		 */
		public function __construct($plugin_name, $version)
		{
			$this->set_vars();
			$this->plugin_name = $plugin_name;
			$this->version = $version;
			$this->include_design_system();

			add_action( 'admin_print_scripts', array( $this, 'filter_admin_notices' ) );

			// Add AJAX action hooks
			add_action('wp_ajax_wt_piew_top_header_loaded', array($this, 'update_top_header_loaded'));
		}

		/**
		 *	Set config vars
		 */
		public function set_vars()
		{
			$this->top_header_loadedoption_name = 'wbft_product_top_header_loaded';
			$this->top_header_loaded = absint(get_option($this->top_header_loadedoption_name));
		}

		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles()
		{
			if (Wt_Import_Export_For_Woo_Basic_Common_Helper::wt_is_screen_allowed()) {
				wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wt-import-export-for-woo-admin.css', array(), $this->version, 'all');
			}
		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts()
		{
			if (Wt_Import_Export_For_Woo_Basic_Common_Helper::wt_is_screen_allowed()) {
				/* enqueue scripts */
				if (!function_exists('is_plugin_active')) {
					include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				}
				if ( class_exists( 'WooCommerce' ) ) {
					$tiptip_handle = version_compare( WC()->version, '10.3.0', '>=' ) ? 'wc-jquery-tiptip' : 'jquery-tiptip';
					wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wt-import-export-for-woo-admin.js', array('jquery', $tiptip_handle), $this->version, false);
					wp_enqueue_script($this->plugin_name . '_wbftHeaderScripts', plugin_dir_url(__FILE__) . 'js/wbftHeaderScripts.js', array('jquery'), $this->version, false);
				} else {
					wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wt-import-export-for-woo-admin.js', array('jquery'), $this->version, false);
					wp_enqueue_script(WT_IEW_PLUGIN_ID_BASIC . '-tiptip', WT_P_IEW_PLUGIN_URL . 'admin/js/tiptip.js', array('jquery'), WT_P_IEW_VERSION, false);
				}

				// Localize script with AJAX URL and nonce
				wp_localize_script($this->plugin_name . '_wbftHeaderScripts', 'wt_piew_params', array(
					'ajax_url' => admin_url('admin-ajax.php'),
				));
				$order_addon_active_status = false;
				$user_addon_active_status = false;

				if (is_plugin_active('order-import-export-for-woocommerce/order-import-export-for-woocommerce.php')) {
					$order_addon_active_status = true;
				}
				if (is_plugin_active('users-customers-import-export-for-wp-woocommerce/users-customers-import-export-for-wp-woocommerce.php')) {
					$user_addon_active_status = true;
				}


				$is_variable_product_exist = false;

				$variable_products_count = wp_count_posts('product_variation')->publish ?? 0;
				$is_variable_product_exist = $variable_products_count > 0 && !get_option('wt_ier_pro_banner_dismissed');


				$params = array(
					'nonces' => array(
						'main' => wp_create_nonce(WT_IEW_PLUGIN_ID_BASIC),
					),
					'ajax_url' => admin_url('admin-ajax.php'),
					'plugin_id' => WT_IEW_PLUGIN_ID_BASIC,
					'msgs' => array(
						'settings_success' => __('Settings updated.', 'product-import-export-for-woo'),
						'all_fields_mandatory' => __('All fields are mandatory', 'product-import-export-for-woo'),
						'settings_error' => __('Unable to update Settings.', 'product-import-export-for-woo'),
						'template_del_error' => __('Unable to delete template', 'product-import-export-for-woo'),
						'template_del_loader' => __('Deleting template...', 'product-import-export-for-woo'),
						'value_empty' => __('Value is empty.', 'product-import-export-for-woo'),
						// translators: %1$s is the opening link tag, %2$s is the closing link tag
						'error' => sprintf(__('An unknown error has occurred! Refer to our %1$stroubleshooting guide%2$s for assistance.', 'product-import-export-for-woo'), '<a href="' . WT_IEW_DEBUG_BASIC_TROUBLESHOOT . '" target="_blank">', '</a>'),
						'success' => __('Success.', 'product-import-export-for-woo'),
						'loading' => __('Loading...', 'product-import-export-for-woo'),
						'sure' => __('Are you sure?', 'product-import-export-for-woo'),
						'use_expression' => __('Apply', 'product-import-export-for-woo'),
						'cancel' => __('Cancel', 'product-import-export-for-woo'),
						'hide_features' => __('Hide features', 'product-import-export-for-woo'),
						'show_features' => __('Show features', 'product-import-export-for-woo'),
						'changes_not_saved'=> __('Changes that you made may not be saved.', 'product-import-export-for-woo')

					),
					'is_variable_product_exist' => $is_variable_product_exist,
					'pro_plugins' => array(
						'order' => array(
							'url' => "https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=Order_Import_Export&utm_content=" . WT_P_IEW_VERSION,
							'name' => __('Order, Coupon, Subscription Export Import for WooCommerce', 'product-import-export-for-woo'),
							'icon_url' => WT_P_IEW_PLUGIN_URL . 'assets/images/gopro/order-ie.svg',
							'sample_csv_url' => "https://www.webtoffee.com/wp-content/uploads/2021/03/Order_SampleCSV.csv",
							'is_active' => $order_addon_active_status
						),
						'coupon' => array(
							'url' => "https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=Order_Import_Export&utm_content=" . WT_P_IEW_VERSION,
							'name' => __('Order, Coupon, Subscription Export Import for WooCommerce', 'product-import-export-for-woo'),
							'icon_url' => WT_P_IEW_PLUGIN_URL . 'assets/images/gopro/order-ie.svg',
							'sample_csv_url' => "https://www.webtoffee.com/wp-content/uploads/2016/09/Coupon_Sample_CSV.csv",
							'is_active' => $order_addon_active_status
						),
						'product' => array(
							'url' => "https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=Product_Import_Export&utm_content=" . WT_P_IEW_VERSION,
							'name' => __('Product Import Export Plugin For WooCommerce', 'product-import-export-for-woo'),
							'icon_url' => WT_P_IEW_PLUGIN_URL . 'assets/images/gopro/product-ie.svg',
							'sample_csv_url' => "https://www.webtoffee.com/wp-content/uploads/2021/03/Product_SampleCSV.csv",
							'is_active' => true
						),
						'product_review' => array(
							'url' => "https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=Product_Import_Export&utm_content=" . WT_P_IEW_VERSION,
							'name' => __('Product Import Export Plugin For WooCommerce', 'product-import-export-for-woo'),
							'icon_url' => WT_P_IEW_PLUGIN_URL . 'assets/images/gopro/product-ie.svg',
							'sample_csv_url' => "https://www.webtoffee.com/wp-content/uploads/2021/04/product_review_SampleCSV.csv",
							'is_active' => true
						),
						'product_categories' => array(
							'url' => "https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=Product_Import_Export&utm_content=" . WT_P_IEW_VERSION,
							'name' => __('Product Import Export Plugin For WooCommerce', 'product-import-export-for-woo'),
							'icon_url' => WT_P_IEW_PLUGIN_URL . 'assets/images/gopro/product-ie.svg',
							'sample_csv_url' => "https://www.webtoffee.com/wp-content/uploads/2021/09/Sample-CSV-of-product-categories.csv",
							'is_active' => true
						),
						'product_tags' => array(
							'url' => "https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=Product_Import_Export&utm_content=" . WT_P_IEW_VERSION,
							'name' => __('Product Import Export Plugin For WooCommerce', 'product-import-export-for-woo'),
							'icon_url' => WT_P_IEW_PLUGIN_URL . 'assets/images/gopro/product-ie.svg',
							'sample_csv_url' => "https://www.webtoffee.com/wp-content/uploads/2021/09/Sample-CSV-with-product-tags.csv",
							'is_active' => true
						),
						'user' => array(
							'url' => "https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=User_Import_Export&utm_content=" . WT_P_IEW_VERSION,
							'name' => __('WordPress Users & WooCommerce Customers Import Export', 'product-import-export-for-woo'),
							'icon_url' => WT_P_IEW_PLUGIN_URL . 'assets/images/gopro/user-ie.svg',
							'sample_csv_url' => "https://www.webtoffee.com/wp-content/uploads/2020/10/Sample_Users.csv",
							'is_active' => $user_addon_active_status
						),
						'subscription' => array(
							'url' => "https://www.webtoffee.com/product/order-import-export-plugin-for-woocommerce/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=Order_Import_Export&utm_content=" . WT_P_IEW_VERSION,
							'name' => __('Order, Coupon, Subscription Export Import for WooCommerce', 'product-import-export-for-woo'),
							'icon_url' => WT_P_IEW_PLUGIN_URL . 'assets/images/gopro/order-ie.svg',
							'sample_csv_url' => "https://www.webtoffee.com/wp-content/uploads/2021/04/Subscription_Sample_CSV.csv",
							'is_active' => false
						),
					)
				);
				wp_localize_script($this->plugin_name, 'wt_iew_basic_params', $params);
			}
		}

		/**
		 * Registers menu options
		 * Hooked into admin_menu
		 *
		 * @since    1.0.0
		 */
		public function admin_menu()
		{
			$menus = array(
				'general-settings' => array(
					'menu',
					__('General Settings', 'product-import-export-for-woo'),
					__('General Settings', 'product-import-export-for-woo'),
					apply_filters('wt_import_export_allowed_capability', 'import'),
					WT_IEW_PLUGIN_ID_BASIC,
					array($this, 'admin_settings_page'),
					'dashicons-controls-repeat',
					56
				),
				'scheduled-job' => array(
					'submenu',
					WT_IEW_PLUGIN_ID_BASIC,
					__('Schedule Job', 'product-import-export-for-woo'),
					__('Scheduled Job', 'product-import-export-for-woo') . ' <img src="' . plugin_dir_url(__FILE__) . '../assets/images/wt_iew_crown.svg" alt="crown" style="vertical-align: middle;">',
					apply_filters('wt_import_export_allowed_capability', 'import'),
					'wt_iew_scheduled_job',
					array($this, 'admin_scheduled_job_page')
				)
			);
			$menus = apply_filters('wt_iew_admin_menu_basic', $menus);

			$menu_order = array("export", "export-sub", "import", "history", "history_log", "general-settings", "scheduled-job", "general-settings-sub");
			$this->wt_menu_order_changer($menus, $menu_order);

			$main_menu = reset($menus); //main menu must be first one

			$parent_menu_key = $main_menu ? $main_menu[4] : WT_IEW_PLUGIN_ID_BASIC;


			/* adding general settings menu */
			$menus['general-settings-sub'] = array(
				'submenu',
				$parent_menu_key,
				__('General Settings', 'product-import-export-for-woo'),
				__('General Settings', 'product-import-export-for-woo'),
				apply_filters('wt_import_export_allowed_capability', 'import'),
				WT_IEW_PLUGIN_ID_BASIC,
				array($this, 'admin_settings_page')
			);
			if (0 < count($menus)) {
				foreach ($menus as $menu) {
					if ('submenu' === $menu[0]) {
						/* currently we are only allowing one parent menu */
						add_submenu_page($parent_menu_key, $menu[2], $menu[3], $menu[4], $menu[5], $menu[6]);
					} else {
						add_menu_page($menu[1], $menu[2], $menu[3], $menu[4], $menu[5], $menu[6], $menu[7]);
					}
				}
			}

			add_submenu_page($parent_menu_key, esc_html__('Pro upgrade', 'product-import-export-for-woo'), '<span class="wt-go-premium">' . esc_html__('Pro upgrade', 'product-import-export-for-woo') . '</span>', 'import', $parent_menu_key . '-premium', array($this, 'admin_upgrade_premium_settings'));

			if (function_exists('remove_submenu_page')) {
				//remove_submenu_page(WT_PIEW_POST_TYPE, WT_PIEW_POST_TYPE);
			}
		}

		public function wt_menu_order_changer(&$arr, $index_arr)
		{
			$arr_t = array();
			foreach ($index_arr as $i => $v) {
				foreach ($arr as $k => $b) {
					if ($k == $v)
						$arr_t[$k] = $b;
				}
			}
			$arr = $arr_t;
		}

		public function admin_settings_page()
		{
			include(plugin_dir_path(__FILE__) . 'partials/wt-import-export-for-woo-admin-display.php');
		}


		public function admin_upgrade_premium_settings()
		{

			wp_safe_redirect(admin_url('admin.php?page=wt_import_export_for_woo_basic#wt-pro-upgrade'));
			exit();
		}
		public function admin_scheduled_job_page()
		{

			include(plugin_dir_path(__FILE__) . 'partials/wt-import-export-for-woo-admin-schedule-job.php');
		}



		/**
		 * 	Save admin settings and module settings ajax hook
		 */
		public function save_settings()
		{
			$out = array(
				'status' => false,
				'msg' => __('Error', 'product-import-export-for-woo'),
			);

			if (Wt_Iew_Sh::check_write_access(WT_IEW_PLUGIN_ID_BASIC)) {
				$advanced_settings = Wt_Import_Export_For_Woo_Basic_Common_Helper::get_advanced_settings();
				$advanced_fields = Wt_Import_Export_For_Woo_Basic_Common_Helper::get_advanced_settings_fields();
				$validation_rule = Wt_Import_Export_For_Woo_Basic_Common_Helper::extract_validation_rules($advanced_fields);
				$new_advanced_settings = array();
				foreach ($advanced_fields as $key => $value) {
					$form_field_name = isset($value['field_name']) ? $value['field_name'] : '';
					$field_name = (substr($form_field_name, 0, 8) !== 'wt_iew_' ? 'wt_iew_' : '') . $form_field_name;
					$validation_key = str_replace('wt_iew_', '', $field_name);
					// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verification performed in the Wt_Iew_Sh::check_write_access() method.
					if (isset($_POST[$field_name])) {
						// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitization performed in the Wt_Iew_Sh::sanitize_data() method.
						$new_advanced_settings[$field_name] = Wt_Iew_Sh::sanitize_data(wp_unslash($_POST[$field_name]), $validation_key, $validation_rule);
					}
					// phpcs:enable WordPress.Security.NonceVerification.Missing -- Nonce verification performed in the Wt_Iew_Sh::check_write_access() method.
				}

				$checkbox_items = array('wt_iew_enable_import_log', 'wt_iew_enable_history_auto_delete', 'wt_iew_include_bom');
				foreach ($checkbox_items as $checkbox_item) {
					$new_advanced_settings[$checkbox_item] = isset($new_advanced_settings[$checkbox_item]) ? $new_advanced_settings[$checkbox_item] : 0;
				}

				Wt_Import_Export_For_Woo_Basic_Common_Helper::set_advanced_settings($new_advanced_settings);
				$out['status'] = true;
				$out['msg'] = __('Settings Updated', 'product-import-export-for-woo');
				do_action('wt_iew_after_advanced_setting_update_basic', $new_advanced_settings);
			}
			echo json_encode($out);
			exit();
		}


		/**
		 * 	Delete pre-saved templates entry from DB - ajax hook
		 */
		public function delete_template()
		{

			$out = array(
				'status' => false,
				'msg' => __('Error', 'product-import-export-for-woo'),
			);

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification performed in the Wt_Iew_Sh::check_write_access() method.
			if (Wt_Iew_Sh::check_write_access(WT_IEW_PLUGIN_ID_BASIC)) { // @codingStandardsIgnoreLine.
				
				if (isset($_POST['template_id'])) { // @codingStandardsIgnoreLine.
					global $wpdb;
					$template_id = absint(wp_unslash($_POST['template_id'])); // @codingStandardsIgnoreLine.
					$tb = $wpdb->prefix . Wt_Import_Export_For_Woo_Basic::$template_tb;
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->query($wpdb->prepare("DELETE FROM {$tb} WHERE id=%d", $template_id)); // @codingStandardsIgnoreLine.
					$out['status'] = true;
					$out['msg'] = __('Template deleted successfully', 'product-import-export-for-woo');
					$out['template_id'] = $template_id;
				}
			}
			wp_send_json($out);
		}

		/**
	 Registers modules: admin	 
		 */
		public function admin_modules()
		{
			$wt_iew_admin_modules = get_option('wt_iew_admin_modules');
			if ($wt_iew_admin_modules === false) {
				$wt_iew_admin_modules = array();
			}
			foreach (self::$modules as $module) //loop through module list and include its file
			{
				$is_active = 1;
				if (isset($wt_iew_admin_modules[$module])) {
					$is_active = $wt_iew_admin_modules[$module]; //checking module status
				} else {
					$wt_iew_admin_modules[$module] = 1; //default status is active
				}
				$module_file = plugin_dir_path(__FILE__) . "modules/$module/$module.php";
				if (file_exists($module_file) && $is_active == 1) {
					self::$existing_modules[] = $module; //this is for module_exits checking
					require_once $module_file;
				} else {
					$wt_iew_admin_modules[$module] = 0;
				}
			}
			$out = array();
			foreach ($wt_iew_admin_modules as $k => $m) {
				if (in_array($k, self::$modules)) {
					$out[$k] = $m;
				}
			}

			update_option('wt_iew_admin_modules', $out);


			/**
			 *	Add on modules 
			 */
			include_once(ABSPATH . 'wp-admin/includes/plugin.php');
			foreach (self::$addon_modules as $module) //loop through module list and include its file
			{
				$plugin_file = "wt-import-export-for-woo-$module/wt-import-export-for-woo-$module.php";
				if (is_plugin_active($plugin_file)) {
					$module_file = WP_PLUGIN_DIR . "/wt-import-export-for-woo-$module/$module/$module.php";
					if (file_exists($module_file)) {
						self::$existing_modules[] = $module;
						require_once $module_file;
					}
				}
			}


			$addon_modules_basic = array(
				'order' => 'order-import-export-for-woocommerce',
				'coupon' => 'order-import-export-for-woocommerce',
				'product' => 'product-import-export-for-woo',
				'product_review' => 'product-import-export-for-woo',
				'product_categories' => 'product-import-export-for-woo',
				'product_tags' => 'product-import-export-for-woo',
				'user' => 'users-customers-import-export-for-wp-woocommerce',
			);
			foreach ($addon_modules_basic as $module_key => $module_path) {
				if (is_plugin_active("{$module_path}/{$module_path}.php")) {
					$module_file = WP_PLUGIN_DIR . "/{$module_path}/admin/modules/$module_key/$module_key.php";
					if (file_exists($module_file)) {
						self::$existing_modules[] = $module_key;
						require_once $module_file;
					}
				}
			}
		}

		public static function module_exists($module)
		{
			return in_array($module, self::$existing_modules);
		}

		/**
		 * Envelope settings tab content with tab div.
		 * relative path is not acceptable in view file
		 */
		public static function envelope_settings_tabcontent($target_id, $view_file = "", $html = "", $variables = array(), $need_submit_btn = 0)
		{
?>
			<div class="wt-iew-tab-content" data-id="<?php echo esc_attr($target_id); ?>"></div>
			<?php
			if ($view_file != "" && file_exists($view_file)) {
				include_once $view_file;
			} else {
				echo wp_kses_post($html);
			}
			?>
			<?php
			if ($need_submit_btn == 1) {
				include WT_P_IEW_PLUGIN_PATH . "admin/views/admin-settings-save-button.php";
			}
			?>
			</div>
<?php
		}

		/**
		 *	Plugin page action links
		 */
		public function plugin_action_links($links)
		{
			$links[] = '<a href="' . esc_url(admin_url('admin.php?page=' . WT_IEW_PLUGIN_ID_BASIC)) . '">' . esc_html__('Settings', 'product-import-export-for-woo') . '</a>';
			$links[] = '<a href="' . esc_url('https://www.webtoffee.com/') . '" target="_blank">' . esc_html__('Documentation', 'product-import-export-for-woo') . '</a>';
			$links[] = '<a href="' . esc_url('https://www.webtoffee.com/support/') . '" target="_blank">' . esc_html__('Support', 'product-import-export-for-woo') . '</a>';
			return $links;
		}

		public function tools_wtexport_text()
		{

			// translators: %s is the URL to the exporter page
			echo "<p><b>" . wp_kses_post( sprintf(__('Export WooCommerce products, product categories, product tags and product reviews in CSV format using <a href="%s">this exporter</a>.', 'product-import-export-for-woo' ), esc_url( admin_url('admin.php?page=wt_import_export_for_woo_basic_export&wt_to_export=product')))) . "</b></p>";

			if (!is_plugin_active('users-customers-import-export-for-wp-woocommerce/users-customers-import-export-for-wp-woocommerce.php')) {
				echo "<p><b>" . wp_kses_post( sprintf(
				/* translators: %s: User Import Export for WooCommerce plugin  URL */
				__('You can export Users and WooCommerce customers in CSV format using the plugin <a href="%s" target="_blank">Import Export WordPress Users and WooCommerce Customers</a>.', 'product-import-export-for-woo'),
					esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=users-customers-import-export-for-wp-woocommerce'))
				)) . "</b></p>";
			} else {
				// translators: %s is the URL to the exporter page
				echo "<p><b>" . wp_kses_post( sprintf(__('Export Users and WooCommerce customers in CSV format using <a href="%s">this exporter</a>.', 'product-import-export-for-woo'), esc_url(admin_url('admin.php?page=wt_import_export_for_woo_basic_export&wt_to_export=user')))) . "</b></p>";
			}

			if (!is_plugin_active('order-import-export-for-woocommerce/order-import-export-for-woocommerce.php')) {
				echo "<p><b>" . wp_kses_post( sprintf(
				/* translators: %s: Order Import Export for WooCommerce plugin  URL */
				__('You can export WooCommerce orders and coupons in CSV format using the plugin <a href="%s" target="_blank">Order Export & Order Import for WooCommerce</a>.', 'product-import-export-for-woo'),
					esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=order-import-export-for-woocommerce'))
				)) . "</b></p>";
			} else {
				// translators: %s is the URL to the exporter page
				echo "<p><b>" . wp_kses_post( sprintf( __('Export WooCommerce orders and coupons in CSV format using <a href="%s">this exporter</a>.', 'product-import-export-for-woo'), esc_url(admin_url('admin.php?page=wt_import_export_for_woo_basic_export&wt_to_export=order')))) . "</b></p>";
			}
		}

		/**
		 * 	Load the design system files and initiate it.
		 * 	
		 *  @since    3.0.0
		 */
		public function include_design_system()
		{
			if (!$this->ds_loaded) {
				include_once plugin_dir_path(__FILE__) . 'wt-ds/class-wbte-ds.php';
				/**
				 * Just initiate it. This is to load the CSS and JS.
				 */
				Wbte\Pimpexp\Ds\Wbte_Ds::get_instance(WT_P_IEW_VERSION);
				$this->ds_loaded = true;
			}
		}

		public function update_top_header_loaded()
		{
			$result = update_option($this->top_header_loadedoption_name, 1);
			wp_send_json_success(array(
				'success' => $result,
				'message' => $result ? 'Updated successfully' : 'Update failed'
			));
		}

		public function filter_admin_notices() { 
			// Exit if not on the plugin screen.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification not needed.		
			if ( empty( $_REQUEST['page'] ) || ! $this->is_plugin_page() ) { 
				return;
			}
			
			global $wp_filter;
			
			// Notices types to filter.
			$notices_types = array(
				'user_admin_notices',
				'admin_notices',
				'all_admin_notices',
			); 

			// List of classes to preserve
			$preserve_classes = array(
				'product_import_export_review_request',
				'order_import_export_review_request', 
				'user_import_export_review_request',
				'woocommerce',
				'wt_bfcm_twenty_twenty_five', // Preserve BFCM 2025 banner
			);

			foreach ( $notices_types as $type ) { 
				// Check if there are callbacks for this notice type.
				if ( empty( $wp_filter[ $type ]->callbacks ) || ! is_array( $wp_filter[ $type ]->callbacks ) ) {
					continue;
				}
				// Process each callback for the given priority.
				foreach ( $wp_filter[ $type ]->callbacks as $priority => $hooks ) {  
					foreach ( $hooks as $name => $arr ) {
						// If the callback is a closure, remove it.
						if ( is_object( $arr['function'] ) && $arr['function'] instanceof \Closure ) {
							unset( $wp_filter[ $type ]->callbacks[ $priority ][ $name ] );
							continue;
						}

						$class = ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) ? strtolower( get_class( $arr['function'][0] ) ) : '';
						
						// Skip if class matches any of the preserve classes
						$should_preserve = false;
						foreach ($preserve_classes as $preserve_class) {
							if (!empty($class) && strpos($class, $preserve_class) === 0) {
								$should_preserve = true;
								break;
							}
						}
						
						if ($should_preserve) {
							continue;
						}
						// Remove other callbacks
						unset( $wp_filter[ $type ]->callbacks[ $priority ][ $name ] );
					}
				}
			}
		}
	
		private function is_plugin_page() {
			// Early return if 'page' parameter is not set.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification not needed.
			$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
			if ( empty( $page ) ) {
				return false;
			}
			// List of plugin pages.
			$plugin_pages = array(
				'wt_import_export_for_woo_basic_export', 
				'wt_import_export_for_woo_basic_import', 
				'wt_import_export_for_woo_basic_history',
				'wt_import_export_for_woo_basic_history_log', 
				'wt_iew_scheduled_job',
				'wt_import_export_for_woo_basic',
			);
	
			// Check if the current 'page' parameter contains any of the plugin pages.
			return in_array( $page, $plugin_pages, true );
		}


		/**
		 * 	Initiate non apache info message.
		 * 
		 * 	@since 2.5.7
		 */
		public function init_non_apache_info(){
			$non_apache_info = new Wt_Non_Apache_Info('product');
			$non_apache_info->plugin_title = 'Product Import Export';
		}

		/**
		 *  Screens to show Black Friday and Cyber Monday Banner.
		 *
		 *  @since 2.5.7
		 *  @param array $screen_ids Array of screen ids.
		 *  @return array            Array of screen ids.
		 */
		public function wt_bfcm_banner_screens( $screen_ids ) {
			
			$screen_ids[] = 'toplevel_page_wt_import_export_for_woo_basic_export';
			
			$screen_ids[] = 'webtoffee-import-export-basic_page_wt_import_export_for_woo_basic_import';
			
			$screen_ids[] = 'webtoffee-import-export-basic_page_wt_iew_scheduled_job';
			
			$screen_ids[] = 'webtoffee-import-export-basic_page_wt_import_export_for_woo_basic';
					
			return $screen_ids;
		}

	}
}

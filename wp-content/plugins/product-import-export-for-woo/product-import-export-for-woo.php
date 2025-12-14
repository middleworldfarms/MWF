<?php
/*
  Plugin Name: Product Import Export for WooCommerce
  Plugin URI: https://wordpress.org/plugins/product-import-export-for-woo/
  Description: Import and Export Products From and To your WooCommerce Store.
  Author: WebToffee
  Author URI: https://www.webtoffee.com/product/product-import-export-woocommerce/
  Version: 2.5.9
  License:           GPLv3
  License URI:       https://www.gnu.org/licenses/gpl-3.0.html
  Text Domain: product-import-export-for-woo
  Domain Path: /languages
  WC tested up to: 10.3
  Requires at least: 3.0
  Requires PHP: 5.6
 */

if ( !defined( 'ABSPATH' ) || !is_admin() ) {
	return;
}


// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
}

define( 'WT_P_IEW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WT_P_IEW_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WT_P_IEW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WT_P_IEW_PLUGIN_FILENAME', __FILE__ );
if ( !defined( 'WT_IEW_PLUGIN_ID_BASIC' ) ) {
	define( 'WT_IEW_PLUGIN_ID_BASIC', 'wt_import_export_for_woo_basic' );
}
define( 'WT_P_IEW_PLUGIN_NAME', 'Product Import Export for WooCommerce' );
define( 'WT_P_IEW_PLUGIN_DESCRIPTION', 'Import and Export Products From and To your WooCommerce Store.' );

if ( !defined( 'WT_IEW_DEBUG_BASIC' ) ) {
	define( 'WT_IEW_DEBUG_BASIC', false );
}
if ( !defined( 'WT_IEW_DEBUG_BASIC_TROUBLESHOOT' ) ) {
	define( 'WT_IEW_DEBUG_BASIC_TROUBLESHOOT', 'https://www.webtoffee.com/finding-php-error-logs/' );
}

if ( ! defined( 'WBTE_PIEW_CROSS_PROMO_BANNER_VERSION' ) ) {
    // This constant must be unique for each plugin. Update this value when updating to a new banner.
    define ( 'WBTE_PIEW_CROSS_PROMO_BANNER_VERSION', '1.0.1' );
}


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WT_P_IEW_VERSION', '2.5.9' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wt-import-export-for-woo-activator.php
 */
function activate_wt_import_export_for_woo_basic_product() {
	wt_product_activation_check();
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wt-import-export-for-woo-activator.php';
	Wt_Import_Export_For_Woo_Basic_Activator_Product::activate();
	wt_product_imp_exp_basic_migrate_serialized_data_to_json();
}

require_once plugin_dir_path( __FILE__ ) . 'wt_product_import_export_welcome-script.php';


/* Checking WC is actived or not */
if ( !function_exists( 'is_plugin_active' ) ) {
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

add_action( 'plugins_loaded', 'wt_product_basic_check_for_woocommerce' );

if ( !function_exists( 'wt_product_basic_check_for_woocommerce' ) ) {

	function wt_product_basic_check_for_woocommerce() {


		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', 'wt_wc_missing_warning_product_basic' );
		}
		if ( !function_exists( 'wt_wc_missing_warning_product_basic' ) ) {

			function wt_wc_missing_warning_product_basic() {

				$install_url = wp_nonce_url( add_query_arg( array( 'action' => 'install-plugin', 'plugin' => 'woocommerce', ), admin_url( 'update.php' ) ), 'install-plugin_woocommerce' );
				$class		 = 'notice notice-error';
				$post_type	 = 'product';
				// translators: %1$s is the post type (e.g., Product), %2$s is the WooCommerce installation URL
				$message	 = sprintf( __( 'The <b>WooCommerce</b> plugin must be active for <b>%1$s CSV Import Export (BASIC)</b> plugin to work effectively. Please <a href="%2$s" target="_blank">install & activate WooCommerce</a>.', 'product-import-export-for-woo' ), ucfirst( $post_type ), esc_url( $install_url ) );
				printf( '<div class="%s"><p>%s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
			}

		}
	}
}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wt-import-export-for-woo-deactivator.php
 */
function deactivate_wt_import_export_for_woo_basic_product() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wt-import-export-for-woo-deactivator.php';
	Wt_Import_Export_For_Woo_Basic_Deactivator_Product::deactivate();
}

register_activation_hook( __FILE__, 'activate_wt_import_export_for_woo_basic_product' );
register_deactivation_hook( __FILE__, 'deactivate_wt_import_export_for_woo_basic_product' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wt-import-export-for-woo.php';

$advanced_settings = get_option('wt_iew_advanced_settings', array());
$ier_get_max_execution_time = (isset($advanced_settings['wt_iew_maximum_execution_time']) && $advanced_settings['wt_iew_maximum_execution_time'] != '') ? $advanced_settings['wt_iew_maximum_execution_time'] : ini_get('max_execution_time');

if (strpos(@ini_get('disable_functions'), 'set_time_limit') === false) {
		// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- setting the execution time limit chosen by the user.
        @set_time_limit((int)$ier_get_max_execution_time);
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wt_import_export_for_woo_basic_product() {

	if ( !defined( 'WT_IEW_BASIC_STARTED' ) ) {
		define( 'WT_IEW_BASIC_STARTED', 1 );
		$plugin = new Wt_Import_Export_For_Woo_Basic();
		$plugin->run();
	}
}

/** this added for a temporary when a plugin update with the option upload zip file. need to remove this after some version release */
if ( !get_option( 'wt_p_iew_is_active' ) ) {
	activate_wt_import_export_for_woo_basic_product();
}

add_action('init', function () {
	if ( get_option( 'wt_p_iew_is_active' ) ) {
		run_wt_import_export_for_woo_basic_product();
	}
});

/* Plugin page links */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wt_piew_plugin_action_links_basic_product' );

function wt_piew_plugin_action_links_basic_product( $links ) {
	$plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wt_import_export_for_woo_basic_export') . '">' . __('Export', 'product-import-export-for-woo') . '</a>',
		'<a href="' . admin_url('admin.php?page=wt_import_export_for_woo_basic_import') . '">' . __('Import', 'product-import-export-for-woo') . '</a>',
		'<a href="https://www.webtoffee.com/product-import-export-plugin-woocommerce-user-guide/" target="_blank">' . __( 'Documentation', 'product-import-export-for-woo' ) . '</a>',
		'<a href="https://wordpress.org/support/plugin/product-import-export-for-woo/" target="_blank">' . __( 'Support', 'product-import-export-for-woo' ) . '</a>',
		'<a href="https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_listing&utm_medium=product_imp_exp_basic&utm_campaign=Product_Import_Export&utm_content=' . WT_P_IEW_VERSION . '" style="color:#3db634;">' . __('Premium Upgrade', 'product-import-export-for-woo') . '</a>',
	);
	if ( array_key_exists( 'deactivate', $links ) ) {
		$links[ 'deactivate' ] = str_replace( '<a', '<a class="pipe-deactivate-link"', $links[ 'deactivate' ] );
	}
	return array_merge( $plugin_links, $links );
}

/*
 *  Displays update information for the plugin. 
 */

function wt_product_import_export_for_woo_update_message( $data, $response ) {

	if ( isset( $data[ 'upgrade_notice' ] ) ) {
		add_action( 'admin_print_footer_scripts', 'wt_product_imex_basic_plugin_screen_update_js' );
		printf(
		'<div class="update-message wt-product-update-message">%s</div>', wp_kses_post( $data[ 'upgrade_notice' ] )
		);
	}
}

add_action( 'in_plugin_update_message-product-import-export-for-woo/product-import-export-for-woo.php', 'wt_product_import_export_for_woo_update_message', 10, 2 );


if ( !function_exists( 'wt_product_imex_basic_plugin_screen_update_js' ) ) {

	function wt_product_imex_basic_plugin_screen_update_js() {
		?>
		<script>
		( function ( $ ) {
		var update_dv = $( '#product-import-export-for-woo-update' );
		update_dv.find( '.wt-product-update-message' ).next( 'p' ).remove();
		update_dv.find( 'a.update-link:eq(0)' ).click( function () {
			$( '.wt-product-update-message' ).remove();
		} );
		} )( jQuery );
		</script>
		<?php
	}

}
// uninstall feedback catch
include_once plugin_dir_path( __FILE__ ) . 'includes/class-wf-prodimpexp-plugin-uninstall-feedback.php';


/* for temparary fix, wc_get_product_object is not support below WC3.9.0  */ 
if(!function_exists('wt_wc_get_product_object')){  // need change this approch, cant activate WC while product add on in active state
    function wt_wc_get_product_object( $product_type, $product_id = 0 ) {
            $classname = WC_Product_Factory::get_product_classname( $product_id, $product_type );
            return new $classname( $product_id );
    }
}

include_once 'class-wt-product-review-request.php';

// Load Common Helper Class (needed by non-apache-info)
if ( ! class_exists( 'Wt_Import_Export_For_Woo_Basic_Common_Helper' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'helpers/class-wt-common-helper.php';
}

add_action( 'wt_product_addon_basic_help_content', 'wt_product_import_export_basic_help_content' );

function wt_product_import_export_basic_help_content() {
	if ( defined( 'WT_IEW_PLUGIN_ID_BASIC' ) ) {
		?>
		<li>
			<img src="<?php echo esc_url(WT_P_IEW_PLUGIN_URL); ?>assets/images/sample-csv.png">
			<h3><?php esc_html_e( 'Sample Product CSV', 'product-import-export-for-woo' ); ?></h3>
			<p><?php esc_html_e( 'Familiarize yourself with the sample CSV.', 'product-import-export-for-woo' ); ?></p>
			<a target="_blank" href="https://www.webtoffee.com/wp-content/uploads/2021/03/Product_SampleCSV.csv" class="button button-primary">
				<?php esc_html_e( 'Get Product CSV', 'product-import-export-for-woo' ); ?>        
			</a>
		</li>
		<?php
	}
}




add_action( 'wt_product_addon_basic_gopro_content', 'wt_product_addon_basic_gopro_content' );

function wt_product_addon_basic_gopro_content() {
	if ( defined( 'WT_IEW_PLUGIN_ID_BASIC' ) ) {
    ?>
        <div class="wt-ier-product wt-ier-product_review wt-ier-product_tags wt-ier-product_categories wt-ier-gopro-cta wt-ierpro-features"  style="display: none;">
                    <ul class="ticked-list wt-ierpro-allfeat">
						<li><?php esc_html_e('Import and export in XLS and XLSX formats', 'product-import-export-for-woo'); ?><span class="wt-iew-upgrade-to-pro-new-feature"><?php esc_html_e( 'New', 'product-import-export-for-woo' ); ?></span></li>
						<li><?php esc_html_e('All free version features', 'product-import-export-for-woo'); ?></li>
						<li><?php esc_html_e('XML file type support', 'product-import-export-for-woo'); ?></li>							
                        <li><?php esc_html_e('Export and import variable products, subscription products and custom product types', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Export and import custom fields and third-party plugin fields', 'product-import-export-for-woo'); ?></li>            
                        <li><?php esc_html_e('Run scheduled automatic import and export', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Import from URL, FTP/SFTP', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Export to FTP/SFTP', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Option to export product images as a separate zip file', 'product-import-export-for-woo'); ?></li>
                        <li><?php esc_html_e('Tested compatibility with major third-party plugins', 'product-import-export-for-woo'); ?></li>						
                    </ul>    
                    <div class="wt-ierpro-btn-wrapper"> 
                        <a href="<?php echo esc_url("https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=Product_Import_Export&utm_content=" . WT_P_IEW_VERSION); ?>" target="_blank"  class="wt-ierpro-outline-btn"><?php esc_html_e('UPGRADE TO PREMIUM', 'product-import-export-for-woo'); ?></a>
                    </div>
                    <p style="padding-left:25px;"><b><a href="<?php echo esc_url(admin_url('admin.php?page=wt_import_export_for_woo_basic#wt-pro-upgrade')); ?>" target="_blank"><?php esc_html_e('Get more import export addons >>', 'product-import-export-for-woo'); ?></a></b></p>
        </div>
    <?php
	}
}


/**
 * Add Export to CSV link in product listing page near the filter button.
 * 
 * @param string $which The location of the extra table nav markup: 'top' or 'bottom'.
 */
function export_csv_linkin_product_listing_page($which) {

	$currentScreen = get_current_screen();

	if ( 'edit-product' === $currentScreen->id  && !is_plugin_active( 'wt-import-export-for-woo/wt-import-export-for-woo.php' ) ) {
		echo '<a target="_blank" href="' . esc_url(admin_url('admin.php?page=wt_import_export_for_woo_basic_export&wt_to_export=product')) . '" class="button" style="height:32px;" >' . esc_html__('Export to CSV', 'product-import-export-for-woo') . ' </a>';
	}
}

add_filter('manage_posts_extra_tablenav', 'export_csv_linkin_product_listing_page');

/*
 * Add CSS for Pro Upgrade link in export/import menu
 */
add_action('admin_head', 'wt_pro_upgrad_link');

if (!function_exists('wt_pro_upgrad_link')) {

	function wt_pro_upgrad_link() {
		echo '<style>.wp-submenu li span.wt-go-premium {font-weight: 700;color: #28e499;} </style>';
	}

}

// HPOS compatibility decleration
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * Convert serialized data to JSON in database tables
 * 
 * @since    2.5.1 Added to migrate serialized data to JSON format for better compatibility and security
 * @access   public
 * @return   boolean    Success status of the conversion
 */
function wt_product_imp_exp_basic_migrate_serialized_data_to_json() {
    global $wpdb;
        
    $tables = array(
        'mapping' => $wpdb->prefix . 'wt_iew_mapping_template',
        'history' => $wpdb->prefix . 'wt_iew_action_history'
    );
        
    $success = true;
        
    foreach ($tables as $table_type => $table_name) {
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results("SELECT id, data FROM {$table_name}", ARRAY_A);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            
        if ($rows) {
            foreach ($rows as $row) {
                // Check if data is already in JSON format
                $json_check = json_decode($row['data'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Skip if already valid JSON
                    continue;
                }
                    
                // Check if data is serialized
                if (is_serialized($row['data'])) {
					
					include_once plugin_dir_path(__FILE__) . 'helpers/class-wt-common-helper.php';
                    $unserialized_data = Wt_Import_Export_For_Woo_Basic_Common_Helper::wt_unserialize_safe($row['data']);
                    if ($unserialized_data !== false) {
                        $json_data = wp_json_encode($unserialized_data);
						// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $update_result = $wpdb->update(
                            $table_name,
                            array('data' => $json_data),
                            array('id' => $row['id']),
                            array('%s'),
                            array('%d')
                        );
						// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        if ($update_result === false) {
                            $success = false;
                            break 2; // Break both loops if update fails
                        }
                    }
                }
            }
        }
    }
        
    // If migration was successful, store the option
    if ($success) {
        update_option('wt_p_iew_basic_json_migration_complete', 'yes');
    }
        
    return $success;
}
/**
 * Check and convert serialized data to JSON if not already done
 */
function wt_product_imp_exp_basic_check_and_convert_to_json() {
    $migration_complete = get_option('wt_p_iew_basic_json_migration_complete');
    if (empty($migration_complete) || $migration_complete !== 'yes') {
        wt_product_imp_exp_basic_migrate_serialized_data_to_json();
    }
}
add_action('admin_init', 'wt_product_imp_exp_basic_check_and_convert_to_json');



<?php
/*
  Plugin Name: Export and Import Users and Customers
  Plugin URI: https://wordpress.org/plugins/users-customers-import-export-for-wp-woocommerce/
  Description: Export and Import User/Customers details From and To your WordPress/WooCommerce.
  Author: WebToffee
  Author URI: https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/
  Version: 2.6.9
  Text Domain: users-customers-import-export-for-wp-woocommerce
  Domain Path: /languages
  WC tested up to: 10.3
  Requires at least: 3.0.1
  Requires PHP: 5.6
  License: GPLv3
  License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */



if (!defined('ABSPATH') || !is_admin()) {
    return;
}


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('WT_U_IEW_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WT_U_IEW_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WT_U_IEW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WT_U_IEW_PLUGIN_FILENAME', __FILE__);
if (!defined('WT_IEW_PLUGIN_ID_BASIC')) {
    define('WT_IEW_PLUGIN_ID_BASIC', 'wt_import_export_for_woo_basic');
}
define('WT_U_IEW_PLUGIN_NAME', 'User Import Export for WordPress/WooCommerce');
define('WT_U_IEW_PLUGIN_DESCRIPTION', 'Import and Export User From and To your WordPress/WooCommerce Store.');

if (!defined('WT_IEW_DEBUG_BASIC')) {
    define('WT_IEW_DEBUG_BASIC', false);
}
if (!defined('WT_IEW_DEBUG_BASIC_TROUBLESHOOT')) {
    define('WT_IEW_DEBUG_BASIC_TROUBLESHOOT', 'https://www.webtoffee.com/finding-php-error-logs/');
}

if ( ! defined( 'WBTE_UIEW_CROSS_PROMO_BANNER_VERSION' ) ) {
    // This constant must be unique for each plugin. Update this value when updating to a new banner.
    define ( 'WBTE_UIEW_CROSS_PROMO_BANNER_VERSION', '1.0.1' );
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WT_U_IEW_VERSION', '2.6.9');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wt-import-export-for-woo-activator.php
 */
function activate_wt_import_export_for_woo_basic_user() {
    wt_user_activation_check();
    require_once plugin_dir_path(__FILE__) . 'includes/class-wt-import-export-for-woo-activator.php';
    Wt_Import_Export_For_Woo_Basic_Activator_User::activate();
    wt_user_imp_exp_basic_migrate_serialized_data_to_json();
}

require_once plugin_dir_path( __FILE__ ) . 'user_import_export_welcome-script.php';


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wt-import-export-for-woo-deactivator.php
 */
function deactivate_wt_import_export_for_woo_basic_user() {

    require_once plugin_dir_path(__FILE__) . 'includes/class-wt-import-export-for-woo-deactivator.php';
    Wt_Import_Export_For_Woo_Basic_Deactivator_User::deactivate();
}

register_activation_hook(__FILE__, 'activate_wt_import_export_for_woo_basic_user');
register_deactivation_hook(__FILE__, 'deactivate_wt_import_export_for_woo_basic_user');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wt-import-export-for-woo.php';

$advanced_settings = get_option('wt_iew_advanced_settings', array());
$ier_get_max_execution_time = (isset($advanced_settings['wt_iew_maximum_execution_time']) && $advanced_settings['wt_iew_maximum_execution_time'] != '') ? $advanced_settings['wt_iew_maximum_execution_time'] : ini_get('max_execution_time');

if (strpos(@ini_get('disable_functions'), 'set_time_limit') === false) {
    // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Ini set is not a success.
    @set_time_limit($ier_get_max_execution_time);
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
function run_wt_import_export_for_woo_basic_user() {

    if (!defined('WT_IEW_BASIC_STARTED')) {
        define('WT_IEW_BASIC_STARTED', 1);
        $plugin = new Wt_Import_Export_For_Woo_Basic();
        $plugin->run();
    }
}

/** this added for a temporary when a plugin update with the option upload zip file. need to remove this after some version release */
if (!get_option('wt_u_iew_is_active')) {
    activate_wt_import_export_for_woo_basic_user();
}

add_action('init', function () {
    if (get_option('wt_u_iew_is_active')) {
        run_wt_import_export_for_woo_basic_user();
    }
});


/* Plugin page links */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wt_uiew_plugin_action_links_basic_user');

function wt_uiew_plugin_action_links_basic_user($links) {

    $plugin_links = array(
        '<a href="' . esc_url(admin_url('admin.php?page=wt_import_export_for_woo_basic_export')) . '">' . __('Export', 'users-customers-import-export-for-wp-woocommerce') . '</a>',
		'<a href="' . esc_url(admin_url('admin.php?page=wt_import_export_for_woo_basic_import')) . '">' . __('Import', 'users-customers-import-export-for-wp-woocommerce') . '</a>',
        '<a href="https://www.webtoffee.com/user-import-export-plugin-wordpress-user-guide/" target="_blank">' . __('Documentation', 'users-customers-import-export-for-wp-woocommerce') . '</a>',
        '<a target="_blank" href="https://wordpress.org/support/plugin/users-customers-import-export-for-wp-woocommerce/">' . __('Support', 'users-customers-import-export-for-wp-woocommerce') . '</a>',
        '<a target="_blank" href="https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/?utm_source=free_plugin_listing&utm_medium=user_imp_exp_basic&utm_campaign=User_Import_Export&utm_content=' . WT_U_IEW_VERSION . '" style="color:#3db634;">' . __('Premium Upgrade', 'users-customers-import-export-for-wp-woocommerce') . '</a>'
    );

    if (array_key_exists('deactivate', $links)) {
        $links['deactivate'] = str_replace('<a', '<a class="userimport-deactivate-link"', $links['deactivate']);
    }
    return array_merge($plugin_links, $links);
}

/*
 *  Displays update information for a plugin. 
 */

function wt_users_customers_import_export_for_wp_woocommerce_update_message($data, $response) {
    if (isset($data['upgrade_notice'])) {
        add_action('admin_print_footer_scripts', 'wt_users_customers_imex_plugin_screen_update_js');
        $msg = str_replace(array('<p>', '</p>'), array('<div>', '</div>'), $data['upgrade_notice']);
        echo '<style type="text/css">
            #users-customers-import-export-for-wp-woocommerce-update .update-message p:last-child{ display:none;}     
            #users-customers-import-export-for-wp-woocommerce-update ul{ list-style:disc; margin-left:30px;}
            .wf-update-message{ padding-left:30px;}
            </style>
            <div class="update-message wf-update-message">' . wp_kses_post(wpautop($msg)) . '</div>';
    }
}

add_action('in_plugin_update_message-users-customers-import-export-for-wp-woocommerce/users-customers-import-export-for-wp-woocommerce.php', 'wt_users_customers_import_export_for_wp_woocommerce_update_message', 10, 2);

if (!function_exists('wt_users_customers_imex_plugin_screen_update_js')) {

    function wt_users_customers_imex_plugin_screen_update_js() {
        ?>
        <script>
            (function ($) {
                var update_dv = $('#users-customers-import-export-for-wp-woocommerce-update');
                update_dv.find('.wf-update-message').next('p').remove();
                update_dv.find('a.update-link:eq(0)').on('click', function () {
                    $('.wf-update-message').remove();
                });
            })(jQuery);
        </script>
        <?php
    }

}
// uninstall feedback catch
include_once plugin_dir_path(__FILE__) . 'includes/class-wt-userimport-uninstall-feedback.php';

include_once 'user_import_export_review_request.php';

// Load Common Helper Class (needed by non-apache-info)
if ( ! class_exists( 'Wt_Import_Export_For_Woo_Basic_Common_Helper' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'helpers/class-wt-common-helper.php';
}

add_action('wt_user_addon_basic_help_content', 'wt_user_import_basic_help_content');

function wt_user_import_basic_help_content() {
    if (defined('WT_IEW_PLUGIN_ID_BASIC')) {
        ?>
        <li>
            <img src="<?php echo esc_url(WT_U_IEW_PLUGIN_URL); ?>assets/images/sample-csv.png">
            <h3><?php esc_html_e('Sample User CSV', 'users-customers-import-export-for-wp-woocommerce'); ?></h3>
            <p><?php esc_html_e('Familiarize yourself with the sample CSV.', 'users-customers-import-export-for-wp-woocommerce'); ?></p>
            <a target="_blank" href="https://www.webtoffee.com/wp-content/uploads/2020/10/Sample_Users.csv" class="button button-primary">
        <?php esc_html_e('Get User CSV', 'users-customers-import-export-for-wp-woocommerce'); ?>        
            </a>
        </li>
        <?php
    }
}


add_action( 'wt_user_addon_basic_gopro_content', 'wt_user_addon_basic_gopro_content' );

function wt_user_addon_basic_gopro_content() {
	if ( defined( 'WT_IEW_PLUGIN_ID_BASIC' ) ) {
    ?>
                <div class="wt-ier-user wt-ier-gopro-cta wt-ierpro-features" style="display: none;">                    
                    <ul class="ticked-list wt-ierpro-allfeat">                        
						<li><?php esc_html_e('Import and export in XLS and XLSX formats', 'users-customers-import-export-for-wp-woocommerce'); ?><span class="wt-iew-upgrade-to-pro-new-feature"><?php esc_html_e( 'New', 'users-customers-import-export-for-wp-woocommerce' ); ?></span></li>
						<li><?php esc_html_e('All free version features', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
						<li><?php esc_html_e('XML file type support', 'users-customers-import-export-for-wp-woocommerce'); ?></li>	
                        <li><?php esc_html_e('Export and import custom fields and third-party plugin fields', 'users-customers-import-export-for-wp-woocommerce'); ?></li> 
                        <li><?php esc_html_e('Option to send emails to new users on import', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
						<li><?php esc_html_e('Customize email send to new users on import', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                        <li><?php esc_html_e('Import from URL, FTP/SFTP', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                        <li><?php esc_html_e('Export to FTP/SFTP', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
						<li><?php esc_html_e('Run scheduled automatic import and export ', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                        <li><?php esc_html_e('Tested compatibility with major third-party plugins.', 'users-customers-import-export-for-wp-woocommerce'); ?></li>
                    </ul>    
                    <div class="wt-ierpro-btn-wrapper"> 
                        <a href="<?php echo esc_url("https://www.webtoffee.com/product/wordpress-users-woocommerce-customers-import-export/?utm_source=free_plugin_revamp&utm_medium=basic_revamp&utm_campaign=User_Import_Export&utm_content=" . WT_U_IEW_VERSION); ?>" target="_blank"  class="wt-ierpro-outline-btn"><?php esc_html_e('UPGRADE TO PREMIUM', 'users-customers-import-export-for-wp-woocommerce'); ?></a>
                    </div>
                    <p style="padding-left:25px;"><b><a href="<?php echo esc_url(admin_url('admin.php?page=wt_import_export_for_woo_basic#wt-pro-upgrade')); ?>" target="_blank"><?php esc_html_e('Get more import export addons >>', 'users-customers-import-export-for-wp-woocommerce'); ?></a></b></p>
                </div>
    <?php
	}
}

/**
 * Add Export to CSV link in users listing page near the filter button.
 * 
 * @param string $which The location of the extra table nav markup: 'top' or 'bottom'.
 */
function export_csv_linkin_user_listing_page($which) {

	$currentScreen = get_current_screen();

	if ('users' === $currentScreen->id && !is_plugin_active( 'wt-import-export-for-woo/wt-import-export-for-woo.php' ) ) {
		echo '<a target="_blank" href="' . esc_url(admin_url('admin.php?page=wt_import_export_for_woo_basic_export&wt_to_export=user')) . '" class="button" >' . esc_html__('Export to CSV', 'users-customers-import-export-for-wp-woocommerce') . ' </a>';
	}
}

add_filter('manage_users_extra_tablenav', 'export_csv_linkin_user_listing_page');

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
 * @since    2.6.3 Added to migrate serialized data to JSON format for better compatibility and security
 * @access   public
 * @return   boolean    Success status of the conversion
 */
function wt_user_imp_exp_basic_migrate_serialized_data_to_json() {
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
                    require_once plugin_dir_path(__FILE__) . 'helpers/class-wt-common-helper.php';
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
        update_option('wt_u_iew_basic_json_migration_complete', 'yes');
    }
        
    return $success;
}

/**
 * Check and convert serialized data to JSON if not already done
 */
function wt_user_imp_exp_basic_check_and_convert_to_json() {
    $migration_complete = get_option('wt_u_iew_basic_json_migration_complete');
    if (empty($migration_complete) || $migration_complete !== 'yes') {
        wt_user_imp_exp_basic_migrate_serialized_data_to_json();
    }
}
add_action('admin_init', 'wt_user_imp_exp_basic_check_and_convert_to_json');
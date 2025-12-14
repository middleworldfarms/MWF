<?php
/**
 * Fired during plugin activation
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    Wt_Import_Export_For_Woo
 * @subpackage Wt_Import_Export_For_Woo/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wt_Import_Export_For_Woo
 * @subpackage Wt_Import_Export_For_Woo/includes
 * @author     Webtoffee <info@webtoffee.com>
 */
if(!class_exists('Wt_Import_Export_For_Woo_Basic_Activator_User')){
class Wt_Import_Export_For_Woo_Basic_Activator_User {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() 
	{
		global $wpdb;
		delete_option('wt_u_iew_is_active'); /* remove if exists */

	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );       
        if(is_multisite()) 
        {
            // Get all blogs in the network and activate plugin on each one
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach($blog_ids as $blog_id ) 
            {
                switch_to_blog( $blog_id );
                self::install_tables();
				self::update_cross_promo_banner_version();
                restore_current_blog();
            }
        }
        else 
        {
            self::install_tables();
			self::update_cross_promo_banner_version();
        }

        add_option('wt_u_iew_is_active', 1);
	}

	/**
	 *	Check and update the cross promotion banner version.
	 */
	public static function update_cross_promo_banner_version() {
		$current_latest = get_option('wbfte_promotion_banner_version');

		if ( false === $current_latest ||  // User is installing the plugin first time.
			version_compare( $current_latest, WBTE_UIEW_CROSS_PROMO_BANNER_VERSION, '<') // $current_latest is lesser than the installed version in this plugin.
		) {
			update_option('wbfte_promotion_banner_version', WBTE_UIEW_CROSS_PROMO_BANNER_VERSION);
		}
	}

	public static function install_tables()
	{
		global $wpdb;
		$charset_collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$charset_collate = $wpdb->get_charset_collate();
		}
		//install necessary tables
		
		//creating table for saving template data================
        $table_name = $wpdb->prefix . 'wt_iew_mapping_template';
        if(!$wpdb->get_results($wpdb->prepare("SHOW TABLES LIKE %s", $table_name), ARRAY_N)) 
        {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange
            $sql_settings = "CREATE TABLE IF NOT EXISTS `$table_name` (
				`id` INT NOT NULL AUTO_INCREMENT, 
				`template_type` VARCHAR(255) NOT NULL, 
				`item_type` VARCHAR(255) NOT NULL, 
				`name` VARCHAR(255) NOT NULL, 
				`data` LONGTEXT NOT NULL, 
				PRIMARY KEY (`id`)
			) $charset_collate;";
            dbDelta($sql_settings);
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange
        }
        //creating table for saving template data================

        //creating table for saving export/import history================
        $table_name = $wpdb->prefix . 'wt_iew_action_history';
        if(!$wpdb->get_results($wpdb->prepare("SHOW TABLES LIKE %s", $table_name), ARRAY_N)) 
        {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange
            $sql_settings = "CREATE TABLE IF NOT EXISTS `$table_name` (
				`id` INT NOT NULL AUTO_INCREMENT, 
				`template_type` VARCHAR(255) NOT NULL, 
				`item_type` VARCHAR(255) NOT NULL,
				`file_name` VARCHAR(255) NOT NULL, 
				`created_at` INT NOT NULL DEFAULT '0', 
				`status` INT NOT NULL DEFAULT '0', 
				`status_text` VARCHAR(255) NOT NULL,
				`offset` INT NOT NULL DEFAULT '0', 
				`total` INT NOT NULL DEFAULT '0', 
				`data` LONGTEXT NOT NULL, 
				PRIMARY KEY (`id`)
			) $charset_collate;";
            dbDelta($sql_settings);
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange
        }
        //creating table for saving export/import history================

	}
}
}

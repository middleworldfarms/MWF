<?php
/**
 * GPLVault Client Plugin Helper Class file
 *
 * @since 4.0.0-beta
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GPLVault_Helper
 *
 * Helper functions
 *
 * @since 4.0.0-beta
 * @package GPLVault Updater Manager
 */
class GPLVault_Helper {
	private static $instance = null;

	private function __clone() {}
	private function __construct() {
	}

	/**
	 * Method get_class()
	 * Get the class name string
	 *
	 * @return string Class Name
	 */
	public static function get_class() {
		return __CLASS__;
	}

	/**
	 * Method instance()
	 * Get the Singleton instance of the class
	 *
	 * @return GPLVault_Helper Class instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function wp_filesystem() {
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			ob_start();
			if ( file_exists( ABSPATH . '/wp-admin/includes/screen.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/screen.php';
			}
			if ( file_exists( ABSPATH . '/wp-admin/includes/template.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/template.php';
			}

			$creds = request_filesystem_credentials( 'test' );
			ob_end_clean();

			if ( empty( $creds ) ) {
				if ( ! defined( 'GV_UPDATER_FS_METHOD' ) ) {
					/**
					 * @const (string) Defined save file system method
					 */
					define( 'GV_UPDATER_FS_METHOD', get_filesystem_method() );
				}

				if ( ! defined( 'FS_METHOD' ) ) {
					define( 'FS_METHOD', 'direct' );
				}
			}
			$init = WP_Filesystem( $creds );
		} else {
			$init = true;
		}
		return $init;
	}

	public static function get_filesystem_method() {
		if ( defined( 'GV_UPDATER_FS_METHOD' ) ) {
			return GV_UPDATER_FS_METHOD;
		}

		return get_filesystem_method();
	}

	public static function all_plugins( $exclude_blocked = true ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'plugin.php';
		}

		$wp_plugins = (array) get_plugins();
		if ( true !== $exclude_blocked ) {
			return $wp_plugins;
		}

		$blocked_plugins = gv_settings_manager()->blocked_plugins();
		foreach ( $blocked_plugins as $file ) {
			unset( $wp_plugins[ $file ] );
		}

		return $wp_plugins;
	}

	public static function wp_plugins_normalized( $keep_desc = false ) {
		$plugins = self::all_plugins();
		if ( empty( $plugins ) ) {
			return array();
		}
		$normalized_data = array();
		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$data = array();
			foreach ( $plugin_data as $data_key => $data_value ) {
				$data[ sanitize_title( $data_key ) ] = $data_value;
			}
			$data['plugin_basename'] = $plugin_file;

			if ( false === $keep_desc ) {
				unset( $data['description'] );
			}

			$normalized_data[ $plugin_file ] = $data;
		}

		return $normalized_data;
	}

	public static function all_themes( $exclude_blocked = true ) {
		if ( ! function_exists( 'wp_get_themes' ) ) {
			require_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'theme.php';
		}
		$all_themes = wp_get_themes();
		if ( true !== $exclude_blocked ) {
			return $all_themes;
		}
		$blocked_themes = gv_settings_manager()->blocked_themes();
		foreach ( $blocked_themes as $theme_dir ) {
			unset( $all_themes[ $theme_dir ] );
		}

		return $all_themes;
	}

	public static function normalized_themes( $fields = array() ) {
		$themes = self::all_themes();

		// Bail early
		if ( empty( $themes ) ) {
			return $themes;
		}

		if ( ! empty( $fields ) ) {
			$fields = array_map( 'strtolower', $fields );
		}

		$defaults = array(
			'name',
			'title',
			'version',
			'parent_theme',
			'author',
		);

		$fields = wp_parse_args( $fields, $defaults );

		$normalized = array();

		foreach ( $themes as $theme_slug => $theme_data ) {
			$data = array();
			foreach ( $fields as $prop ) {
				if ( 'author' === $prop ) {
					$data['author'] = $theme_data->get( 'Author' );
					continue;
				}
				$data[ "{$prop}" ] = $theme_data->{$prop};
			}
			$data['stylesheet']        = $theme_data->get_stylesheet();
			$data['parent_stylesheet'] = $theme_data->parent() ? $theme_data->parent()->get_stylesheet() : '';
			$normalized[ $theme_slug ] = $data;
		}

		return $normalized;
	}

	public static function active_theme() {
		if ( ! function_exists( 'wp_get_theme' ) ) {
			require_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'theme.php';
		}

		return wp_get_theme();
	}

	public static function active_plugin_slugs() {
		static $cache = array();

		if ( ! empty( $cache ) ) {
			return $cache;
		}

		$cache = is_multisite() ? (array) get_site_option( 'active_sitewide_plugins', array() ) : (array) get_option( 'active_plugins', array() );

		return $cache;
	}

	public static function active_plugins() {
		$all_plugins    = self::all_plugins();
		$active_plugins = self::active_plugin_slugs();

		$data = array();
		foreach ( $active_plugins as $base ) {
			if ( isset( $all_plugins[ $base ] ) ) {
				$data[ $base ] = $all_plugins[ $base ];
			}
		}

		return $data;
	}

	public static function gv_plugins( $exclude_blocked = true ) {
		$installed_plugins = self::all_plugins( $exclude_blocked );
		$gv_plugins        = gv_settings_manager()->get_available_plugins();

		return array_intersect_key( $gv_plugins, $installed_plugins );
	}

	public static function gv_themes( $exclude_blocked = true ) {
		$installed_themes = self::all_themes( $exclude_blocked );
		$gv_themes        = gv_settings_manager()->get_available_themes();

		return array_intersect_key( $gv_themes, $installed_themes );
	}

	public static function wp_plugins( $exclude_blocked = true ) {
		$installed_plugins = self::all_plugins( $exclude_blocked );
		$gv_plugins        = gv_settings_manager()->get_available_plugins();
		return array_intersect_key( $installed_plugins, $gv_plugins );
	}

	public static function wp_themes( $exclude_blocked = true ) {
		$install_themes = self::all_themes( $exclude_blocked );
		$gv_themes      = gv_settings_manager()->get_available_themes();

		return array_intersect_key( $install_themes, $gv_themes );
	}

	public static function blocked_plugins() {
		$blocked_plugins = gv_settings_manager()->blocked_plugins();
		if ( empty( $blocked_plugins ) ) {
			return array();
		}

		//      $gv_plugins = self::wp_plugins( false );
		$all_plugins = self::all_plugins( false );

		$result = array();
		foreach ( $blocked_plugins as $plugin_file ) {
			if ( isset( $all_plugins[ $plugin_file ] ) ) {
				$result[ $plugin_file ] = $all_plugins[ $plugin_file ];
			}
		}

		return $result;
	}

	public static function get_updates_data() {
		$counts = array(
			'plugins' => 0,
			'themes'  => 0,
			'total'   => 0,
		);

		$plugins = current_user_can( 'update_plugins' );

		if ( $plugins ) {
			$update_plugins = get_site_transient( GPLVault_Admin::UPDATES_KEY_PLUGINS );
			if ( ! empty( $update_plugins->response ) ) {
				$counts['plugins'] = count( $update_plugins->response );
			}
		}

		$themes = current_user_can( 'update_themes' );

		if ( $themes ) {
			$update_themes = get_site_transient( GPLVault_Admin::UPDATES_KEY_THEMES );

			if ( ! empty( $update_themes->response ) ) {
				$counts['themes'] = count( $update_themes->response );
			}
		}

		$counts['total'] = $counts['plugins'] + $counts['themes'];
		$titles          = array();

		if ( $counts['plugins'] ) {
			/* translators: %d: Number of available GPLVault plugin updates. */
			$titles['plugins'] = sprintf( _n( '%d Plugin Update', '%d Plugin Updates', $counts['plugins'], 'gplvault' ), $counts['plugins'] );
		}

		if ( $counts['themes'] ) {
			/* translators: %d: Number of available GPLVault theme updates. */
			$titles['themes'] = sprintf( _n( '%d Theme Update', '%d Theme Updates', $counts['themes'], 'gplvault' ), $counts['themes'] );
		}

		$update_title = $titles ? esc_attr( implode( ', ', $titles ) ) : '';

		$update_data = array(
			'counts' => $counts,
			'title'  => $update_title,
		);

		return apply_filters( 'gv_get_update_data', $update_data, $titles );
	}

	public static function update_plugins_data() {
		if ( wp_installing() ) {
			return;
		}

		// Include an unmodified $wp_version.
		require ABSPATH . WPINC . '/version.php';
		$plugins = self::wp_plugins();

		// remove GPLVault Updater from the list
		unset( $plugins[ GPLVault()->plugin_basename() ] );
		$active  = self::active_plugin_slugs();
		$current = get_site_transient( GPLVault_Admin::UPDATES_KEY_PLUGINS );

		if ( ! is_object( $current ) ) {
			$current = new stdClass();
		}

		$new_option               = new stdClass();
		$new_option->last_checked = time();

		$doing_cron = wp_doing_cron();

		switch ( current_filter() ) {
			case 'gv_upgrader_process_complete':
			case 'upgrader_process_complete':
				$timeout = 0;
				break;
			case 'gv_load_plugins':
				$timeout = HOUR_IN_SECONDS;
				break;
			default:
				if ( $doing_cron ) {
					$timeout = 2 * HOUR_IN_SECONDS;
				} else {
					$timeout = 6 * HOUR_IN_SECONDS;
				}
		}

		$time_not_changed = isset( $current->last_checked ) && $timeout > ( time() - $current->last_checked );

		if ( $time_not_changed ) {
			$plugin_changed = false;

			foreach ( $plugins as $file => $p ) {
				$new_option->checked[ $file ] = $p['Version'];

				if ( isset( $current->checked ) &&
					( ! isset( $current->checked[ $file ] ) || (string) $current->checked[ $file ] !== (string) $p['Version'] ) ) {
					$plugin_changed = true;
				}
			}

			if ( isset( $current->response ) && is_array( $current->response ) ) {
				foreach ( $current->response as $plugin_file => $update_details ) {
					if ( ! isset( $plugins[ $plugin_file ] ) ) {
						$plugin_changed = true;
						break;
					}
				}
			}

			// Bail if we've checked recently and if nothing has changed.
			if ( ! $plugin_changed ) {
				return;
			}
		}

		$current->last_checked = time();
		set_site_transient( GPLVault_Admin::UPDATES_KEY_PLUGINS, $current );

		$response = self::get_updated_plugins();

		if ( $response ) {
			$new_option->response     = $response['updates'];
			$new_option->translations = array();
			$new_option->no_update    = $response['no_update'];
		} else {
			$new_option->response     = array();
			$new_option->translations = array();
			$new_option->no_update    = array();
		}

		set_site_transient( GPLVault_Admin::UPDATES_KEY_PLUGINS, $new_option );
	}

	public static function update_themes_data() {
		if ( wp_installing() ) {
			return;
		}

		$installed_themes = self::wp_themes();
		$last_update      = get_site_transient( GPLVault_Admin::UPDATES_KEY_THEMES );

		if ( ! is_object( $last_update ) ) {
			$last_update = new stdClass();
		}

		$checked = array();

		foreach ( $installed_themes as $theme ) {
			$checked[ $theme->get_stylesheet() ] = $theme->get( 'Version' );
		}

		$doing_cron = wp_doing_cron();

		switch ( current_filter() ) {
			case 'gv_upgrader_process_complete':
			case 'upgrader_process_complete':
				$timeout = 0;
				break;
			case 'gv_load_themes':
				$timeout = HOUR_IN_SECONDS;
				break;
			default:
				if ( $doing_cron ) {
					$timeout = 2 * HOUR_IN_SECONDS;
				} else {
					$timeout = 6 * HOUR_IN_SECONDS;
				}
		}

		$time_not_changed = isset( $last_update->last_checked ) && $timeout > ( time() - $last_update->last_checked );

		if ( $time_not_changed ) {
			$theme_changed = false;

			foreach ( $installed_themes as $slug => $t ) {
				if (
					isset( $last_update->checked ) &&
					( ! isset( $last_update->checked[ $slug ] ) || (string) $last_update->checked[ $slug ] !== (string) $t['Version'] )
				) {
					$theme_changed = true;
				}
			}

			if ( isset( $last_update->response ) && is_array( $last_update->response ) ) {
				foreach ( $last_update->response as $slug => $update_details ) {
					if ( ! isset( $checked[ $slug ] ) ) {
						$theme_changed = true;
						break;
					}
				}
			}

			if ( ! $theme_changed ) {
				return;
			}
		}

		$last_update->last_checked = time();
		set_site_transient( GPLVault_Admin::UPDATES_KEY_THEMES, $last_update );

		$new_update               = new stdClass();
		$new_update->last_checked = time();
		$new_update->checked      = $checked;

		$response = self::get_updated_themes();

		if ( is_array( $response ) ) {
			$new_update->response  = $response['updates'];
			$new_update->no_update = $response['no_update'];
		}

		set_site_transient( GPLVault_Admin::UPDATES_KEY_THEMES, $new_update );
	}

	public static function get_updated_themes( $only_updates = false ) {
		$local_themes = self::wp_themes();
		$gv_themes    = self::gv_themes();

		if ( empty( $gv_themes ) || empty( $local_themes ) ) {
			return false;
		}

		$updates    = array();
		$no_updates = array();

		foreach ( $gv_themes as $slug => $t ) {
			if ( isset( $local_themes[ $slug ] ) ) {
				/** @var WP_Theme $local */
				$local                = $local_themes[ $slug ];
				$item                 = array();
				$item['id']           = $t['product_id'];
				$item['theme']        = $slug;
				$item['new_version']  = $t['version'];
				$item['url']          = $t['url'];
				$item['package']      = '';
				$item['requires']     = $t['wp_version'] ?? '';
				$item['requires_php'] = $t['php_version'] ?? '';

				if ( version_compare( $item['new_version'], $local->get( 'Version' ), '>' ) ) {
					$updates[ $slug ] = $item;
				} else {
					$no_updates[ $slug ] = $item;
				}
			}
		}

		if ( $only_updates ) {
			return array( 'updates' => $updates );
		}

		return array(
			'updates'   => $updates,
			'no_update' => $no_updates,
		);
	}

	public static function get_updated_plugins( $only_updates = false ) {
		$local_plugins = self::wp_plugins();
		$gv_plugins    = self::gv_plugins();

		if ( empty( $gv_plugins ) || empty( $local_plugins ) ) {
			return false;
		}

		$updates    = array();
		$no_updates = array();

		foreach ( $gv_plugins as $file => $p ) {
			if ( isset( $local_plugins[ $file ] ) ) {
				$local                = $local_plugins[ $file ];
				$plugin               = new stdClass();
				$plugin->id           = $p['product_id'];
				$plugin->slug         = $p['slug'];
				$plugin->plugin       = $p['plugin_basename'];
				$plugin->new_version  = $p['version'];
				$plugin->url          = empty( $local['PluginURI'] ) ? $p['url'] : $local['PluginURI'];
				$plugin->package      = '';
				$plugin->icons        = array();
				$plugin->banners      = array();
				$plugin->banners_rtl  = array();
				$plugin->tested       = empty( $local['RequiresWP'] ) ? $p['wp_version_tested'] : $local['RequiresWP'];
				$plugin->requires_php = empty( $local['RequiresPHP'] ) ? $p['php_version'] : $local['RequiresPHP'];

				if ( version_compare( $plugin->new_version, $local['Version'], '>' ) ) {
					$updates[ $file ] = $plugin;
				} else {
					$no_updates[ $file ] = $plugin;
				}
			}
		}

		if ( $only_updates ) {
			return array( 'updates' => $updates );
		}

		return array(
			'updates'   => $updates,
			'no_update' => $no_updates,
		);
	}

	/**
	 * @param WP_Error $error
	 * @return string|null
	 */
	public static function request_error_title( WP_Error $error ) {
		switch ( trim( $error->get_error_code() ) ) {
			case 'http_request_error':
			case 'http_request_failed':
				return __( 'HTTP Request Failed to Connect Server', 'gplvault' );
			case 'api_status_code':
				return __( 'Server Response Status Error', 'gplvault' );
			case 'api_error_empty':
				return __( 'No Response Information', 'gplvault' );
			case 'api_wcam_error':
				return __( 'Error', 'gplvault' );
			case 'api_gvsam_error':
				return __( 'Server API Error', 'gplvault' );
			case 'api_server_down':
				return __( 'API Server Down!', 'gplvault' );
			case 'gv_error_api_paused':
				return __( 'API Request Paused.', 'gplvault' );
			default:
				return null;
		}
	}
}

<?php

defined( 'ABSPATH' ) || exit;

class GPLVault_Settings_Manager {
	const API_SETTINGS_MAIN  = 'gplvault_updater_api_settings';
	const API_KEY            = 'api_key';
	const PRODUCT_KEY        = 'product_id';
	const API_ERROR_RESPONSE = 'gplvault_api_last_response';

	const DEACTIVATION_KEY = 'gplvault_updater_activated';
	const INSTANCE_KEY     = 'gplvault_updater_instance';

	const EXTRA_SETTINGS_KEY = 'gplvault_updater_extras';
	const EXTRA_SECTION      = 'gplvault_extra_section';
	const EXTRA_LOG          = 'disable_log';

	const PLUGINS_ALL = 'gplvault_available_plugins';
	const THEMES_ALL  = 'gplvault_available_themes';

	const USER_SUBS_STATUS = 'gplvault_subscription_status';

	const ADMIN_NOTICES_KEY = 'gplvault_admin_notices';
	const NOTICE_CUSTOM     = 'gplvault_admin_notice_';

	const BLOCKED_PLUGINS = 'gplvault_blocked_plugins';
	const BLOCKED_THEMES  = 'gplvault_blocked_themes';

	const CLIENT_SCHEMA  = 'gplvault_client_schema';
	const LICENSE_STATUS = 'gplvault_license_status';

	const API_PAUSE_FLAG   = 'gplvault_pause_api';
	const ORIGIN_DOWN_FLAG = 'gplvault_origin_down';

	const API_LOG_BASE = 'gplvault_api_error';

	protected static $singleton = null;

	/**
	 * @return GPLVault_Settings_Manager
	 */
	public static function instance() {
		if ( is_null( self::$singleton ) ) {
			self::$singleton = new self();
		}

		return self::$singleton;
	}

	public function __construct() {
	}

	private function __clone() {}

	public function __wakeup() {}


	public function license_status( $default = array() ) {
		return $this->get( static::LICENSE_STATUS, $default );
	}

	public function save_license_status( $status = array() ) {
		return $this->update( static::LICENSE_STATUS, $status );
	}

	public function remove_license_status() {
		return $this->delete( static::LICENSE_STATUS );
	}

	/**
	 * @return array
	 */
	public function client_schema() {
		$schema = $this->get( static::CLIENT_SCHEMA, array() );
		if ( ! empty( $schema ) ) {
			return $schema;
		}
		$schema = $this->transient( static::CLIENT_SCHEMA );
		return empty( $schema ) ? array() : $schema;
	}

	/**
	 * @param array $schema
	 *
	 * @return bool
	 */
	public function save_client_schema( $schema ) {
		return $this->update( static::CLIENT_SCHEMA, $schema );
	}

	public function remove_client_schema() {
		return $this->delete( static::CLIENT_SCHEMA );
	}

	public function blocked_plugins( $default = array() ) {
		return $this->get( static::BLOCKED_PLUGINS, $default );
	}

	public function save_blocked_plugins( $plugins ) {
		if ( ! is_array( $plugins ) ) {
			$plugins = (array) $plugins;
		}

		return $this->update( static::BLOCKED_PLUGINS, array_unique( $plugins ) );
	}

	public function remove_blocked_plugins() {
		return $this->delete( static::BLOCKED_PLUGINS );
	}

	public function blocked_themes( $default = array() ) {
		return $this->get( static::BLOCKED_THEMES, $default );
	}

	public function save_blocked_themes( $themes ) {
		return $this->update( static::BLOCKED_THEMES, array_unique( $themes ) );
	}

	public function remove_blocked_themes() {
		return $this->delete( static::BLOCKED_THEMES );
	}

	/**
	 * Get the data of all available plugins on GPLVault server
	 *
	 * @return array
	 */
	public function get_available_plugins() {
		return $this->get( static::PLUGINS_ALL, array() );
	}

	public function get_by_slug( $slug, $type = null, $data = array() ) {
		if ( is_null( $type ) ) {
			$plugins = $data;
			if ( empty( $data ) ) {
				$plugins = $this->get_available_plugins();
			}

			$result = array_filter(
				$plugins,
				function( $plugin ) use ( $slug ) {
					return $plugin['slug'] === $slug;
				}
			);

			return current( $result );
		}

		$themes = $data;

		if ( empty( $data ) ) {
			$themes = $this->get_available_themes();
		}

		$result = array_filter(
			$themes,
			function( $theme ) use ( $slug ) {
				return $theme['slug'] === $slug;
			}
		);

		return current( $result );
	}

	/**
	 * Get the data of all available themes on GPLVault server
	 *
	 * @return array
	 */
	public function get_available_themes() {
		return $this->get( static::THEMES_ALL, array() );
	}

	/**
	 * Get option value for provided option key.
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed|false
	 */
	public function get( $key, $default = false ) {
		if ( is_multisite() ) {
			return get_site_option( $key, $default );
		}

		return get_option( $key, $default );
	}

	/**
	 * Add a new option
	 *
	 * Works both for multi-site and single site WordPress
	 *
	 * @param string $key
	 * @param string $value
	 * @param string|bool $autoload
	 *
	 * @return bool
	 */
	public function add( $key, $value = '', $autoload = 'no' ) {
		return $this->update( $key, $value, $autoload );
	}

	/**
	 * Updates options for both multi-site and single site
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param string|bool $autoload
	 *
	 * @return bool
	 */
	public function update( $key, $value, $autoload = 'no' ) {
		if ( is_multisite() ) {
			return update_site_option( $key, $value );
		}

		return update_option( $key, $value, $autoload );
	}

	public function get_api_error_response( $default = array() ) {
		return $this->get( static::API_ERROR_RESPONSE, $default );
	}

	public function save_api_error_response( $payload = array() ) {
		return $this->update( static::API_ERROR_RESPONSE, $payload );
	}

	public function delete_api_error_response() {
		return $this->delete( static::API_ERROR_RESPONSE );
	}

	/**
	 * Delete an option from WordPress Options on both Multi-site and single site WordPress
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function delete( $key ) {
		if ( is_multisite() ) {
			return delete_site_option( $key );
		}

		return delete_option( $key );
	}

	/**
	 * Updates option for total list of available plugins
	 *
	 * @param array $plugin_lists
	 */
	public function update_plugins_catalog( $plugin_lists ) {
		$this->update( static::PLUGINS_ALL, $plugin_lists );
	}

	/**
	 * Check whether the option for plugin schema store empty
	 *
	 * @return bool
	 */
	public function has_plugins() {
		return ! empty( $this->get_available_plugins() );
	}

	/**
	 * Checks if the theme schema is empty
	 * @return bool
	 */
	public function has_themes() {
		return ! empty( $this->get_available_themes() );
	}

	public function has_schema() {
		return $this->has_plugins() && $this->has_themes();
	}

	public function remove_plugins_catalog() {
		return $this->delete( static::PLUGINS_ALL );
	}

	public static function clean( $input ) {
		if ( is_array( $input ) ) {
			return array_map( array( __CLASS__, 'clean' ), $input );
		} else {
			return is_scalar( $input ) ? sanitize_text_field( $input ) : $input;
		}
	}

	/**
	 * Updates option for total list of available themes
	 *
	 * @param array $theme_lists
	 *
	 * @return bool
	 */
	public function update_themes_catalog( $theme_lists ) {
		return $this->update( static::THEMES_ALL, $theme_lists );
	}

	public function remove_themes_catalog() {
		return $this->delete( static::THEMES_ALL );
	}

	public function remove_api_key() {
		return $this->delete( static::API_SETTINGS_MAIN );
	}

	public function remove_all_schema() {
		$this->remove_themes_catalog();
		$this->remove_plugins_catalog();
	}

	/**
	 * Manages to retrieve transient for both multisite and single site
	 *
	 * @see get_transient()
	 * @see get_site_transient()
	 *
	 * @since 4.0.0-beta
	 * @param string $key
	 * @return mixed
	 */
	public function transient( $key ) {
		if ( is_multisite() ) {
			return get_site_transient( $key );
		}

		return get_transient( $key );
	}

	/**
	 * Delete transient for given key on either single site or multisite
	 *
	 * @see delete_transient()
	 * @see delete_site_transient()
	 *
	 * @since 4.0.0-beta
	 * @param string $key
	 * @return bool
	 */
	public function delete_transient( $key ) {
		if ( is_multisite() ) {
			return delete_site_transient( $key );
		}

		return delete_transient( $key );
	}

	/**
	 * Manages to set transient on both multisite and single site
	 * @see set_site_transient()
	 * @see set_transient()
	 *
	 * @since 4.0.0-beta
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param int $expiration
	 * @return bool
	 */
	public function set_transient( $key, $data, $expiration = 0 ) {
		if ( is_multisite() ) {
			return set_site_transient( $key, $data, $expiration );
		}

		return set_transient( $key, $data, $expiration );
	}

	public function clear_api_settings() {
		return $this->update( static::API_SETTINGS_MAIN, array() );
	}

	public function has_api_settings() {
		return $this->api_key_exists() && $this->product_id_exists();
	}

	public function deactivate_api_settings() {
		$this->update( static::DEACTIVATION_KEY, 'no' );
	}

	public function set_initial() {
		if ( empty( $this->get( static::INSTANCE_KEY ) ) ) {
			$this->update( static::INSTANCE_KEY, gv_generate_password() );
		}

		if ( empty( $this->get( static::API_SETTINGS_MAIN, array() ) ) ) {
			$this->update( static::DEACTIVATION_KEY, 'no' );
		}
	}

	public function remove_initial() {
		foreach ( array(
			static::INSTANCE_KEY,
			static::DEACTIVATION_KEY,
		) as $option ) {
			$this->delete( $option );
		}
	}

	public function deactivation() {
		foreach ( array(
			static::DEACTIVATION_KEY,
		) as $option ) {
			$this->delete( $option );
		}
	}

	public function api_key_exists() {
		$api_key = $this->get_api_key();
		return ! empty( $api_key );
	}

	public function get_api_key( $default = false ) {
		return $this->get_api_settings( static::API_KEY, $default );
	}

	public function get_product_id( $default = false ) {
		return $this->get_api_settings( static::PRODUCT_KEY, $default );
	}

	public function product_id_exists() {
		$product_id = $this->get_product_id();

		return ! empty( $product_id );
	}

	public function is_subscription_lifetime() {
		$product_id = $this->get_product_id();
		if ( empty( $product_id ) ) {
			return false;
		}
		return ( 1178429 === (int) $product_id );
	}

	public function update_api_key( $key ) {
		$settings = $this->get_api_settings( null, array() );

		$settings[ static::API_KEY ] = sanitize_text_field( $key );

		return $this->update( static::API_SETTINGS_MAIN, $settings );
	}

	public function get_instance_id( $default = false ) {
		return $this->get( static::INSTANCE_KEY, $default );
	}

	public function refresh_instance_id() {
		$instance_id = gv_generate_password();
		$this->update( static::INSTANCE_KEY, $instance_id );

		return $instance_id;
	}

	public function save_api_settings( $value ) {
		$value = gv_clean( $value );
		return $this->update( static::API_SETTINGS_MAIN, $value );
	}

	public function get_api_settings( $key = null, $default = false ) {
		$settings = $this->get( static::API_SETTINGS_MAIN, array() );

		if ( empty( $settings ) ) {
			return $default;
		}

		if ( is_null( $key ) ) {
			return $settings;
		}

		if ( is_array( $settings ) && array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		return $default;
	}

	public function extra_settings( $default = false ) {
		return $this->get( static::EXTRA_SETTINGS_KEY, $default );

	}

	public function disable_log( $default = 'no' ) {
		$extra = $this->extra_settings( array() );

		if ( isset( $extra[ static::EXTRA_LOG ] ) ) {
			return $extra[ static::EXTRA_LOG ];
		}

		return $default;
	}

	public function is_logging_disabled() {
		$disable_log = gv_clean( $this->disable_log() );

		return ( 'yes' === $disable_log );
	}

	/**
	 * @param string $default
	 * @return bool
	 */
	public function get_activation_status( $default = 'no' ) {
		$status = trim( $this->get( static::DEACTIVATION_KEY, $default ) );
		return filter_var( $status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	}

	public function enable_activation_status() {
		return $this->update( static::DEACTIVATION_KEY, 'yes' );
	}

	public function disable_activation_status() {
		return $this->update( static::DEACTIVATION_KEY, 'no' );
	}

	public function license_is_activated() {
		return $this->get_activation_status();
	}


	public function get_notices( $default = array() ) {
		return $this->get( static::ADMIN_NOTICES_KEY, $default );
	}

	public function store_notices( $notices ) {
		return $this->update( static::ADMIN_NOTICES_KEY, $notices );
	}

	public function remove_notices() {
		return $this->delete( static::ADMIN_NOTICES_KEY );
	}

	public function notice_custom( $name, $default = false ) {
		$key = static::NOTICE_CUSTOM . $name;
		return $this->get( $key, $default );
	}

	public function save_notice_custom( $name, $value ) {
		$key = static::NOTICE_CUSTOM . $name;

		return $this->update( $key, $value );
	}

	public function remove_notice_custom( $name ) {
		$key = static::NOTICE_CUSTOM . $name;

		return $this->delete( $key );
	}

	public function installed_plugins() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$wp_plugins        = get_plugins();
		$available_plugins = (array) $this->get_available_plugins();

		$result = array();

		if ( ! empty( $available_plugins ) ) {
			foreach ( $wp_plugins as $key => $plugin ) {
				if ( array_key_exists( $key, $available_plugins ) ) {
					$result[ $key ] = $available_plugins[ $key ];
				}
			}
		}

		return $result;
	}

	public function installed_themes() {
		require_once ABSPATH . 'wp-admin/includes/theme.php';
		$wp_themes        = wp_get_themes();
		$available_themes = (array) $this->get_available_themes();

		$result = array();

		if ( ! empty( $available_themes ) ) {
			foreach ( $wp_themes as $key => $plugin ) {
				if ( array_key_exists( $key, $available_themes ) ) {
					$result[ $key ] = $available_themes[ $key ];
				}
			}
		}

		return $result;
	}

	public function subscription_status( $default = array() ) {
		return $this->get( static::USER_SUBS_STATUS, $default );
	}

	public function store_subscription_status( $status ) {
		return $this->update( static::USER_SUBS_STATUS, $status );
	}

	public function remove_subscription_status( $keep = false ) {
		if ( $keep ) {
			return $this->store_subscription_status( array() );
		}
		return $this->delete( static::USER_SUBS_STATUS );
	}

	public function pause_api() {
		return $this->update( static::API_PAUSE_FLAG, true );
	}

	public function resume_api() {
		return $this->update( static::API_PAUSE_FLAG, false );
	}

	/**
	 * @return bool
	 */
	public function is_api_paused() {
		$status = (int) $this->get( static::API_PAUSE_FLAG, false );

		return (bool) $status;
	}

	public function set_origin_down_status() {
		return $this->update( static::ORIGIN_DOWN_FLAG, true );
	}

	public function set_origin_up_status() {
		return $this->update( static::ORIGIN_DOWN_FLAG, false );
	}

	public function is_origin_down() {
		$status = (int) $this->get( static::ORIGIN_DOWN_FLAG, false );

		return (int) $status;
	}

	public function log_api_error( $action, $message, $data = array() ) {
		$log_key = empty( $action ) ? static::API_LOG_BASE . '_unknown' : static::API_LOG_BASE . '_' . sanitize_key( $action );
		$payload = array(
			'action'  => $action,
			'message' => $message,
			'data'    => $data,
			'date'    => current_time( 'mysql', true ),
		);

		return $this->set_transient( $log_key, $payload, MONTH_IN_SECONDS );
	}

	public function get_api_error_log( $action ) {
		$log_key = empty( $action ) ? static::API_LOG_BASE . '_unknown' : static::API_LOG_BASE . '_' . sanitize_key( $action );
		return $this->transient( $log_key );
	}
}

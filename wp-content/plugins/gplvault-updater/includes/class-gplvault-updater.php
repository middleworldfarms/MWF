<?php
/**
 * This file contains a class to manage the GPLVault Updater self updates through the WordPress native update system
 *
 * @package GPLVault Update Manager
 */
class GPLVault_Updater {
	private static $instance;
	private $basename;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->basename = GPLVault()->plugin_basename();
	}

	public function init() {
		if ( gv_settings_manager()->license_is_activated() ) {
			$this->init_hooks();
		} else {
			$this->remove_hooks();
		}
	}

	private function init_hooks() {
		add_filter( 'http_request_args', array( $this, 'update_check' ), 5, 2 );
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_plugins' ) );
		add_filter( 'pre_set_transient_update_plugins', array( $this, 'update_plugins' ) );
		add_filter( 'pre_transient_update_plugins', array( $this, 'update_plugins' ) );
	}

	private function remove_hooks() {
		remove_filter( 'http_request_args', array( $this, 'update_check' ), 5, 2 );
		remove_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
		remove_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_plugins' ) );
		remove_filter( 'pre_set_transient_update_plugins', array( $this, 'update_plugins' ) );
		remove_filter( 'pre_transient_update_plugins', array( $this, 'update_plugins' ) );
	}

	public function update_check( $request, $url ) {
		if ( ! is_string( $url ) || ! isset( $request['body']['plugins'] ) ) {
			return $request;
		}
		// Plugin update request.
		if ( false !== strpos( $url, '//api.wordpress.org/plugins/update-check/1.1/' ) ) {

			// Decode JSON so we can manipulate the array.
			$data = json_decode( $request['body']['plugins'] );

			// Remove the GPLVault Updater.
			unset( $data->plugins->{$this->basename} );

			// Encode back into JSON and update the response.
			$request['body']['plugins'] = wp_json_encode( $data );
		}

		return $request;
	}

	public function plugins_api( $result, $action, $args ) {
		if ( isset( $args->slug ) && GV_UPDATER_SLUG === $args->slug ) {
			$schema = $this->get_schema();
			if ( $schema ) {
				$result = (object) $schema;
			}
		}

		return $result;
	}

	public function update_plugins( $transient ) {
		$schema = $this->get_schema();
		if ( $schema && isset( $schema['new_version'] ) && version_compare( GPLVault()->version(), $schema['new_version'], '<' ) ) {
			$transient = is_object( $transient ) ? $transient : new stdClass();
			$transient->response[ GPLVault()->plugin_basename() ] = (object) $schema;
		}
		return $transient;
	}

	/**
	 * Return the schema for the client.
	 *
	 * @return false|array Returns the schema if it exists, false otherwise.
	 */
	private function get_schema() {
		$schema = gv_settings_manager()->client_schema();

		return empty( $schema ) ? false : $schema;
	}
}

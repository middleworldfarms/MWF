<?php

defined( 'ABSPATH' ) || exit;

class GPLVault_Items {
	protected static $singleton;

	private static $initiated = false;

	/**
	 * @var GPLVault_Settings_Manager $settings
	 */
	protected $settings;

	/**
	 * @var GPLVault_API_Manager $api
	 */
	private $api;

	public static function instance() {
		return new self();
	}

	private function __construct() {
	}

	private function __clone() { }

	public function __wakeup() { }

	public function init() {
		if ( true === self::$initiated ) {
			return;
		}
		self::$initiated = true;
		$this->settings  = GPLVault_Settings_Manager::instance();
		$this->api       = GPLVault_API_Manager::instance();

		// Check for theme & plugin updates.
		if ( $this->settings->license_is_activated() ) {
			$this->init_hooks();
		} else {
			$this->remove_hooks();
		}
	}

	private function init_hooks() {
		add_filter( 'http_request_args', array( $this, 'update_check' ), 5, 2 );
		add_action( 'deleted_plugin', array( __CLASS__, 'deleted_plugin' ), 10, 2 );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_plugins' ), PHP_INT_MAX, 1 );
		add_filter( 'pre_set_transient_update_plugins', array( $this, 'update_plugins' ), PHP_INT_MAX, 1 );
		add_filter( 'site_transient_update_plugins', array( $this, 'update_plugins' ), PHP_INT_MAX, 1 );

		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'update_themes' ), 999999999 );
		add_filter( 'pre_set_transient_update_themes', array( $this, 'update_themes' ), 999999999 );
		add_filter( 'site_transient_update_themes', array( $this, 'update_themes' ), 999999999 );

		add_filter( 'site_transient_update_plugins', array( $this, 'disable_update_plugins' ), 999999997 );
		add_filter( 'site_transient_update_themes', array( $this, 'disable_update_themes' ), 999999997 );

		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 999999999, 3 );
		add_filter( 'themes_api', array( $this, 'themes_api' ), 999999999, 3 );
		add_filter( 'upgrader_pre_download', array( $this, 'disable_upgrader_pre_download' ), 999999999, 3 );
		add_filter( 'upgrader_package_options', array( $this, 'maybe_deferred_package' ), 50 );
	}

	private function remove_hooks() {
		remove_filter( 'http_request_args', array( $this, 'update_check' ), 5 );
		remove_action( 'deleted_plugin', array( __CLASS__, 'deleted_plugin' ), 10 );

		remove_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_plugins' ), 999999999 );
		remove_filter( 'pre_set_transient_update_plugins', array( $this, 'update_plugins' ), 999999999 );
		remove_filter( 'site_transient_update_plugins', array( $this, 'update_plugins' ), 999999999 );

		remove_filter( 'pre_set_site_transient_update_themes', array( $this, 'update_themes' ), 999999999 );
		remove_filter( 'pre_set_transient_update_themes', array( $this, 'update_themes' ), 999999999 );
		remove_filter( 'site_transient_update_themes', array( $this, 'update_themes' ), 999999999 );

		remove_filter( 'site_transient_update_plugins', array( $this, 'disable_update_plugins' ), 999999997 );
		remove_filter( 'site_transient_update_themes', array( $this, 'disable_update_themes' ), 999999997 );

		remove_filter( 'plugins_api', array( $this, 'plugins_api' ), 999999999 );
		remove_filter( 'themes_api', array( $this, 'themes_api' ), 999999999 );
		remove_filter( 'upgrader_pre_download', array( $this, 'disable_upgrader_pre_download' ), 999999999 );
		remove_filter( 'upgrader_package_options', array( $this, 'maybe_deferred_package' ), 50 );
	}

	public function maybe_deferred_package( $options ) {
		$package = $options['package'];

		if ( false !== strrpos( $package, 'gv_delayed_download' ) && false !== strrpos( $package, 'gv_item_id' ) ) {
			parse_str( wp_parse_url( $package, PHP_URL_QUERY ), $vars );

			if ( $vars['gv_item_id'] ) {
				$download_url = $this->api->set_initials()->download( array( 'product_id' => $vars['gv_item_id'] ) );

				if ( ! empty( $download_url ) ) {
					$options['package'] = $download_url;
				}
			}
		}

		return $options;
	}

	public function update_check( $request, $url = '' ) {
		// invalid url
		if ( ! is_string( $url ) || empty( $url ) ) {
			return $request;
		}

		// required data is not in the request body
		if ( ! isset( $request['body']['themes'], $request['body']['plugins'] ) ) {
			return $request;
		}

		if ( false !== strpos( $url, '//api.wordpress.org/themes/update-check/1.1/' ) ) {
			$installed_themes = $this->installed_themes();

			if ( empty( $installed_themes ) ) {
				return $request;
			}

			$data = json_decode( $request['body']['themes'] );

			foreach ( $installed_themes as $slug => $theme ) {
				unset( $data->themes->{$slug} );
			}

			// Encode back into JSON and update the response.
			$request['body']['themes'] = wp_json_encode( $data );
		}

		if ( false !== strpos( $url, '//api.wordpress.org/plugins/update-check/1.1/' ) ) {
			$installed_plugins = $this->installed_plugins();
			if ( empty( $installed_plugins ) ) {
				return $request;
			}

			// Decode JSON so we can manipulate the array.
			$data = json_decode( $request['body']['plugins'] );

			// Remove the excluded plugins.
			foreach ( $installed_plugins as $slug => $plugin ) {
				unset( $data->plugins->$slug );
			}

			// Encode back into JSON and update the response.
			$request['body']['plugins'] = wp_json_encode( $data );
		}

		return $request;
	}

	public function rewrite_plugin_data( $w_plugins ) {
		$gv_plugins = GPLVault_Helper::gv_plugins();

		foreach ( $w_plugins as $plugin_file => $plugin_data ) {
			if ( isset( $gv_plugins[ $plugin_file ] ) ) {
				$gv_item             = $gv_plugins[ $plugin_file ];
				$plugin_data['id']   = $gv_item['product_id'];
				$plugin_data['slug'] = $gv_item['slug'];

				$w_plugins[ $plugin_file ] = $plugin_data;
			}
		}

		return $w_plugins;
	}

	public function update_plugins( $transient ) {
		$all_plugins = GPLVault_Helper::all_plugins();
		$gv_plugins  = $this->installed_plugins();

		if ( empty( $gv_plugins ) ) {
			return $transient;
		}

		$transient = isset( $transient ) && is_object( $transient ) ? $transient : new stdClass();

		foreach ( $gv_plugins as $plugin_file => $p_data ) {
			if ( isset( $all_plugins[ $plugin_file ] ) ) {
				if ( version_compare( $all_plugins[ $plugin_file ]['Version'], $p_data['version'], '<' ) ) {
					$package_url = gv_api_manager()->deferred_download( $p_data['product_id'] );

					$data              = new stdClass();
					$data->slug        = $p_data['slug'];
					$data->plugin      = $plugin_file;
					$data->new_version = $p_data['version'];
					$data->url         = isset( $p_data['url'] ) ? esc_url( $p_data['url'] ) : '';
					$data->package     = $package_url ? $package_url : '';

					$transient->response[ $plugin_file ] = $data;
				} elseif ( isset( $transient->response[ $plugin_file ] ) ) {
						unset( $transient->response[ $plugin_file ] );
				}
			}
		}

		return $transient;
	}

	public function update_themes( $transient ) {
		$all_themes = GPLVault_Helper::all_themes();
		$themes     = $this->installed_themes();

		if ( empty( $themes ) ) {
			return $transient;
		}

		foreach ( $all_themes as $key => $theme ) {
			if ( isset( $themes[ $key ] ) ) {
				if ( $theme->exists() ) {
					if ( version_compare( $theme->get( 'Version' ), $themes[ $key ]['version'], '<' ) ) {

						$theme_url = $this->api->deferred_download( $themes[ $key ]['product_id'] );

						$transient                   = isset( $transient ) && is_object( $transient ) ? $transient : new stdClass();
						$transient->response[ $key ] = array(
							'theme'       => $key,
							'new_version' => $themes[ $key ]['version'],
							'url'         => $themes[ $key ]['url'] ? $themes[ $key ]['url'] : '',
							'package'     => $theme_url ? $theme_url : '',
						);
					} elseif ( isset( $transient->response[ $key ] ) ) {
							unset( $transient->response[ $key ] );
					}
				}
			}
		}

		return $transient;
	}

	public function disable_update_plugins( $transient ) {
		return $transient;
	}

	public function disable_update_themes( $transient ) {
		return $transient;
	}

	public function plugins_api( $response, $action, $args ) {
		$installed_plugins = $this->installed_plugins();

		if ( ! empty( $installed_plugins ) ) {
			if ( 'plugin_information' === $action && isset( $args->slug ) ) {
				foreach ( $installed_plugins as $key => $plugin ) {
					if ( $plugin['slug'] === $args->slug ) {
						$plugin_file           = $this->api->deferred_download( $plugin['product_id'] );
						$response              = new stdClass();
						$response->slug        = $args->slug;
						$response->name        = ! empty( $plugin['short_name'] ) ? $plugin['short_name'] : $plugin['name'];
						$response->plugin_name = $plugin['plugin_basename'];
						$response->version     = $plugin['version'];
						$response->author      = $plugin['author'];
						$response->homepage    = isset( $plugin['url'] ) ? $plugin['url'] : '';
						if ( isset( $plugin['last_updated'] ) && $plugin['last_updated'] ) {
							$response->last_updated = $plugin['last_updated'];
						}
						$response->requires      = $plugin['wp_version'] ?? '';
						$response->tested        = $plugin['wp_version_tested'] ?? '';
						$response->sections      = array( 'description' => wp_strip_all_tags( $plugin['description'] ) );
						$response->download_link = $plugin_file ? $plugin_file : '';
						break;
					}
				}
			}
		}
		return $response;
	}

	public function themes_api( $response, $action, $args ) {
		$installed_themes = $this->installed_themes();

		if ( ! empty( $installed_themes ) ) {
			if ( 'theme_information' === $action && isset( $args->slug ) ) {
				foreach ( $installed_themes as $key => $theme_data ) {
					if ( $theme_data['slug'] === $args->slug ) {
						$theme_file = $this->api->deferred_download( $theme_data['product_id'] );

						$response          = new stdClass();
						$response->slug    = $args->slug;
						$response->name    = ! empty( $theme_data['short_name'] ) ? $theme_data['short_name'] : $theme_data['name'];
						$response->version = $theme_data['version'];
						$response->author  = $theme_data['author'];
						if ( isset( $theme_data['last_updated'] ) && $theme_data['last_updated'] ) {
							$response->last_updated = $theme_data['last_updated'];
						}
						$response->requires      = $theme_data['wp_version'];
						$response->requires_php  = $theme_data['php_version'];
						$response->sections      = array( 'description' => wp_strip_all_tags( $theme_data['description'] ) );
						$response->download_link = $theme_file ? $theme_file : '';
						break;
					}
				}
			}
		}

		return $response;
	}

	public function disable_upgrader_pre_download( $reply, $package, $upgrader ) {
		return false;
	}

	public static function deleted_plugin( $plugin_file, $is_deleted ) {
		if ( ! $is_deleted ) {
			return;
		}

		$gv_plugins = GPLVault_Helper::gv_plugins( false );

		if ( ! isset( $gv_plugins[ $plugin_file ] ) ) {
			return;
		}

		$gv_plugins_option = gv_settings_manager()->get_available_plugins();
		unset( $gv_plugins_option[ $plugin_file ] );
		gv_settings_manager()->update_plugins_catalog( $gv_plugins_option );
	}

	private function installed_themes() {
		return GPLVault_Helper::gv_themes();
	}

	private function installed_plugins() {
		$all_plugins = GPLVault_Helper::gv_plugins();
		unset( $all_plugins[ GPLVault()->plugin_basename() ] );

		return $all_plugins;
	}
}

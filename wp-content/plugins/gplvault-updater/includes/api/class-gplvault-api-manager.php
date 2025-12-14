<?php

defined( 'ABSPATH' ) || exit;

class GPLVault_API_Manager {
	protected static $singleton;

	protected $api_url = '';

	protected $instance_id;
	protected $api_key;
	protected $product_id;
	protected $object;
	protected $is_running = false;
	protected $context;
	protected $logger;
	protected $log_message_pattern = array(
		'info'     => '{message} Context: {context}',
		'warning'  => '',
		'error'    => '{message} Status Code: {status_code}, Error Code: {error_code}, Context: {context}',
		'critical' => '',
	);

	/**
	 * @var GPLVault_Settings_Manager
	 */
	protected $settings_manager;

	private $request_query = array();

	/**
	 * @var string
	 */
	protected $api_namespace = 'wp-json/gvsam/v2/';
	/**
	 * @var string
	 */
	private $action;

	/**
	 * @var array|WP_Error|null
	 */
	private $response;
	/**
	 * @var array
	 */
	private $debug_data = array();


	public static function instance( $singleton = false ) {
		if ( $singleton ) {
			if ( is_null( self::$singleton ) ) {
				self::$singleton = new self();
			}

			return self::$singleton;
		}

		return self::make();
	}

	protected function __construct() {
		$this->api_url = defined( 'GV_UPDATER_API_URL' ) ? trailingslashit( GV_UPDATER_API_URL ) : 'https://www.gplvault.com/';
		$this->logger  = gv_new_logger( 'api-manager' );

		$this->load_dependencies();
		$this->set_initials();
		$this->debug_data = array();
	}

	public static function make() {
		return new self();
	}

	/**
	 * @global string $wp_version
	 * @return string
	 */
	public static function ua_string() {
		global $wp_version;
		require ABSPATH . WPINC . '/version.php';
		return 'GPLVaultClient/' . GPLVault()->version() . '; WP/' . $wp_version . '; ' . home_url( '/' );
	}

	public static function request_headers() {
		return array(
			'X-GV-Client'   => rawurlencode( home_url( '/' ) ),
			'X-GV-Version'  => GPLVault()->version(),
			'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
			'Accept'        => 'application/json',
		);
	}

	public function is_successful_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );

		return ! ( 0 === $response_code || $response_code >= 400 );
	}

	/**
	 * @param string $route
	 * @param array $options
	 * @return array|WP_Error
	 */
	private function request( $route = '', $options = array() ) {
		$defaults = array(
			'method'     => 'GET',
			'user-agent' => static::ua_string(),
			'headers'    => static::request_headers(),
			'timeout'    => 10,
			'accept'     => 'application/json',
		);

		$req_options = wp_parse_args( $options, $defaults );
		$url         = $this->get_url( $route );

		// Request to API URL
		$response       = wp_remote_request( $url, $req_options );
		$this->response = $response;
		$this->set_debug_data( $response );

		if ( ! empty( $this->debug_data['response_error_code'] ) ) {
			$this->settings_manager->save_api_error_response( $this->debug_data );
			$log_message = isset( $this->debug_data['log_message'] ) ? $this->debug_data['log_message'] : __( 'Unknown API error occurred.', 'gplvault' );
			$log_message = rtrim( $log_message, '.' ) . '.';
			$this->settings_manager->log_api_error( $this->action, $log_message, $this->debug_data );

			if ( 500 <= $this->debug_data['response_code'] ) {
				$this->settings_manager->set_origin_down_status();
			} else {
				$this->settings_manager->set_origin_up_status();
			}

			// Logging Request Error
			$this->logger->error(
				$this->log_message_pattern['error'],
				array(
					'message'     => $log_message,
					'status_code' => (string) $this->debug_data['response_code'],
					'context'     => $this->context,
					'error_code'  => (string) ( $this->debug_data['response_error_code'] ?? 'unknown' ),
				)
			);

			return new WP_Error(
				$this->debug_data['response_error_code'],
				$this->debug_data['response_message'],
				$this->debug_data
			);
		}

		if ( ! empty( $this->settings_manager->get_api_error_response() ) ) {
			$this->settings_manager->delete_api_error_response();
		}
		$this->settings_manager->set_origin_up_status();
		$this->settings_manager->resume_api();

		$request_body = wp_remote_retrieve_body( $response );

		// Logging Request Success
		$this->logger->info(
			$this->log_message_pattern['info'],
			array(
				'message' => 'Request completed successfully.',
				'context' => $this->context,
			)
		);
		if ( empty( $request_body ) ) {
			return array();
		}

		return json_decode( $request_body, true );
	}

	/**
	 * @return bool
	 */
	public static function is_paused() {
		return gv_settings_manager()->is_api_paused();
	}

	public static function is_origin_down() {
		return gv_settings_manager()->is_origin_down();
	}

	/**
	 * @param array $params
	 * @return GPLVault_API_Manager $this
	 */
	protected function set_query( $params = array(), $is_w = false ): self {
		$defaults = array(
			'api_key'  => $this->api_key,
			'instance' => $this->instance_id,
		);

		if ( $is_w ) {
			$defaults['wc-api']     = 'wc-am-api';
			$defaults['object']     = $this->object;
			$defaults['product_id'] = $this->product_id;
		} else {
			$defaults['subscription'] = $this->product_id;
		}

		$this->request_query = wp_parse_args( $params, $defaults );

		return $this;
	}

	public function get_url( $path = '' ) {
		if ( ! empty( $path ) ) {
			$url = trailingslashit( $this->api_url ) . trailingslashit( $this->api_namespace ) . trailingslashit( $path );
		} else {
			$url = trailingslashit( $this->api_url );
		}

		if ( empty( $this->request_query ) ) {
			return $url;
		}

		return add_query_arg( $this->request_query, $url );
	}

	public function set_api_key( $key ) {
		$this->api_key = $key;

		return $this;
	}

	public function set_product_id( $product_id ) {
		$this->product_id = $product_id;

		return $this;
	}

	public function healthcheck() {
		$this->request_query = array();
		$this->action        = 'healthcheck';
		$this->context       = 'Server Healthcheck';

		return $this->request( 'healthcheck' );
	}

	public function status( $args = array() ) {
		$this->context = 'Activation Status';

		if ( self::is_origin_down() ) {
			$this->logger->warning(
				'The origin server may be down. Request has been throttled. Context: {context}',
				array(
					'context' => $this->context,
				)
			);
			return $this->service_down_error();
		}

		$this->action = 'status';

		$defaults = array(
			'wc_am_action' => 'status',
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->set_query( $args, true )->request();
	}

	public function activate( $args = array() ) {
		$this->context = 'Client Activation';

		if ( self::is_origin_down() ) {
			$this->logger->warning(
				'The origin server may be down. Request has been throttled. Context: {context}',
				array(
					'context' => $this->context,
				)
			);
			return $this->service_down_error();
		}

		$this->action = 'activate';

		$defaults = array(
			'wc_am_action' => 'activate',
			'instance'     => $this->instance_id ? $this->instance_id : $this->settings_manager->refresh_instance_id(),
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->set_query( $args, true )->request();
	}

	/**
	 * @param array $args
	 * @return array|WP_Error
	 */
	public function deactivate( $args = array() ) {
		$this->context = 'Client Deactivation';

		if ( self::is_origin_down() ) {
			$this->logger->warning(
				'The origin server may be down. Request has been throttled. Context: {context}',
				array(
					'context' => $this->context,
				)
			);
			return $this->service_down_error();
		}

		$this->action = 'deactivate';

		$defaults = array(
			'wc_am_action' => 'deactivate',
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->set_query( $args, true )->request();
	}

	public function schema( $args = array(), $options = array() ) {
		$this->context = 'Schema Data Fetch';

		if ( self::is_origin_down() ) {
			$this->logger->warning(
				'The origin server may be down. Request has been throttled. Context: {context}',
				array(
					'context' => $this->context,
				)
			);
			return $this->service_down_error();
		}

		if ( self::is_paused() ) {
			$this->logger->warning(
				'The API request is paused, please debug if your site is sending too many requests to our server. Context: {context}',
				array(
					'context' => $this->context,
				)
			);
			return $this->pause_error();
		}

		$this->action = 'schema';

		$this->set_initials();

		$items = self::schema_payload();

		$headers                 = self::request_headers();
		$headers['content-type'] = 'application/x-www-form-urlencoded';
		$payload                 = array(
			'domain' => $this->domain(),
			'items'  => wp_json_encode( $items ),
		);

		$default_options = array(
			'method'  => 'POST',
			'headers' => $headers,
			'body'    => $payload,
		);

		return $this->set_query( $args )->request( 'schema', wp_parse_args( $options, $default_options ) );
	}

	public function deferred_download( $product_id ) {
		if ( empty( $product_id ) ) {
			return '';
		}

		$args = array(
			'gv_delayed_download' => true,
			'gv_item_id'          => $product_id,
		);

		$admin_url = self_admin_url( 'admin.php?page=' . GPLVault_Admin::SLUG_SETTINGS );

		return add_query_arg( $args, esc_url( $admin_url ) );
	}

	public function resource( $resource_id, $args = array() ) {
		$this->context = 'Resource Content Fetch';
		if ( self::is_origin_down() ) {
			$this->logger->warning(
				'The origin server may be down. Request has been throttled. Context: {context}',
				array(
					'context' => $this->context,
				)
			);

			return '';
		}

		if ( self::is_paused() ) {
			$this->logger->warning(
				'The API request is paused, please debug if your site is sending too many requests to our server. Context: {context}',
				array(
					'context' => $this->context,
				)
			);

			return '';
		}

		$this->action = 'resource';

		$this->set_initials();
		$defaults = array(
			'domain' => $this->domain(),
		);
		$args     = wp_parse_args( $args, $defaults );
		$response = $this->set_query( $args )->request( "resource/{$resource_id}" );

		if ( is_wp_error( $response ) ) {
			return '';
		}
		return $response['resource_url'];
	}

	public function download( $args ) {
		$this->context = 'Download Package Data';

		if ( self::is_origin_down() ) {
			$this->logger->warning(
				'The origin server may be down. Request has been throttled. Context: {context}',
				array(
					'context' => $this->context,
				)
			);

			return '';
		}

		if ( self::is_paused() ) {
			$this->logger->warning(
				'The API request is paused, please debug if your site is sending too many requests to our server. Context: {context}',
				array(
					'context' => $this->context,
				)
			);

			return '';
		}

		$this->action = 'download';

		$this->set_initials();
		$defaults = array(
			'domain' => $this->domain(),
		);

		$args = wp_parse_args( $args, $defaults );

		$response = $this->set_query( $args )->request( 'download' );

		if ( is_wp_error( $response ) || empty( $response['package'] ) ) {
			return '';
		}

		return $response['package'];
	}

	public function client_schema( $args = array() ) {
		$this->context = 'Client Schema Fetch';

		if ( self::is_origin_down() ) {
			$this->logger->warning(
				'The origin server may be down. Request has been throttled. Context: {context}',
				array(
					'context' => $this->context,
				)
			);

			return $this->service_down_error();
		}

		if ( self::is_paused() ) {
			$this->logger->warning(
				'The API request is paused, please debug if your site is sending too many requests to our server. Context: {context}',
				array(
					'context' => $this->context,
				)
			);
			return $this->pause_error();
		}

		$this->action = 'client_schema';

		$this->set_initials();
		$defaults = array(
			'domain'   => $this->object,
			'instance' => $this->instance_id,
		);

		$args = wp_parse_args( $args, $defaults );
		return $this->set_query( $args )->request( 'client-schema' );
	}

	protected function load_dependencies() {
		if ( ! function_exists( 'gv_settings_manager' ) ) {
			require_once GPLVault()->includes_path( '/gplvault-functions.php' );
		}

		$this->settings_manager = gv_settings_manager();
	}

	public function set_initials() {
		$this->object      = $this->domain();
		$this->api_key     = $this->settings_manager->get_api_key();
		$this->product_id  = $this->settings_manager->get_product_id();
		$this->instance_id = $this->settings_manager->get_instance_id( null );
		$this->api_url     = defined( 'GV_UPDATER_API_URL' ) ? trailingslashit( GV_UPDATER_API_URL ) : 'https://www.gplvault.com/';

		return $this;
	}

	public function set_instance( $instance ) {
		$this->instance_id = $instance;

		return $this;
	}

	public function domain() {
		return str_ireplace( array( 'http://', 'https://' ), '', home_url() );
	}

	public static function schema_payload() {
		$active_plugins = GPLVault_Helper::active_plugins();
		unset( $active_plugins[ GPLVault()->plugin_basename() ] );

		$plugins = array();
		foreach ( $active_plugins as $plugin_file => $plugin_data ) {
			$plugins[ $plugin_file ] = $plugin_data['Version'];
		}

		$themes     = array();
		$all_themes = GPLVault_Helper::all_themes();
		foreach ( $all_themes as $dir => $theme ) {
			$themes[ $dir ] = $theme->get( 'Version' );
		}

		$payload = array(
			'plugins' => $plugins,
			'themes'  => $themes,
		);

		// Allow modifying the payload (for full catalog refresh)
		return apply_filters( 'gplvault_schema_payload', $payload );
	}

	/**
	 * @param array|WP_Error $response
	 * @return $this
	 */
	private function set_debug_data( $response ) {
		$response_message = wp_remote_retrieve_response_message( $response );

		$response_code     = (int) wp_remote_retrieve_response_code( $response );
		$raw_response_body = wp_remote_retrieve_body( $response );
		$response_datetime = wp_remote_retrieve_header( $response, 'date' );
		$response_at       = empty( $response_datetime ) ? 0 : $response_datetime;
		if ( 0 !== $response_at && ! is_numeric( $response_at ) ) {
			$response_time = strtotime( $response_at );
		} else {
			$response_time = (int) $response_at;
		}
		$response_body    = empty( $raw_response_body ) ? array() : json_decode( $raw_response_body, true );
		$this->debug_data = array(
			'request_action'       => $this->action,
			'response_code'        => $response_code,
			'response_date'        => empty( $response_time ) ? time() : $response_time,
			'response_cf_ray'      => wp_remote_retrieve_header( $response, 'cf-ray' ),
			'response_status_code' => $response_code,
			'response_error_code'  => '',
			'response_body'        => array(),
			'response_message'     => $response_message,
			'is_wcam_error'        => false,
			'should_pause'         => (bool) $this->should_pause_api(),
		);
		if ( is_wp_error( $response ) ) {
			$this->debug_data['response_body']       = $response->get_error_data();
			$this->debug_data['response_error_code'] = $response->get_error_code();
			$this->debug_data['response_message']    = $response->get_error_message();
			$this->debug_data['log_message']         = $response->get_error_message();
		}
		if ( $response_code >= 400 || ( isset( $response_body['code'] ) && 100 === (int) $response_body['code'] ) ) {
			$general_message  = empty( $response_message ) ? '' : rtrim( $response_message, '.' ) . '. ';
			$general_message .= isset( $response_body['message'] ) && ! empty( $response_body['message'] )
				? $response_body['message'] : '';

			$this->debug_data['log_message']      = $general_message;
			$this->debug_data['response_message'] = $general_message;
			$this->debug_data['response_body']    = $response_body;

			if ( isset( $response_body['code'] ) && 100 === (int) $response_body['code'] ) {
				$this->debug_data['response_message']     = isset( $response_body['error'] ) ? $response_body['error'] : $response_message;
				$this->debug_data['is_wcam_error']        = true;
				$this->debug_data['response_error_code']  = 'api_wcam_error';
				$this->debug_data['response_status_code'] = (int) $response_body['code'];
				$this->debug_data['response_body']        = $response_body['data'] ?? array();
				$this->debug_data['log_message']          = $this->debug_data['response_message'];
			} elseif ( 401 === $response_code ) {
				// error maybe generated by WP REST API
				$this->debug_data['response_error_code']  = $response_body['code'];
				$this->debug_data['response_status_code'] = $response_body['code'];
				$this->debug_data['response_body']        = $response_body['data'] ?? array();
			} else {
				$this->debug_data['response_error_code'] = 'api_status_code';
			}

			if ( 403 === $response_code ) {
				/* translators: 1: Message from the HTTP Response 2: Line Ending character */
				$this->debug_data['response_message'] = sprintf( __( '%1$s. %2$s Your request was blocked by our firewall. Probably your server IP is blacklisted in any IP blacklist database such as www.abuseipdb.com.', 'gplvault' ), $response_message, PHP_EOL );
				$this->debug_data['log_message']      = __( 'Forbidden. Requests are blocked by firewall.', 'gplvault' );
			}

			if ( 429 === $response_code ) {
				$this->debug_data['response_message'] = __( 'Too many requests. Your server has generated too many requests to our api server. Please check your server or deactivate the GPLVault Update Manager plugin.', 'gplvault' );
				$this->debug_data['log_message']      = __( 'Too many requests.', 'gplvault' );
			}

			if ( 414 === $response_code ) {
				$this->debug_data['response_message'] = __( 'Request-URI Too Long. You have installed too many items in your system. Please reduce the number of plugins and themes or EXCLUDE non-GPLVault items from GPLVault Update Manager settings panel.', 'gplvault' );
				$this->debug_data['log_message']      = __( 'Request-URI Too Long.', 'gplvault' );
			}

			if ( 500 === $response_code ) {
				$this->debug_data['response_message'] = __( 'API Server Internal Error. Maybe the API Server has critical server error. Please try again later or check our server.', 'gplvault' );
				$this->debug_data['log_message']      = __( 'API Server Internal Error.', 'gplvault' );
			}

			if ( 503 === $response_code ) {
				$this->debug_data['response_message'] = __( 'API Service Unavailable. The API Service is unavailable right now. Please try again later or check our server.', 'gplvault' );
				$this->debug_data['log_message']      = __( 'API Service Unavailable.', 'gplvault' );
			}

			if ( in_array( $response_code, array( 502, 504 ), true ) ) {
				$this->debug_data['response_message'] = __( 'Gateway error: Unable to reach the API server. Please contact support or try again later.', 'gplvault' );
				$this->debug_data['log_message']      = __( 'Bad Gateway: API server communication failed.', 'gplvault' );
			}

			if ( 521 === $response_code ) {
				$this->debug_data['response_message'] = __( 'API Origin Server Down. API server is down. Please report webmaster to fix the API server and try once the server is up again.', 'gplvault' );
				$this->debug_data['log_message']      = __( 'API Origin Server Down.', 'gplvault' );
			}

			if ( in_array( $response_code, array( 522, 524 ), true ) ) {
				$this->debug_data['response_message'] = __( 'API Origin Server Connection Timed Out. If api server is slow for long, it would be better to deactivate the client plugin.', 'gplvault' );
				$this->debug_data['log_message']      = __( 'API Origin Server Connection Timed Out.', 'gplvault' );
			}

			if ( 523 === $response_code ) {
				$this->debug_data['response_message'] = __( 'API Origin server is unreachable. Please contact webmaster as soon as possible.', 'gplvault' );
				$this->debug_data['log_message']      = __( 'API Origin server is unreachable.', 'gplvault' );
			}

			if ( 525 === $response_code ) {
				$this->debug_data['response_message'] = __( 'API Origin SSL handshake failed. Please report webmaster to resolve the SSL error.', 'gplvault' );
				$this->debug_data['log_message']      = __( 'API Origin SSL handshake failed.', 'gplvault' );
			}
		}

		return $this;
	}

	public function should_pause_api() {
		if ( is_null( $this->response ) ) {
			return null;
		}

		$response_code = (int) wp_remote_retrieve_response_code( $this->response );

		if ( $response_code >= 500 && $response_code < 600 ) {
			return true;
		}

		return false;
	}

	/**
	 * @return WP_Error
	 */
	private function pause_error() {
		$last_error_request = $this->settings_manager->get_api_error_response();
		return new WP_Error(
			'gv_error_api_paused',
			__( 'Maybe your server is making too many requests or you have subscription issue with GPLVault. If your server is making too many requests, the api requests will be resumed soon. Please check back later.', 'gplvault' ),
			$last_error_request ? $last_error_request : array()
		);
	}

	private function service_down_error() {
		$last_error_request = $this->settings_manager->get_api_error_response();
		return new WP_Error(
			'api_server_down',
			__( 'Right now the GPLVault server is down. Please try again later.', 'gplvault' ),
			$last_error_request ? $last_error_request : array()
		);
	}
}

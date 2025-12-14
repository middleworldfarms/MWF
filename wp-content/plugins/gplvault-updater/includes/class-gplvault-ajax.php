<?php

/**
 * Class GPLVault_Ajax handles all Ajax activities in the plugin
 *
 * @since 4.0.0-beta
 */
class GPLVault_Ajax {
	const NONCE_KEY = 'gplvault_ajax';
	const ACTION    = 'gplvault_updater_request';

	/**
	 * @var GPLVault_Ajax|null Singleton instance of the class
	 */
	private static $instance = null;

	/**
	 * @return array
	 */
	private function getBindings() {
		$defaults = array();
		return apply_filters( 'gv_ajax_bindings', $defaults );
	}


	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init() {
		add_action( 'wp_ajax_' . self::ACTION, array( $this, 'handle' ) );
	}

	public function handle() {
		$request_verification = check_ajax_referer( static::NONCE_KEY, 'security', false );

		if ( false === $request_verification ) {
			$this->send_response(
				new WP_Error( 'gv_ajax_nonce', __( 'Invalid request source.', 'gplvault' ) ),
				WP_Http::FORBIDDEN
			);
		}

		$context = isset( $_POST['context'] ) ? sanitize_key( $_POST['context'] ) : '';

		if ( empty( $context ) ) {
			$this->send_response(
				new WP_Error( 'gv_ajax_context', sprintf( __( 'Required request %s parameter missing.', 'gplvault' ), 'context' ) ),
				WP_Http::BAD_REQUEST
			);
		}

		$bindings = $this->getBindings();

		if ( ! isset( $bindings[ $context ] ) ) {
			$this->send_response(
				new WP_Error( 'gv_ajax_handler', __( 'Missing request handler.', 'gplvault' ) ),
				WP_Http::NOT_IMPLEMENTED
			);
		}

		$response = call_user_func( $bindings[ $context ], $_POST );

		if ( is_wp_error( $response ) ) {
			/** @var WP_Error $response */
			$error_data  = $response->get_error_data();
			$http_status = $error_data['http_status'] ?? WP_Http::OK;
			unset( $error_data['http_status'] );
			$this->send_response( $response, $http_status );
		} else {
			$http_status = $response['http_status'] ?? WP_Http::OK;
			unset( $response['http_status'] );
			$this->send_response( $response, $http_status );
		}
	}

	private function send_response( $data = null, $status_code = null, $options = 0 ) {
		$response = array( 'success' => ! is_wp_error( $data ) );
		if ( ! isset( $data ) ) {
			wp_send_json( $response, $status_code, $options );
		}

		$result = array(
			'status'      => ! is_wp_error( $data ),
			'status_code' => $status_code,
		);

		/** @var WP_Error|array $data */
		if ( is_wp_error( $data ) ) {
			$result['errorCode'] = $data->get_error_code();
			$result['message']   = $data->get_error_message();
			if ( $data->get_error_data() ) {
				$result['payload'] = $data->get_error_data();
			}
		} else {
			$result['payload'] = $data;
		}

		$response['data'] = $result;

		wp_send_json( $response, $status_code, $options );
	}
}

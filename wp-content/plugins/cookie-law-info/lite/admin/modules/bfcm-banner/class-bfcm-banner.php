<?php
/**
 * Class BFCM_Banner file.
 *
 * @package CookieYes
 */

namespace CookieYes\Lite\Admin\Modules\Bfcm_banner;

use CookieYes\Lite\Includes\Modules;
use CookieYes\Lite\Includes\Notice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles BFCM Banner Operation
 *
 * @class       Bfcm_banner
 * @version     3.0.0
 * @package     CookieYes
 */
class Bfcm_banner extends Modules {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cky/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/settings/notices/bfcm_banner';

	private static $instance;

	/**
	 * Return the current instance of the class
	 *
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register REST API routes
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_banner_status' ),
					'permission_callback' => array( $this, 'permission_check' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'dismiss_notice' ),
					'permission_callback' => array( $this, 'permission_check' ),
				),
			)
		);
	}

	/**
	 * Get banner status
	 *
	 * @return WP_REST_Response
	 */
	public function get_banner_status() {
		$result = $this->check_condition();
		if ( is_array( $result ) ) {
			return new \WP_REST_Response( $result, 200 );
		}
		return new \WP_REST_Response( array( 'show' => false ), 200 );
	}

	/**
	 * Handle notice dismissal
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function dismiss_notice( $request ) {
		$expiry = $request->get_param( 'expiry' );
		$notice = Notice::get_instance();
		$notice->dismiss( 'bfcm_banner', $expiry );
		return new \WP_REST_Response( array( 'success' => true ), 200 );
	}

	/**
	 * Check permissions
	 *
	 * @return boolean
	 */
	public function permission_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct('bfcm_banner');
	}

	/**
	 * Initialize the class
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}


	/**
	 * Check if banner should be shown
	 *
	 * @return array|boolean Returns array with 'show' and 'deadline' if should show, false otherwise
	 */
	public function check_condition() {
		$notices = Notice::get_instance()->get();
		$current_year = gmdate( 'Y' );
		$deadlines = array(
			$current_year . '-11-28', // Deadline 1: November 28
			$current_year . '-12-15', // Deadline 2: December 15
			$current_year . '-12-31', // Deadline 3: December 31 (final)
		);
		
		$current_date = gmdate( 'Y-m-d' ); // UTC format
		$final_deadline = end( $deadlines ); // December 31

		// If past final deadline (after December 31, i.e., January 1 00:00:00 UTC or later), automatically dismiss the banner
		// Note: Banner shows until end of December 31, hides at 12:00 AM on January 1
		if ( $current_date > $final_deadline ) {
			if ( ! isset( $notices['bfcm_banner'] ) ) {
				$notice = Notice::get_instance();
				$notice->dismiss( 'bfcm_banner', 0 ); // Permanent dismissal
			}
			return false;
		}

		// If manually dismissed, don't show
		if ( isset( $notices['bfcm_banner'] ) ) {
			return false;
		}

		// Determine which deadline to show based on current date
		$active_deadline = null;
		foreach ( $deadlines as $deadline ) {
			if ( $current_date <= $deadline ) {
				$active_deadline = $deadline;
				break;
			}
		}

		// If no active deadline found (shouldn't happen, but safety check)
		if ( null === $active_deadline ) {
			return false;
		}

		return array(
			'show'     => true,
			'deadline' => $active_deadline,
		);
	}
}


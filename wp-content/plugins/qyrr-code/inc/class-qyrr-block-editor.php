<?php

namespace qyrr;

/**
 * Check for Block Editor Class
 */
class QYRR_Block_Editor {

	/**
	 * Contains instance or null
	 *
	 * @var object|null
	 */
	private static $instance = null;

	/**
	 * Returns instance of QYRR_Block_Editor.
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
	 * Setting up constructor
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'use_block_editor_for_post_type', array( $this, 'enable_block_editor' ), 999999, 2 );
		add_action( 'init', array( $this, 'register_selector_block' ) );
	}

	public function enable_block_editor( $enabled, $post_type ) {
		return 'qr' === $post_type ? true : $enabled;
	}

	/**
	 * Register selector block in WordPress.
	 *
	 * @return void
	 */
	public function register_selector_block() {
		$settings = array(
			'render_callback' => array( $this, 'render_selector_block' ),
			'attributes'      => array(
				'qr_id'  => array(
					'type'    => 'string',
					'default' => 0
				),
				'size'   => array(
					'type'    => 'string',
					'default' => 200
				),
				'format' => array(
					'type'    => 'string',
					'default' => 'png'
				),
			)
		);

		register_block_type(
			'qyrr-code/qr-selector',
			array_merge(
				array(
					'editor_script' => 'qyrr-code-script',
					'editor_style'  => 'qyrr-code-style',
				),
				$settings
			)
		);
	}

	/**
	 * Returns the shortcode for a selected QR code.
	 *
	 * @param array $attributes the list attributes from the block.
	 *
	 * @return string
	 */
	public function render_selector_block( array $attributes ) {
		$qr_id     = esc_attr( $attributes['qr_id'] );
		$shortcode = '[qyrr code="' . $qr_id . '"]';

		return do_shortcode( $shortcode );
	}
}

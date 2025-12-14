<?php

namespace qyrr;

/**
 * Shortcode Class
 */
class QYRR_Shortcode {

	/**
	 * Return instance of QYRR_Shortcode
	 *
	 * @return void
	 */
	public static function get_instance() {
		new QYRR_Shortcode();
	}

	/**
	 * Constructor for QYRR_Shortcode.
	 */
	public function __construct() {
		add_shortcode( 'qyrr', array( $this, 'add_shortcode' ) );
	}

	/**
	 * Register a shortcode for qr code display.
	 *
	 * @param array $atts array of possible attributes.
	 *
	 * @return false|string|void
	 */
	public function add_shortcode( array $atts ) {

		/* check if qr code is set */
		if ( ! isset( $atts['code'] ) ) {
			return;
		}

		$uploads_directory = wp_upload_dir();
		$dir_url           = $uploads_directory['baseurl'] . DIRECTORY_SEPARATOR . 'qyrr' . DIRECTORY_SEPARATOR . $atts['code'] . DIRECTORY_SEPARATOR;
		$dir_path          = $uploads_directory['basedir'] . DIRECTORY_SEPARATOR . 'qyrr' . DIRECTORY_SEPARATOR . $atts['code'] . DIRECTORY_SEPARATOR;
		$file_name         = apply_filters( 'qyrr_file_name', 'qr-code', $atts['code'] );

		// Check file type.
		if ( file_exists( $dir_path . $file_name . '.png' ) ) {
			$image_url = $dir_url . $file_name . '.png';

		} elseif ( file_exists( $dir_path . $file_name . '.svg' ) ) {
			$image_url = $dir_url . $file_name . '.svg';

		} else {
			$image_url = '';
		}
		ob_start();
		?>

		<?php if ( ! empty( $image_url ) ) : ?>
            <div id="qr-code">
                <img src="<?php echo esc_url( $image_url ); ?>?ts=<?php echo esc_attr( time() ); ?>"/>
            </div>
		<?php endif; ?>
		<?php

		return ob_get_clean();
	}
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

/**
 * Compatibility for the Rank Math SEO plugin.
 *
 * @since 4.4.2
 *
 * @link https://wordpress.org/plugins/seo-by-rank-math/
 */
class ET_Builder_Plugin_Compat_Rank_Math_SEO extends ET_Builder_Plugin_Compat_Base {
	/**
	 * Constructor.
	 *
	 * @since 4.4.2
	 */
	public function __construct() {
		$this->plugin_id = 'seo-by-rank-math/rank-math.php';
		$this->init_hooks();
	}

	/**
	 * Hook methods to WordPress.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	public function init_hooks() {
		// Bail if there's no version found.
		if ( ! $this->get_plugin_version() ) {
			return;
		}

		add_filter( 'rank_math/sitemap/content_before_parse_html_images', [ $this, 'do_shortcode' ], 10, 2 );
	}

	/**
	 * Process Divi shortcodes in content before Rank Math parses it for HTML images.
	 *
	 * This method ensures that Divi modules, including those with icons or dynamic content,
	 * are properly rendered into HTML before Rank Math processes the content for sitemap
	 * generation. It avoids errors caused by icons or unsupported elements being misinterpreted
	 * as images.
	 *
	 * @since ??
	 *
	 * @param string $content The raw post content containing potential Divi shortcodes.
	 *
	 * @return string The processed content with shortcodes rendered into HTML.
	 */
	public function do_shortcode( $content ) {
		// Check if content includes ET shortcode.
		if ( false === strpos( $content, '[et_pb_section' ) ) {
			// None found, bye.
			return $content;
		}

		// Load modules (only once).
		if ( ! did_action( 'et_builder_ready' ) ) {
			et_builder_init_global_settings();
			et_builder_add_main_elements();
		}

		// Render the shortcode.
		return apply_filters( 'the_content', $content );
	}
}

new ET_Builder_Plugin_Compat_Rank_Math_SEO();

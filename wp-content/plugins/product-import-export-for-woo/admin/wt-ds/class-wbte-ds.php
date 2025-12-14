<?php
/**
 *  WebToffee DS main class file.
 *
 *  @since 1.0.0
 *  @package Wt_Ds
 */

namespace Wbte\Pimpexp\Ds;

if ( ! defined( 'WPINC' ) ) {
	die;
}

use Wbte\Pimpexp\Ds\Classes\Wt_Ds_Template_Engine as Template_Engine;

/**
 *  To render the component.
 */
class Wbte_Ds {

	/**
	 * Plugin specific prefix.
	 *
	 * @var string
	 */
	private $prefix = 'wbte_pimpexp_';

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * To store template processing class object.
	 *
	 * @var null|object
	 */
	private $engine = null;

	/**
	 * To save the component data loaded from the config JSON.
	 *
	 * @var array
	 */
	private $config = array();

	/**
	 * Base path. To include files.
	 *
	 * @var string
	 */
	private $base_path = '';

	/**
	 * Base URL. To link CSS, JS files.
	 *
	 * @var string
	 */
	private $base_url = '';

	/**
	 * To store class object.
	 *
	 * @var null|object
	 */
	private static $instance = null;

	/**
	 * Image relative path with respect to wp-content directory.
	 *
	 * @var string
	 */
	public $img_base_path = '';

	/**
	 * Icon relative path with respect to wp-content directory.
	 *
	 * @var string
	 */
	public $icon_base_path = '';


	/**
	 * Initiate the class.
	 *
	 * @param string $version  Plugin version.
	 * @param string $prefix   Plugin prefix(Optional).
	 */
	public function __construct( $version, $prefix = '' ) {

		$this->base_path = plugin_dir_path( __FILE__ );
		$this->base_url  = plugin_dir_url( __FILE__ );

		// Normalize paths for consistent handling across operating systems.
		$wp_content_dir_normalized = wp_normalize_path( WP_CONTENT_DIR );
		$base_path_normalized      = wp_normalize_path( $this->base_path );

		// Preparing icon base path with respect to wp content directory.
		$this->icon_base_path = str_replace( $wp_content_dir_normalized, '', $base_path_normalized . 'icons/' );

		// Preparing image base path with respect to wp content directory.
		$this->img_base_path = str_replace( $wp_content_dir_normalized, '', $base_path_normalized . 'images/' );

		include_once $this->base_path . 'classes/class-wt-ds-template-engine.php';

		$this->prefix  = $prefix ? $prefix : $this->prefix; // This is for plugins to use custom prefixes.
		$this->version = $version; // Plugin version.

		$this->load_config(); // Read the json file and store it as an array.

		// Alter kses allowed.
		add_filter( 'wp_kses_allowed_html', array( $this, 'kses_allowed_html' ), 10, 2 );

		// Load the CSS and JS.
		add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_and_scripts' ), 10 );

		$this->engine = new Template_Engine( $this->prefix ); // Initiate the template parsing class.
	}


	/**
	 * Get class instance.
	 *
	 * @param string $prefix   Plugin prefix(Optional).
	 */
	public static function get_instance( $prefix = '' ) {
		self::$instance = is_null( self::$instance ) ? new Wbte_Ds( $prefix ) : self::$instance;
		return self::$instance;
	}


	/**
	 * Read the config JSON and store it as class variable.
	 */
	protected function load_config() {

		$config_file = $this->base_path . 'wbte-ds-config.json';

		if ( file_exists( $config_file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$contents     = file_get_contents( $config_file );
			$this->config = $this->is_valid_json( $contents ) ? json_decode( $contents, true ) : array();
		}
	}


	/**
	 *  Enqueue CSS and JS.
	 *
	 *  @access public
	 */
	public function load_styles_and_scripts() {

		wp_enqueue_style( $this->prefix . 'ds_css', $this->base_url . 'css/style.css', array(), $this->version, 'all' );
		wp_enqueue_script( $this->prefix . 'ds_js', $this->base_url . 'js/script.js', array( 'jquery' ), $this->version, false );

		// Localize the script.
		$params = array(
			'icon_base_url' => esc_url( WP_CONTENT_URL . $this->icon_base_path ),
			'img_base_url'  => esc_url( WP_CONTENT_URL . $this->img_base_path ),
		);
		wp_localize_script( $this->prefix . 'ds_js', $this->prefix . 'ds_js_params', $params );
	}


	/**
	 * Checks the current JSON string is valid.
	 *
	 *  @param  string $json  JSON string.
	 */
	protected function is_valid_json( $json ) {

		if ( function_exists( 'json_validate' ) ) { // For PHP 8.3+.
			return json_validate( $json );
		} else {
			json_decode( $json );
			return ( json_last_error() === JSON_ERROR_NONE );
		}
	}


	/**
	 *  Fetch component template HTML from template file.
	 *
	 *  @param  string $slug  Component slug.
	 */
	protected function fetch_template_html( $slug ) {
		$file_path = $this->base_path . 'views/' . $slug . '.html';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return file_exists( $file_path ) ? file_get_contents( $file_path ) : '';
	}


	/**
	 *  Prepare variation component slug.
	 *
	 *  @param  string $parent_slug  Parent component slug.
	 *  @param  string $slug         Component slug.
	 */
	public function prepare_variation_slug( $parent_slug, $slug ) {
		return $parent_slug . '-' . $slug;
	}

	/**
	 *  Get the component HTML.
	 *
	 *  @param   string $slug_str  Component slug. If multiple slugs, separate by space. First one will be the parent and others are its attributes.
	 *  @param   array  $args      Array of arguments.
	 *  @param   object $engine    Template engine object(Optional).
	 *  @return  string   Component HTML or empty string.
	 */
	public function get_component( $slug_str, $args, $engine = null ) {

		$html              = '';
		$slugs             = array();
		$slug_arr          = explode( ' ', $slug_str ); // If multiple slugs. Assuming the first one will be the parent and others are its attributes.
		$parent_slug       = array_shift( $slug_arr );
		$components_config = isset( $this->config['components'] ) && is_array( $this->config['components'] ) ? $this->config['components'] : array();

		if ( ! empty( $components_config[ $parent_slug ] ) ) { // Component found.
			$slugs[]     = $parent_slug;
			$parent_comp = $components_config[ $parent_slug ];
			$html        = $this->fetch_template_html( $parent_slug );
			$attributes  = ! empty( $parent_comp['attributes'] ) ? $parent_comp['attributes'] : array();

			foreach ( $slug_arr as $slug ) {
				// Prepare the attribute slug.
				$attr_slug = $this->prepare_variation_slug( $parent_slug, $slug );
				if ( ! empty( $attributes[ $attr_slug ] ) ) { // Attribute found.

					$slugs[]   = $attr_slug;
					$attr_data = $attributes[ $attr_slug ];
					$attr_html = $this->fetch_template_html( $attr_slug );

					// Replace or append the HTML.
					if ( ! empty( $attr_data['replace_html'] ) && ! empty( $attr_html ) ) {
						$html = $attr_html; // Replace the HTML.
					} else {
						$html .= $attr_html; // Append the HTML.
					}
				}
			}

			// Generate HTML for pagination component.
			if ( 'pagination' === $parent_slug ) {
				$pagination_comp_html_args         = isset( $args['values'] ) && is_array( $args['values'] ) ? $args['values'] : array();
				$pagination_comp_html_args['html'] = $html;
				$html                              = $this->prepare_pagination_component_html( $pagination_comp_html_args );
			}

			$args['html']       = $html;
			$args['variations'] = $slugs;
			$args['parent_obj'] = $this;
			$args['icon_base']  = $this->icon_base_path;
			$args['img_base']   = $this->img_base_path;

			// Separate template engine object will be provided when component is rendered via component placeholder. This is to isolate the component from its parent component.
			$html = is_null( $engine ) ? $this->engine->render( $args ) : $engine->render( $args );

			return $html;
		}

		return '';
	}


	/**
	 *  Get absolute URL of assets like icons, images.
	 *  This will be useful when using DS assets directly.
	 *
	 *  @param   array $args      Array of arguments.
	 *  @return  string     Absolute URL of the asset. Empty string if the file not found.
	 */
	public function get_asset( $args ) {
		$type = isset( $args['type'] ) ? $args['type'] : '';
		$name = isset( $args['name'] ) ? $args['name'] : '';
		if ( 'icon' === $type && file_exists( WP_CONTENT_DIR . $this->icon_base_path . $name . '.svg' ) ) {
			return WP_CONTENT_URL . $this->icon_base_path . $name . '.svg';
		} elseif ( 'image' === $type && file_exists( WP_CONTENT_DIR . $this->img_base_path . $name ) ) {
			return WP_CONTENT_URL . $this->img_base_path . $name;
		}
		return '';
	}


	/**
	 *  Get the component from component placeholders.
	 *  This is using in inner component situations.
	 *
	 *  @param   array $args      Array of arguments.
	 *  @return  string     Component HTML or empty string.
	 */
	public function render_component_placeholder( $args ) {
		$html  = '';
		$slugs = isset( $args['slug'] ) && is_array( $args['slug'] ) ? $args['slug'] : array();

		if ( ! empty( $slugs ) ) {
			$slug_str = implode( ' ', $slugs );
			unset( $args['slug'] );

			// This is to isolate the current component from other components.
			$engine = new Template_Engine( $this->prefix );

			$html = $this->get_component( $slug_str, $args, $engine );

			unset( $engine );
		}

		return $html;
	}


	/**
	 * Prepare the HTML for pagination component.
	 *
	 * @param array $args The arguments for preparing the pagination component HTML.
	 * @return string The HTML for the pagination component.
	 */
	private function prepare_pagination_component_html( $args ) {

		$total  = $args['total'] ?? 0;
		$limit  = $args['limit'] ?? 0;
		$mxnav  = $args['max_nav'] ?? 5;
		$crpage = $args['current_page'] ?? 1;
		$html   = $args['html'] ?? '';
		$url    = $args['url'] ?? '';

		if ( $total <= 0 || $limit <= 0 ) {
			return '';
		}

		if ( isset( $args['offset'] ) ) {
			// Taking current page.
			$offset    = $args['offset'];
			$crpage    = floor( ( $offset + $limit ) / $limit );
			$url_param = '{offset}';
		} else {
			$url_param = '{pagenum}';
		}

		$limit = $limit <= 0 ? 1 : $limit;
		$ttpg  = ceil( $total / $limit );
		if ( $ttpg < $crpage ) {
			return '';
		}

		// Pagination calculations.
		$mxnav      = $ttpg < $mxnav ? $ttpg : $mxnav;
		$mxnav_mid  = floor( $mxnav / 2 );
		$pgstart    = $mxnav_mid >= $crpage ? 1 : $crpage - $mxnav_mid;
		$mxnav_mid += $mxnav_mid >= $crpage ? ( $mxnav_mid - $crpage ) : 0;  // Adjusting the other half with the first half balance.
		$pgend      = $crpage + $mxnav_mid;
		if ( $pgend > $ttpg ) {
			$pgend = $ttpg;
		}

		$find_replace = array();

		// Pagination attributes.
		$pagination_attr = ' data-offset="{offset}" data-pagenum="{pagenum}"' . ( $url ? ' href="' . esc_url( $url ) . $url_param . '"' : '' );

		// Previous button.
		$prev_offset_attr = '';
		if ( $crpage > 1 ) {
			$offset           = ( ( $crpage - 2 ) * $limit );
			$prev_offset_attr = str_replace( array( '{offset}', '{pagenum}' ), array( $offset, ( $crpage - 1 ) ), $pagination_attr );
		}
		$find_replace['{{prev_button}}'] = '<a class="' . ( $crpage <= 1 ? 'disabled' : 'pagenav' ) . '"' . $prev_offset_attr . '>{{wbte-ds-icon-left-arrow-2}}</a>';

		// First page.
		if ( 1 < $pgstart ) {
			$page_offset_attr               = str_replace( array( '{offset}', '{pagenum}' ), array( 0, 1 ), $pagination_attr );
			$find_replace['{{first_page}}'] = '<a class="pagenav" ' . $page_offset_attr . '>1</a>';
		}

		// First dots.
		if ( 2 < $pgstart ) {
			$find_replace['{{first_dots}}'] = '<a class="dots">...</a>';
		}

		// Menu numbers.
		$find_replace['{{numbers}}'] = '';
		for ( $i = $pgstart; $i <= $pgend; $i++ ) {
			$i                = (int) $i;
			$page_offset      = '';
			$page_offset_attr = '';
			$offset           = ( $i * $limit ) - $limit;
			if ( $i !== $crpage ) {
				$page_offset_attr = str_replace( array( '{offset}', '{pagenum}' ), array( $offset, $i ), $pagination_attr );
			}
			$find_replace['{{numbers}}'] .= '<a class="' . ( $i === $crpage ? 'current' : 'pagenav' ) . '" ' . $page_offset_attr . '>' . $i . '</a>';
		}

		// Last dots.
		if ( $pgend <= ( $ttpg - 2 ) ) {
			$find_replace['{{last_dots}}'] = '<a class="dots">...</a>';
		}

		// Last page.
		if ( $pgend < $ttpg ) {
			$page_offset_attr              = str_replace( array( '{offset}', '{pagenum}' ), array( ( ( $ttpg - 1 ) * $limit ), $ttpg ), $pagination_attr );
			$find_replace['{{last_page}}'] = '<a class="pagenav" ' . $page_offset_attr . '>' . $ttpg . '</a>';
		}

		// Next button.
		$next_offset_attr = '';
		if ( $crpage < $ttpg ) {
			$offset           = ( $crpage * $limit );
			$next_offset_attr = str_replace( array( '{offset}', '{pagenum}' ), array( $offset, ( $crpage + 1 ) ), $pagination_attr );
		}

		$find_replace['{{next_button}}'] = '<a class="' . ( $crpage < $ttpg ? 'pagenav' : 'disabled' ) . '"' . $next_offset_attr . '>{{wbte-ds-icon-right-arrow-3}}</a>';
		return str_replace( array_keys( $find_replace ), array_values( $find_replace ), $html );
	}

	/**
	 *  Render the HTML.
	 *
	 *  @param   array $args      Array of arguments.
	 *  @return  string     Rendered HTML.
	 */
	public function render_html( $args ) {
		$args               = is_array( $args ) ? $args : array();
		$args['html']       = $args['html'] ?? '';
		$args['variations'] = $args['variations'] ?? array();
		$args['parent_obj'] = $this;
		$args['icon_base']  = $this->icon_base_path;
		$args['img_base']   = $this->img_base_path;

		return $this->engine->render( $args );
	}

	/**
	 *  Alter kses allowed HTML.
	 *
	 *  @param   array  $allowedposttags  Allowed tags.
	 *  @param   string $context          Context.
	 *  @return  array  Allowed tags.
	 */
	public function kses_allowed_html( $allowedposttags, $context ) {

		if ( 'post' === $context ) {
			// Define SVG tags and their safer allowed attributes.
			$svg_tags = array(
				'svg'      => array(
					'xmlns'               => true,
					'width'               => true,
					'height'              => true,
					'viewBox'             => true,
					'fill'                => true,
					'stroke'              => true,
					'stroke-width'        => true,
					'preserveAspectRatio' => true,
				),
				'g'        => array(
					'fill'         => true,
					'stroke'       => true,
					'stroke-width' => true,
				),
				'path'     => array(
					'd'            => true,
					'fill'         => true,
					'stroke'       => true,
					'stroke-width' => true,
				),
				'circle'   => array(
					'cx'           => true,
					'cy'           => true,
					'r'            => true,
					'fill'         => true,
					'stroke'       => true,
					'stroke-width' => true,
				),
				'rect'     => array(
					'x'            => true,
					'y'            => true,
					'width'        => true,
					'height'       => true,
					'fill'         => true,
					'stroke'       => true,
					'stroke-width' => true,
				),
				'line'     => array(
					'x1'           => true,
					'y1'           => true,
					'x2'           => true,
					'y2'           => true,
					'stroke'       => true,
					'stroke-width' => true,
				),
				'polygon'  => array(
					'points'       => true,
					'fill'         => true,
					'stroke'       => true,
					'stroke-width' => true,
				),
				'polyline' => array(
					'points'       => true,
					'fill'         => true,
					'stroke'       => true,
					'stroke-width' => true,
				),
				'text'     => array(
					'x'            => true,
					'y'            => true,
					'dx'           => true,
					'dy'           => true,
					'text-anchor'  => true,
					'fill'         => true,
					'stroke'       => true,
					'stroke-width' => true,
				),
			);

			$allowedposttags = array_merge( $allowedposttags, $svg_tags );
		}

		return $allowedposttags;
	}
}

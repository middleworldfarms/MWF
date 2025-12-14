<?php
/**
 *  Template processing class file.
 *
 *  @since 1.0.0
 *  @package Wt_Ds
 */

namespace Wbte\Pimpexp\Ds\Classes;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 *  To process the templates.
 */
class Wt_Ds_Template_Engine {

	/**
	 * For placeholders.
	 *
	 * @var array   Associative array.
	 */
	protected $variables = array();

	/**
	 * Plugin specific prefix. This will be added to class names.
	 *
	 * @var string
	 */
	protected $prefix = '';


	/**
	 * Image base URL
	 *
	 * @var string
	 */
	protected $img_base_url = '';

	/**
	 * Icon base URL
	 *
	 * @var string
	 */
	protected $icon_base_url = '';

	/**
	 * Icon base path
	 *
	 * @var string
	 */
	protected $icon_base_path = '';

	/**
	 * Regular expression to search icon placeholder.
	 *
	 * @var string
	 */
	protected $icon_regex = '/wbte-ds-icon-([^}]*)/';


	/**
	 * To store render function arguments.
	 * This value is using in template rendering section.
	 *
	 * @var array
	 */
	protected $args = array();


	/**
	 * This is to store placeholders and its processed values.
	 *
	 * @var array
	 */
	protected $find_replace = array();

	/**
	 * Boolean attributes.
	 *
	 * @var array $boolean_attributes
	 */
	private static $boolean_attributes = array( 'checked', 'disabled', 'readonly', 'required', 'autofocus', 'multiple' );

	/**
	 *  Initiate the class
	 *
	 *  @param  string $prefix     Plugin specific prefix.
	 */
	public function __construct( $prefix ) {
		$this->prefix = $prefix;
	}


	/**
	 *  Process the template.
	 *  This method will replace placeholder.
	 *  Add CSS classes.
	 *  Add custom attributes and CSS classes, If given.
	 *
	 *  $args = array(
	 *      'html'          => '<div data-main="1" data-id="{{wbte-ds-attr-data-id}}" data-overlay="{{wbte-ds-attr-data-overlay}}">
	 *                              <div data-class="popup-head">{{popup_title}}<div data-class="popup-close"><img src="{{wbte-ds-icon-close}}"></div></div>
	 *                              {{template:popup_content}}
	 *                          </div>', // Component HTML (Mandatory).
	 *      'variations'    => array('popup', 'popup-large'), // Slugs (Mandatory).
	 *      'values'        => array( // (Optional).
	 *                              'popup_title'   => 'Popup',
	 *                              'data-id'       => 'abcd',
	 *                              'data-overlay'  => 1,
	 *                              'template'      => array(
	 *                                                      'popup_content' => 'absolute_template_path',
	 *                                                  ),
	 *                          ), // Placeholder values. Template values.
	 *      'icon_base'     => 'wbte-ds/icons/', // Icon relative path with respect to wp-content directory (Optional. Mandatory when any icon dependencies are there).
	 *      'img_base'      => 'wbte-ds/images/', // Image relative path with respect to wp-content directory (Optional. Mandatory when any image dependencies are there).
	 *      'attr'          => array('id' => 'popup_id') // Cutsom attributes (Optional).
	 *      'class'         => array('popup_custom_css') // Cutsom CSS classes (Optional).
	 *  );
	 *
	 *  @param  string $args  Plugin specific prefix.
	 *  @return string $html  Processed HTML of the component.
	 */
	public function render( $args ) {

		$this->args = $args; // The same argument is using in template rendering section.

		// Append a wrapping element. This is for elements without a parent element. This will be removed while returning.
		$html = '<root>' . $args['html'] . '</root>';

		// Values to replace placeholders.
		$this->variables = isset( $args['values'] ) && is_array( $args['values'] ) ? $args['values'] : array();

		$icon_base            = trim( isset( $args['icon_base'] ) ? $args['icon_base'] : 'wbte-ds/icons' );
		$icon_base            = ( isset( $icon_base[0] ) && '/' !== $icon_base[0] ? '/' . $icon_base : $icon_base );
		$this->icon_base_url  = trailingslashit( WP_CONTENT_URL . $icon_base );
		$this->icon_base_path = trailingslashit( WP_CONTENT_DIR . $icon_base );

		$img_base           = trim( isset( $args['img_base'] ) ? $args['img_base'] : 'wbte-ds/images' );
		$img_base           = ( isset( $img_base[0] ) && '/' !== $img_base[0] ? '/' . $img_base : $img_base );
		$this->img_base_url = trailingslashit( WP_CONTENT_URL . $img_base );

		$doc = new \DOMDocument();
		$doc->encoding = 'UTF-8'; // Add encoding specification
		libxml_use_internal_errors( true );

		$html = '<?xml encoding="UTF-8">' . 
           '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . 
           $html;

		$doc->loadHTML( $html ); // , LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		libxml_clear_errors();

		$xpath = new \DOMXPath( $doc );

		// Process data-for attributes.
		$for_nodes = $xpath->query( '//*[@data-for]' );
		foreach ( $for_nodes as $node ) {
			$loop_data = $node->getAttribute( 'data-for' );
			$node->removeAttribute( 'data-for' );
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$node->parentNode->replaceChild( $this->process_loop( $doc, $node, $loop_data ), $node );
		}

		// Reassign args.
		$this->args = $args;

		// Process data-if attributes.
		$if_nodes = $xpath->query( '//*[@data-if]' );
		foreach ( $if_nodes as $node ) {
			$condition = $node->getAttribute( 'data-if' );
			$node->removeAttribute( 'data-if' );
			if ( ! $this->process_conditions( $condition ) ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$node->parentNode->removeChild( $node );
			}
		}

		// Process classes, attributes.
		$this->process_class_and_attr( $xpath );

		// Check and replace icon src.
		$this->process_icon_srcs( $xpath );

		// Check and replace image src.
		$this->process_img_srcs( $xpath );

		// Process placeholders of variables, icons, templates, components.
		$this->process_placeholders( $xpath );

		$root_elms = $doc->getElementsByTagName( 'root' );
		$html      = $this->dom_inner_html( $root_elms[0] );

		// Replace placeholders.
		return str_replace( array_keys( $this->find_replace ), array_values( $this->find_replace ), $html );
	}

	/**
	 *  Process and add CSS classes and custom attributes.
	 *
	 *  @param  DOMXPath $xpath   DOMXPath.
	 */
	protected function process_class_and_attr( $xpath ) {

		$args = $this->args;

		// Variations or slug classes.
		$variations = isset( $args['variations'] ) && is_array( $args['variations'] ) ? $args['variations'] : array();

		$main_element_class_added = 0;

		$nodes = $xpath->query( '//*[@data-class]' );

		if ( is_object( $nodes ) && $nodes->length > 0 ) {

			foreach ( $nodes as $node ) {
				// These classes are DS specific classes so need to add the prefix.
				$css_classes = $this->process_css_class_string( $node, 'data-class' );
				$new_classes = array();

				if ( ! empty( $css_classes ) ) {

					foreach ( $css_classes as $css_class ) {
						$css_class = trim( $css_class );
						if ( $css_class ) {
							$new_classes[] = $this->prefix . $css_class;
						}
					}

					$node->removeAttribute( 'data-class' );
				}

				if ( $node->getAttribute( 'data-main' ) ) {

					if ( ! empty( $variations ) ) {
						foreach ( $variations as $variation ) {
							$variation = trim( $variation );
							if ( $variation ) {
								$new_classes[] = $this->prefix . $variation;
							}
						}

						// Any additional CSS class to add.
						$new_classes = isset( $args['class'] ) && is_array( $args['class'] ) ? array_merge( $new_classes, $args['class'] ) : $new_classes;

						$main_element_class_added = 1;
					}

					$node->removeAttribute( 'data-main' );

					// Add additional attributes.
					$this->add_attr( $node, $args );
				}

				$outer_csses = $this->process_css_class_string( $node, 'class' ); // Any external CSS classes.
				$new_classes = array_merge( $outer_csses, $new_classes );

				if ( ! empty( $new_classes ) ) {
					$node->setAttribute( 'class', esc_attr( implode( ' ', $new_classes ) ) );
				}
			}
		}

		if ( 0 === $main_element_class_added ) { // CSS not added to main element. May be `data-class` not available to main element.

			$nodes = $xpath->query( '//*[@data-main]' );

			if ( is_object( $nodes ) && $nodes->length > 0 ) {
				foreach ( $nodes as $node ) {
					$new_classes = array();
					if ( ! empty( $variations ) ) {
						foreach ( $variations as $variation ) {
							$variation = trim( $variation );
							if ( $variation ) {
								$new_classes[] = $this->prefix . $variation;
							}
						}
					}

					// Any additional CSS class to add.
					$new_classes = isset( $args['class'] ) && is_array( $args['class'] ) ? array_merge( $new_classes, $args['class'] ) : $new_classes;

					$outer_csses = $this->process_css_class_string( $node, 'class' ); // Any external CSS classes.
					$new_classes = array_merge( $outer_csses, $new_classes );

					if ( ! empty( $new_classes ) ) {
						$node->setAttribute( 'class', esc_attr( implode( ' ', $new_classes ) ) );
					}
					$node->removeAttribute( 'data-main' );

					// Add additional attributes.
					$this->add_attr( $node, $args );
				}
			}
		}

		// Add conditional attributes.
		$nodes = $xpath->query( '//*[@*[starts-with(name(), "data-bind-")]]' );

		if ( is_object( $nodes ) && $nodes->length > 0 ) {

			foreach ( $nodes as $node ) {

				// CSS Class.
				$bind_class      = $node->getAttribute( 'data-bind-class' );
				$bind_data_class = $node->getAttribute( 'data-bind-data-class' );

				if ( $bind_class || $bind_data_class ) { // If any of the class attribute exists.

					$class_arr = $this->process_css_class_string( $node, 'class' ); // Any existing CSS classes.

					if ( $bind_data_class ) {
						$bind_data_class = $this->remove_braces( $bind_data_class );
						$class_arr       = $this->process_conditional_css_classes( $class_arr, $bind_data_class, $this->prefix );
						$node->removeAttribute( 'data-bind-data-class' );
					}

					if ( $bind_class ) {
						$bind_class = $this->remove_braces( $bind_class );
						$class_arr  = $this->process_conditional_css_classes( $class_arr, $bind_class );
						$node->removeAttribute( 'data-bind-class' );
					}

					if ( ! empty( $class_arr ) ) {
						$node->setAttribute( 'class', esc_attr( implode( ' ', $class_arr ) ) );
					}
				}

				// href attribute.
				$bind_href = $node->getAttribute( 'data-bind-href' );
				if ( $bind_href ) {
					$href = $this->render_variable_values( trim( $bind_href ) );
					if ( $href ) {
						$node->setAttribute( 'href', esc_url( $href ) );
					}
					$node->removeAttribute( 'data-bind-href' );
				}

				// String attributes.
				$string_attributes = array( 'id', 'name', 'target', 'placeholder', 'data-id' );
				foreach ( $string_attributes as $attr ) {
					$this->add_string_optional_attr( $node, 'data-bind-' . $attr );
				}

				// Boolean attributes.
				foreach ( self::$boolean_attributes as $attr ) {
					$this->add_bool_optional_attr( $node, 'data-bind-' . $attr );
				}
			}
		}

		// Process component attribute placehoders.
		$attributes = $xpath->query( '//@*[contains(., "{{wbte-ds-attr-") or contains(., "%7B%7Bwbte-ds-attr-")]' );

		if ( is_object( $attributes ) ) {
			$regex = '/{{wbte-ds-attr-([^}]*)}}/';
			foreach ( $attributes as $attribute ) {

				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$node_value = preg_match( '/href|src|action/', $attribute->name ) ? urldecode( $attribute->nodeValue ) : $attribute->nodeValue;

				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				if ( preg_match( $regex, $node_value, $matches ) ) {
					$attr_value = $this->render_variable_values( $matches[1] );

					if ( ! in_array( $attribute->nodeName, self::$boolean_attributes, true ) && empty( $attr_value ) ) {
						$attribute->ownerElement->removeAttribute( $attribute->nodeName );
						continue;
					}

					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$attribute->nodeValue = ( 'src' === $attribute->nodeName || 'href' === $attribute->nodeName ) ? esc_url( $attr_value ) : esc_attr( $attr_value );
				} else {
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$attribute->nodeValue = '';
				}
			}
		}
	}


	/**
	 *  Add string type optional attributes.
	 *  Example: data-bind-target, data-bind-id.
	 *
	 *  @param  object $node   Dom object.
	 *  @param  string $attr   Attribute name.
	 *  @return void
	 */
	private function add_string_optional_attr( $node, $attr ) {
		$bind_attr = $node->getAttribute( $attr );
		if ( $bind_attr ) {
			$attr_vl = $this->render_variable_values( trim( $bind_attr ) );
			if ( $attr_vl ) {
				$node->setAttribute( str_replace( 'data-bind-', '', $attr ), esc_attr( $attr_vl ) );
			}
			$node->removeAttribute( $attr );
		}
	}


	/**
	 *  Add bool type optional attributes.
	 *  Example: data-bind-checked, data-bind-disabled, data-bind-readonly, data-bind-required, data-bind-multiple, data-bind-autofocus.
	 *
	 *  @param  object $node   Dom object.
	 *  @param  string $attr   Attribute name.
	 *  @return void
	 */
	private function add_bool_optional_attr( $node, $attr ) {

		$bind_attr = $node->getAttribute( $attr );
		if ( $bind_attr ) {
			$attr_vl = $this->render_variable_values( trim( $bind_attr ) );
			if ( $attr_vl && ( '1' === $attr_vl || 1 === $attr_vl || true === $attr_vl || 'true' === strtolower( $attr_vl ) ) ) {
				$attr_name = str_replace( 'data-bind-', '', $attr );
				$node->setAttribute( $attr_name, $attr_name );
			}
			$node->removeAttribute( $attr );
		}
	}


	/**
	 *  This method is used to remove curly braces from conditional attributes.
	 *
	 *  @param  string $attr_vl   Value of conditional attribute.
	 *  @return string     $attr_vl   Processed value of conditional attribute.
	 */
	protected function remove_braces( $attr_vl ) {
		return str_replace( array( '{', '}' ), '', $attr_vl );
	}


	/**
	 *  This method will process the dynamic binding of CSS classes.
	 *
	 *  @param  string[] $class_arr   CSS class name array.
	 *  @param  string   $class_str   CSS classes with condition string from data-bind attribute.
	 *  @param  string   $prefix      Optional. Prefix. This will be applicable for DS specific CSS classes added via data-class attribute.
	 *  @return string[]   $class_arr   Processed CSS class name array.
	 */
	protected function process_conditional_css_classes( $class_arr, $class_str, $prefix = '' ) {

		$class_bindings = explode( ',', $class_str );

		foreach ( $class_bindings as $class_binding ) {
			list($class_name, $condition) = explode( ':', $class_binding );
			$class_name                   = trim( trim( $class_name ), '\'\"' );
			if ( ! empty( $this->render_variable_values( trim( $condition ) ) ) ) {
				$class_arr[] = $prefix . $class_name;
			}
		}

		return $class_arr;
	}


	/**
	 *  Process icon URLs for HTML attribute
	 *
	 *  @param  DOMXPath $xpath   DOMXPath.
	 */
	protected function process_icon_srcs( $xpath ) {
		$icons = $xpath->query( '//@*[contains(., "{{wbte-ds-icon-")]' );

		if ( is_object( $icons ) ) {
			foreach ( $icons as $icon ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				if ( preg_match( $this->icon_regex, $icon->nodeValue, $matches ) ) {
					$icon_name = $this->prepare_icon_name( $matches[1] ); // Check there is a name or a variable.
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$icon->nodeValue = $icon_name ? esc_url( $this->icon_base_url . $icon_name . '.svg' ) : '';
				}
			}
		}
	}


	/**
	 *  Process image URLs for HTML attribute
	 *
	 *  @param  DOMXPath $xpath   DOMXPath.
	 */
	protected function process_img_srcs( $xpath ) {
		$imgs = $xpath->query( '//@*[contains(., "{{wbte-ds-img-")]' );

		if ( is_object( $imgs ) ) {
			foreach ( $imgs as $img ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				if ( preg_match( '/wbte-ds-img-([^}]*)/', $img->nodeValue, $matches ) ) {
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$img->nodeValue = esc_url( $this->img_base_url . $matches[1] );
				}
			}
		}
	}


	/**
	 *  Take text node and check for placeholders.
	 *  This method will handle variables, templates, icons(not src), components.
	 *
	 *  @param  DOMXPath $xpath   DOMXPath.
	 */
	protected function process_placeholders( $xpath ) {
		$text_nodes = $xpath->query( '//text()' );
		$texts      = '';
		foreach ( $text_nodes as $text_node ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$texts .= $text_node->nodeValue;
		}

		$matches = array();

		if ( preg_match_all( '/{{\s*([^}]*)\s*}}/', $texts, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $key => $match ) {
				$this->find_replace[ $match[0] ] = $this->parse_placeholder_values( $match[1] );
			}
		}
	}


	/**
	 *  Get inner HTML of a dom node.
	 *
	 *  @param  object $element      Dom object.
	 *  @return string  $inner_html   Inner HTML of the element.
	 */
	protected function dom_inner_html( $element ) {
		$inner_html = '';
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$children = $element->childNodes;

		foreach ( $children as $child ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$inner_html .= $element->ownerDocument->saveHTML( $child );
		}

		// Correct self-closing tags.
		$self_closing_tags = array( 'input', 'img', 'br', 'hr', 'meta', 'link', 'base', 'area', 'col', 'embed', 'param', 'source', 'track', 'wbr' );
		foreach ( $self_closing_tags as $tag ) {
			$inner_html = preg_replace( '/<' . $tag . '([^>]*)(?<!\/)>/i', '<' . $tag . '$1 />', $inner_html );
		}

		// Replace simplified boolean attributes with their full form.
		foreach ( self::$boolean_attributes as $attribute ) {
			$inner_html = preg_replace(
				'/(?<=\s|<|\b)' . $attribute . '(?=\s|>|\/)/i',
				$attribute . '="' . $attribute . '"',
				$inner_html
			);
		}

		return $inner_html;
	}

	/**
	 *  Evaluate the individual condition checks.
	 *  This is a sub function for below `process_conditions`.
	 *
	 *  @param  string $condition  Condition string.
	 *  @return bool    The condition is true or false.
	 */
	private function evaluate_condition( $condition ) {
		if ( preg_match( '/^\s*([\w.]+)\s*$/', $condition, $matches ) ) {
			return ! empty( $this->render_variable_values( $matches[1] ) );
		}
		if ( preg_match( '/^\s*!([\w.]+)\s*$/', $condition, $matches ) ) {
			return empty( $this->render_variable_values( $matches[1] ) );
		}

		$out               = false;
		$allowed_operators = array( '==', '===', '!==', '>', '<', '>=', '<=' );

		if ( preg_match( '/^\s*([\'"]?[\w.]+[\'"]?)\s*(==|===|!==|>|<|>=|<=)\s*([\'"]?[\w.]+[\'"]?)\s*$/', $condition, $matches )
			&& in_array( $matches[2], $allowed_operators, true )
		) {
			$var1 = $this->render_variable_values( $matches[1] );
			$var2 = $this->render_variable_values( $matches[3] );

			switch ( $matches[2] ) {
				case '==':
					// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$out = $var1 == $var2;
					break;

				case '===':
					$out = $var1 === $var2;
					break;

				case '!==':
					$out = $var1 !== $var2;
					break;

				case '>':
					$out = $var1 > $var2;
					break;

				case '<':
					$out = $var1 < $var2;
					break;

				case '>=':
					$out = $var1 >= $var2;
					break;

				case '<=':
					$out = $var1 <= $var2;
					break;

				default:
					$out = false;
					break;
			}
		}

		return $out;
	}


	/**
	 *  This function will split the condition checks into simple parts and evalute using `evaluate_condition` method.
	 *
	 *  @param  string $conditions  Condition string.
	 *  @return bool    The condition is true or false.
	 */
	protected function process_conditions( $conditions ) {

		$pattern = '/\(([^()]*(?:(?R)[^()]*)*)\)/';

		if ( preg_match_all( $pattern, $conditions, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$processed_condition = $this->process_conditions( $match[1] );
				$conditions          = str_replace( $match[0], ( $processed_condition ? '1' : '0' ), $conditions );
			}
		}

		$conditions = trim( $conditions );

		if ( false !== strpos( $conditions, '&&' ) ) {
			$conditions = explode( '&&', $conditions );
				$out    = true;

			foreach ( $conditions as $condition ) {
				if ( ! $this->process_conditions( trim( $condition ) ) ) {
					$out = false;
					break;
				}
			}

				return $out;
		} elseif ( false !== strpos( $conditions, '||' ) ) {

			$conditions = explode( '||', $conditions );
			$out        = false;

			foreach ( $conditions as $condition ) {
				if ( $this->process_conditions( trim( $condition ) ) ) {
					$out = true;
					break;
				}
			}

			return $out;
		} else {
			return $this->evaluate_condition( trim( $conditions ) );
		}
	}


	/**
	 *  Process the loop HTML attributes.
	 *
	 *  @param  DOMDocument $doc        Dom document.
	 *  @param  DOMNode     $node       Dom node.
	 *  @param  string      $loop_data  Loop attribute data.
	 *  @return DOMNode|DocumentFragment
	 */
	private function process_loop( $doc, $node, $loop_data ) {

		list($variables, $array_var) = explode( ' in ', $loop_data );

		$variables = explode( ',', trim( $variables, '()' ) );
		$item_var  = trim( $variables[0] );
		$index_var = isset( $variables[1] ) ? trim( $variables[1] ) : null;

		if ( ! $array_var || ! $item_var ) {
			return $node;
		}

		$array_var_val = $this->render_variable_values( $array_var );
		if ( ! is_array( $array_var_val ) ) {
			return $node;
		}

		$fragment = $doc->createDocumentFragment();
		$node->setAttribute( 'data-main', 1 ); // This is to enable custom attribute adding via `render` function.
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$html = $node->ownerDocument->saveHTML( $node ); // HTML to repeat.

		/**
		 * Take a backup of the main variables and add it back once the loop was completed.
		 * If a variable with same name in the loop already exists in the main variables list.
		 */
		$vars_back = $this->variables;
		$new_args  = $this->args;

		foreach ( $array_var_val as $index => $item ) {

			$new_args['values'][ $item_var ] = $item; // Assign the loop value to the args variables list.

			if ( ! is_null( $index_var ) ) { // Assign the loop index to the args variables list.
				$new_args['values'][ $index_var ] = $index;
			}

			$new_args['html']       = $html;
			$new_args['variations'] = array(); // Variations not required for loop HTML.
			$new_args['attr']       = isset( $item['attr'] ) && is_array( $item['attr'] ) ? $item['attr'] : array();
			$new_args['class']      = isset( $item['class'] ) && is_array( $item['class'] ) ? $item['class'] : array();

			$fragment->appendXML( $this->render( $new_args ) );
		}

		// The `$this->variables` was overwritten by the above `$this->render`. So we are restoring the original values.
		$this->variables = $vars_back;

		return $fragment;
	}

	/**
	 *  Cleanup the class names
	 *
	 *  @param   object $node          node object.
	 *  @param   string $attr_name     Attribute name.
	 *  @return  array     $css_classes   CSS class array.
	 */
	private function process_css_class_string( $node, $attr_name ) {
		$css_classes = $node->getAttribute( $attr_name ); // These classes are DS specific classes so need to add the prefix.
		return $css_classes ? array_unique( array_filter( explode( ' ', $css_classes ) ) ) : array();
	}


	/**
	 *  Add custom attribute to the dom node.
	 *
	 *  @param  object $node   Dom object.
	 *  @param  array  $args   Arguments array.
	 */
	private function add_attr( &$node, $args ) {
		if ( isset( $args['attr'] ) && is_array( $args['attr'] ) ) {
			foreach ( $args['attr'] as $attr_key => $attr_value ) {
				$attr_key = sanitize_title( $attr_key );
				if ( $attr_key ) {
					$node->setAttribute( $attr_key, esc_attr( $attr_value ) );
				}
			}
		}
	}


	/**
	 *  This method will parse placeholder values.
	 *  Handles variables, icons, templates, components.
	 *
	 *  @param   string $text   Inner text of a text node.
	 *  @return  string   $text   Processed text.
	 */
	private function parse_placeholder_values( $text ) {
		// Check the current placeholder is need to load a template.
		if ( 'template:' === substr( $text, 0, 9 ) ) {
			return $this->process_templates( substr( $text, 9 ) );
		}

		// Check the current placeholder is need to render an HTML.
		if ( 'html:' === substr( $text, 0, 5 ) ) {
			return $this->process_html( substr( $text, 5 ) );
		}

		// Check the current placeholder is need call a filter hook.
		if ( 'filter:' === substr( $text, 0, 7 ) ) {
			return $this->process_filter( substr( $text, 7 ) );
		}

		// Check the current placeholder is an icon.
		if ( preg_match( $this->icon_regex, $text, $icon_matches ) ) {
			return $this->process_icons( $icon_matches[1] );
		}

		// Check the current placeholder is a component.
		if ( preg_match( '/wbte-ds-component\s+(.*)/', $text, $component_matches ) ) {
			return $this->process_components( $component_matches[1] );
		}

		return $this->render_variable_values( $text );
	}


	/**
	 *  Process and replace template placeholder with template HTML.
	 *
	 *  @param  string $template_key   Template key.
	 *  @return string   Processed template HTML or empty string.
	 */
	private function process_templates( $template_key ) {
		if ( isset( $this->variables['templates'][ $template_key ] ) && file_exists( $this->variables['templates'][ $template_key ] ) ) {
			ob_start();
			include $this->variables['templates'][ $template_key ];
			$html = ob_get_clean();

			$args_back             = $this->args; // Save it for main component.
			$template_args         = $this->args;
			$template_args['html'] = $html; // Add template HTML.

			// Variations are not required for templates. So unset it if exists.
			if ( isset( $template_args['variations'] ) ) {
				unset( $template_args['variations'] );
			}

			// Render the template.
			$html       = $this->render( $template_args );
			$this->args = $args_back; // Restore the saved arguments.

			return $html;
		}

		return '';
	}

	/**
	 *  Process and replace HTML placeholder with HTML.
	 *
	 *  @param  string $html_key   HTML key.
	 *  @return string   Processed HTML or empty string.
	 */
	private function process_html( $html_key ) {

		if ( isset( $this->variables['html'] ) && isset( $this->variables['html'][ $html_key ] ) ) {

			$args_back         = $this->args; // Save it for main component.
			$html_args         = $this->args;
			$html_args['html'] = $this->variables['html'][ $html_key ]; // Add the HTML.

			// Variations are not required for plain HTML. So unset it if exists.
			if ( isset( $html_args['variations'] ) ) {
				unset( $html_args['variations'] );
			}

			// Render the HTML.
			$html       = $this->render( $html_args );
			$this->args = $args_back; // Restore the saved arguments.

			return $html;
		}

		return '';
	}


	/**
	 *  Process and replace wp filter placeholder with filter content.
	 *
	 *  @param  string $filter_data   Filter data string. It will be a space separated string.
	 *  @return string   Processed HTML or empty string.
	 */
	private function process_filter( $filter_data ) {

		$filter_data_arr = array_map( 'trim', explode( ' ', $filter_data ) );
		$filter_key      = array_shift( $filter_data_arr ); // Assumes the first item is the filter key and the other items are filter arguments.

		if ( isset( $this->variables['filter'] ) && isset( $this->variables['filter'][ $filter_key ] ) && is_string( $this->variables['filter'][ $filter_key ] ) ) {

			$apply_filter_args = array( $this->variables['filter'][ $filter_key ] ); // Add filter name as first item for `apply_filters`.

			foreach ( $filter_data_arr as $filter_arg ) {
				$apply_filter_args[] = $this->render_variable_values( $filter_arg );
			}

			// WordPress `apply_filters` requires minimum 2 arguments.
			$filter_html = count( $apply_filter_args ) > 1 ? call_user_func_array( 'apply_filters', $apply_filter_args ) : '';

			if ( is_string( $filter_html ) ) { // Check and render the content. The content may contain placeholders.
				$args_back           = $this->args; // Save it for main component.
				$filter_args         = $this->args; // Render arguments for filter placeholder.
				$filter_args['html'] = $filter_html; // Add the HTML.

				// Variations are not required for filter placeholder. So unset it if exists.
				if ( isset( $filter_args['variations'] ) ) {
					unset( $filter_args['variations'] );
				}

				// Render the HTML.
				$html       = $this->render( $filter_args );
				$this->args = $args_back; // Restore the saved arguments.

				return $html;
			}
		}

		return '';
	}

	/**
	 *  Process icons. This method will return SVG element instead of URL.
	 *
	 *  @param   string $icon_name   Icon name without extension.
	 *  @return  string   SVG element.
	 */
	private function process_icons( $icon_name ) {

		$icon_name = $this->prepare_icon_name( $icon_name );
		if ( ! $icon_name ) { // Unable to process the icon name.
			return '';
		}

		$file_path = $this->icon_base_path . $icon_name . '.svg';

		if ( file_exists( $file_path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			return file_get_contents( $file_path );
		}

		return '';
	}


	/**
	 *  Check the icon_name is a variable. If yes, we want to take the value.
	 *
	 *  @param  string $icon_name  Icon name.
	 *  @return string  $icon_name  Processed icon name.
	 */
	private function prepare_icon_name( $icon_name ) {
		if ( preg_match( '/^\(\((.*)\)\)$/', $icon_name, $matches ) ) {
			$icon_name = $this->render_variable_values( $matches[1] );
		}
		return $icon_name;
	}


	/**
	 *  Process the component string and return the processed component HTML.
	 *
	 *  @param   string $params_string   Parameter string.
	 *  @return  string   $html            Processed component HTML.
	 */
	private function process_components( $params_string ) {

		if ( isset( $this->args['parent_obj'] ) && is_object( $this->args['parent_obj'] ) && method_exists( $this->args['parent_obj'], 'render_component_placeholder' ) ) {

			$params = $this->extract_component_params( $params_string );
			return $this->args['parent_obj']->render_component_placeholder( $params );
		} else {
			return '';
		}
	}


	/**
	 *  Process simple arithmetic operations.
	 *
	 *  @param   string $matches    Parameter string.
	 *  @return  int|float|string               Result of arithmetic operation.
	 */
	private function process_arithmetic( $matches, $text ) {
		$var1 = $this->render_variable_values( $matches[1] );
		$var2 = $this->render_variable_values( $matches[3] );

		if ( ! is_numeric( $var1 ) || ! is_numeric( $var2 ) ) {
			return '';
		}
		$out = '';
		switch ( $matches[2] ) {
			case '+':
				$out = $var1 + $var2;
				break;

			case '-':
				$out = $var1 - $var2;
				break;

			case '*':
				$out = $var1 * $var2;
				break;

			case '/':
				$out = ( ( 0 === $var2 || '0' === $var2 ) ? '' : $var1 / $var2 ); // Prevent return by zero error.
				break;

			case '%':
				$out = $var1 % $var2;
				break;

			default:
				$out = '';
				break;
		}

		return str_replace( $matches[0], $out, $text );
	}


	/**
	 *  To extract parameters from component string.
	 *  This will extract. slug, values, class, attr etc.
	 *
	 *  @param   string $params_string   Parameter string.
	 *  @return  array    $params          Multi-dimentional array of params.
	 */
	private function extract_component_params( $params_string ) {
		$params = array();

		// Extract slug.
		if ( preg_match( '/--slug:\[([^\]]+)\]/', $params_string, $slug_match ) ) {
			$params['slug'] = explode( ' ', $this->render_variable_values( trim( $slug_match[1] ) ) );
		}

		// Extract values.
		if ( preg_match( '/--values:\[([^\]]+)\]/', $params_string, $values_match ) ) {
			$params['values'] = $this->parse_key_value_pairs( trim( $values_match[1] ) );
		}

		// Extract class.
		if ( preg_match( '/--class:\[([^\]]+)\]/', $params_string, $class_match ) ) {
			$params['class'] = $this->parse_array( trim( $class_match[1] ) );
		}

		// Extract attributes.
		if ( preg_match( '/--attr:\[([^\]]+)\]/', $params_string, $attr_match ) ) {
			$params['attr'] = $this->parse_key_value_pairs( trim( $attr_match[1] ) );
		}

		return $params;
	}


	/**
	 *  Parse key value pairs string to associative array.
	 *  This method is used to to process the component string.
	 *  This method will replace variables with real value.
	 *
	 *  @param string $key_value_var   Key value pair string.
	 *  @return array   Associative array.
	 */
	private function parse_key_value_pairs( $key_value_var ) {

		$result = array();

		if ( preg_match_all( '/([\w-]+)=(".*?"|\'.*?\'|[^,]+)/', $key_value_var, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$result[ $match[1] ] = $this->render_variable_values( $match[2] );
			}
		}

		return $result;
	}


	/**
	 *  Parse array string to array.
	 *  This method is used to to process the component string.
	 *  This method will replace variables with real value.
	 *
	 *  @param string $array_var   Array string.
	 *  @return array   Array of values.
	 */
	private function parse_array( $array_var ) {
		$items  = explode( ',', $array_var );
		$result = array();

		foreach ( $items as $item ) {

			$result[] = $this->render_variable_values( $item );
		}

		return $result;
	}


	/**
	 *  Check if the current string is a variable and replace it.
	 *
	 *  @param   string $value   The value to check.
	 *  @return  mixed    The assigned value or the original string.
	 */
	private function render_variable_values( $value ) {

		// Check the current placeholder is a simple arithmetic operation.
		if ( preg_match( '/([\w.]+)\s*([\+\-\*\/\%])\s*([\w.]+)/', $value, $arithmetic_matches ) ) {
			$value = $this->process_arithmetic( $arithmetic_matches, $value );
		}

		// Check this is a condition.
		if ( preg_match( '/(.*?)\s*\?\s*(.*?)\s*:\s*(.*)/', $value, $condition_matches ) ) {
			if ( $this->process_conditions( trim( $condition_matches[1] ) ) ) {
				return $this->render_variable_values( trim( $condition_matches[2] ) );
			} else {
				return $this->render_variable_values( trim( $condition_matches[3] ) );
			}
		}

		$trimmed = trim( $value, " '\"" );

		if ( $value === $trimmed && ! is_numeric( $trimmed ) ) { // The `value` is not surrounded by quotes and not numeric, so it may be a variable.

			// Do a deep check. May be the item is a sub item of an array. Eg: `item.title`.
			$val_arr = explode( '.', $trimmed );
			$out     = $this->variables;

			foreach ( $val_arr as $val ) {

				if ( preg_match( '/^\(\((.*)\)\)$/', $val, $matches ) ) { // Check the current `val` is already a variable. So we want to take the value.
					$val = $this->render_variable_values( $matches[1] );
				}

				if ( $val && isset( $out[ $val ] ) ) {
					$out = $out[ $val ];
				} else {

					if ( 'length' === $val ) { // If the request is for length.
						$out = ( is_array( $out ) || is_object( $out ) ? count( $out ) : ( is_string( $out ) ? strlen( $out ) : '' ) );
					} else {
						$out = '';
					}
					break;
				}
			}

			return $out;

		} else {
			if ( is_numeric( $trimmed ) && is_string( $trimmed ) ) {
				$trimmed = ( strpos( $trimmed, '.' ) !== false ? floatval( $trimmed ) : intval( $trimmed ) );
			}
			return $trimmed;
		}
	}
}

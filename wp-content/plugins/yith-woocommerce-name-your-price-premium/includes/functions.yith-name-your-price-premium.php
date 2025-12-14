<?php
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Name Your Price Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! function_exists( 'ywcnp_json_search_product_categories' ) ) {
	/** Search product category by term
	 *
	 * @param string $x x.
	 * @param array  $taxonomy_types taxonomy types.
	 *
	 * @author YITH <plugins@yithemes.com>
	 * @since  1.0.0
	 *
	 */
	function ywcnp_json_search_product_categories( $x = '', $taxonomy_types = array( 'product_cat' ) ) {

		global $wpdb;
		$term = (string) urldecode( stripslashes( wp_strip_all_tags( isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '' ) ) ); //phpcs:ignore WordPress.Security.NonceVerification
		$term = '%' . $term . '%';

		$query_cat = $wpdb->prepare(
			"SELECT {$wpdb->terms}.term_id,{$wpdb->terms}.name, {$wpdb->terms}.slug
                                   FROM {$wpdb->terms} INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                                   WHERE {$wpdb->term_taxonomy}.taxonomy IN (%s) AND {$wpdb->terms}.slug LIKE %s",
			implode( ',', $taxonomy_types ),
			$term
		);

		$product_categories = $wpdb->get_results( $query_cat );//phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared

		$to_json = array();

		foreach ( $product_categories as $product_category ) {

			$to_json[ $product_category->term_id ] = '#' . $product_category->term_id . '-' . $product_category->name;
		}

		wp_send_json( $to_json );

	}
}
add_action( 'wp_ajax_yith_json_search_product_categories', 'ywcnp_json_search_product_categories', 10 );


add_filter( 'ywcnp_product_types', 'ywcnp_premium_product_type_allowed' );

if ( ! function_exists( 'ywcnp_premium_product_type_allowed' ) ) {

	/** Add premium product type allowed
	 *
	 * @param array $types types.
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 */
	function ywcnp_premium_product_type_allowed( $types ) {

		$new_type = array( 'variable', 'variation', 'grouped' );

		return array_merge( $types, $new_type );
	}
}

add_filter( 'ywcnp_add_error_message', 'ywcnp_add_premium_message' );

if ( ! function_exists( 'ywcnp_add_premium_message' ) ) {
	/** Add premium message error
	 *
	 * @param array $messages messages.
	 *
	 * @return mixed
	 * @since  1.0.0
	 *
	 */
	function ywcnp_add_premium_message( $messages ) {

		$messages['min_error'] = get_option( 'ywcnp_min_price_error_label' );
		$messages['max_error'] = get_option( 'ywcnp_max_price_error_label' );

		return $messages;
	}
}

if ( ! function_exists( 'ywcnp_get_suggest_price' ) ) {
	/** Get suggested price
	 *
	 * @param int $product_id product id.
	 *
	 * @return float
	 * @since  1.0.0
	 *
	 */
	function ywcnp_get_suggest_price( $product_id ) {
		global $woocommerce_wpml, $sitepress;
		if ( isset( $sitepress ) ) {
			/**
			 * APPLY_FILTERS: translate_object_id
			 *
			 * WPML filter to get the right object id.
			 *
			 * @param int $product_id
			 * @param string $post_type
			 * @param bool $return_original_if_missing
			 * @param string $default_lang
			 */
			$product_id = apply_filters( 'translate_object_id', $product_id, get_post_type( $product_id ), false, $sitepress->get_default_language() );
		}

		$product    = wc_get_product( $product_id );
		$sugg_price = '';

		if ( $product ) {
			$sugg_price = $product->get_meta( '_ywcnp_simple_suggest_price' );
		}
// @codingStandardsIgnoreStart
		/*
		   if ( isset( $woocommerce_wpml ) ) {

			   $sugg_price = apply_filters( 'wcml_raw_price_amount', $sugg_price );
		   }*/

// @codingStandardsIgnoreEnd

		/**
		 * APPLY_FILTERS: ywcnp_get_suggest_price
		 *
		 * filter suggested price.
		 *
		 * @param mixed $sugg_price
		 * @param WC_Product $product
		 */
		return apply_filters( 'ywcnp_get_suggest_price', $sugg_price, $product );
	}
}

if ( ! function_exists( 'ywcnp_get_min_price' ) ) {
	/**
	 * Get min price
	 *
	 * @param int $product_id product id.
	 *
	 * @return float
	 * @since  1.0.0
	 *
	 */
	function ywcnp_get_min_price( $product_id ) {
		global $woocommerce_wpml, $sitepress;

		if ( isset( $sitepress ) ) {
			/**
			 * APPLY_FILTERS: translate_object_id
			 *
			 * WPML filter to get the right object id.
			 *
			 * @param int $product_id
			 * @param string $post_type
			 * @param bool $return_original_if_missing
			 * @param string $default_lang
			 */
			$product_id = apply_filters( 'translate_object_id', $product_id, get_post_type( $product_id ), false, $sitepress->get_default_language() );
		}

		$product   = wc_get_product( $product_id );
		$min_price = '';

		if ( $product ) {
			$min_price = $product->get_meta( '_ywcnp_simple_min_price' );
		}
// @codingStandardsIgnoreStart
		/*
		  if ( isset( $woocommerce_wpml ) ) {

			  $min_price = apply_filters( 'wcml_raw_price_amount', $min_price );
		  }*/

// @codingStandardsIgnoreEnd
		/**
		 * APPLY_FILTERS: ywcnp_get_min_price
		 *
		 * filter suggested minimum price.
		 *
		 * @param mixed $min_price
		 * @param WC_Product $product
		 */
		return apply_filters( 'ywcnp_get_min_price', $min_price, $product );
	}
}

if ( ! function_exists( 'ywcnp_get_max_price' ) ) {
	/**
	 * Get max price
	 *
	 * @param int $product_id product id.
	 *
	 * @return float
	 * @since  1.0.0
	 *
	 */
	function ywcnp_get_max_price( $product_id ) {
		global $woocommerce_wpml, $sitepress;

		if ( isset( $sitepress ) ) {
			/**
			 * APPLY_FILTERS: translate_object_id
			 *
			 * WPML filter to get the right object id.
			 *
			 * @param int $product_id
			 * @param string $post_type
			 * @param bool $return_original_if_missing
			 * @param string $default_lang
			 */
			$product_id = apply_filters( 'translate_object_id', $product_id, get_post_type( $product_id ), false, $sitepress->get_default_language() );

		}

		$product   = wc_get_product( $product_id );
		$max_price = '';

		if ( $product ) {
			$max_price = $product->get_meta( '_ywcnp_simple_max_price' );
		}
		// @codingStandardsIgnoreStart
		/*
		   if ( isset( $woocommerce_wpml ) ) {

			   $max_price = apply_filters( 'wcml_raw_price_amount', $max_price );
		   }
   		*/

		// @codingStandardsIgnoreEnd
		/**
		 * APPLY_FILTERS: ywcnp_get_max_price
		 *
		 * filter suggested minimum price.
		 *
		 * @param mixed $max_price
		 * @param WC_Product $product
		 */
		return apply_filters( 'ywcnp_get_max_price', $max_price, $product );
	}
}

if ( ! function_exists( 'ywcnp_get_suggest_price_html' ) ) {
	/**
	 * Ywcnp_get_suggest_price_html
	 *
	 * @param int $product_id product id.
	 *
	 * @return string suggest price html
	 * @since  1.0.0
	 *
	 */
	function ywcnp_get_suggest_price_html( $product_id ) {
		if ( $product_id instanceof WC_Product ) {
			$product_id = $product_id->get_id();
		}
		$product    = wc_get_product( $product_id );
		$sugg_price = wc_format_decimal( ywcnp_get_suggest_price( $product_id ) );
		$min_price  = wc_format_decimal( ywcnp_get_min_price( $product_id ) );
		$price_html = '';
		$price      = '';
		if ( ! empty( $sugg_price ) ) {

			$price      = wc_price( $sugg_price );
			$price_html = get_option( 'ywcnp_suggest_price_label' );

		} elseif ( ! empty( $min_price ) ) {

			$price      = wc_price( $min_price );
			$price_html = get_option( 'ywcnp_min_price_label' );
		} else {
			$price = '';
			/**
			 * APPLY_FILTERS: ywcnp_from_free_label
			 *
			 * filter "From Free!" label.
			 *
			 * @param string $label
			 */
			$price_html = apply_filters( 'ywcnp_from_free_label', __( 'From Free!', 'yith-woocommerce-name-your-price' ) );
		}

		if ( ywcnp_product_has_subscription( $product ) ) {
			$price = ywcnp_get_price_subscription( $product, $price );
		}

		$price = sprintf( '<span class="ywcnp_suggest_label">%s %s</span>', $price_html, $price );

		return $price;
	}
}

if ( ! function_exists( 'ywcnp_get_min_price_html' ) ) {
	/**
	 * Ywcnp_get_min_price_html
	 *
	 * @param int $product_id product id.
	 *
	 * @return string min price html
	 * @since  1.0.0
	 *
	 */
	function ywcnp_get_min_price_html( $product_id ) {
		$product   = wc_get_product( $product_id );
		$min_price = wc_format_decimal( ywcnp_get_min_price( $product_id ) );
		if ( empty( $min_price ) ) {
			return '<span class="amount"></span>';
		}

		if ( ywcnp_product_has_subscription( $product ) ) {

			$min_price = ywcnp_get_price_subscription( $product, wc_price( $min_price ) );
		} else {
			$min_price = wc_price( $min_price );
		}
		$price = sprintf( '<span>%s%s</span>', get_option( 'ywcnp_min_price_label' ), $min_price );

		return $price;

	}
}

if ( ! function_exists( 'ywcnp_get_max_price_html' ) ) {
	/**
	 * Ywcnp_get_max_price_html
	 *
	 * @param int $product_id product id.
	 *
	 * @return string max price html
	 * @since  1.0.0
	 *
	 */
	function ywcnp_get_max_price_html( $product_id ) {
		$product   = wc_get_product( $product_id );
		$max_price = wc_format_decimal( ywcnp_get_max_price( $product_id ) );

		if ( empty( $max_price ) ) {
			return '<span class="amount"></span>';
		}

		if ( ywcnp_product_has_subscription( $product ) ) {

			$max_price = ywcnp_get_price_subscription( $product, wc_price( $max_price ) );
		} else {
			$max_price = wc_price( $max_price );
		}
		$price = sprintf( '<span>%s%s</span>', get_option( 'ywcnp_max_price_label' ), $max_price );

		return $price;
	}
}

if ( ! function_exists( 'ywcnp_product_has_subscription' ) ) {
	/**
	 * Check if product has subscription
	 *
	 * @param WC_Product $product product.
	 *
	 * @return bool
	 * @since  1.0.0
	 *
	 */
	function ywcnp_product_has_subscription( $product ) {
		return ( defined( 'YITH_YWSBS_PREMIUM' ) && class_exists( 'YITH_WC_Subscription' ) && 'yes' === $product->get_meta( '_ywsbs_subscription' ) );
	}
}


if ( ! function_exists( 'ywcnp_product_has_rule' ) ) {
	/**
	 * Check if a category has a "name your price" rule
	 *
	 * @param int $product_id product id.
	 *
	 * @return string
	 * @sincr  1.0.0
	 *
	 */
	function ywcnp_product_has_rule( $product_id ) {

		$last_category_rule_id = '';
		$categories            = wp_get_object_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
		$categories_ids        = ywcnp_get_category_ids_rule_vendor();
		foreach ( $categories as $category_id ) {

			$has_rule = ! ywcnp_is_multivendor_active() ? ywcnp_get_woocommerce_term_meta( $category_id, '_ywcnp_enable_rule', true ) === 'yes' : in_array( $category_id, $categories_ids, true );

			if ( 'yes' === $has_rule ) {
				$last_category_rule_id = $category_id;
			}
		}

		return $last_category_rule_id;
	}
}

if ( ! function_exists( 'ywcnp_get_price_subscription' ) ) {
	/**
	 * Get price in subscription format
	 *
	 * @param mixed $product product.
	 * @param mixed $price price.
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 */
	function ywcnp_get_price_subscription( $product, $price ) {

		add_filter( 'ywsbs_not_process_change_price_html', '__return_false');
		if ( version_compare( YITH_YWSBS_VERSION, '2.0', '>=' ) ) {
			$price = YWSBS_Subscription_Helper()->change_price_html( $price, $product );
		} else {
			$price = YITH_WC_Subscription()->change_price_html( $price, $product );
		}
		remove_filter( 'ywsbs_not_process_change_price_html', '__return_false');
		// @codingStandardsIgnoreStart
		// $price_is_per = get_post_meta( $product_id, '_ywsbs_price_is_per', true );
		// $price_time_option = get_post_meta( $product_id, '_ywsbs_price_time_option', true );

		// $price .= ' / ' . $price_is_per . ' ' . $price_time_option;
		// @codingStandardsIgnoreEnd
		return $price;
	}
}

if ( ! function_exists( 'ywcnp_is_multivendor_active' ) ) {
	/**
	 * Check if YITH WooCommerce Multivendor Premium is active
	 *
	 * @return bool
	 * @since  1.0.0
	 */
	function ywcnp_is_multivendor_active() {

		return defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM;
	}
}

if ( ! function_exists( 'ywcnp_is_multivendor_name_your_price_enabled' ) ) {
	/**
	 * Check if Name Your Price is enabled for Vendors
	 *
	 * @return bool
	 * @since  1.0.0
	 */
	function ywcnp_is_multivendor_name_your_price_enabled() {
		$option = get_option( 'yith_wpv_vendors_option_name_your_price_management', 'no' );

		return 'yes' === $option;
	}
}

if ( ! function_exists( 'ywcnp_get_category_rule_vendor' ) ) {
	/**
	 * Ywcnp_get_category_rule_vendor
	 *
	 * @param mixed $category_id category id.
	 * @param mixed $field field.
	 *
	 * @return bool | array
	 * @since  1.0.0
	 *
	 */
	function ywcnp_get_category_rule_vendor( $category_id, $field ) {

		if ( ywcnp_is_multivendor_active() ) {
			$vendor    = function_exists( 'yith_wcmv_get_vendor' ) ? yith_wcmv_get_vendor( 'current', 'user' ) : yith_get_vendor( 'current', 'user' );
			$vendor_id = function_exists( 'yith_wcmv_get_vendor' ) ? $vendor->get_id() : $vendor->id;

			$rules = get_user_meta( $vendor_id, '_ywcnp_vendor_cat_rules', true );

			$single_rule = false;

			if ( $rules ) {
				foreach ( $rules as $key => $rule ) {
					if ( $rule['category'] === $category_id ) {
						$single_rule = $rules[ $key ];
						continue;
					}
				}
			}

			if ( $single_rule ) {

				return $single_rule[ $field ];
			}

			return false;
		} else {
			return false;
		}

	}
}

if ( ! function_exists( 'ywcnp_get_category_ids_rule_vendor' ) ) {
	/**
	 * Return all category ids set in rules vendor
	 *
	 * @return array
	 * @since  1.0.0
	 */
	function ywcnp_get_category_ids_rule_vendor() {

		if ( ywcnp_is_multivendor_active() ) {
			$vendor    = function_exists( 'yith_wcmv_get_vendor' ) ? yith_wcmv_get_vendor( 'current', 'user' ) : yith_get_vendor( 'current', 'user' );
			$vendor_id = function_exists( 'yith_wcmv_get_vendor' ) ? $vendor->get_id() : $vendor->id;

			$category_ids = array();
			$rules        = get_user_meta( $vendor_id, '_ywcnp_vendor_cat_rules', true );

			if ( $rules ) {
				foreach ( $rules as $key => $rule ) {
					if ( 'yes' === $rule['enabled'] ) {
						$category_ids[] = $rule['category'];
					}
				}
			}

			return $category_ids;
		}

		return array();
	}
}

if ( ! function_exists( 'ywcnp_save_product_meta_global_rule' ) ) {
	/**
	 * Save product meta for global category rules
	 *
	 * @param array $args args.
	 *
	 * @since  1.0.0
	 *
	 */
	function ywcnp_save_product_meta_global_rule( $args = array() ) {

		$default = array(
			'product_id'  => '',
			'sugg_price'  => '',
			'min_price'   => '',
			'max_price'   => '',
			'is_override' => 'no',
		);

		$default = wp_parse_args( $args, $default );

		extract( $default ); //phpcs:ignore WordPress.PHP.DontExtract

		$product      = wc_get_product( $product_id );
		$product_meta = false;

		if ( $product->is_type( 'simple' ) ) {

			$is_override = $product->get_meta( '_ywcnp_simple_is_override' );
			$is_override = $is_override ? $is_override : 'no';

			if ( 'no' === $is_override ) {
				$product_meta = array(
					'_ywcnp_enabled_product'      => 'yes',
					'_is_nameyourprice'           => true,
					'_ywcnp_simple_suggest_price' => $sugg_price,
					'_ywcnp_simple_min_price'     => $min_price,
					'_ywcnp_simple_max_price'     => $max_price,
					'_ywcnp_simple_is_override'   => 'no',
				);
			}
		} elseif ( $product->is_type( 'variation' ) ) {

			$is_override = $product->get_meta( '_ywcnp_variation_is_override' );
			$is_override = $is_override ? $is_override : 'no';

			if ( 'no' === $is_override ) {
				$product_meta = array(
					'_ywcnp_enabled_variation'     => 'yes',
					'_is_nameyourprice'            => true,
					'_ywcnp_simple_suggest_price'  => $sugg_price,
					'_ywcnp_simple_min_price'      => $min_price,
					'_ywcnp_simple_max_price'      => $max_price,
					'_ywcnp_variation_is_override' => 'no',
				);

				$parent_id      = $product->get_parent_id();
				$parent_product = wc_get_product( $parent_id );

				$parent_product->update_meta_data( '_variation_has_nameyourprice', true );
			}
		}

		if ( $product_meta && ! $product->is_type( 'variable' ) ) {

			foreach ( $product_meta as $meta_key => $meta_value ) {
				$product->update_meta_data( $meta_key, $meta_value );
			}
		}
		$product->save();

	}
}

if ( ! function_exists( 'ywcnp_remove_product_meta_global_rule' ) ) {
	/**
	 * Remove product meta for global category rule
	 *
	 * @param int $product_id product id.
	 *
	 * @since  1.0.0
	 *
	 */
	function ywcnp_remove_product_meta_global_rule( $product_id ) {

		$product      = wc_get_product( $product_id );
		$product_meta = false;

		if ( $product->is_type( 'simple' ) ) {

			$is_override = $product->get_meta( '_ywcnp_simple_is_override' );
			$is_override = $is_override ? $is_override : 'no';

			if ( 'no' === $is_override ) {
				$product_meta = array(
					'_ywcnp_enabled_product',
					'_is_nameyourprice',
					'_ywcnp_simple_is_override',
				);
			}
		} elseif ( $product->is_type( 'variation' ) ) {

			$is_override = $product->get_meta( '_ywcnp_variation_is_override', true );
			$is_override = $is_override ? $is_override : 'no';

			if ( 'no' === $is_override ) {
				$product_meta = array(
					'_ywcnp_enabled_variation',
					'_is_nameyourprice',
					'_ywcnp_variation_is_override',
				);

				$parent_id      = $product->get_parent_id();
				$parent_product = wc_get_product( $parent_id );
				$parent_product->delete_meta_data( '_variation_has_nameyourprice' );
				$parent_product->save();
			}
		}

		if ( $product_meta ) {
			foreach ( $product_meta as $meta ) {
				yit_delete_prop( $product, $meta );
			}
		}
	}
}

if ( ! function_exists( 'ywcnp_get_product_id_by_category' ) ) {
	/**
	 * Get all product_id
	 *
	 * @param array $category_ids category ids.
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 */
	function ywcnp_get_product_id_by_category( $category_ids ) {

		$product_ids = array();
		if ( ywcnp_is_multivendor_active() && ! ( current_user_can( 'edit_users' ) ) ) {

			$vendor = function_exists( 'yith_wcmv_get_vendor' ) ? yith_wcmv_get_vendor( 'current', 'user' ) : yith_get_vendor( 'current', 'user' );

			if ( $vendor->is_valid() && $vendor->has_limited_access() ) {

				$products = $vendor->get_products();

				$products_count = count( $products );
				if ( is_array( $products ) && $products_count > 0 ) {

					foreach ( $products as $product_id ) {

						$product = wc_get_product( $product_id );

						$product_categories = wc_get_product_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

						if ( ! in_array( $category_ids, $product_categories ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
							continue;
						}

						if ( $product->is_type( 'variable' ) ) {

							if ( $product->has_child() ) {

								$childs = $product->get_children();
								foreach ( $childs as $child ) {
									$product_ids[] = $child;

								}
							}
						} else {
							$product_ids[] = $product_id;
						}
					}
				}
			}
		} else {
			$product_ids = array();

			$args = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'post_parent'    => 0,
				'posts_per_page' => - 1,
				'tax_query'      => array( // phpcs:ignore slow query ok.
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => explode( ',', $category_ids ),
					),
				),
			);

			wp_reset_postdata();

			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) {

					$query->the_post();

					$product_id = $query->post->ID;
					$product    = wc_get_product( $product_id );

					if ( $product->is_type( 'variable' ) ) {

						if ( $product->has_child() ) {

							$childs = $product->get_children();
							foreach ( $childs as $child ) {
								$product_ids[] = $child;

							}
						}
					} else {
						$product_ids[] = $product_id;
					}
				}
			}

			wp_reset_postdata();
		}

		return $product_ids;
	}
}


if ( ! function_exists( 'ywcnp_get_woocommerce_term_meta' ) ) {
	/**
	 * Ywcnp_get_woocommerce_term_meta
	 *
	 * @param int    $term_id term id.
	 * @param string $key key.
	 * @param bool   $single single.
	 *
	 * @return term_meta
	 * @since  1.0.10
	 *
	 */
	function ywcnp_get_woocommerce_term_meta( $term_id, $key, $single = true ) {
		$is_wc_lower_2_6 = version_compare( WC()->version, '2.6', '<' );

		return ! $is_wc_lower_2_6 ? get_term_meta( $term_id, $key, $single ) : get_metadata( 'woocommerce_term', $term_id, $key, $single );
	}
}

if ( ! function_exists( 'ywcnp_update_woocommerce_term_meta' ) ) {
	/**
	 * Ywcnp_update_woocommerce_term_meta
	 *
	 * @param int    $term_id term id.
	 * @param string $meta_key meta key.
	 * @param string $meta_value meta value.
	 * @param string $prev_value previous value.
	 *
	 * @return bool|int|WP_Error
	 * @since  1.0.10
	 *
	 */
	function ywcnp_update_woocommerce_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
		$is_wc_lower_2_6 = version_compare( WC()->version, '2.6', '<' );

		return ! $is_wc_lower_2_6 ? update_term_meta( $term_id, $meta_key, $meta_value, $prev_value ) : update_metadata( 'woocommerce_term', $term_id, $meta_key, $meta_value, $prev_value );

	}
}

<?php // phpcs:ignore WordPress.NamingConventions
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


if ( ! class_exists( 'YITH_WooCommerce_Name_Your_Price_Premium' ) ) {
	/**
	 * Implement premium features
	 *
	 * @since  1.0.0
	 * Class YITH_WooCommerce_Name_Your_Price_Premium
	 * @author YITH <plugins@yithemes.com>
	 */
	class YITH_WooCommerce_Name_Your_Price_Premium extends YITH_WooCommerce_Name_Your_Price {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WooCommerce_Name_Your_Price_Premium, single instance
		 */
		protected static $instance;

		/**
		 *
		 * __construct function
		 */
		public function __construct() {

			parent::__construct();

			// Manage plugin activation license !
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_updates' ), 99 );

			// Add premium tabs !
			add_filter( 'ywcnp_add_premium_tab', array( $this, 'add_premium_tab' ) );

			// Add ajax action for load admin template !
			add_action( 'wp_ajax_add_new_rule_admin', array( $this, 'add_new_rule_admin' ) );
			add_action( 'wp_ajax_nopriv_add_new_rule_admin', array( $this, 'add_new_rule_admin' ) );

			// Print price for simple product !
			add_filter( 'ywcnp_get_product_price_html', array( $this, 'get_nameyourprice_html' ), 20, 2 );
			add_filter( 'ywsbs_add_cart_item_data', array( $this, 'ywcnp_add_cart_item_data_subscription' ), 20, 3 );

			add_action( 'init', array( $this, 'init_multivendor_integration' ), 20 );

			// Set product variation as purchasable !
			add_filter( 'woocommerce_variation_is_visible', array( $this, 'ywcnp_variation_is_visible' ), 10, 4 );

			if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {

				add_action(
					'woocommerce_variable_product_sync_data',
					array(
						$this,
						'variable_product_sync_data',
					),
					15
				);
			} else {
				add_action( 'woocommerce_variable_product_sync', array( $this, 'variable_product_sync' ), 15, 2 );
			}
			add_filter( 'woocommerce_get_variation_price', array( $this, 'get_variation_price' ), 10, 4 );
			add_filter( 'woocommerce_get_variation_regular_price', array( $this, 'get_variation_price' ), 10, 4 );
		}


		/**
		 * Get_variation_price
		 *
		 * @param string     $price      price.
		 * @param WC_Product $product    product.
		 * @param string     $min_or_max min or max.
		 * @param mixed      $display    display.
		 *
		 * @return string
		 * @since  1.0.0
		 *
		 */
		public function get_variation_price( $price, $product, $min_or_max, $display ) {

			if ( ywcnp_product_is_name_your_price( $product ) ) {
				$price = $product->get_meta( '_' . $min_or_max . '_variation_price' );

				return $price;
			}

			return $price;
		}

		/**
		 * Ywcnp_variation_is_visible
		 *
		 * @param bool                 $visible      visible.
		 * @param int                  $variation_id variation id.
		 * @param int                  $parent_id    parent id.
		 * @param WC_Product_Variation $variation    variation.
		 *
		 * @return bool
		 * @since  1.0.0
		 *
		 */
		public function ywcnp_variation_is_visible( $visible, $variation_id, $parent_id, $variation ) {

			if ( ywcnp_product_is_name_your_price( $variation ) ) {
				return true;
			}

			return $visible;
		}


		/**
		 * Return single instance
		 *
		 * @return YITH_WooCommerce_Name_Your_Price_Premium
		 * @since  1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/** Register plugins for activation tab
		 *
		 * @return void
		 * @since    1.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YWCNP_DIR . 'plugin-fw/licence/lib/yit-licence.php';
				require_once YWCNP_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YWCNP_INIT, YWCNP_SECRET_KEY, YWCNP_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    1.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YWCNP_DIR . 'plugin-fw/lib/yit-upgrade.php';
			}
			YIT_Upgrade()->register( YWCNP_SLUG, YWCNP_INIT );
		}

		/**
		 * Add premium tabs in plugin options
		 *
		 * @param array $tabs tabs.
		 *
		 * @return mixed
		 * @since  1.0.0
		 *
		 */
		public function add_premium_tab( $tabs ) {

			$tabs['category-rules'] = __( 'Active Rules', 'yith-woocommerce-name-your-price' );

			return $tabs;
		}


		/**
		 * Return empty rule in admin
		 *
		 * @since  1.0.0
		 */
		public function add_new_rule_admin() {

			if ( isset( $_REQUEST['ywcnp_add_new_rule'] ) && isset( $_REQUEST['ywcnp_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

				$current_rule = sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_add_new_rule'] ) ); //phpcs:ignore WordPress.Security.NonceVerification
				$option_id    = sanitize_text_field( wp_unslash( $_REQUEST['ywcnp_id'] ) );           //phpcs:ignore WordPress.Security.NonceVerification
				$params       = array(
					'option_id'    => $option_id,
					'current_rule' => $current_rule,
				);

				$params['params'] = $params;
				ob_start();

				wc_get_template( 'nameyourprice-single-rule.php', $params, '', YWCNP_TEMPLATE_PATH );
				$template = ob_get_contents();

				ob_end_clean();
				wp_send_json( array( 'result' => $template ) );

			}
		}

		/**
		 * Ywcnp_add_cart_item_data_subscription
		 *
		 * @param cart   $new_cart          new_cart.
		 * @param string $new_cart_item_key new_cart_item_key.
		 * @param cart   $old_cart_item     old_cart_item.
		 *
		 * @return mixed
		 */
		public function ywcnp_add_cart_item_data_subscription( $new_cart, $new_cart_item_key, $old_cart_item ) {

			if ( isset( $old_cart_item['ywcnp_amount'] ) ) {

				$new_cart_item = $new_cart->cart_contents[ $new_cart_item_key ];
				/**
				 * Product
				 *
				 * @var WC_Product $product product.
				 */
				$product   = $new_cart_item['data'];
				$meta_args = array(
					'price'              => $old_cart_item['ywcnp_amount'],
					'subscription_price' => $old_cart_item['ywcnp_amount'],
				);

				foreach ( $meta_args as $meta_key => $meta_value ) {
					$product->update_meta_data( $meta_key, $meta_value );
				}
			}

			return $new_cart;
		}

		/**
		 * Get_nameyourprice_html
		 *
		 * @param float      $price   price.
		 * @param WC_Product $product product.
		 *
		 * @return string
		 * @since  1.0.0
		 *
		 */
		public function get_nameyourprice_html( $price, $product ) {

			if ( ywcnp_product_is_name_your_price( $product ) ) {

				if ( $product->is_type( 'simple' ) ) {
					$price = ywcnp_get_suggest_price_html( $product );
				} elseif ( $product->is_type( 'variable' ) ) {

					/**
					 * Product
					 *
					 * @var WC_Product_Variable $product
					 */
					$variation_ids = $product->get_visible_children();

					$prices = array();

					foreach ( $variation_ids as $variation_id ) {

						$sugg_price = ywcnp_get_suggest_price( $variation_id );
						$min_price  = ywcnp_get_min_price( $variation_id );
					$nyp_price  = empty( $sugg_price ) ? $min_price : $sugg_price;
					if ( ! empty( $nyp_price ) ) {
						$prices[] = $nyp_price;
					} else {

						$variation_product = wc_get_product( $variation_id );
						// Safety check: only add price if product exists
						if ( $variation_product && is_object( $variation_product ) ) {
							$prices[] = $variation_product->get_price();
						}
					}
				}

				$min_price = ! empty( $prices ) ? min( $prices ) : '';
					$max_price = ! empty( $prices ) ? max( $prices ) : '';

					$price = $min_price !== $max_price ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $min_price ), wc_price( $max_price ) ) : wc_price( $min_price );
					$price = "<span class='ywcnp_variable_price_html'>$price</span>";
					/**
					 * APPLY_FILTERS: ywcnp_get_variation_name_price_html
					 *
					 * filter the variation price html.
					 *
					 * @param string     $price
					 * @param float      $min_price
					 * @param float      $max_price
					 * @param WC_Product $product
					 */
					$price = apply_filters( 'ywcnp_get_variation_name_price_html', $price, $min_price, $max_price, $product );

				}
			}

			return $price;
		}


		/**
		 * Product
		 *
		 * @param WC_Product_Variable $product product.
		 */
		public function variable_product_sync_data( $product ) {

			$product_id = $product->get_id();
			$children   = $product->get_visible_children();

			$this->variable_product_sync( $product_id, $children );
		}

		/**
		 * Cariable_product_sync
		 *
		 * @param int   $product_id product id.
		 * @param mixed $children   children.
		 *
		 * @since  1.0.0
		 *
		 */
		public function variable_product_sync( $product_id, $children ) {

			$product = wc_get_product( $product_id );
			if ( $children ) {

				$min_price    = null;
				$max_price    = null;
				$min_price_id = null;
				$max_price_id = null;

				// Main active prices !
				$min_price    = null;
				$max_price    = null;
				$min_price_id = null;
				$max_price_id = null;

				// Regular prices !
				$min_regular_price    = null;
				$max_regular_price    = null;
				$min_regular_price_id = null;
				$max_regular_price_id = null;

				// Sale prices !
				$min_sale_price    = null;
				$max_sale_price    = null;
				$min_sale_price_id = null;
				$max_sale_price_id = null;

				foreach ( array( 'price', 'regular_price' ) as $price_type ) {

					foreach ( $children as $child_id ) {

						$product_child = wc_get_product( $child_id );

						if ( ywcnp_product_is_name_your_price( $product_child ) ) {

							// Skip hidden variations !
							if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
								$stock = $product_child->get_stock_quantity();
								if ( '' !== $stock && $stock <= get_option( 'woocommerce_notify_no_stock_amount' ) ) {
									continue;
								}
							}

							// Get the minimum price for this variation !
							$child_min_price = ywcnp_get_min_price( $child_id );
							$child_max_price = ywcnp_get_max_price( $child_id );

							// If there is no set minimum, technically the min is 0 !
							$child_min_price = ! empty( $child_min_price ) ? $child_min_price : 0;

							$current_min = $child_min_price;
							$current_max = empty( $child_max_price ) ? $child_min_price : $child_max_price;

							// Find min price !
							if ( is_null( ${"min_{$price_type}"} ) || $current_min < ${"min_{$price_type}"} ) {
								${"min_{$price_type}"}    = $current_min;
								${"min_{$price_type}_id"} = $child_id;
							}

							// Find max price !
							if ( is_null( ${"max_{$price_type}"} ) || $current_max > ${"max_{$price_type}"} ) {
								${"max_{$price_type}"}    = $current_max;
								${"max_{$price_type}_id"} = $child_id;
							}
						} else {

							$price_function = 'get_' . $price_type;
							$child_price    = $product_child->$price_function();

							// Skip non-priced variations !
							if ( '' === $child_price ) {
								continue;
							}

							// Skip hidden variations !
							if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
								$stock = $product_child->get_stock_quantity();
								if ( '' !== $stock && $stock <= get_option( 'woocommerce_notify_no_stock_amount' ) ) {
									continue;
								}
							}

							// Find min price !
							if ( is_null( ${"min_{$price_type}"} ) || $child_price < ${"min_{$price_type}"} ) {
								${"min_{$price_type}"}    = $child_price;
								${"min_{$price_type}_id"} = $child_id;
							}

							// Find max price !
							if ( $child_price > ${"max_{$price_type}"} ) {
								${"max_{$price_type}"}    = $child_price;
								${"max_{$price_type}_id"} = $child_id;
							}
						}
					}

					// Store prices !
					yit_save_prop( $product, '_min_variation_' . $price_type, ${"min_{$price_type}"} );
					yit_save_prop( $product, '_max_variation_' . $price_type, ${"max_{$price_type}"} );

					// Store ids !
					yit_save_prop( $product, '_min_' . $price_type . '_variation_id', ${"min_{$price_type}_id"} );
					yit_save_prop( $product, '_max_' . $price_type . '_variation_id', ${"max_{$price_type}_id"} );
				}

				// The VARIABLE PRODUCT price should equal the min price of any type !
				yit_save_prop( $product, '_price', $min_price );

				wc_delete_product_transients( $product_id );

			}

		}

		/**
		 * Init multivendor integration
		 *
		 * @since  1.0.0
		 */
		public function init_multivendor_integration() {

			YITH_Name_Your_Price_Compatibility();

		}


		/**
		 *
		 * Plugin_row_meta
		 *
		 * @param array  $new_row_meta_args new_row_meta_args.
		 * @param mixed  $plugin_meta       plugin meta.
		 * @param string $plugin_file       plugin file.
		 * @param mixed  $plugin_data       plugin data.
		 * @param string $status            status.
		 * @param string $init_file         init file.
		 *
		 * @return array
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWCNP_INIT' ) {
			$new_row_meta_args = parent::plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file );

			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}


	}
}

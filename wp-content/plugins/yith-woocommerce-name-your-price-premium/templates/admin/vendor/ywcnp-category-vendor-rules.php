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


if ( ! class_exists( 'YWCNP_Category_Vendor_Rules' ) ) {

	/**
	 * YWCNP_Category_Vendor_Rules
	 */
	class YWCNP_Category_Vendor_Rules {


		/**
		 * Single instance of the class
		 *
		 * @var \YWCNP_Category_Rules
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * YITH WooCommerce Name Your Price Premium vendor
		 *
		 * @var YITH_Vendor
		 */
		protected $vendor;

		/**
		 * The vendor id
		 *
		 * @var int
		 */
		protected $vendor_id;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YWCNP_Category_Rules
		 * @since 1.0.0
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self( $_REQUEST ); //phpcs:ignore WordPress.Security.NonceVerification

			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @return  mixed
		 * @since   1.0.0
		 * @author  YITHEMES
		 */
		public function __construct() {

			add_filter( 'set-screen-option', array( $this, 'set_options' ), 10, 3 );
			add_action( 'current_screen', array( $this, 'add_options' ) );
			$this->vendor    = function_exists( 'yith_wcmv_get_vendor' ) ? yith_wcmv_get_vendor( 'current', 'user' ) : yith_get_vendor( 'current', 'user' );
			$this->vendor_id = function_exists( 'yith_wcmv_get_vendor' ) ? $this->vendor->get_id() : $this->vendor->id;
		}


		/**
		 * Print table
		 *
		 * @author YITHEMES
		 * @since 1.0.0
		 */
		public function output() {

			$table = new YITH_Category_Rule_Vendor_Table();

			$table->vendor_id = $this->vendor_id;

			$list_query_args = array(
				'page' => isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '',
			);

			$message       = '';
			$notice        = '';
			$data_selected = '';
			$value         = '';
			$list_url      = esc_url( add_query_arg( $list_query_args, admin_url( 'admin.php' ) ) );

			if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), basename( __FILE__ ) ) ) {

				$item_valid = $this->validate_fields( $_POST );

				if ( true !== $item_valid ) {

					$notice = $item_valid;

				} else {

					$category_ids  = isset( $_POST['category_ids'] ) && ! is_array( $_POST['category_ids'] ) ? explode( ',', wp_unslash( $_POST['category_ids'] ) ) : wp_unslash( $_POST['category_ids'] );
					$suggest_price = isset( $_POST['_ywcnp_suggest_price'] ) ? sanitize_text_field( wp_unslash( $_POST['_ywcnp_suggest_price'] ) ) : '';
					$minimum_price = isset( $_POST['_ywcnp_min_price'] ) ? sanitize_text_field( wp_unslash( $_POST['_ywcnp_min_price'] ) ) : '';
					$maximum_price = isset( $_POST['_ywcnp_max_price'] ) ? sanitize_text_field( wp_unslash( $_POST['_ywcnp_max_price'] ) ) : '';
					$is_enabled    = isset( $_POST['_ywcnp_enable_rule'] ) ? 'yes' : 'no';
					$has_rule      = 'yes';

					$vendor_rules = get_user_meta( $this->vendor_id, '_ywcnp_vendor_cat_rules', true );

					$vendor_rules = $vendor_rules ? $vendor_rules : array();

					if ( ! empty( $_POST['insert'] ) ) {

						foreach ( $category_ids as $category_id ) {

							$vendor_rules[] = $this->add_rule_for_vendor( $category_id, $suggest_price, $minimum_price, $maximum_price, $is_enabled );

							$product_ids = ywcnp_get_product_id_by_category( $category_id );

							foreach ( $product_ids as $product_id ) {
								$default = array(
									'product_id' => $product_id,
									'sugg_price' => $suggest_price,
									'min_price'  => $minimum_price,
									'max_price'  => $maximum_price,

								);

								if ( 'yes' === $is_enabled ) {
									ywcnp_save_product_meta_global_rule( $default );
								} else {
									ywcnp_remove_product_meta_global_rule( $product_id );
								}
							}
						}

						update_user_meta( $this->vendor_id, '_ywcnp_vendor_cat_rules', $vendor_rules );
						update_user_meta( $this->vendor_id, '_ywcnp_vendor_has_rules', $has_rule );

						/* translators: %s: Is the categories array added successfully */
						$message = sprintf( _n( '%s rule added successfully', '%s rules added successfully', count( $category_ids ), 'yith-woocommerce-name-your-price' ), count( $category_ids ) );

					} elseif ( ! empty( $_POST['edit'] ) ) {

						foreach ( $category_ids as $category_id ) {

							$this->update_vendor_rule( $category_id, $suggest_price, $minimum_price, $maximum_price, $is_enabled );

							$product_ids = ywcnp_get_product_id_by_category( $category_id );
							foreach ( $product_ids as $product_id ) {
								$default = array(
									'product_id' => $product_id,
									'sugg_price' => $suggest_price,
									'min_price'  => $minimum_price,
									'max_price'  => $maximum_price,

								);

								if ( 'yes' === $is_enabled ) {
									ywcnp_save_product_meta_global_rule( $default );
								} else {
									ywcnp_remove_product_meta_global_rule( $product_id );
								}
							}
						}

						$message = __( 'Rule updated successfully', 'yith-woocommerce-name-your-price' );

					}
				}
			}
			$table->prepare_items();

			$item = array(
				'ID'            => '',
				'suggest_price' => '',
				'min_price'     => '',
				'max_price'     => '',
				'enabled'       => '',

			);

			if ( 'delete' === $table->current_action() ) {
				$count = 1;
				if ( isset( $_GET['id'] ) && is_array( $_GET['id'] ) ) {
					$count = count( $_GET['id'] );
				}
				/* translators: %s: Is the categories array removed successfully */
				$message = sprintf( _n( '%s rule removed successfully', '%s rules removed successfully', $count, 'yith-woocommerce-name-your-price' ), $count );
			}

			if ( isset( $_GET['id'] ) && ! empty( $_GET['action'] ) && ( 'edit' === $_GET['action'] ) ) {

				$single_rule = $this->get_vendor_rule_by( 'category', sanitize_text_field( wp_unslash( $_GET['id'] ) ) );

				$s_price    = $single_rule ? $single_rule['sugg_price'] : '';
				$min_price  = $single_rule ? $single_rule['min_price'] : '';
				$max_price  = $single_rule ? $single_rule['max_price'] : '';
				$is_enabled = $single_rule ? $single_rule['enabled'] : '';
				$item       = array(
					'ID'            => sanitize_text_field( wp_unslash( $_GET['id'] ) ),
					'suggest_price' => $s_price,
					'min_price'     => $min_price,
					'max_price'     => $max_price,
					'enabled'       => $is_enabled,

				);

				$category      = get_term( sanitize_text_field( wp_unslash( $_GET['id'] ) ), 'product_cat' );
				$data_selected = wp_kses_post( $category->name );
				$value         = sanitize_text_field( wp_unslash( $_GET['id'] ) );
			}
			?>
            <div class="yith-plugin-fw-wp-page-wrapper ywcnp_product_list">
                <div class="wrap">
                    <h1 class="wp-heading-inline">
						<?php
						esc_html_e( 'Category rule list', 'yith-woocommerce-name-your-price' );
						?>
                    </h1>
					<?php

					if ( empty( $_GET['action'] ) || ( 'insert' !== $_GET['action'] && 'edit' !== $_GET['action'] ) ) :
						?>
						<?php
						$query_args   = array(
							'page'   => sanitize_text_field( wp_unslash( $_GET['page'] ) ),
							'action' => 'insert',
						);
						$add_form_url = esc_url( add_query_arg( $query_args, admin_url( 'admin.php' ) ) );
						?>
                        <a class="page-title-action yith-add-button"
                           href="<?php echo esc_attr( $add_form_url ); ?>"><?php esc_html_e( 'Add rule', 'yith-woocommerce-name-your-price' ); ?></a>
					<?php endif; ?>
                    <hr class="wp-header-end"/>
					<?php
					$show_notice_message_type = false;
					$message_to_show          = '';
					if ( ! empty( $notice ) ) {
						$message_to_show          = $notice;
						$show_notice_message_type = 'error';
					}
					if ( ! empty( $message ) ) {
						$message_to_show          = $message;
						$show_notice_message_type = 'success';
					}
					?>
					<?php if ( $show_notice_message_type ) : ?>
                        <div class="notice notice-<?php echo esc_attr( $show_notice_message_type ); ?> is-dismissible"><p><?php echo esc_html( $message_to_show ); ?></p></div>
					<?php
					endif;

					if ( ! empty( $_GET['action'] ) && ( 'insert' === $_GET['action'] || 'edit' === $_GET['action'] ) ) :
						?>

                        <form id="form" method="POST">
                            <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( basename( __FILE__ ) ) ); ?>"/>
                            <table class="form-table ywcnp_product_list">
                                <tbody>
                                <tr valign="top" class="yith-plugin-fw-panel-wc-row ajax-products">
                                    <th scope="row">
                                        <label for="product"><?php esc_html_e( 'Select category', 'yith-woocommerce-name-your-price' ); ?></label>
                                    </th>
                                    <td class="forminp forminp-ajax-products">

										<?php if ( 'edit' === $_GET['action'] ) : ?>
                                            <input id="category_id" name="category_ids" type="hidden"
                                                   value="<?php echo esc_attr( $item['ID'] ); ?>"/>
										<?php endif; ?>

										<?php

										$args = array(
											'id'               => 'category_ids',
											'class'            => 'wc-product-search',
											'name'             => 'category_ids',
											'data-multiple'    => ( 'edit' === $_GET['action'] ) ? false : true,
											'data-placeholder' => __( 'Select Category', 'yith-woocommerce-name-your-price' ),
											'data-selected'    => array( $value => $data_selected ),
											'data-action'      => 'yith_json_search_product_categories',
											'value'            => $value,

										);

										yit_add_select2_fields( $args );
										?>

                                        <span
                                                class="description"><?php esc_html_e( 'Select the product categories to which you want to apply the rule', 'yith-woocommerce-name-your-price' ); ?></span>

                                    </td>
                                </tr>
                                <tr valign="top" class="yith-plugin-fw-panel-wc-row checkbox">
                                    <th scope="row">
                                        <label><?php esc_html_e( 'Rule behavior', 'yith-woocommerce-name-your-price' ); ?></label>
                                    </th>
                                    <td class="forminp forminp-checkbox">
                                        <input type="checkbox"
                                               name="_ywcnp_enable_rule" <?php checked( 'yes', $item['enabled'] ); ?> />
                                        <span
                                                class="description"><?php esc_html_e( 'If selected, the rule will be applied to the products of the selected categories. If not, the rule will be just created but not applied.', 'yith-woocommerce-name-your-price' ); ?></span>
                                    </td>
                                </tr>
                                <tr valign="top" class="yith-plugin-fw-panel-wc-row text">
                                    <th scope="row">
                                        <label><?php esc_html_e( 'Suggested Price', 'yith-woocommerce-name-your-price' ); ?></label>
                                    </th>
                                    <td class="forminp forminp-text">
                                        <input type="text" class="ywcnp_suggest_price wc_input_price"
                                               name="_ywcnp_suggest_price"
                                               value="<?php echo esc_attr( $item['suggest_price'] ); ?>"/>
                                        <span
                                                class="description"><?php esc_html_e( 'Select the suggested price for your product, leave blank not to suggest a price', 'yith-woocommerce-name-your-price' ); ?></span>
                                    </td>
                                </tr>
                                <tr valign="top" class="yith-plugin-fw-panel-wc-row text">
                                    <th scope="row">
                                        <label><?php esc_html_e( 'Minimum Price', 'yith-woocommerce-name-your-price' ); ?></label>
                                    </th>
                                    <td class="forminp forminp-text">
                                        <input type="text" class="ywcnp_min_price wc_input_price" name="_ywcnp_min_price"
                                               value="<?php echo esc_attr( $item['min_price'] ); ?>"/>
                                        <span
                                                class="description"><?php esc_html_e( 'Set the minimum price for your product, leave blank not to set a minimum price', 'yith-woocommerce-name-your-price' ); ?></span>
                                    </td>
                                </tr>
                                <tr valign="top" class="yith-plugin-fw-panel-wc-row text">
                                    <th scope="row">
                                        <label><?php esc_html_e( 'Maximum Price', 'yith-woocommerce-name-your-price' ); ?></label>
                                    </th>
                                    <td class="forminp forminp-text">
                                        <input type="text" class="ywcnp_max_price wc_input_price" name="_ywcnp_max_price"
                                               value="<?php echo esc_attr( $item['max_price'] ); ?>"/>
                                        <span
                                                class="description"><?php esc_html_e( 'Set the maximum price for your product, leave blank not to set a maximum price', 'yith-woocommerce-name-your-price' ); ?></span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <input
                                    id="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['action'] ) ) ); ?>"
                                    name="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['action'] ) ) ); ?>"
                                    type="submit"
                                    value="<?php echo( ( 'insert' === $_GET['action'] ) ? esc_html__( 'Add category rule', 'yith-woocommerce-name-your-price' ) : esc_html__( 'Update category rule', 'yith-woocommerce-name-your-price' ) ); ?>"
                                    class="button-primary"
                            />
                            <a class="button-secondary"
                               href="<?php echo esc_attr( $list_url ); ?>"><?php esc_html_e( 'Return to rule list', 'yith-woocommerce-name-your-price' ); ?></a>
                        </form>
					<?php else : ?>
                        <form id="custom-table" method="GET" action="<?php echo esc_attr( $list_url ); ?>">
                            <input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ); ?>"/>
							<?php $table->display(); ?>
                        </form>
					<?php endif; ?>
                </div>
            </div>
			<?php

		}


		/**
		 * Validate input fields
		 *
		 * @param mixed $item array POST data array.
		 *
		 * @return  bool|string
		 * @since   1.0.0
		 * @author  Alberto Ruggiero
		 *
		 */
		private function validate_fields( $item ) {

			$messages = array();

			if ( empty( $item['category_ids'] ) ) {
				$messages[] = __( 'Select at least one category', 'yith-woocommerce-name-your-price' );
			}

			if ( empty( $messages ) ) {
				return true;
			}

			return implode( '<br />', $messages );

		}

		/**
		 * Add screen options for list table template
		 *
		 * @return  void
		 * @since   1.0.0
		 * @author  Alberto Ruggiero
		 */
		public function add_options() {

			if ( ( isset( $_GET['page'] ) && 'yith_vendor_settings' === $_GET['page'] ) && ( isset( $_GET['tab'] ) && 'category-rules' === $_GET['tab'] ) && ( ! isset( $_GET['action'] ) || ( 'edit' !== $_GET['action'] && 'insert' !== $_GET['action'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification

				$option = 'per_page';

				$args = array(
					'label'   => __( 'Categories', 'yith-woocommerce-name-your-price' ),
					'default' => 10,
					'option'  => 'items_per_page',
				);

				add_screen_option( $option, $args );

			}

		}

		/**
		 * Set screen options for list table template
		 *
		 * @param mixed $status status.
		 * @param mixed $option option.
		 * @param mixed $value value.
		 *
		 * @return  mixed
		 * @since   1.0.0
		 *
		 * @author  Alberto Ruggiero
		 */
		public function set_options( $status, $option, $value ) {

			return ( 'items_per_page' === $option ) ? $value : $status;

		}

		/**
		 * Add_rule_for_vendor
		 *
		 * @param mixed $category_id category_id.
		 * @param mixed $sugg_price sugg_price.
		 * @param mixed $min_price min_price.
		 * @param mixed $max_price max_price.
		 * @param mixed $is_enable is_enable.
		 *
		 * @return array
		 */
		public function add_rule_for_vendor( $category_id, $sugg_price, $min_price, $max_price, $is_enable ) {

			return array(
				'category'   => $category_id,
				'sugg_price' => $sugg_price,
				'min_price'  => $min_price,
				'max_price'  => $max_price,
				'enabled'    => $is_enable,

			);
		}

		/**
		 * Get_vendor_rule_by
		 *
		 * @param string $by by.
		 * @param mixed  $value value.
		 *
		 * @return bool
		 */
		public function get_vendor_rule_by( $by = 'category', $value ) {

			$vendor_rules = get_user_meta( $this->vendor_id, '_ywcnp_vendor_cat_rules', true );

			$vendor_rules = $vendor_rules ? $vendor_rules : array();

			foreach ( $vendor_rules as $key => $rule ) {
				if ( $rule[ $by ] === $value ) {
					return $vendor_rules[ $key ];
				}
			}

			return false;
		}


		/**
		 * Update_vendor_rule
		 *
		 * @param mixed $category category.
		 * @param mixed $sugg_price sugg_price.
		 * @param mixed $min_price min_price.
		 * @param mixed $max_price max_price.
		 * @param mixed $is_enable is_anable.
		 *
		 * @return bool|int
		 */
		public function update_vendor_rule( $category, $sugg_price, $min_price, $max_price, $is_enable ) {

			$item = array(
				'category'   => $category,
				'sugg_price' => $sugg_price,
				'min_price'  => $min_price,
				'max_price'  => $max_price,
				'enabled'    => $is_enable,
			);

			$vendor_rules = get_user_meta( $this->vendor_id, '_ywcnp_vendor_cat_rules', true );
			$vendor_rules = $vendor_rules ? $vendor_rules : array();

			foreach ( $vendor_rules as $key => $rule ) {
				if ( $rule['category'] === $category ) {
					$vendor_rules[ $key ] = $item;
					continue;
				}
			}

			return update_user_meta( $this->vendor_id, '_ywcnp_vendor_cat_rules', $vendor_rules );
		}

	}
}
/**
 * Return single instance
 *
 * @return YWCNP_Category_Rules
 * @since 1.0.0
 * @author YITHEMES
 */
function YWCNP_Category_Vendor_Rule() { // phpcs:ignore WordPress.NamingConventions
	return YWCNP_Category_Vendor_Rules::get_instance();
}

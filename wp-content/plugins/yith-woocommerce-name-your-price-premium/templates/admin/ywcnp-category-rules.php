<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 *  @package YITH WooCommerce Name Your Price Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YWCNP_Category_Rules' ) ) {

	/**
	 * YWCNP_Category_Rules
	 */
	class YWCNP_Category_Rules {


		/**
		 * Single instance of the class
		 *
		 * @var \YWCNP_Category_Rules
		 * @since 1.0.0
		 */
		protected static $instance;


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
		 * @author  YITH <plugins@yithemes.com>
		 */
		public function __construct() {

			add_filter( 'set-screen-option', array( $this, 'set_options' ), 10, 3 );
			add_action( 'current_screen', array( $this, 'add_options' ) );
		}


		/**
		 * Print table
		 *
		 * @since 1.0.0
		 */
		public function output() {

			global $wpdb;

			$table = new YITH_Custom_Table(
				array(
					'singular' => __( 'category', 'yith-woocommerce-name-your-price' ),
					'plural'   => __( 'categories', 'yith-woocommerce-name-your-price' ),
				)
			);

			global $wpdb;
			$termmeta_term_id = 'term_id';

			if ( version_compare( WC()->version, '2.6', '<' ) ) {
				$termmeta_table   = $wpdb->woocommerce_termmeta;
				$termmeta_term_id = 'woocommerce_' . $termmeta_term_id;
			} else {
				$termmeta_table = $wpdb->termmeta;
			}

			$table->options = array(
				'select_table'     => $wpdb->prefix . 'terms a INNER JOIN ' . $wpdb->prefix . 'term_taxonomy b ON a.term_id = b.term_id INNER JOIN ' . $termmeta_table . ' c ON c.' . $termmeta_term_id . ' = a.term_id',
				'select_columns'   => array(
					'a.term_id AS ID',
					'a.name',
					'MAX(CASE WHEN c.meta_key = "_ywcnp_category_has_rule" THEN c.meta_value ELSE NULL END) AS category_has_rule',

				),
				'select_where'     => 'b.taxonomy = "product_cat" AND ( c.meta_key = "_ywcnp_category_has_rule" ) AND c.meta_value = "yes"',
				'select_group'     => 'a.term_id',
				'select_order'     => 'a.name',
				'select_order_dir' => 'ASC',
				'per_page_option'  => 'items_per_page',
				'count_table'      => '( SELECT COUNT(*) FROM ' . $wpdb->prefix . 'terms a INNER JOIN ' . $wpdb->prefix . 'term_taxonomy b ON a.term_id = b.term_id INNER JOIN ' . $termmeta_table . ' c ON c.' . $termmeta_term_id . ' = a.term_id WHERE b.taxonomy = "product_cat" AND ( c.meta_key = "_ywcnp_category_has_rule" ) AND c.meta_value = "yes" GROUP BY a.term_id ) AS count_table',
				'count_where'      => '',
				'key_column'       => 'ID',
				'view_columns'     => array(
					'cb'           => '<input type="checkbox" />',
					'category'     => __( 'Category', 'yith-woocommerce-name-your-price' ),
					'suggest'      => __( 'Suggested Price', 'yith-woocommerce-name-your-price' ),
					'min_price'    => __( 'Minimum Price', 'yith-woocommerce-name-your-price' ),
					'max_price'    => __( 'Maximum Price', 'yith-woocommerce-name-your-price' ),
					'enabled_rule' => __( 'Enabled', 'yith-woocommerce-name-your-price' ),
				),
				'hidden_columns'   => array(),
				'sortable_columns' => array(
					'category' => array( 'post_title', true ),
				),
				'custom_columns'   => array(
					'column_category'     => function ( $item, $me ) {

						$edit_query_args = array(
							'page'   => isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification
							'tab'    => isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification,
							'action' => 'edit',
							'id'     => $item['ID'],
						);
						$edit_url        = esc_url( add_query_arg( $edit_query_args, admin_url( 'admin.php' ) ) );

						$delete_query_args = array(
							'page'   => isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification
							'tab'    => isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification,
							'action' => 'delete',
							'id'     => $item['ID'],
						);
						$delete_url        = esc_url( add_query_arg( $delete_query_args, admin_url( 'admin.php' ) ) );

						$actions = array(
							'edit'   => '<a href="' . $edit_url . '">' . __( 'Edit rule', 'yith-woocommerce-name-your-price' ) . '</a>',
							'delete' => '<a href="' . $delete_url . '">' . __( 'Remove rule from list', 'yith-woocommerce-name-your-price' ) . '</a>',
						);

						return sprintf( '<strong><a class="tips" href="%s" data-tip="%s">#%d %s </a></strong> %s', $edit_url, __( 'Edit rule', 'yith-woocommerce-name-your-price' ), $item['ID'], $item['name'], $me->row_actions( $actions ) );
					},
					'column_suggest'      => function ( $item, $me ) {

						$price = ywcnp_get_woocommerce_term_meta( $item['ID'], '_ywcnp_suggest_price', true );

						return empty( $price ) ? '<span>' . __( 'No Price', 'yith-woocommerce-name-your-price' ) . '</span>' : wc_price( $price );
					},
					'column_min_price'    => function ( $item, $me ) {

						$price = ywcnp_get_woocommerce_term_meta( $item['ID'], '_ywcnp_min_price', true );

						return empty( $price ) ? '<span>' . __( 'No Price', 'yith-woocommerce-name-your-price' ) . '</span>' : wc_price( $price );

					},
					'column_max_price'    => function ( $item, $me ) {

						$price = ywcnp_get_woocommerce_term_meta( $item['ID'], '_ywcnp_max_price', true );

						return empty( $price ) ? '<span>' . __( 'No Price', 'yith-woocommerce-name-your-price' ) . '</span>' : wc_price( $price );

					},
					'column_enabled_rule' => function ( $item, $me ) {

						$is_enabled = ywcnp_get_woocommerce_term_meta( $item['ID'], '_ywcnp_enable_rule', true );
						if ( 'yes' === $is_enabled ) {
							$class = 'show';
							$tip   = __( 'Enabled', 'yith-woocommerce-name-your-price' );
						} else {
							$class = 'hide';
							$tip   = __( 'Disabled', 'yith-woocommerce-name-your-price' );
						}

						return sprintf( '<mark class="%s tips" data-tip="%s">%s</mark>', $class, $tip, $tip );

					},
				),
				'bulk_actions'     => array(
					'actions'   => array(
						'delete' => __( 'Remove rule from list', 'yith-woocommerce-name-your-price' ),
					),
					'functions' => array(
						'function_delete' => function () {
							global $wpdb;

							$ids = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : array(); //phpcs:ignore WordPress.Security.NonceVerification
							$ids = is_array( $ids ) ? $ids : explode( ',', $ids );

							if ( is_array( $ids ) ) {
								foreach ( $ids as $id ) {

									$product_ids = ywcnp_get_product_id_by_category( $id );

									foreach ( $product_ids as $product_id ) {
										ywcnp_remove_product_meta_global_rule( $product_id );

									}
								}

								$ids = implode( ',', $ids );
							}

							$termmeta_term_id = 'term_id';

							if ( version_compare( WC()->version, '2.6', '<' ) ) {
								$termmeta_table   = $wpdb->woocommerce_termmeta;
								$termmeta_term_id = 'woocommerce_' . $termmeta_term_id;
							} else {
								$termmeta_table = $wpdb->termmeta;
							}

							if ( ! empty( $ids ) ) {

								$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
									"UPDATE {$termmeta_table} SET meta_value='no' WHERE ( meta_key = '_ywcnp_category_has_rule' ) AND {$termmeta_term_id} IN ( $ids )" //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
								);
							}

						},
					),
				),
			);

			$list_query_args = array(
				'page' => isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification
				'tab'  => isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification,
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

					$category_ids = isset( $_POST['category_ids'] ) ?  wp_unslash( $_POST['category_ids'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					if ( ! is_array( $category_ids ) ) {
						$category_ids = explode( ',', $category_ids );
					}

					$suggest_price = isset( $_POST['_ywcnp_suggest_price'] ) ? sanitize_text_field( wp_unslash( $_POST['_ywcnp_suggest_price'] ) ) : '';
					$minimum_price = isset( $_POST['_ywcnp_min_price'] ) ? sanitize_text_field( wp_unslash( $_POST['_ywcnp_min_price'] ) ) : '';
					$maximum_price = isset( $_POST['_ywcnp_max_price'] ) ? sanitize_text_field( wp_unslash( $_POST['_ywcnp_max_price'] ) ) : '';
					$is_enabled    = isset( $_POST['_ywcnp_enable_rule'] ) ? 'yes' : 'no';
					$has_rule      = 'yes';

					foreach ( $category_ids as $category_id ) {

						ywcnp_update_woocommerce_term_meta( $category_id, '_ywcnp_suggest_price', $suggest_price );
						ywcnp_update_woocommerce_term_meta( $category_id, '_ywcnp_min_price', $minimum_price );
						ywcnp_update_woocommerce_term_meta( $category_id, '_ywcnp_max_price', $maximum_price );
						ywcnp_update_woocommerce_term_meta( $category_id, '_ywcnp_category_has_rule', $has_rule );
						ywcnp_update_woocommerce_term_meta( $category_id, '_ywcnp_enable_rule', $is_enabled );

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

					if ( ! empty( $_POST['insert'] ) ) {
						/* translators: %s: Is the categories array added successfully */
						$message = sprintf( _n( '%s rule added successfully', '%s rules added successfully', count( $category_ids ), 'yith-woocommerce-name-your-price' ), count( $category_ids ) );

					} elseif ( ! empty( $_POST['edit'] ) ) {

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
				'enabled'       => 'no',

			);

			if ( 'delete' === $table->current_action() ) {
				$ids = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : array();
				$ids = is_array( $ids ) ? $ids : explode( ',', $ids );
				/* translators: %s: Is the categories array removes successfully */
				$message = sprintf( _n( '%s category removed successfully', '%s categories removed successfully', count( $ids ), 'yith-woocommerce-name-your-price' ), count( $ids ) );
			}

			if ( isset( $_GET['id'] ) && ! empty( $_GET['action'] ) && ( 'edit' === $_GET['action'] ) ) {

				$item = array(
					'ID'            => sanitize_text_field( wp_unslash( $_GET['id'] ) ),
					'suggest_price' => ywcnp_get_woocommerce_term_meta( sanitize_text_field( wp_unslash( $_GET['id'] ) ), '_ywcnp_suggest_price', true ),
					'min_price'     => ywcnp_get_woocommerce_term_meta( sanitize_text_field( wp_unslash( $_GET['id'] ) ), '_ywcnp_min_price', true ),
					'max_price'     => ywcnp_get_woocommerce_term_meta( sanitize_text_field( wp_unslash( $_GET['id'] ) ), '_ywcnp_max_price', true ),
					'enabled'       => ywcnp_get_woocommerce_term_meta( sanitize_text_field( wp_unslash( $_GET['id'] ) ), '_ywcnp_enable_rule', true ),
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
								'page'   => isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification
								'tab'    => isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification,
								'action' => 'insert',
							);
							$add_form_url = esc_url( add_query_arg( $query_args, admin_url( 'admin.php' ) ) );
							?>
							<a class="page-title-action yith-add-button"
							href="<?php echo esc_attr( $add_form_url ); ?>"><?php esc_html_e( 'Add rule', 'yith-woocommerce-name-your-price' ); ?></a>
						<?php endif; ?>
					<hr class="wp-header-end" />
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
											'id'          => 'category_ids',
											'class'       => 'wc-product-search',
											'name'        => 'category_ids',
											'data-multiple' => ( 'edit' === $_GET['action'] ) ? false : true,
											'data-placeholder' => __( 'Select Category', 'yith-woocommerce-name-your-price' ),
											'data-selected' => array( $value => $data_selected ),
											'data-action' => 'yith_json_search_product_categories',
											'value'       => $value,

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
							<input type="hidden" name="tab" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ); ?>"/>
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
		 */
		public function add_options() {

			if ( 'yith-plugins_page_yith_wcnp_panel' === get_current_screen()->id && ( isset( $_GET['tab'] ) && 'category-rules' === $_GET['tab'] ) && ( ! isset( $_GET['action'] ) || ( 'edit' !== $_GET['action'] && 'insert' !== $_GET['action'] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification

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
		 * @param  mixed $status status.
		 * @param  mixed $option option.
		 * @param  mixed $value value.
		 *
		 * @return  mixed
		 * @since   1.0.0
		 *
		 */
		public function set_options( $status, $option, $value ) {

			return ( 'items_per_page' === $option ) ? $value : $status;

		}

	}
}
/**
 * Return single instance
 *
 * @return YWCNP_Category_Rules
 * @since 1.0.0
 */
function YWCNP_Category_Rule() { // phpcs:ignore WordPress.NamingConventions
	return YWCNP_Category_Rules::get_instance();
}

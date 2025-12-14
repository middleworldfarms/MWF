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


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


if ( ! class_exists( 'YITH_Category_Rule_Vendor_Table' ) ) {
	/**
	 * Class YITH_Category_Rule_Vendor_Table
	 *
	 * List vendor category rules
	 *
	 * array(
	 *
	 *  array( 'category' => '',
	 *         'sugg_price' => '',
	 *         'min_price'  => '',
	 *         'max_price' => '',
	 *         'enabled'   => 'no'
	 *      ),
	 *  .......
	 * )
	 */
	class YITH_Category_Rule_Vendor_Table extends WP_List_Table {

		/**
		 * Vendor id
		 *
		 * @var int, current vendor id
		 */
		public $vendor_id;

		/**
		 * __construct function
		 */
		public function __construct() {

			global $status, $page;
			parent::__construct(
				array(
					'singular' => _x( 'rule', 'yith-woocommerce-name-your-price' ),     // singular name of the listed records !
					'plural'   => _x( 'rules', 'yith-woocommerce-name-your-price' ),    // plural name of the listed records !
					'ajax'     => false,        // does this table support ajax?
				)
			);
		}

		/**
		 * Column_default
		 *
		 * @param object $item item.
		 * @param string $column_name column_name.
		 *
		 * @return mixed
		 * @since 1.0.0
		 * @author YITH <plugins@yithemes.com>
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				case 'sugg_price':
				case 'min_price':
				case 'max_price':
					return empty( $item[ $column_name ] ) ? '<span>' . __( 'No Price', 'yith-woocommerce-name-your-price' ) . '</span>' : wc_price( $item[ $column_name ] );
					break;
				case 'enabled':
					$is_enabled = $item[ $column_name ];
					if ( 'yes' === $is_enabled ) {
						$class = 'show';
						$tip   = __( 'Enabled', 'yith-woocommerce-name-your-price' );
					} else {
						$class = 'hide';
						$tip   = __( 'Disabled', 'yith-woocommerce-name-your-price' );
					}

					return sprintf( '<mark class="%s tips" data-tip="%s">%s</mark>', $class, $tip, $tip );
					break;
				default:
					return print_r( $item, true );
			}
		}

		/**
		 * Get column category
		 *
		 * @param array $item item.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function column_category( $item ) {

			$edit_query_args   = array(
				'page'   => isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification
				'action' => 'edit',
				'id'     => $item['category'],
			);
			$delete_query_args = array(
				'page'   => isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '', //phpcs:ignore WordPress.Security.NonceVerification
				'action' => 'delete',
				'id'     => $item['category'],
			);
			$edit_url          = esc_url( add_query_arg( $edit_query_args, admin_url( 'admin.php' ) ) );
			$delete_url        = esc_url( add_query_arg( $delete_query_args, admin_url( 'admin.php' ) ) );
			$actions           = array(
				'edit'   => '<a href="' . $edit_url . '">' . __( 'Edit rule', 'yith-woocommerce-name-your-price' ) . '</a>',
				'delete' => '<a href="' . $delete_url . '">' . __( 'Remove rule from list', 'yith-woocommerce-name-your-price' ) . '</a>',
			);

			$category_id   = $item['category'];
			$category_name = get_term_by( 'term_id', $category_id, 'product_cat' );

			return sprintf(
				'<strong><a class="tips" href="%s" data-tip="%s">#%d %s </a></strong> %s',
				$edit_url,
				__( 'Edit rule', 'yith-woocommerce-name-your-price' ),
				$category_id,
				$category_name->name,
				$this->row_actions( $actions )
			);
		}

		/**
		 * Get column checkbox
		 *
		 * @param object $item item.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="id[]" value="%1$s" />',
				/*$2%s*/
				$item['category']                // The value of the checkbox should be the record's id !
			);
		}

		/**
		 * Get columns
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function get_columns() {
			$columns = array(
				'cb'         => '<input type="checkbox" />', // Render a checkbox instead of text !
				'category'   => __( 'Category', 'yith-woocommerce-name-your-price' ),
				'sugg_price' => __( 'Suggested Price', 'yith-woocommerce-name-your-price' ),
				'min_price'  => __( 'Minimum Price', 'yith-woocommerce-name-your-price' ),
				'max_price'  => __( 'Maximum Price', 'yith-woocommerce-name-your-price' ),
				'enabled'    => __( 'Enabled', 'yith-woocommerce-name-your-price' ),
			);

			return $columns;
		}

		/**
		 * Get sortable columns
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'category' => array( 'category', false ),     // true means it's already sorted !
			);

			return $sortable_columns;
		}

		/**
		 * Get bulck actions
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function get_bulk_actions() {
			$actions = array(
				'delete' => 'Delete',
			);

			return $actions;
		}


		/**
		 * Add delete bulk action
		 *
		 * @since 1.0.0
		 */
		public function process_bulk_action() {

			// Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() ) {

				if ( isset( $_GET['id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

					$items = get_user_meta( $this->vendor_id, '_ywcnp_vendor_cat_rules', true );

					$items = $items ? $items : array();

					$ids = is_array( $_GET['id'] ) ? array_map( 'intval', wp_unslash( $_GET['id'] ) ) : array( intval( wp_unslash( $_GET['id'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification

					foreach ( $ids as $id ) {
						foreach ( $items as $key => $item ) {

							if ( intval($item['category'] ) === $id ) {
								unset( $items[ $key ] );
							}
						}
					}

					update_user_meta( $this->vendor_id, '_ywcnp_vendor_cat_rules', $items );
				}
			}

		}

		/**
		 * Prepare items
		 *
		 * @since 1.0.0
		 */
		public function prepare_items() {

			$per_page = 10;

			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->process_bulk_action();

			$items = get_user_meta( $this->vendor_id, '_ywcnp_vendor_cat_rules', true );
			$items = $items ? $items : array();

			/**
			 * Ywcnp_usort_reorder
			 *
			 * @param mixed $a a.
			 * @param mixed $b b.
			 *
			 * @return $result
			 */
			function ywcnp_usort_reorder( $a, $b ) {
				$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'category'; // phpcs:ignore WordPress.Security.NonceVerification, If no sort default to category name, !
				$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'asc'; // phpcs:ignore WordPress.Security.NonceVerification, If no order, default to asc !
				$result  = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order !

				return ( 'asc' === $order ) ? $result : - $result; // Send final sort direction to usort !
			}

			usort( $items, 'ywcnp_usort_reorder' );

			$current_page = $this->get_pagenum();
			$total_items  = count( $items );

			$items = array_slice( $items, ( ( $current_page - 1 ) * $per_page ), $per_page );

			/**
			 * REQUIRED. Now we can add our *sorted* data to the items property, where
			 * it can be used by the rest of the class.
			 */
			$this->items = $items;

			/**
			 * REQUIRED. We also have to register our pagination options & calculations.
			 */
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,                  // WE have to calculate the total number of items !
					'per_page'    => $per_page,                     // WE have to determine how many items to show on a page !
					'total_pages' => ceil( $total_items / $per_page ),   // WE have to calculate the total number of pages !
				)
			);
		}
	}
}

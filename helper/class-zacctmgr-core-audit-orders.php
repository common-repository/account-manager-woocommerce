<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	class ZACCTMGR_Core_Audit_Order extends WP_List_Table {

		private $order;

		public function __construct( $order ) {
			$this->order = $order;
			parent::__construct(
				array(
					'singular' => 'audit_order',
					'plural'   => 'audit_orders',
					'ajax'     => false,
				)
			);
		}

		public function print_overview() {
			$this->prepare_items();
			echo '<div class="zacctmgr_overview_wrap">';
			echo '<hr class="wp-header-end"/>';
			echo '<h2 class="screen-reader-text">Audit Order List</h2>';
			if ( count( $this->items ) == 0 ) {
				echo '<div class="zacctmgr_row" style="margin: 3rem auto; display: block; text-align: center;">';
				echo '<h2 style="font-size: 26px; color: #737373;">No changes were made on this order</h2>';
				echo '</div>';
			} else {
				$this->display();
			}
			echo '</div>';
		}

		public function column_default( $order_audit, $column_name ) {
			$user = get_user_by( 'id', $order_audit->user_id );

			switch ( $column_name ) {
				case 'username':
					return get_avatar( $user->ID ) . '<a style="margin-left:1rem;" href="' . get_edit_user_link( $user->ID ) . '">' . $user->user_nicename . '</a>';
				case 'name':
					return '<span>' . $user->display_name . '</span>';
				case 'action':
					return '<span>' . $order_audit->action . '</span>';
				case 'old_value':
					if ( $order_audit->is_commission_change == 1 ) {
						if ( $order_audit->old_value != '' ) {
							return '<span>$' . number_format( $order_audit->old_value, 2, '.', ',' ) . '</span>';
						} else {
							return '<span>$' . number_format( 0, 2, '.', ',' ) . '</span>';
						}
					} else {
						return '<span>' . $order_audit->old_value . '</span>';
					}
				case 'new_value':
					if ( $order_audit->is_commission_change == 1 ) {
						return '<span>$' . number_format( $order_audit->new_value, 2, '.', ',' ) . '</span>';
					} else {
						return '<span>' . $order_audit->new_value . '</span>';
					}
				case 'timestamp':
					return '<span>' . $order_audit->timestamp . '</span>';
			}

			return '';
		}

		public function display_tablenav( $which ) {
			echo '<div style="display:none"></div>';
		}

		public function get_columns() {
			$columns = array(
				'username'  => 'Username',
				'name'      => 'Name',
				'action'    => 'Action',
				'old_value' => 'Old Value',
				'new_value' => 'New Value',
				'timestamp' => 'Timestamp',
			);

			return $columns;
		}

		public function prepare_items() {
			/**
			 * Init column headers.
			 */
			$this->_column_headers = array( $this->get_columns(), array(), array() );

			global $wpdb;

			$table_name = $wpdb->prefix . 'zacctmgr_acm_order_audit_mapping';

			$order_id = $this->order->get_id();

			$query = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_id=$order_id ORDER BY timestamp DESC; " );

			$this->items = $query;
		}
	}

?>
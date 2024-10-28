<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}
	
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}
	
	class ZACCTMGR_Core_Audit_Manager_Commission extends WP_List_Table {
		
		private $manager_id;
		
		public function __construct( $manager_id ) {
			$this->manager_id = $manager_id;
			parent::__construct(
				array(
					'singular' => 'audit_manager_commission',
					'plural'   => 'audit_manager_commissions',
					'ajax'     => false,
				)
			);
		}
		
		public function print_overview() {
			$this->prepare_items();
			echo '<div class="zacctmgr_overview_wrap">';
			echo '<hr class="wp-header-end"/>';
			echo '<h2 class="screen-reader-text">Audit Log</h2>';
			if ( count( $this->items ) == 0 ) {
				echo '<div class="zacctmgr_row" style="margin: 3rem auto; display: block; text-align: center;">';
				echo '<h2 style="font-size: 26px; color: #737373;">No changes were made on this account manager</h2>';
				echo '</div>';
			} else {
				$this->display();
			}
			echo '</div>';
		}
		
		public function column_default( $manager_audit, $column_name ) {
			$user = get_user_by( 'id', $manager_audit->user_id );
			
			switch ( $column_name ) {
				case 'username':
					return get_avatar( $user->ID ) . '<a style="margin-left:1rem;" href="' . get_edit_user_link( $user->ID ) . '">' . $user->user_nicename . '</a>';
				case 'name':
					return '<span>' . $user->display_name . '</span>';
				case 'action':
					return '<span>' . $manager_audit->action . '</span>';
				case 'old_value':
					return '<span>' . $manager_audit->old_value . '</span>';
				case 'new_value':
					return '<span>' . $manager_audit->new_value . '</span>';
				case 'timestamp':
					return '<span>' . $manager_audit->timestamp . '</span>';
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
			
			$table_name = $wpdb->prefix . 'zacctmgr_acm_manager_commission_audit_mapping';
			
			$query = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$this->manager_id ORDER BY timestamp DESC; " );
			
			$this->items = $query;
		}
	}
	?>
<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	function zacctmgr_sanitize_array( $data = [] ) {
		if ( $data ) {
			foreach ( $data as &$value ) {
				$value = sanitize_text_field( $value );
			}
		}

		return $data;
	}

	/**
	 * Retrieve list of eligible users who can be manager
	 *
	 * @param array $roles Optional. The roles that are eligible to be manager
	 *
	 * @return array List of users (WP_User).
	 * @see WP_User
	 *
	 */
	function zacctmgr_get_em_users( $roles = null ) {
		if ( ! $roles ) {
			$roles = zacctmgr_get_selected_roles();
		}

		$users = get_users( array(
			'role__in' => $roles
		) );

		return $users;
	}

	/**
	 * Retrieve the manager_id for an user
	 *
	 * @param $user_id . The user id for which you want the manager_id
	 *
	 * @return int manager_id.
	 */
	function zacctmgr_get_manager_id( $user_id ) {
		return (int) get_the_author_meta( 'zacctmgr_assigned', $user_id );
	}

	/**
	 * Set or Update manager_id for an user
	 *
	 * @param $user_id . The user_id of the user
	 * @param $manager_id . The manager_id to be set
	 *
	 * @return void.
	 */
	function zacctmgr_set_manager_id( $user_id, $manager_id ) {
		$current_manager_id = get_user_meta( $user_id, 'zacctmgr_assigned', true );
		if ( $current_manager_id != $manager_id ) {
			$timestamp = date( "Y-m-d H:i:s" );
			update_user_meta( $user_id, 'zacctmgr_assigned', $manager_id );
			update_user_meta( $user_id, 'zacctmgr_assigned_date', $timestamp );
			$assignment = array(
				'customer_id' => $user_id,
				'manager_id'  => $manager_id,
				'timestamp'   => $timestamp
			);

			zacctmgr_insert_account_manager_assignment( $assignment );
		}
	}

	/**
	 * Retrieve all the roles
	 *
	 * @return array of WP_Roles.
	 */
	function zacctmgr_get_roles() {
		global $wp_roles;

		$all_roles      = $wp_roles->roles;
		$editable_roles = apply_filters( 'editable_roles', $all_roles );

		return $editable_roles;
	}

	/**
	 * Retrieve the selected roles option
	 *
	 * @default administrator, shop_manager
	 *
	 * @return array of WP_Roles.
	 */
	function zacctmgr_get_selected_roles() {
		return get_option( 'zacctmgr_selected_roles', [ 'administrator', 'shop_manager' ] );
	}

	/**
	 * Retrieve the allowed WooCommerce statuses option
	 *
	 * @default processing, completed
	 *
	 * @return array of allowed statuses.
	 */
	function zacctmgr_get_allowed_wc_statuses() {
		return get_option( 'zacctmgr_allowed_woo_statuses', [ 'wc-processing', 'wc-completed' ] );
	}

	/**
	 * Retrieve the refund commission calculation method
	 *
	 * @default no_change
	 *
	 * @return string
	 */
	function zacctmgr_refund_commission_setting() {
		return get_option( 'zacctmgr_refund_commission_setting', 'no_change' );
	}

	/**
	 * Retrieve the allowed users to edit commission option
	 *
	 * @return array of allowed users.
	 */
	function zacctmgr_get_allowed_edit_commission_users() {
		return get_option( 'zacctmgr_allowed_edit_commission_users', [] );
	}

	/**
	 * Retrieve the Users Allowed to Edit Commission option
	 *
	 * @return mixed zacctmgr_user_allow_edit_commission_setting
	 */
	function zacctmgr_get_user_allow_edit_commission_setting() {
		return get_option( 'zacctmgr_user_allow_edit_commission_setting', 'administrators' );
	}

	/**
	 * Retrieve the Users Allowed to Edit Orders option
	 *
	 * @return mixed zacctmgr_user_allow_edit_order_commission_setting
	 */
	function zacctmgr_get_user_allow_edit_order_commission_setting() {
		return get_option( 'zacctmgr_user_allow_edit_order_commission_setting', 'administrators' );
	}

	/**
	 * Retrieve the allowed users to edit order commission and assignments option (edit orders)
	 *
	 * @return array of allowed users.
	 */
	function zacctmgr_get_allowed_edit_order_commission_users() {
		return get_option( 'zacctmgr_allowed_edit_order_commission_users', [] );
	}

	/**
	 * Retrieve the allowed users to edit others commission option (edit customers)
	 *
	 * @return array of allowed users.
	 */
	function zacctmgr_get_allowed_edit_others_commission_users() {
		return get_option( 'zacctmgr_allowed_edit_others_commission_users', [] );
	}

	/**
	 * Retrieve the Users Allowed to Edit Customers option
	 *
	 * @return mixed zacctmgr_user_allow_edit_others_commission_setting
	 */
	function zacctmgr_get_user_allow_edit_others_commission_setting() {
		return get_option( 'zacctmgr_user_allow_edit_others_commission_setting', 'administrators' );
	}

	/**
	 * Retrieve the Customer Accounts Allow No Account Manager Assignment option
	 *
	 * @return mixed zacctmgr_allowed_no
	 */
	function zacctmgr_get_allowed_no_manager() {
		return get_option( 'zacctmgr_allowed_no', 0 );
	}

	/**
	 * Retrieve the default manager for new customer option
	 *
	 * @return mixed zacctmgr_default.
	 */
	function zacctmgr_get_default_manager() {
		return get_option( 'zacctmgr_default', 0 );
	}

	/**
	 * Retrieve the Hide "Setting" Page in the menu option
	 *
	 * @return mixed zacctmgr_hide_settings_in_menu
	 */
	function zacctmgr_get_hide_settings_in_menu() {
		return get_option( 'zacctmgr_hide_settings_in_menu', 0 );
	}

	/**
	 * Retrieve the "Modified order values recalculate commission" value
	 *
	 * @return mixed zacctmgr_hide_settings_in_menu
	 */
	function zacctmgr_order_recalculate_commission() {
		return get_option( 'zacctmgr_order_recalculate_commission', 'yes' );
	}

	/**
	 * Retrieve who can access "Settings" page option
	 *
	 * @return mixed zacctmgr_hide_settings_in_menu
	 */
	function zacctmgr_get_user_access_settings() {
		return get_option( 'zacctmgr_user_access_settings', 'administrators' );
	}

	/**
	 * Retrieve list of top customers
	 *
	 * @param integer $limit Optional. The limit for search
	 *
	 * @return array List of customers (WP_User).
	 * @see WP_User
	 *
	 */
	function zacctmgr_get_top_customers( $limit = 5 ) {
		$users = get_users();

		$items = [];
		if ( $users ) {
			foreach ( $users as $user ) {
				$user->total = (float) ( wc_get_customer_total_spent( $user->ID ) );
				$items[]     = $user;
			}
		}

		if ( count( $items ) > 1 ) {
			usort( $items, "zacctmgr_cmp_function" );
		}

		if ( count( $items ) > $limit ) {
			$items = array_slice( $items, 0, $limit );
		}

		return $items;
	}

	/**
	 * Retrieve list of top Managers
	 *
	 * @param integer $limit Optional. The limit for search
	 *
	 * @return array List of Managers (WP_User).
	 * @see WP_User
	 *
	 */
	function zacctmgr_get_top_managers( $limit = 10 ) {
		$users = get_users();

		$items = [];

		if ( $users ) {
			foreach ( $users as $user ) {
				$total      = (float) ( wc_get_customer_total_spent( $user->ID ) );
				$manager_id = zacctmgr_get_manager_id( $user->ID );

				if ( $manager_id == 0 ) {
					continue;
				}
				$key = 'manager_' . $manager_id;
				if ( isset( $items[ $key ] ) ) {
					$items[ $key ]['total'] += $total;
					$items[ $key ]['child'] ++;
				} else {
					$manager_data = get_user_by( 'ID', $manager_id );
					if ( ! $manager_data ) {
						continue;
					}

					$items[ $key ]['total'] = $total;
					$items[ $key ]['child'] = 1;
				}
			}
		}

		$newItems = [];

		if ( count( $items ) > 0 ) {
			foreach ( $items as $key => $value ) {
				$key        = str_replace( 'manager_', '', $key );
				$manager_id = (int) $key;

				$object        = new stdClass;
				$object->ID    = $manager_id;
				$object->total = $value['total'];
				$object->child = $value['child'];

				$newItems[] = $object;
			}
		}

		if ( count( $newItems ) > 1 ) {
			usort( $newItems, "zacctmgr_cmp_function" );
		}

		if ( count( $newItems ) > $limit ) {
			$newItems = array_slice( $newItems, 0, $limit );
		}

		return $newItems;
	}

	function zacctmgr_cmp_function( $a, $b ) {
		if ( $a->total == $b->total ) {
			return 0;
		}

		return ( $a->total > $b->total ) ? - 1 : 1;
	}

	/**
	 * Retrieve list of Managers by criteria if provided
	 *
	 * @param string $search . Optional The search criteria
	 *
	 * @return WP_User_Query result of the search.
	 * @see WP_User
	 *
	 */
	function zacctmgr_get_managers_query_by_key( $search = '' ) {
		$current_page = $offset = 0;
		$per_page     = - 1;

		$query = new WP_User_Query(
			array(
				'number'     => $per_page,
				'offset'     => $offset,
				'role__in'   => zacctmgr_get_selected_roles(),
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'     => 'first_name',
						'value'   => $search,
						'compare' => 'LIKE'
					),
					array(
						'key'     => 'last_name',
						'value'   => $search,
						'compare' => 'LIKE'
					)
				)
			)
		);

		return $query;
	}

	/**
	 * Retrieve list of Customers by criteria if provided
	 *
	 * @param string $search . Optional The search criteria
	 *
	 * @return WP_User_Query result of the search.
	 * @see WP_User
	 *
	 */
	function zacctmgr_get_customers_query_by_key( $search = '' ) {
		$current_page = $offset = 0;
		$per_page     = - 1;

		$query = new WP_User_Query(
			array(
				'number'     => $per_page,
				'offset'     => $offset,
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'     => 'first_name',
						'value'   => $search,
						'compare' => 'LIKE'
					),
					array(
						'key'     => 'last_name',
						'value'   => $search,
						'compare' => 'LIKE'
					),
					array(
						'key'     => 'billing_company',
						'value'   => $search,
						'compare' => 'LIKE'
					),
				)
			)
		);

		return $query;
	}

	/**
	 * Retrieve list of Managers pagination functionality included in the params
	 *
	 * @param array $params . Optional containing: current_page and per_page for pagination purposes
	 *
	 * @return WP_User_Query result of the search.
	 * @see WP_User
	 *
	 */
	function zacctmgr_get_managers_query( $params = [] ) {
		$current_page = 0;
		$per_page     = 20;
		extract( $params );

		$offset = $current_page != 0 ? ( $current_page - 1 ) * $per_page : 0;

		$query = new WP_User_Query(
			array(
				'role__in' => zacctmgr_get_selected_roles(),
				'number'   => $per_page,
				'offset'   => $offset
			)
		);

		return $query;
	}

	/**
	 * Retrieve list of customer pagination functionality included in the params
	 *
	 * @param array $params . Optional containing: current_page and per_page for pagination purposes
	 *
	 * @return WP_User_Query result of the search.
	 * @see WP_User
	 *
	 */
	function zacctmgr_get_customers_query( $params = array(), $orderby = '', $order = '' ) {
		$current_page = $manager_id = 0;
		$per_page     = - 1;
		$name         = '';

		extract( $params );

		$offset = $current_page != 0 ? ( $current_page - 1 ) * $per_page : 0;

		$final = [];

		if ( $orderby == 'spent' ) {
			$final['meta_key'] = '_money_spent';
			$final['orderby']  = 'meta_value_num';
			$final['order']    = $order;
		} elseif ( $orderby == 'accountmanager' ) {

		}

		if ( $name != '' ) {
			$final['meta_query'] = [
				'relation' => 'OR',
				[
					'key'     => 'first_name',
					'value'   => $name,
					'compare' => 'LIKE'
				],
				[
					'key'     => 'last_name',
					'value'   => $name,
					'compare' => 'LIKE'
				],
				[
					'key'     => 'billing_company',
					'value'   => $name,
					'compare' => 'LIKE'
				]
			];
		}

		if ( $manager_id != 0 ) {
			$final['meta_key']   = 'zacctmgr_assigned';
			$final['meta_value'] = $manager_id;
		}


		$final['number'] = $per_page;
		$final['offset'] = $offset;

		$query = new WP_User_Query( $final );

		return $query;
	}

	/**
	 * Retrieve list of customers for a manager
	 *
	 * @param $params array of params containing $manager_id, $start_date, $end_date.
	 *
	 * @return array of WP_User result of the search.
	 * @see WP_User
	 *
	 */
	function zacctmgr_get_customer_list_by_manager( $params ) {
		$manager_id = $params['manager_id'];
		$start_date = $params['start_date'];
		$end_date   = $params['end_date'];

		global $wpdb;

		$table_name = $wpdb->prefix . 'zacctmgr_acm_assignments_mapping';
		if ( isset( $start_date ) && isset( $end_date ) ) {
			$results = $wpdb->get_results( "SELECT customer_id FROM $table_name WHERE timestamp >= '$start_date' AND timestamp <= '$end_date' AND manager_id=$manager_id;" );
		} else {
			$results = $wpdb->get_results( "SELECT customer_id FROM $table_name WHERE manager_id=$manager_id;" );
		}
		$customers = [];

		if ( $results ) {
			foreach ( $results as $result ) {
				$customers[] = (int) $result->customer_id;
			}
		}

		return $customers;
	}

	/**
	 * Retrieve the timestamp of assigning for a customer and his manager
	 *
	 * @param $params array of params containing $manager_id, $start_date, $end_date.
	 *
	 * @return array of WP_User result of the search.
	 * @see WP_User
	 *
	 */
	function zacctmgr_get_customer_assignment_timestamp( $params ) {
		$manager_id  = $params['manager_id'];
		$start_date  = $params['start_date'];
		$end_date    = $params['end_date'];
		$customer_id = $params['customer_id'];

		global $wpdb;

		$table_name = $wpdb->prefix . 'zacctmgr_acm_assignments_mapping';

		$result = $wpdb->get_results( "SELECT timestamp FROM $table_name WHERE timestamp >= '$start_date' AND timestamp <= '$end_date' AND manager_id=$manager_id AND customer_id=$customer_id;" );

		return $result[0]->timestamp;
	}

	/**
	 * Retrieve list of customers for a manager filtered by assigned_date
	 *
	 * @param integer $manager_id .
	 *
	 * @return array of WP_User result of the search.
	 * @see WP_User
	 *
	 */
	function zacctmgr_get_assigned_date_customer_list_by_manager( $manager_id ) {
		if ( ! isset( $_GET['range'] ) ) {
			$range = 'all';
		} else {
			$range = $_GET['range'];
		}
		switch ( $range ) {
			case '7day':
				$start_date = date( 'Y-m-d 00:00:00', strtotime( '-7 day' ) );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'month':
				$start_date = date( 'Y-m-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'last_month' :
				$start_date = date( 'Y-m-01 00:00:00', strtotime( '-1 month' ) );
				$end_date   = date( 'Y-m-d 23:59:59', strtotime( 'last day of previous month' ) );
				break;
			case 'year':
				$start_date = date( 'Y-01-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'custom':
				$start_date = date( 'Y-m-d 00:00:00', strtotime( $_GET['start_date'] ) );
				$end_date   = date( 'Y-m-d 23:59:59', strtotime( $_GET['end_date'] ) );
				break;
			default:
				$start_date = date( '1970-01-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'zacctmgr_acm_assignments_mapping';

		$results = $wpdb->get_results( "SELECT DISTINCT customer_id, timestamp FROM $table_name WHERE timestamp >= '$start_date' AND timestamp <= '$end_date' AND manager_id=$manager_id;" );

		$customers = [];

		if ( $results ) {
			foreach ( $results as $result ) {
				if ( ! in_array( (int) $result->customer_id, $customers ) ) {
					$customers[] = (int) $result->customer_id;
				}
			}
		}

		return $customers;
	}

	/**
	 * Retrieve total commission for a list of managers
	 *
	 * @param array $managers .
	 *
	 * @return array containing total commission for a list of managers.
	 */
	function zacctmgr_get_total_commission_by_managers( $managers ) {
		$data = null;

		$total_new = $total_existing = 0;

		if ( $managers && count( $managers ) > 0 ) {
			foreach ( $managers as $manager ) {
				$temp                             = zacctmgr_get_total_commission_by_manager( $manager );
				$data['managers'][ $manager->ID ] = $temp;
				$total_new                        += $temp['total']['new'];
				$total_existing                   += $temp['total']['existing'];
			}
		}

		if ( $total_new != 0 || $total_existing != 0 ) {
			$data['total']['new']      = $total_new;
			$data['total']['existing'] = $total_existing;
			$data['total']['total']    = $total_new + $total_existing;
		} else {
			$data = null;
		}

		return $data;
	}

	/**
	 * Retrieve total commission for an order
	 *
	 * @param WC_Order $order .
	 *
	 * @return float commission by order
	 */
	function zacctmgr_get_total_commission_by_order( $order ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';

		if ( $order->get_date_created()->date( 'Y-m-d H:i:s' ) < get_option( 'zacctmgr_v2_install_date' ) ) {
			return number_format( 0, 2, '.', ',' );
		}

		$order_id        = (int) $order->get_id();
		$customer        = $order->get_user();
		$status          = $order->get_status();
		$order_timestamp = $order->get_date_created()->date( 'Y-m-d H:i:s' );
		$manager_name    = get_post_meta( $order_id, '_account_manager', true );
		$user_query      = new WP_User_Query( array(
			'search'         => $manager_name,
			'search_columns' => array( 'display_name' ),
			'number'         => 1
		) );

		$results = $user_query->get_results();

		$manager = $results[0];

		$manager_commission_rate_query = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager->ID AND customer_id IS NULL AND timestamp <= '$order_timestamp' ORDER BY timestamp DESC LIMIT 1;" );

		if ( count( $manager_commission_rate_query ) != 0 ) {
			if ( $manager_commission_rate_query[0]->no_commission == 1 ) {
				return 0;
			}
			if ( $manager_commission_rate_query[0]->order_level == 1 ) {
				$commission_rate = $manager_commission_rate_query[0];
			}
			if ( $manager_commission_rate_query[0]->customer_account_level == 1 ) {
				$customer_commission_rate_query = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager->ID AND customer_id=$customer->ID AND customer_account_level=1  AND timestamp <= '$order_timestamp' ORDER BY timestamp DESC LIMIT 1;" );
				if ( count( $customer_commission_rate_query ) != 0 ) {
					if ( $customer_commission_rate_query[0]->order_level == 0 ) {
						$commission_rate = $customer_commission_rate_query[0];
					} else {
						$manager_customer_account_level_commission_rate_query = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager->ID AND customer_id IS NULL AND customer_account_level=1  AND timestamp <= '$order_timestamp' ORDER BY timestamp DESC LIMIT 1;" );
						if ( count( $manager_customer_account_level_commission_rate_query ) != 0 ) {
							$commission_rate = $manager_customer_account_level_commission_rate_query[0];
						}
					}
				} else {
					$manager_customer_account_level_commission_rate_query = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager->ID AND customer_id IS NULL AND customer_account_level=1  AND timestamp <= '$order_timestamp' ORDER BY timestamp DESC LIMIT 1;" );
					if ( count( $manager_customer_account_level_commission_rate_query ) != 0 ) {
						$commission_rate = $manager_customer_account_level_commission_rate_query[0];
					}
				}
			}
		} else {
			return number_format( 0, 2, '.', ',' );
		}


		$allowed_statuses = zacctmgr_get_allowed_wc_statuses();

		if ( ! in_array( 'wc-' . $status, $allowed_statuses ) ) {
			return number_format( 0, 2, '.', ',' );
		}

		$data = 0;
		if ( ! $customer ) {
			return $data;
		}

		if ( $commission_rate->no_commission == 1 ) {
			$data = 0;
		} else {
			$orders = get_posts( array(
				'numberposts' => $commission_rate->new_order_commission_limit,
				'meta_key'    => '_customer_user',
				'meta_value'  => $customer->ID,
				'post_type'   => wc_get_order_types(),
				'post_status' => $allowed_statuses,
				'orderby'     => 'post_date',
				'order'       => 'ASC'
			) );

			if ( ! $orders ) {
				return number_format( $data, 2, '.', ',' );
			}

			$price        = (float) $order->get_total();
			$tax          = (float) $order->get_total_tax();
			$coupon       = (float) $order->get_total_discount();
			$shipping     = (float) $order->get_total_shipping();
			$shipping_tax = (float) $order->get_shipping_tax();

			$price += $coupon;


			if ( $commission_rate->new_order_commission_limit != 0 ) {
				$isNew = false;
				if ( count( $orders ) > 0 ) {
					foreach ( $orders as $item ) {
						if ( $order_id == $item->ID ) {
							$isNew = true;
							break;
						}
					}
				}
			} else {
				$isNew = false;
			}

			if ( $isNew ) { // New
				/* New Commission */
				if ( $commission_rate->new_order_commission_fixed_type == 1 ) {
					$data = $commission_rate->new_order_commission_value;
					update_post_meta( $order_id, '_new_commission_type', 1 );

				} else {
					if ( $commission_rate->new_order_exclude_coupon_amount == 1 ) {//With coupon applied (true)
						$price -= $coupon;
					}
					if ( $commission_rate->new_order_exclude_taxes_amount == 1 ) {
						$price -= $tax;
					}
					if ( $commission_rate->new_order_exclude_shipping_costs == 1 ) {
						$price -= $shipping;
					}
					if ( $commission_rate->new_order_exclude_shipping_tax_amount == 1 ) {
						$price -= $shipping_tax;
					}

					$data = (float) ( $commission_rate->new_order_commission_value * $price / 100 );

					update_post_meta( $order_id, '_new_commission_type', 1 );
				}
			} /* New Commission End */
			else { // Existing
				/* Existing Commission */
				if ( $commission_rate->existing_order_commission_fixed_type == 1 ) {
					$data = $commission_rate->existing_order_commission_value;
					update_post_meta( $order_id, '_new_commission_type', 0 );

				} else {
					if ( $commission_rate->existing_order_exclude_coupon_amount == 1 ) { //With coupon applied (true)
						$price -= $coupon;
					}
					if ( $commission_rate->existing_order_exclude_taxes_amount == 1 ) {
						$price -= $tax;
					}
					if ( $commission_rate->existing_order_exclude_shipping_costs == 1 ) {
						$price -= $shipping;
					}
					if ( $commission_rate->existing_order_exclude_shipping_tax_amount == 1 ) {
						$price -= $shipping_tax;
					}

					$data = (float) ( $commission_rate->existing_order_commission_value * $price / 100 );

					update_post_meta( $order_id, '_new_commission_type', 0 );
				}
				/* Existing Commission End */
			}
		}

		return $data;
	}

	/**
	 * Retrieve total commission for a customer
	 *
	 * @param WP_User $customer .
	 *
	 * @return array of floats containing total commision for a customer
	 */
	function zacctmgr_get_total_commission_by_customer( $customer ) {
		$data = [];

		$v2_date = get_option( 'zacctmgr_v2_install_date' );

		$allowed_statuses = zacctmgr_get_allowed_wc_statuses();

		$orders = get_posts( [
			'numberposts' => - 1,
			'meta_key'    => '_customer_user',
			'meta_value'  => $customer->ID,
			'post_type'   => wc_get_order_types(),
			'post_status' => $allowed_statuses,
			'orderby'     => 'post_date',
			'order'       => 'ASC',
			'date_query'  => array(
				'after' => $v2_date
			)

		] );

		$total = 0;
		if ( count( $orders ) != 0 ) {
			foreach ( $orders as $order ) {
				$commission = str_replace( ',', '', get_post_meta( $order->ID, '_commission', true ) );
				if ( $commission != '' ) {
					$total += (float) $commission;
				}
			}
		}

		$data = [
			'total' => $total
		];

		return $data;
	}

	/**
	 * Retrieve total commission for a manager
	 *
	 * @param WP_User $manager .
	 *
	 * @return array of floats containing total commision for a customer
	 */
	function zacctmgr_get_total_commission_by_manager( $manager ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';

		$allowed_statuses = zacctmgr_get_allowed_wc_statuses();

		if ( ! isset( $_GET['range'] ) ) {
			$range = 'all';
		} else {
			$range = $_GET['range'];
		}
		switch ( $range ) {
			case '7day':
				$start_date = date( 'Y-m-d 00:00:00', strtotime( '-7 day' ) );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'month':
				$start_date = date( 'Y-m-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'last_month' :
				$start_date = date( 'Y-m-01 00:00:00', strtotime( '-1 month' ) );
				$end_date   = date( 'Y-m-d 23:59:59', strtotime( 'last day of previous month' ) );
				break;
			case 'year':
				$start_date = date( 'Y-01-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'custom':
				$start_date = date( 'Y-m-d 00:00:00', strtotime( $_GET['start_date'] ) );
				$end_date   = date( 'Y-m-d 23:59:59', strtotime( $_GET['end_date'] ) );
				break;
			default:
				$start_date = date( '1970-01-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
		}

		$data           = [];
		$commission_new = $commission_existing = 0;

		$v2_date_installed = get_option( 'zacctmgr_v2_install_date' );

		if ( $start_date < $v2_date_installed ) {
			$start_date = $v2_date_installed;
		}

		$orders = get_posts( array(
			'numberposts' => - 1,
			'meta_key'    => '_account_manager',
			'meta_value'  => $manager->display_name,
			'post_type'   => wc_get_order_types(),
			'post_status' => $allowed_statuses,
			'date_query'  => array(
				'after'  => $start_date,
				'before' => $end_date
			),
			'orderby'     => 'post_date',
			'order'       => 'ASC'
		) );

		if ( ! $orders || count( $orders ) == 0 ) {
			return null;
		}

		foreach ( $orders as $item ) {
			$order           = wc_get_order( $item->ID );
			$order_timestamp = $order->get_date_created()->date( 'Y-m-d H:i:s' );

			$query = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager->ID AND timestamp <= '$order_timestamp' ORDER BY timestamp DESC LIMIT 1;" );

			if ( count( $query ) == 0 ) {
				continue;
			}

			if ( $query[0]->customer_account_level == 1 ) {
				$customer_id           = $order->get_customer_id();
				$commission_rate_query = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager->ID AND customer_id=$customer_id AND timestamp <= '$order_timestamp' ORDER BY timestamp DESC LIMIT 1;" );
				if ( count( $commission_rate_query ) != 0 ) {
					$commission_rate = $commission_rate_query[0];
				} else {
					$commission_rate = $query[0];
				}
			} else {
				$commission_rate = $query[0];
			}

			$new_orders = get_posts( array(
				'numberposts' => $commission_rate->new_order_commission_limit,
				'meta_key'    => '_customer_user',
				'meta_value'  => $order->get_customer_id(),
				'post_type'   => wc_get_order_types(),
				'post_status' => $allowed_statuses,
				'orderby'     => 'post_date',
				'order'       => 'ASC'
			) );

			if ( $commission_rate->new_order_commission_limit != 0 ) {
				$isNew = false;
				foreach ( $new_orders as $new_order_item ) {
					if ( $new_order_item->ID == $item->ID ) {
						$isNew = true;
						break;
					}
				}
			} else {
				$isNew = false;
			}

			if ( $isNew ) {
				$order_new_commission_type = get_post_meta( $order->get_id(), '_new_commission_type', true );
				if ( $order_new_commission_type != "" ) {

					if ( $order_new_commission_type == 1 ) {
						$c              = str_replace( ',', '', get_post_meta( $order->get_id(), '_commission', true ) );
						$commission_new += (float) $c;
					} else {
						$c                   = str_replace( ',', '', get_post_meta( $order->get_id(), '_commission', true ) );
						$commission_existing += (float) $c;

					}
				} else {
					$c              = str_replace( ',', '', get_post_meta( $order->get_id(), '_commission', true ) );
					$commission_new += (float) $c;
				}
			} else {
				$order_new_commission_type = get_post_meta( $order->get_id(), '_new_commission_type', true );
				if ( $order_new_commission_type != "" ) {

					if ( $order_new_commission_type == 1 ) {
						$c              = str_replace( ',', '', get_post_meta( $order->get_id(), '_commission', true ) );
						$commission_new += (float) $c;
					} else {
						$c                   = str_replace( ',', '', get_post_meta( $order->get_id(), '_commission', true ) );
						$commission_existing += (float) $c;

					}
				} else {
					$c                   = str_replace( ',', '', get_post_meta( $order->get_id(), '_commission', true ) );
					$commission_existing += (float) $c;
				}
			}

		}


		$data['total']['new']      = $commission_new;
		$data['total']['existing'] = $commission_existing;
		$data['total']['total']    = $commission_new + $commission_existing;

		return $data;
	}

	/**
	 * Retrieve report for manager
	 *
	 * @param int $manager_id .
	 *
	 * @return Object $report
	 */
	function zacctmgr_get_manager_report( $manager_id ) {
		include_once( WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php' );
		include_once( WC()->plugin_path() . '/includes/admin/reports/class-wc-report-sales-by-date.php' );


		$manager      = get_user_by( 'id', $manager_id );
		$report       = new stdClass();
		$reportObject = new WC_Report_Sales_By_Date();
		if ( ! isset( $_GET['range'] ) ) {
			$report->range = 'all';
		} else {
			$report->range = $_GET['range'];
		}
		switch ( $report->range ) {
			case '7day':
				$report->start_date = date( 'Y-m-d 00:00:00', strtotime( '-7 day' ) );
				$report->end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'month':
				$report->start_date = date( 'Y-m-01 00:00:00' );
				$report->end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'last_month' :
				$report->start_date = date( 'Y-m-01 00:00:00', strtotime( '-1 month' ) );
				$report->end_date   = date( 'Y-m-d 23:59:59', strtotime( 'last day of previous month' ) );
				break;
			case 'year':
				$report->start_date = date( 'Y-01-01 00:00:00' );
				$report->end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'custom':
				$report->start_date = date( 'Y-m-d 00:00:00', strtotime( $_GET['start_date'] ) );
				$report->end_date   = date( 'Y-m-d 23:59:59', strtotime( $_GET['end_date'] ) );
				break;
			default:
				$report->start_date = date( '1970-01-01 00:00:00' );
				$report->end_date   = date( 'Y-m-d 23:59:59' );
				break;
		}

		$customers = zacctmgr_get_customer_list_by_manager( array(
			'manager_id' => $manager_id,
			'start_date' => $report->start_date,
			'end_date'   => $report->end_date
		) );

		if ( ! $customers || count( $customers ) == 0 ) {
			return null;
		}

		$reportObject->group_by_query = "YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)";

		$assigned_customers_in_range = zacctmgr_get_assigned_date_customer_list_by_manager( $manager_id );
		$report->customers           = $assigned_customers_in_range;

		$report->detailed_order_items = (array) $reportObject->get_order_report_data(
			array(
				'data'         => array(
					'_qty'            => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => 'SUM',
						'name'            => 'order_item_count',
					),
					'_product_id'     => array(
						'type'     => 'order_item_meta',
						'name'     => 'product_id',
						'function' => ''
					),
					'post_date'       => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
					'order_item_name' => array(
						'type'     => 'order_item',
						'function' => '',
						'name'     => 'order_item_name',
					),
				),
				'where'        => array(
					array(
						'key'      => 'order_items.order_item_type',
						'value'    => 'line_item',
						'operator' => '=',
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				),
				'where_meta'   => array(
					#array(
					#	'meta_key'   => '_customer_user',
					#	'meta_value' => $customers,
					#	'operator'   => 'IN',
					#	'type'       => 'parent'
					#),
					array(
						'meta_key'   => '_account_manager',
						'meta_value' => $manager->display_name,
						'operator'   => '=',
						'type'       => 'parent'
					)
				),
				'group_by'     => 'order_items.order_item_name',
				'order_by'     => 'order_item_count DESC',
				'query_type'   => 'get_results',
				'filter_range' => false,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
			)
		);

		$report->order_counts = (array) $reportObject->get_order_report_data(
			array(
				'data'         => array(
					'ID'        => array(
						'type'     => 'post_data',
						'function' => 'COUNT',
						'name'     => 'count',
						'distinct' => true,
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'group_by'     => $reportObject->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => false,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				'where_meta'   => array(
					#array(
					#	'meta_key'   => '_customer_user',
					#	'meta_value' => $customers,
					#	'operator'   => 'IN',
					#	'type'       => 'parent'
					#),
					array(
						'meta_key'   => '_account_manager',
						'meta_value' => $manager->display_name,
						'operator'   => '=',
						'type'       => 'parent'
					)
				),
				'where'        => array(
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				)
			)
		);

		$report->coupons = (array) $reportObject->get_order_report_data(
			array(
				'data'         => array(
					'order_item_name' => array(
						'type'     => 'order_item',
						'function' => '',
						'name'     => 'order_item_name',
					),
					'discount_amount' => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'coupon',
						'function'        => 'SUM',
						'name'            => 'discount_amount',
					),
					'post_date'       => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'where'        => array(
					array(
						'key'      => 'order_items.order_item_type',
						'value'    => 'coupon',
						'operator' => '=',
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				),
				'where_meta'   => array(
					#array(
					#	'meta_key'   => '_customer_user',
					#	'meta_value' => $customers,
					#	'operator'   => 'IN',
					#	'type'       => 'parent'
					#),
					array(
						'meta_key'   => '_account_manager',
						'meta_value' => $manager->display_name,
						'operator'   => '=',
						'type'       => 'parent'
					)
				),
				'group_by'     => $reportObject->group_by_query . ', order_item_name',
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => false,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
			)
		);

		$report->order_items = (array) $reportObject->get_order_report_data(
			array(
				'data'         => array(
					'_qty'      => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => 'SUM',
						'name'            => 'order_item_count',
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'where'        => array(
					array(
						'key'      => 'order_items.order_item_type',
						'value'    => 'line_item',
						'operator' => '=',
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				),
				'where_meta'   => array(
					array(
						'meta_key'   => '_account_manager',
						'meta_value' => $manager->display_name,
						'operator'   => '=',
						'type'       => 'parent'
					)
				),
				'group_by'     => $reportObject->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => false,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
			)
		);

		$report->refunded_order_items = absint(
			$reportObject->get_order_report_data(
				array(
					'data'         => array(
						'_qty' => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => 'SUM',
							'name'            => 'order_item_count',
						),
					),
					'where'        => array(
						array(
							'key'      => 'order_items.order_item_type',
							'value'    => 'line_item',
							'operator' => '=',
						),
						array(
							'key'      => 'posts.post_date',
							'value'    => $report->start_date,
							'operator' => '>'
						),
						array(
							'key'      => 'posts.post_date',
							'value'    => $report->end_date,
							'operator' => '<'
						)
					),
					'where_meta'   => array(
						array(
							'meta_key'   => '_account_manager',
							'meta_value' => $manager->display_name,
							'operator'   => '=',
							'type'       => 'parent'
						)
					),
					'query_type'   => 'get_var',
					'filter_range' => false,
					'order_types'  => wc_get_order_types( 'order-count' ),
					'order_status' => array( 'refunded' ),
				)
			)
		);

		$report->orders = (array) $reportObject->get_order_report_data(
			array(
				'data'         => array(
					'_order_total'        => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_sales',
					),
					'_order_shipping'     => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_shipping',
					),
					'_order_tax'          => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_tax',
					),
					'_order_shipping_tax' => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_shipping_tax',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'group_by'     => $reportObject->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => false,
				'order_types'  => wc_get_order_types( 'sales-reports' ),
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				'where_meta'   => array(
					array(
						'meta_key'   => '_account_manager',
						'meta_value' => $manager->display_name,
						'operator'   => '=',
						'type'       => 'parent'
					)
				),
				'where'        => array(
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				)
			)
		);

		$report->full_refunds = (array) $reportObject->get_order_report_data(
			array(
				'data'                => array(
					'_order_total'        => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'total_refund',
					),
					'_customer_user'      => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'customer_user',
					),
					'_order_shipping'     => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'total_shipping',
					),
					'_order_tax'          => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'total_tax',
					),
					'_order_shipping_tax' => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'total_shipping_tax',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'group_by'            => 'posts.post_parent',
				'query_type'          => 'get_results',
				'filter_range'        => false,
				'order_status'        => false,
				'parent_order_status' => array( 'refunded' ),
				'where'               => array(
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				),
				'where_meta'          => array(
					array(
						'meta_key'   => '_account_manager',
						'meta_value' => $manager->display_name,
						'operator'   => '=',
						'type'       => 'parent'
					)
				),
			)
		);

		$report->partial_refunds = (array) $reportObject->get_order_report_data(
			array(
				'data'                => array(
					'ID'                  => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'refund_id',
					),
					'_customer_user'      => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'customer_user',
					),
					'_refund_amount'      => array(
						'type'     => 'meta',
						'function' => '',
						'name'     => 'total_refund',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
					'order_item_type'     => array(
						'type'      => 'order_item',
						'function'  => '',
						'name'      => 'item_type',
						'join_type' => 'LEFT',
					),
					'_order_total'        => array(
						'type'     => 'meta',
						'function' => '',
						'name'     => 'total_sales',
					),
					'_order_shipping'     => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_shipping',
						'join_type' => 'LEFT',
					),
					'_order_tax'          => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_tax',
						'join_type' => 'LEFT',
					),
					'_order_shipping_tax' => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_shipping_tax',
						'join_type' => 'LEFT',
					),
					'_qty'                => array(
						'type'      => 'order_item_meta',
						'function'  => 'SUM',
						'name'      => 'order_item_count',
						'join_type' => 'LEFT',
					),
				),
				'group_by'            => 'refund_id',
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => false,
				'order_status'        => false,
				'parent_order_status' => array( 'completed', 'processing', 'on-hold' ),
				'where'               => array(
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				),
				'where_meta'          => array(
					array(
						'meta_key'   => '_account_manager',
						'meta_value' => $manager->display_name,
						'operator'   => '=',
						'type'       => 'parent'
					)
				),
			)
		);

		$report->refund_lines = (array) $reportObject->get_order_report_data(
			array(
				'data'                => array(
					'ID'                  => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'refund_id',
					),
					'_customer_user'      => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'customer_user',
					),
					'_refund_amount'      => array(
						'type'     => 'meta',
						'function' => '',
						'name'     => 'total_refund',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
					'order_item_type'     => array(
						'type'      => 'order_item',
						'function'  => '',
						'name'      => 'item_type',
						'join_type' => 'LEFT',
					),
					'_order_total'        => array(
						'type'     => 'meta',
						'function' => '',
						'name'     => 'total_sales',
					),
					'_order_shipping'     => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_shipping',
						'join_type' => 'LEFT',
					),
					'_order_tax'          => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_tax',
						'join_type' => 'LEFT',
					),
					'_order_shipping_tax' => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_shipping_tax',
						'join_type' => 'LEFT',
					),
					'_qty'                => array(
						'type'      => 'order_item_meta',
						'function'  => 'SUM',
						'name'      => 'order_item_count',
						'join_type' => 'LEFT',
					),
				),
				'group_by'            => 'refund_id',
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => false,
				'order_status'        => false,
				'parent_order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				'where'               => array(
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				),
				'where_meta'          => array(
					array(
						'meta_key'   => '_account_manager',
						'meta_value' => $manager->display_name,
						'operator'   => '=',
						'type'       => 'parent'
					)
				),
			)
		);

		$report->total_tax_refunded          = 0;
		$report->total_shipping_refunded     = 0;
		$report->total_shipping_tax_refunded = 0;
		$report->total_refunds               = 0;

		$refunded_orders = array_merge( $report->partial_refunds, $report->full_refunds );

		foreach ( $refunded_orders as $key => $value ) {
			$report->total_tax_refunded          += floatval( $value->total_tax < 0 ? $value->total_tax * - 1 : $value->total_tax );
			$report->total_refunds               += floatval( $value->total_refund );
			$report->total_shipping_tax_refunded += floatval( $value->total_shipping_tax < 0 ? $value->total_shipping_tax * - 1 : $value->total_shipping_tax );
			$report->total_shipping_refunded     += floatval( $value->total_shipping < 0 ? $value->total_shipping * - 1 : $value->total_shipping );

			// Only applies to parial.
			if ( isset( $value->order_item_count ) ) {
				$report->refunded_order_items += floatval( $value->order_item_count < 0 ? $value->order_item_count * - 1 : $value->order_item_count );
			}
		}

		// Totals from all orders - including those refunded. Subtract refunded amounts.
		$report->total_tax          = wc_format_decimal( array_sum( wp_list_pluck( $report->orders, 'total_tax' ) ) - $report->total_tax_refunded, 2 );
		$report->total_shipping     = wc_format_decimal( array_sum( wp_list_pluck( $report->orders, 'total_shipping' ) ) - $report->total_shipping_refunded, 2 );
		$report->total_shipping_tax = wc_format_decimal( array_sum( wp_list_pluck( $report->orders, 'total_shipping_tax' ) ) - $report->total_shipping_tax_refunded, 2 );

		// Total the refunds and sales amounts. Sales subract refunds. Note - total_sales also includes shipping costs.
		$report->total_sales = wc_format_decimal( array_sum( wp_list_pluck( $report->orders, 'total_sales' ) ) - $report->total_refunds, 2 );
		$report->net_sales   = wc_format_decimal( $report->total_sales - $report->total_shipping - max( 0, $report->total_tax ) - max( 0, $report->total_shipping_tax ), 2 );

		// Total orders and discounts also includes those which have been refunded at some point
		$report->total_coupons         = number_format( array_sum( wp_list_pluck( $report->coupons, 'discount_amount' ) ), 2, '.', '' );
		$report->total_refunded_orders = absint( count( $report->full_refunds ) );

		// Total orders in this period, even if refunded.
		$report->total_orders = absint( array_sum( wp_list_pluck( $report->order_counts, 'count' ) ) );

		// Item items ordered in this period, even if refunded.
		$report->total_items = absint( array_sum( wp_list_pluck( $report->order_items, 'order_item_count' ) ) );

		$month                  = (int) date( 'm' );
		$report->avg_order      = ceil( (float) ( (int) $report->total_orders / $month ) );
		$report->avg_order_size = (float) ( $report->total_sales / $month );
		$report->avg_order_item = ceil( (float) ( (int) $report->order_items / $month ) );

		return $report;
	}

	/**
	 * Retrieve report for customer
	 *
	 * @param int $customer_id .
	 *
	 * @return Object $report
	 */
	function zacctmgr_get_customer_report( $customer_id ) {
		include_once( WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php' );
		include_once( WC()->plugin_path() . '/includes/admin/reports/class-wc-report-sales-by-date.php' );

		$report                       = new stdClass();
		$reportObject                 = new WC_Report_Sales_By_Date();
		$reportObject->group_by_query = "YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)";
		if ( ! isset( $_GET['range'] ) ) {
			$report->range = 'all';
		} else {
			$report->range = $_GET['range'];
		}
		switch ( $report->range ) {
			case '7day':
				$report->start_date = date( 'Y-m-d 00:00:00', strtotime( '-7 day' ) );
				$report->end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'month':
				$report->start_date = date( 'Y-m-01 00:00:00' );
				$report->end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'last_month' :
				$report->start_date = date( 'Y-m-01 00:00:00', strtotime( '-1 month' ) );
				$report->end_date   = date( 'Y-m-d 23:59:59', strtotime( 'last day of previous month' ) );
				break;
			case 'year':
				$report->start_date = date( 'Y-01-01 00:00:00' );
				$report->end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'custom':
				$report->start_date = date( 'Y-m-d 00:00:00', strtotime( $_GET['start_date'] ) );
				$report->end_date   = date( 'Y-m-d 23:59:59', strtotime( $_GET['end_date'] ) );
				break;
			default:
				$report->start_date = date( '1970-01-01 00:00:00' );
				$report->end_date   = date( 'Y-m-d 23:59:59' );
				break;
		}


		$report->detailed_order_items = (array) $reportObject->get_order_report_data(
			array(
				'data'         => array(
					'_qty'            => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => 'SUM',
						'name'            => 'order_item_count',
					),
					'_product_id'     => array(
						'type'     => 'order_item_meta',
						'name'     => 'product_id',
						'function' => ''
					),
					'post_date'       => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
					'order_item_name' => array(
						'type'     => 'order_item',
						'function' => '',
						'name'     => 'order_item_name',
					),
				),
				'where'        => array(
					array(
						'key'      => 'order_items.order_item_type',
						'value'    => 'line_item',
						'operator' => '=',
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				),
				'where_meta'   => array(
					array(
						'meta_key'   => '_customer_user',
						'meta_value' => $customer_id,
						'operator'   => '=',
						'type'       => 'parent'
					),
				),
				'group_by'     => 'order_items.order_item_name',
				'order_by'     => 'order_item_count DESC',
				'query_type'   => 'get_results',
				'filter_range' => true,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
			)
		);

		$report->order_counts = (array) $reportObject->get_order_report_data(
			array(
				'data'         => array(
					'ID'        => array(
						'type'     => 'post_data',
						'function' => 'COUNT',
						'name'     => 'count',
						'distinct' => true,
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'group_by'     => $reportObject->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => false,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				'where'        => array(
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				),
				'where_meta'   => array(
					array(
						'meta_key'   => '_customer_user',
						'meta_value' => $customer_id,
						'operator'   => '=',
						'type'       => 'parent'
					),
				),
			)
		);

		$report->order_counts_all = (array) $reportObject->get_order_report_data(
			array(
				'data'         => array(
					'ID'        => array(
						'type'     => 'post_data',
						'function' => 'COUNT',
						'name'     => 'count',
						'distinct' => true,
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'group_by'     => $reportObject->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => false,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				'where'        => array(
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				)
			)
		);

		$report->coupons = (array) $reportObject->get_order_report_data(
			array(
				'data'         => array(
					'order_item_name' => array(
						'type'     => 'order_item',
						'function' => '',
						'name'     => 'order_item_name',
					),
					'discount_amount' => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'coupon',
						'function'        => 'SUM',
						'name'            => 'discount_amount',
					),
					'post_date'       => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'where'        => array(
					array(
						'key'      => 'order_items.order_item_type',
						'value'    => 'coupon',
						'operator' => '=',
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				),
				'where_meta'   => array(
					array(
						'meta_key'   => '_customer_user',
						'meta_value' => $customer_id,
						'operator'   => '=',
						'type'       => 'parent'
					),
				),
				'group_by'     => $reportObject->group_by_query . ', order_item_name',
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => false,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
			)
		);

		$report->order_items = (array) $reportObject->get_order_report_data(
			array(
				'data'         => array(
					'_qty'      => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => 'SUM',
						'name'            => 'order_item_count',
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'where'        => array(
					array(
						'key'      => 'order_items.order_item_type',
						'value'    => 'line_item',
						'operator' => '=',
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				),
				'where_meta'   => array(
					array(
						'meta_key'   => '_customer_user',
						'meta_value' => $customer_id,
						'operator'   => '=',
						'type'       => 'parent'
					),
				),
				'group_by'     => $reportObject->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => false,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
			)
		);

		$report->refunded_order_items = absint(
			$reportObject->get_order_report_data(
				array(
					'data'         => array(
						'_qty' => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => 'SUM',
							'name'            => 'order_item_count',
						),
					),
					'where'        => array(
						array(
							'key'      => 'order_items.order_item_type',
							'value'    => 'line_item',
							'operator' => '=',
						),
						array(
							'key'      => 'posts.post_date',
							'value'    => $report->start_date,
							'operator' => '>'
						),
						array(
							'key'      => 'posts.post_date',
							'value'    => $report->end_date,
							'operator' => '<'
						)
					),
					'where_meta'   => array(
						array(
							'meta_key'   => '_customer_user',
							'meta_value' => $customer_id,
							'operator'   => '=',
							'type'       => 'parent'
						),
					),
					'query_type'   => 'get_var',
					'filter_range' => false,
					'order_types'  => wc_get_order_types( 'order-count' ),
					'order_status' => array( 'refunded' ),
				)
			)
		);

		$report->orders = (array) $reportObject->get_order_report_data(
			array(
				'data'         => array(
					'_order_total'        => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_sales',
					),
					'_order_shipping'     => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_shipping',
					),
					'_order_tax'          => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_tax',
					),
					'_order_shipping_tax' => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_shipping_tax',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'group_by'     => $reportObject->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => false,
				'order_types'  => wc_get_order_types( 'sales-reports' ),
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				'where'        => array(
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				),
				'where_meta'   => array(
					array(
						'meta_key'   => '_customer_user',
						'meta_value' => $customer_id,
						'operator'   => '=',
						'type'       => 'parent'
					),
				),
			)
		);

		$report->full_refunds = (array) $reportObject->get_order_report_data(
			array(
				'data'                => array(
					'_order_total'        => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'total_refund',
					),
					'_customer_user'      => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'customer_user',
					),
					'_order_shipping'     => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'total_shipping',
					),
					'_order_tax'          => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'total_tax',
					),
					'_order_shipping_tax' => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'total_shipping_tax',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'group_by'            => 'posts.post_parent',
				'query_type'          => 'get_results',
				'filter_range'        => false,
				'order_status'        => false,
				'parent_order_status' => array( 'refunded' ),
				'where'               => array(
					array(
						'key'      => 'parent_meta__customer_user.meta_value',
						'value'    => $customer_id,
						'operator' => '='
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				)
			)
		);

		$report->partial_refunds = (array) $reportObject->get_order_report_data(
			array(
				'data'                => array(
					'ID'                  => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'refund_id',
					),
					'_customer_user'      => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'customer_user',
					),
					'_refund_amount'      => array(
						'type'     => 'meta',
						'function' => '',
						'name'     => 'total_refund',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
					'order_item_type'     => array(
						'type'      => 'order_item',
						'function'  => '',
						'name'      => 'item_type',
						'join_type' => 'LEFT',
					),
					'_order_total'        => array(
						'type'     => 'meta',
						'function' => '',
						'name'     => 'total_sales',
					),
					'_order_shipping'     => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_shipping',
						'join_type' => 'LEFT',
					),
					'_order_tax'          => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_tax',
						'join_type' => 'LEFT',
					),
					'_order_shipping_tax' => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_shipping_tax',
						'join_type' => 'LEFT',
					),
					'_qty'                => array(
						'type'      => 'order_item_meta',
						'function'  => 'SUM',
						'name'      => 'order_item_count',
						'join_type' => 'LEFT',
					),
				),
				'group_by'            => 'refund_id',
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => false,
				'order_status'        => false,
				'parent_order_status' => array( 'completed', 'processing', 'on-hold' ),
				'where'               => array(
					array(
						'key'      => 'parent_meta__customer_user.meta_value',
						'value'    => $customer_id,
						'operator' => '='
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				)
			)
		);

		$report->refund_lines = (array) $reportObject->get_order_report_data(
			array(
				'data'                => array(
					'ID'                  => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'refund_id',
					),
					'_customer_user'      => array(
						'type'     => 'parent_meta',
						'function' => '',
						'name'     => 'customer_user',
					),
					'_refund_amount'      => array(
						'type'     => 'meta',
						'function' => '',
						'name'     => 'total_refund',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
					'order_item_type'     => array(
						'type'      => 'order_item',
						'function'  => '',
						'name'      => 'item_type',
						'join_type' => 'LEFT',
					),
					'_order_total'        => array(
						'type'     => 'meta',
						'function' => '',
						'name'     => 'total_sales',
					),
					'_order_shipping'     => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_shipping',
						'join_type' => 'LEFT',
					),
					'_order_tax'          => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_tax',
						'join_type' => 'LEFT',
					),
					'_order_shipping_tax' => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'total_shipping_tax',
						'join_type' => 'LEFT',
					),
					'_qty'                => array(
						'type'      => 'order_item_meta',
						'function'  => 'SUM',
						'name'      => 'order_item_count',
						'join_type' => 'LEFT',
					),
				),
				'group_by'            => 'refund_id',
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => false,
				'order_status'        => false,
				'parent_order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				'where'               => array(
					array(
						'key'      => 'parent_meta__customer_user.meta_value',
						'value'    => $customer_id,
						'operator' => '='
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->start_date,
						'operator' => '>'
					),
					array(
						'key'      => 'posts.post_date',
						'value'    => $report->end_date,
						'operator' => '<'
					)
				)
			)
		);

		$report->total_tax_refunded          = 0;
		$report->total_shipping_refunded     = 0;
		$report->total_shipping_tax_refunded = 0;
		$report->total_refunds               = 0;

		$refunded_orders = array_merge( $report->partial_refunds, $report->full_refunds );

		foreach ( $refunded_orders as $key => $value ) {
			$report->total_tax_refunded          += floatval( $value->total_tax < 0 ? $value->total_tax * - 1 : $value->total_tax );
			$report->total_refunds               += floatval( $value->total_refund );
			$report->total_shipping_tax_refunded += floatval( $value->total_shipping_tax < 0 ? $value->total_shipping_tax * - 1 : $value->total_shipping_tax );
			$report->total_shipping_refunded     += floatval( $value->total_shipping < 0 ? $value->total_shipping * - 1 : $value->total_shipping );

			// Only applies to parial.
			if ( isset( $value->order_item_count ) ) {
				$report->refunded_order_items += floatval( $value->order_item_count < 0 ? $value->order_item_count * - 1 : $value->order_item_count );
			}
		}

		// Totals from all orders - including those refunded. Subtract refunded amounts.
		$report->total_tax          = wc_format_decimal( array_sum( wp_list_pluck( $report->orders, 'total_tax' ) ) - $report->total_tax_refunded, 2 );
		$report->total_shipping     = wc_format_decimal( array_sum( wp_list_pluck( $report->orders, 'total_shipping' ) ) - $report->total_shipping_refunded, 2 );
		$report->total_shipping_tax = wc_format_decimal( array_sum( wp_list_pluck( $report->orders, 'total_shipping_tax' ) ) - $report->total_shipping_tax_refunded, 2 );

		// Total the refunds and sales amounts. Sales subract refunds. Note - total_sales also includes shipping costs.
		$report->total_sales = wc_format_decimal( array_sum( wp_list_pluck( $report->orders, 'total_sales' ) ) - $report->total_refunds, 2 );
		$report->net_sales   = wc_format_decimal( $report->total_sales - $report->total_shipping - max( 0, $report->total_tax ) - max( 0, $report->total_shipping_tax ), 2 );

		// Total orders and discounts also includes those which have been refunded at some point
		$report->total_coupons         = number_format( array_sum( wp_list_pluck( $report->coupons, 'discount_amount' ) ), 2, '.', '' );
		$report->total_refunded_orders = absint( count( $report->full_refunds ) );

		// Total orders in this period, even if refunded.
		$report->total_orders     = absint( array_sum( wp_list_pluck( $report->order_counts, 'count' ) ) );
		$report->total_orders_all = absint( array_sum( wp_list_pluck( $report->order_counts_all, 'count' ) ) );

		// Item items ordered in this period, even if refunded.
		$report->total_items = absint( array_sum( wp_list_pluck( $report->order_items, 'order_item_count' ) ) );

		$month                  = (int) date( 'm' );
		$report->avg_order      = ceil( (float) ( (int) $report->total_orders / $month ) );
		$report->avg_order_size = (float) ( $report->total_sales / $month );
		$report->avg_order_item = ceil( (float) ( (int) $report->order_items / $month ) );

		return $report;
	}

	/**
	 * Retrieve categories for a product, excluding the Uncategorized category
	 *
	 * @param int $product_id .
	 *
	 * @return WP_Term[]
	 */
	function zacctmgr_get_product_category_by_id( $product_id ) {
		$terms = get_the_terms( $product_id, 'product_cat' );

		$product_categories = [];
		if ( $terms ) {
			foreach ( $terms as $term ) {
				if ( $term->name == 'Uncategorized' ) {
					continue;
				}

				$product_categories[] = $term->name;
			}
		}

		return $product_categories;
	}

	/**
	 * Retrieve last 3 orders for a list of customer
	 *
	 * @param array $customers .
	 *
	 * @return WP_Post[]
	 */
	function zacctmgr_get_orders_by_customers( $customers = [] ) {
		if ( ! isset( $_GET['range'] ) ) {
			$range = 'all';
		} else {
			$range = $_GET['range'];
		}
		switch ( $range ) {
			case '7day':
				$start_date = date( 'Y-m-d 00:00:00', strtotime( '-7 day' ) );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'month':
				$start_date = date( 'Y-m-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'last_month' :
				$start_date = date( 'Y-m-01 00:00:00', strtotime( '-1 month' ) );
				$end_date   = date( 'Y-m-d 23:59:59', strtotime( 'last day of previous month' ) );
				break;
			case 'year':
				$start_date = date( 'Y-01-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'custom':
				$start_date = date( 'Y-m-d 00:00:00', strtotime( $_GET['start_date'] ) );
				$end_date   = date( 'Y-m-d 23:59:59', strtotime( $_GET['end_date'] ) );
				break;
			default:
				$start_date = date( '1970-01-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
		}

		$customer_orders = get_posts( [
			'numberposts' => 3,
			'meta_key'    => '_customer_user',
			'meta_value'  => $customers,
			'operator'    => 'IN',
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
			'orderby'     => 'post_date',
			'order'       => 'DESC',
			'date_query'  => array(
				'after'  => $start_date,
				'before' => $end_date
			)
		] );

		return $customer_orders;
	}

	/**
	 * Retrieve last 3 orders for a customer
	 *
	 * @param int $customer_id .
	 *
	 * @return WP_Post[]
	 */
	function zacctmgr_get_orders_by_customer( $customer_id ) {
		if ( ! isset( $_GET['range'] ) ) {
			$range = 'all';
		} else {
			$range = $_GET['range'];
		}
		switch ( $range ) {
			case '7day':
				$start_date = date( 'Y-m-d 00:00:00', strtotime( '-7 day' ) );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'month':
				$start_date = date( 'Y-m-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'last_month' :
				$start_date = date( 'Y-m-01 00:00:00', strtotime( '-1 month' ) );
				$end_date   = date( 'Y-m-d 23:59:59', strtotime( 'last day of previous month' ) );
				break;
			case 'year':
				$start_date = date( 'Y-01-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
			case 'custom':
				$start_date = date( 'Y-m-d 00:00:00', strtotime( $_GET['start_date'] ) );
				$end_date   = date( 'Y-m-d 23:59:59', strtotime( $_GET['end_date'] ) );
				break;
			default:
				$start_date = date( '1970-01-01 00:00:00' );
				$end_date   = date( 'Y-m-d 23:59:59' );
				break;
		}

		$customer_orders = get_posts( [
			'numberposts' => 3,
			'meta_key'    => '_customer_user',
			'meta_value'  => $customer_id,
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
			'orderby'     => 'post_date',
			'order'       => 'DESC',
			'date_query'  => array(
				'after'  => $start_date,
				'before' => $end_date
			)
		] );

		return $customer_orders;
	}

	/**
	 * Insert a new entry to the zacctmgr_acm_assignments_mapping that keeps information about
	 * Account Manager and the moment he was assigned
	 *
	 * @param $assignment
	 */
	function zacctmgr_insert_account_manager_assignment( $assignment ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'zacctmgr_acm_assignments_mapping';

		$wpdb->insert(
			$table_name,
			array(
				'timestamp'   => $assignment['timestamp'],
				'manager_id'  => $assignment['manager_id'],
				'customer_id' => $assignment['customer_id'],
			)
		);
	}

	/**
	 * Insert a new entry to the zacctmgr_acm_commissions_mapping that keeps information about
	 * Commission changes for an account manager
	 *
	 * @param $commission_entry
	 */
	function zacctmgr_insert_commission_entry( $commission_entry ) {
		if ( $commission_entry['no_commission'] == 0 && $commission_entry['order_level'] == 0 && $commission_entry['customer_account_level'] == 0 ) {
			return;
		}
		global $wpdb;
		$table_name = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';

		$timestamp              = $commission_entry['timestamp'];
		$manager_id             = $commission_entry['manager_id'];
		$customer_id            = $commission_entry['customer_id'];
		$no_commission          = $commission_entry['no_commission'];
		$order_level            = $commission_entry['order_level'];
		$customer_account_level = $commission_entry['customer_account_level'];
		if ( $commission_entry['no_commission'] == 1 ) {
			$new_order_commission_percentage_type       = 0;
			$new_order_commission_fixed_type            = 1;
			$new_order_commission_value                 = 0;
			$new_order_commission_limit                 = 0;
			$new_order_exclude_coupon_amount            = 0;
			$new_order_exclude_taxes_amount             = 0;
			$new_order_exclude_shipping_costs           = 0;
			$new_order_exclude_shipping_tax_amount      = 0;
			$existing_order_commission_percentage_type  = 0;
			$existing_order_commission_fixed_type       = 1;
			$existing_order_commission_value            = 0;
			$existing_order_exclude_coupon_amount       = 0;
			$existing_order_exclude_taxes_amount        = 0;
			$existing_order_exclude_shipping_costs      = 0;
			$existing_order_exclude_shipping_tax_amount = 0;
		} else {
			$new_order_commission_fixed_type      = $commission_entry['new_order_commission_fixed_type'];
			$new_order_commission_value           = $commission_entry['new_order_commission_value'];
			$new_order_commission_limit           = $commission_entry['new_order_commission_limit'];
			$new_order_commission_percentage_type = $commission_entry['new_order_commission_percentage_type'];
			if ( $commission_entry['new_order_commission_percentage_type'] == 1 ) {
				$new_order_exclude_coupon_amount       = $commission_entry['new_order_exclude_coupon_amount'];
				$new_order_exclude_taxes_amount        = $commission_entry['new_order_exclude_taxes_amount'];
				$new_order_exclude_shipping_costs      = $commission_entry['new_order_exclude_shipping_costs'];
				$new_order_exclude_shipping_tax_amount = $commission_entry['new_order_exclude_shipping_tax_amount'];
			} else {
				$new_order_exclude_coupon_amount       = 0;
				$new_order_exclude_taxes_amount        = 0;
				$new_order_exclude_shipping_costs      = 0;
				$new_order_exclude_shipping_tax_amount = 0;
			}

			$existing_order_commission_fixed_type      = $commission_entry['existing_order_commission_fixed_type'];
			$existing_order_commission_value           = $commission_entry['existing_order_commission_value'];
			$existing_order_commission_percentage_type = $commission_entry['existing_order_commission_percentage_type'];
			if ( $commission_entry['existing_order_commission_percentage_type'] == 1 ) {
				$existing_order_exclude_coupon_amount       = $commission_entry['existing_order_exclude_coupon_amount'];
				$existing_order_exclude_taxes_amount        = $commission_entry['existing_order_exclude_taxes_amount'];
				$existing_order_exclude_shipping_costs      = $commission_entry['existing_order_exclude_shipping_costs'];
				$existing_order_exclude_shipping_tax_amount = $commission_entry['existing_order_exclude_shipping_tax_amount'];
			} else {
				$existing_order_exclude_coupon_amount       = 0;
				$existing_order_exclude_taxes_amount        = 0;
				$existing_order_exclude_shipping_costs      = 0;
				$existing_order_exclude_shipping_tax_amount = 0;

			}
		}

		$results = $wpdb->get_results( "SELECT COUNT(*) AS exist FROM $table_name WHERE
				timestamp=$timestamp AND 
				manager_id=$manager_id AND 
				customer_id=$customer_id AND 
				no_commission=$no_commission AND 
				order_level=$order_level AND 
				customer_account_level=$customer_account_level AND 
				new_order_commission_percentage_type=$new_order_commission_percentage_type AND 
				new_order_commission_fixed_type=$new_order_commission_fixed_type AND 
				new_order_commission_value=$new_order_commission_value AND
				new_order_commission_limit=$new_order_commission_limit AND 
				new_order_exclude_coupon_amount=$new_order_exclude_coupon_amount AND 
				new_order_exclude_taxes_amount=$new_order_exclude_taxes_amount AND 
				new_order_exclude_shipping_costs=$new_order_exclude_shipping_costs AND 
				new_order_exclude_shipping_tax_amount=$new_order_exclude_shipping_tax_amount AND 
				existing_order_commission_percentage_type=$existing_order_commission_percentage_type AND 
				existing_order_commission_fixed_type=$existing_order_commission_fixed_type AND 
				existing_order_commission_value=$existing_order_commission_value AND 
				existing_order_exclude_coupon_amount=$existing_order_exclude_coupon_amount AND 
				existing_order_exclude_taxes_amount=$existing_order_exclude_taxes_amount AND 
				existing_order_exclude_shipping_costs=$existing_order_exclude_shipping_costs AND
				existing_order_exclude_shipping_tax_amount=$existing_order_exclude_shipping_tax_amount" );

		if ( count( $results ) == 0 || $results[0]->exist == 0 ) {
			$wpdb->insert(
				$table_name,
				array(
					'timestamp'                                  => $timestamp,
					'manager_id'                                 => $manager_id,
					'customer_id'                                => $customer_id,
					'no_commission'                              => $no_commission,
					'order_level'                                => $order_level,
					'customer_account_level'                     => $customer_account_level,
					'new_order_commission_percentage_type'       => $new_order_commission_percentage_type,
					'new_order_commission_fixed_type'            => $new_order_commission_fixed_type,
					'new_order_commission_value'                 => $new_order_commission_value,
					'new_order_commission_limit'                 => $new_order_commission_limit,
					'new_order_exclude_coupon_amount'            => $new_order_exclude_coupon_amount,
					'new_order_exclude_taxes_amount'             => $new_order_exclude_taxes_amount,
					'new_order_exclude_shipping_costs'           => $new_order_exclude_shipping_costs,
					'new_order_exclude_shipping_tax_amount'      => $new_order_exclude_shipping_tax_amount,
					'existing_order_commission_percentage_type'  => $existing_order_commission_percentage_type,
					'existing_order_commission_fixed_type'       => $existing_order_commission_fixed_type,
					'existing_order_commission_value'            => $existing_order_commission_value,
					'existing_order_exclude_coupon_amount'       => $existing_order_exclude_coupon_amount,
					'existing_order_exclude_taxes_amount'        => $existing_order_exclude_taxes_amount,
					'existing_order_exclude_shipping_costs'      => $existing_order_exclude_shipping_costs,
					'existing_order_exclude_shipping_tax_amount' => $existing_order_exclude_shipping_tax_amount,
				)
			);
		}
	}

	add_action( 'woocommerce_checkout_update_order_meta', 'zacctmgr_add_manager_to_order' );

	/**
	 * Add the account manager to the order meta
	 *
	 * @param $order_id Order's ID
	 */
	function zacctmgr_add_manager_to_order( $order_id ) {
		$order = wc_get_order( $order_id );

		$customer_id = $order->get_customer_id();
		$manager_id  = zacctmgr_get_manager_id( $customer_id );

		$manager_name = get_user_by( 'id', $manager_id )->display_name;
		update_post_meta( $order_id, '_account_manager', $manager_name );
	}

	/**
	 * Check if the current user has the right to change commission rates to any customer
	 *
	 * @return bool
	 */
	function zacctmgr_allow_edit_others_commission() {
		$zacctmgr_user_allow_edit_others_commission_setting = zacctmgr_get_user_allow_edit_others_commission_setting();
		$account_manager_roles                              = zacctmgr_get_selected_roles();
		$allowed_users                                      = zacctmgr_get_allowed_edit_others_commission_users();

		if ( $zacctmgr_user_allow_edit_others_commission_setting == 'administrators' ) {
			$user = new WP_User_Query( array(
					'include'  => get_current_user_id(),
					'role__in' => $account_manager_roles
				)
			);
			if ( count( $user->get_results() ) == 0 ) {
				return false;
			}
		}
		if ( $zacctmgr_user_allow_edit_others_commission_setting == 'users' ) {
			if ( ! in_array( get_current_user_id(), $allowed_users ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if the current user has the right to edit order
	 *
	 * @return bool
	 */
	function zacctmgr_allow_edit_order() {
		$zacctmgr_user_allow_edit_order_setting = zacctmgr_get_user_allow_edit_order_commission_setting();
		$account_manager_roles                  = zacctmgr_get_selected_roles();
		$allowed_users                          = zacctmgr_get_allowed_edit_order_commission_users();

		if ( $zacctmgr_user_allow_edit_order_setting == 'administrators' ) {
			$user = new WP_User_Query( array(
					'include'  => get_current_user_id(),
					'role__in' => $account_manager_roles
				)
			);
			if ( count( $user->get_results() ) == 0 ) {
				return false;
			}
		}
		if ( $zacctmgr_user_allow_edit_order_setting == 'users' ) {
			if ( ! in_array( get_current_user_id(), $allowed_users ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if the current user has the right to edit Commissions
	 *
	 * @return bool
	 */
	function zacctmgr_allow_edit_commission() {
		$zacctmgr_user_allow_edit_commission_setting = zacctmgr_get_user_allow_edit_commission_setting();
		$account_manager_roles                       = zacctmgr_get_selected_roles();
		$allowed_users                               = zacctmgr_get_allowed_edit_commission_users();

		if ( $zacctmgr_user_allow_edit_commission_setting == 'administrators' ) {
			$user = new WP_User_Query( array(
					'include'  => get_current_user_id(),
					'role__in' => $account_manager_roles
				)
			);
			if ( count( $user->get_results() ) == 0 ) {
				return false;
			}
		}
		if ( $zacctmgr_user_allow_edit_commission_setting == 'users' ) {
			if ( ! in_array( get_current_user_id(), $allowed_users ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if the current user has the capability to edit the customer's commission rate
	 *
	 * @param $customer_id Customer's ID
	 *
	 * @return bool
	 */
	function zacctmgr_can_edit_customer_commission( $customer_id ) {
		$current_user_id = get_current_user_id();

		$manager_id = zacctmgr_get_manager_id( $customer_id );

		if ( zacctmgr_allow_edit_commission() && $manager_id == $current_user_id ) {
			return true;
		}

		if ( zacctmgr_allow_edit_others_commission() && zacctmgr_allow_edit_commission() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the current user has the capability to view the customer's commission rate
	 *
	 * @param $customer_id Customer's ID
	 *
	 * @return bool
	 */
	function zacctmgr_can_view_customer( $customer_id ) {
		$current_user_id = get_current_user_id();

		$manager_id = zacctmgr_get_manager_id( $customer_id );

		if ( $current_user_id == $manager_id ) {
			return true;
		}
		if ( zacctmgr_allow_edit_others_commission() ) {
			return true;
		}

		return false;
	}

	add_action( 'init', 'zacctmgr_add_woocommerce_actions' );

	function zacctmgr_add_woocommerce_actions() {
		$allowed_statuses = zacctmgr_get_allowed_wc_statuses();
		if ( $allowed_statuses ) {
			foreach ( $allowed_statuses as $allowed_status ) {
				add_action( 'woocommerce_order_status_' . str_replace( 'wc-', '', $allowed_status ), 'update_status_order_meta' );
			}
		}
	}

	/**
	 * Calculate commission and update it on order meta when the order is set to processing status
	 *
	 * @param $order_id
	 */
	add_action( 'woocommerce_update_order', 'update_status_order_meta' );

	function update_status_order_meta( $order_id ) {
		global $wpdb;
		$zacctmgr_order_recalculate_commission = zacctmgr_order_recalculate_commission();

		$table_name = $wpdb->prefix . 'zacctmgr_acm_order_audit_mapping';

		$order = wc_get_order( $order_id );

		$order_items = $order->get_items();
		$sum         = 0;
		foreach ( $order_items as $order_item ) {
			$sum += $order_item->get_total() + $order_item->get_total_tax();
		}

		if ( $sum == $order->get_total() ) {
			if ( $order->get_status() == 'refunded' ) {
				if ( zacctmgr_refund_commission_setting() == 'zero' ) {
					$old_data = get_post_meta( $order_id, '_commission', true );

					$query = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_id=$order_id AND is_commission_change=1 ORDER BY timestamp DESC LIMIT 1; " );

					if ( count( $query ) > 0 ) {
						if ( ( $query[0]->old_value == $old_data && $query[0]->new_value == number_format( 0, 2, '.', ',' ) ) || $old_data == number_format( 0, 2, '.', ',' ) ) {
						} else {
							$wpdb->insert(
								$table_name,
								array(
									'timestamp'            => current_time( 'mysql' ),
									'user_id'              => get_current_user_id(),
									'order_id'             => $order_id,
									'old_value'            => $old_data,
									'new_value'            => number_format( 0, 2, '.', ',' ),
									'action'               => 'Automatically updated commission to 0 when order was refunded',
									'is_commission_change' => 1,
									'is_manual_change'     => 0
								)
							);
							update_post_meta( $order_id, '_commission', number_format( 0, 2, '.', ',' ) );
						}
					} else {
						$wpdb->insert(
							$table_name,
							array(
								'timestamp'            => current_time( 'mysql' ),
								'user_id'              => get_current_user_id(),
								'order_id'             => $order_id,
								'old_value'            => $old_data,
								'new_value'            => number_format( 0, 2, '.', ',' ),
								'action'               => 'Automatically updated commission to 0 when order was refunded',
								'is_commission_change' => 1,
								'is_manual_change'     => 0
							)
						);
						update_post_meta( $order_id, '_commission', number_format( 0, 2, '.', ',' ) );
					}
				}
			} else {
				if ( $zacctmgr_order_recalculate_commission == 'override' ) {
					$old_data = get_post_meta( $order_id, '_commission', true );
					$data     = zacctmgr_get_total_commission_by_order( $order );
					$query    = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_id=$order_id AND is_commission_change=1  ORDER BY timestamp DESC LIMIT 1; " );

					if ( count( $query ) > 0 ) {
						if ( ( $query[0]->old_value == $old_data && $query[0]->new_value == $data ) || $query[0]->new_value == $data ) {
						} else {
							$wpdb->insert(
								$table_name,
								array(
									'timestamp'            => current_time( 'mysql' ),
									'user_id'              => get_current_user_id(),
									'order_id'             => $order_id,
									'old_value'            => $old_data,
									'new_value'            => $data,
									'action'               => 'Automatically updated commission when order was updated',
									'is_commission_change' => 1,
									'is_manual_change'     => 0
								)
							);
							update_post_meta( $order_id, '_commission', $data );
						}
					} else {
						$wpdb->insert(
							$table_name,
							array(
								'timestamp'            => current_time( 'mysql' ),
								'user_id'              => get_current_user_id(),
								'order_id'             => $order_id,
								'old_value'            => $old_data,
								'new_value'            => $data,
								'action'               => 'Automatically updated commission when order was updated',
								'is_commission_change' => 1,
								'is_manual_change'     => 0
							)
						);
						update_post_meta( $order_id, '_commission', $data );
					}
				} else {
					if ( $zacctmgr_order_recalculate_commission == 'yes' ) {
						$old_data = get_post_meta( $order_id, '_commission', true );
						$data     = zacctmgr_get_total_commission_by_order( $order );
						$query    = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_id=$order_id AND is_commission_change=1 ORDER BY timestamp DESC LIMIT 1; " );

						if ( count( $query ) > 0 ) {
							if ( ( $query[0]->old_value == $old_data && $query[0]->new_value == $data ) || $query[0]->new_value == $data || $query[0]->is_manual_change == 1 ) {
							} else {
								$wpdb->insert(
									$table_name,
									array(
										'timestamp'            => current_time( 'mysql' ),
										'user_id'              => get_current_user_id(),
										'order_id'             => $order_id,
										'old_value'            => $old_data,
										'new_value'            => $data,
										'action'               => 'Automatically updated commission when order was updated',
										'is_commission_change' => 1,
										'is_manual_change'     => 0
									)
								);
								update_post_meta( $order_id, '_commission', $data );
							}
						} else {
							$wpdb->insert(
								$table_name,
								array(
									'timestamp'            => current_time( 'mysql' ),
									'user_id'              => get_current_user_id(),
									'order_id'             => $order_id,
									'old_value'            => $old_data,
									'new_value'            => $data,
									'action'               => 'Automatically updated commission when order was updated',
									'is_commission_change' => 1,
									'is_manual_change'     => 0
								)
							);
							update_post_meta( $order_id, '_commission', $data );
						}
					}
				}
			}
		}
	}

	/**
	 * Retrieve last order level commission rate for a manager
	 *
	 * @param $manager_id
	 *
	 * @return Object|null
	 */
	function zacctmgr_get_latest_order_level( $manager_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "zacctmgr_acm_commissions_mapping";

		$result = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_level=1 AND manager_id=$manager_id ORDER BY timestamp DESC LIMIT 1;" );

		if ( count( $result ) > 0 ) {
			return $result[0];
		}

		return null;
	}


	/**
	 * Retrieve the number of accounts set to order level
	 *
	 * @param $manager_id
	 *
	 * @return array
	 */
	function zacctmgr_get_account_type_number( $manager_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "zacctmgr_acm_commissions_mapping";

		$last_commission_rate = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id ORDER BY timestamp DESC LIMIT 1;" );


		$numbers = array(
			'order_level'            => 0,
			'customer_account_level' => 0
		);

		if ( $last_commission_rate[0]->order_level == 1 && $last_commission_rate[0]->customer_account_level == 0 ) {
			$customers = new WP_User_Query( array(
				'meta_key'   => 'zacctmgr_assigned',
				'meta_value' => $manager_id
			) );

			foreach ( $customers->get_results() as $item ) {
				$numbers['order_level'] ++;
			}

		} else {


			$results = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id ORDER BY timestamp DESC;" );

			$customer = [];


			foreach ( $results as $result ) {
				if ( $result->customer_id == null ) {
					continue;
				}

				if ( isset( $customer[ $result->customer_id ] ) ) {
					continue;
				}

				if ( $result->manager_id != zacctmgr_get_manager_id( $result->customer_id ) ) {
					continue;
				}

				$customer[ $result->customer_id ] = 1;
				if ( $result->order_level == 1 ) {
					$numbers['order_level'] ++;
				} else {
					if ( $result->customer_account_level == 1 ) {
						$numbers['customer_account_level'] ++;
					}
				}
			}
		}

		return $numbers;
	}

	/**
	 * Retrieve number of accounts for a manager (life-time)
	 *
	 * @param $manager_id
	 *
	 * @return array
	 */
	function zacctmgr_get_total_accounts_by_manager( $manager_id ) {
		$accounts = [];
		$total    = 0;
		global $wpdb;
		$table_name = $wpdb->prefix . 'zacctmgr_acm_assignments_mapping';

		$results = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id ORDER BY timestamp DESC;" );

		foreach ( $results as $result ) {
			if ( $result->customer_id == null ) {
				continue;
			}

			if ( isset( $accounts[ $result->customer_id ] ) ) {
				continue;
			}

			$accounts[ $result->customer_id ] = 1;

			$total += (float) wc_get_customer_total_spent( $result->customer_id );
		}

		$result = array(
			'number'        => count( $accounts ),
			'total_revenue' => $total
		);

		return $result;
	}

?>
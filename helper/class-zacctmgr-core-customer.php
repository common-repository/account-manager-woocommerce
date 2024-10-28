<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	class ZACCTMGR_Core_Customer extends WP_List_Table {

		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'customer',
					'plural'   => 'customers',
					'ajax'     => false,
				)
			);
		}

		public function print_overview() {
			$this->prepare_items();

			echo '<div class="wrap zacctmgr_overview_wrap">';
			echo '<h1>Account Manager Overview</h1>';
			echo '<hr class="wp-header-end"/>';
			echo '<h2 class="screen-reader-text">Account Manager List</h2>';

			if ( ! empty( $_GET['link_orders'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'link_orders' ) ) {
				$linked = wc_update_new_customer_past_orders( absint( $_GET['link_orders'] ) );

				echo '<div class="updated"><p>' . sprintf( _n( '%s previous order linked', '%s previous orders linked', $linked, 'woocommerce' ), $linked ) . '</p></div>';
			}

			if ( ! empty( $_GET['refresh'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'refresh' ) ) {
				$user_id = absint( $_GET['refresh'] );
				$user    = get_user_by( 'id', $user_id );

				delete_user_meta( $user_id, '_money_spent' );
				delete_user_meta( $user_id, '_order_count' );

				echo '<div class="updated"><p>' . sprintf( __( 'Refreshed stats for %s', 'woocommerce' ), $user->display_name ) . '</p></div>';
			}

			if ( zacctmgr_allow_edit_others_commission() ) {
				$manager_id = 0;
			} else {
				$manager_id = get_current_user_id();
			}

			$query = zacctmgr_get_customers_query( array(
				'manager_id' => $manager_id,
				'per_page'   => - 1
			) );


			echo '<ul class="subsubsub">';
			echo '<li><a href="' . admin_url() . 'admin.php?page=zacctmgr">All (' . $query->get_total() . ')</a></li>';
			echo '<li>';
			echo '<form method="post" action="' . admin_url( 'admin-post.php' ) . '" id="zacctmgr_export_overview_form">';
			echo '<input type="hidden" name="action" value="zacctmgr_export_overview"/>';
			echo wp_nonce_field( 'zacctmgr_export_overview', 'zacctmgr_export_overview_nonce' );
			echo '<button type="submit" class="button button-default" style="margin-left: 5px; margin-top: -5px; margin-bottom: 5px;">Export</button>';
			echo '</form>';
			echo '</li>';
			echo '</ul>';
			if ( ! isset( $_REQUEST['manager_filter'] ) ) {
				echo '<div style="display: inline-block; margin: 0 1rem; float: right;" id="zacctmgr_search_customer" class="searchform">';
				echo '<label class="screen-reader-text" for="s">Search Customer</label>';
				echo '<input type="text" class="search-field" value="" name="zacctmgr_search_customer_term" data-link="' . admin_url() . '" id="zacctmgr_search_customer_term" placeholder=""/>';
				submit_button( 'Search Customer', '', 'submit', false, array( 'id' => 'zacctmgr_customer_search_submit' ) );
				echo '</div>';
			}
			$this->display();
			echo '</div>';
		}

		public function column_default( $user, $column_name ) {
			switch ( $column_name ) {
				case 'customer_name':
					if ( $user->last_name && $user->first_name ) {
						return $user->last_name . ', ' . $user->first_name;
					} else {
						return ' - ';
					}
				case 'billing_company':
					$billing_company = get_user_meta( $user->ID, 'billing_company', true );

					return $billing_company;

				case 'location':
					$state_code   = get_user_meta( $user->ID, 'billing_state', true );
					$country_code = get_user_meta( $user->ID, 'billing_country', true );
					if ( ! class_exists( 'WooCommerce' ) ) {

						$state   = isset( WC()->countries->states[ $country_code ][ $state_code ] ) ? WC()->countries->states[ $country_code ][ $state_code ] : $state_code;
						$country = isset( WC()->countries->countries[ $country_code ] ) ? WC()->countries->countries[ $country_code ] : $country_code;
					} else {
						$state   = $state_code;
						$country = $country_code;

					}
					$value = '';

					if ( $state ) {
						$value .= $state . ', ';
					}

					$value .= $country;

					if ( $value ) {
						return $value;
					} else {
						return ' - ';
					}

				case 'email':
					$billing_phone = get_user_meta( $user->ID, 'billing_phone', true );

					return '<a href="mailto:' . $user->user_email . '"> ' . $user->user_email . '</a><br/>' . $billing_phone;

				case 'accountmanager':
					$manager_name = '';
					$manager_id   = zacctmgr_get_manager_id( $user->ID );

					if ( $manager_id != 0 ) {
						$first_name = get_user_meta( $manager_id, 'first_name', true );
						$last_name  = get_user_meta( $manager_id, 'last_name', true );

						if ( $first_name == '' && $last_name == '' ) {
							return ' - ';
						} else {
							return $first_name . ' ' . $last_name;
						}
					}

					return $manager_name;

				case 'spent':
					if ( ! class_exists( 'WooCommerce' ) ) {

						return wc_price( wc_get_customer_total_spent( $user->ID ) );
					} else {
						return 0;
					}
				case 'commissions':
					$data = zacctmgr_get_total_commission_by_customer( $user );
					if ( ! class_exists( 'WooCommerce' ) ) {

						return wc_price( $data['total'] );
					} else {
						return 0;
					}

				case 'orders':
					if ( class_exists( 'WooCommerce' ) ) {

						return wc_get_customer_order_count( $user->ID );
					} else {
						return 0;
					}
				case 'last_order':
					if ( class_exists( 'WooCommerce' ) ) {

						$orders = wc_get_orders(
							array(
								'limit'    => 1,
								'status'   => array_map( 'wc_get_order_status_name', wc_get_is_paid_statuses() ),
								'customer' => $user->ID,
							)
						);
					} else {
						$orders = [];
					}

					if ( ! empty( $orders ) ) {
						$order = $orders[0];

						return '<a href="' . admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ) . '">' . _x( '#',
								'hash before order number', 'woocommerce' ) . $order->get_order_number() .
						       '</a> &ndash; ' . wc_format_datetime( $order->get_date_created() );
					} else {
						return '-';
					}

					break;
				case
				'wc_actions':
					ob_start();
					?><p>
					<?php
					do_action( 'woocommerce_admin_user_actions_start', $user );

					$actions = array();

					$actions['refresh'] = array(
						'url'    => wp_nonce_url( add_query_arg( 'refresh', $user->ID ), 'refresh' ),
						'name'   => 'Refresh stats',
						'action' => 'refresh',
					);

					$actions['edit'] = array(
						'url'    => admin_url( 'user-edit.php?user_id=' . $user->ID ),
						'name'   => 'Edit',
						'action' => 'edit',
					);

					$actions['view'] = array(
						'url'    => admin_url( 'edit.php?post_type=shop_order&_customer_user=' . $user->ID ),
						'name'   => 'View orders',
						'action' => 'view',
					);
					if ( class_exists( 'WooCommerce' ) ) {

						$orders = wc_get_orders(
							array(
								'limit'    => 1,
								'status'   => array_map( 'wc_get_order_status_name', wc_get_is_paid_statuses() ),
								'customer' => array( array( 0, $user->user_email ) ),
							)
						);
					} else {
						$orders = null;
					}

					if ( $orders ) {
						$actions['link'] = array(
							'url'    => wp_nonce_url( add_query_arg( 'link_orders', $user->ID ), 'link_orders' ),
							'name'   => 'Link previous orders',
							'action' => 'link',
						);
					}

					$actions['insight'] = array(
						'url'    => admin_url( 'admin.php?page=zacctmgr_insights&customer_id=' . $user->ID ),
						'name'   => 'View insights',
						'action' => 'insight',
					);

					$actions = apply_filters( 'woocommerce_admin_user_actions', $actions, $user );

					foreach ( $actions as $action ) {
						printf( '<a class="button tips %s" href="%s" data-tip="%s">%s</a>', esc_attr( $action['action'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
					}

					do_action( 'woocommerce_admin_user_actions_end', $user );
					?>
                    </p>
					<?php
					$user_actions = ob_get_contents();
					ob_end_clean();

					return $user_actions;
			}

			return '';
		}

		public function get_columns() {
			$columns = array(
				'customer_name'   => 'Name (Last, First)',
				'billing_company' => 'Company',
				'email'           => 'Contact',
				'accountmanager'  => 'Account Manager',
				'orders'          => 'Orders',
				'spent'           => 'Money spent',
				'commissions'     => 'Commissions',
				'last_order'      => 'Last order',
				'wc_actions'      => 'Actions',
			);

			if ( ! zacctmgr_allow_edit_commission() ) {
				unset( $columns['commissions'] );
			}


			return $columns;
		}

		public function get_sortable_columns() {
			return array(
				'accountmanager' => array( 'accountmanager', true ),
				'spent'          => array( 'spent', true ),
				//'commissions' => array('commissions', true)
			);
		}

		public function order_by_last_name( $query ) {
			global $wpdb;

			$s       = ! empty( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : '';
			$orderby = ! empty( $_REQUEST['orderby'] ) ? stripslashes( $_REQUEST['orderby'] ) : '';
			$order   = ! empty( $_REQUEST['order'] ) ? stripslashes( $_REQUEST['order'] ) : '';

			if ( $orderby == '' || $order == '' ) {
				$query->query_from    .= " LEFT JOIN {$wpdb->usermeta} as meta2 ON ({$wpdb->users}.ID = meta2.user_id) ";
				$query->query_where   .= " AND meta2.meta_key = 'last_name' ";
				$query->query_orderby = ' ORDER BY meta2.meta_value, user_login ASC';
			} else {
				if ( $orderby == 'spent' ) {
					$query->query_from    .= " LEFT JOIN {$wpdb->usermeta} as meta2 on ({$wpdb->users}.ID = meta2.user_id) ";
					$query->query_where   .= " AND meta2.meta_key = '_money_spent' ";
					$query->query_orderby = ' ORDER BY meta2.meta_value + 0 ' . $order;
				} elseif ( $orderby == 'accountmanager' ) {
					$query->query_from    .= " LEFT JOIN {$wpdb->usermeta} as meta2 on ({$wpdb->users}.ID = meta2.user_id AND meta2.meta_key='zacctmgr_assigned') ";
					$query->query_from    .= " LEFT JOIN {$wpdb->users} as meta4 on (meta2.meta_value = meta4.ID) ";
					$query->query_orderby = " ORDER BY meta4.display_name " . $order;
				}
			}

			if ( $s ) {
				$query->query_from    .= " LEFT JOIN {$wpdb->usermeta} as meta3 ON ({$wpdb->users}.ID = meta3.user_id)";
				$query->query_where   .= " AND ( user_login LIKE '%" . esc_sql( str_replace( '*', '', $s ) ) . "%' OR user_nicename LIKE '%" . esc_sql( str_replace( '*', '', $s ) ) . "%' OR meta3.meta_value LIKE '%" . esc_sql( str_replace( '*', '', $s ) ) . "%' ) ";
				$query->query_orderby = ' GROUP BY ID ' . $query->query_orderby;
			}

			return $query;
		}

		public function usort_reorder(
			$a, $b
		) {
			$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'accountmanager';
			$order   = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';

			if ( $orderby == 'accountmanager' ) {
				/* A Name */
				$manager_id_a = zacctmgr_get_manager_id( $a->ID );

				$name_a = '';
				if ( $manager_id_a != 0 ) {
					$first_name_a = get_user_meta( $manager_id_a, 'first_name', true );
					$last_name_a  = get_user_meta( $manager_id_a, 'last_name', true );

					if ( $first_name_a != '' || $last_name_a != '' ) {
						$name_a = $first_name_a . ' ' . $last_name_a;
					}
				}
				/* A Name End */

				/* B Name */
				$manager_id_b = zacctmgr_get_manager_id( $b->ID );

				$name_b = '';
				if ( $manager_id_b != 0 ) {
					$first_name_b = get_user_meta( $manager_id_b, 'first_name', true );
					$last_name_b  = get_user_meta( $manager_id_b, 'last_name', true );

					if ( $first_name_b != '' || $last_name_b != '' ) {
						$name_b = $first_name_b . ' ' . $last_name_b;
					}
				}
				/* B Name End */

				$result = strcmp( $name_a, $name_b ) ? 1 : - 1;

				return ( $order === 'asc' ) ? $result : - $result;
			} elseif ( $orderby == 'spent' ) {
				$spent_a = (float) wc_get_customer_total_spent( $a->ID );
				$spent_b = (float) wc_get_customer_total_spent( $b->ID );

				$result = $spent_a >= $spent_b ? 1 : - 1;

				return ( $order === 'asc' ) ? $result : - $result;
			} elseif ( $orderby == 'commissions' ) {
				$data1 = zacctmgr_get_total_commission_by_customer( $a );
				$data2 = zacctmgr_get_total_commission_by_customer( $b );

				$result = $data1['total'] >= $data2['total'] ? 1 : - 1;

				return ( $order === 'asc' ) ? $result : - $result;
			} else {
				return 1;
			}
		}

		public function extra_tablenav( $which ) {
			if ( $which == 'top' ) {
				$manager_id = 0;
				if ( isset( $_REQUEST['manager_filter'] ) ) {
					$manager_id = (int) $_REQUEST['manager_filter'];
				}

				$users = zacctmgr_get_em_users();
				if ( zacctmgr_allow_edit_others_commission() ):
					?>
                    <div class="alignleft actions bulkactions">
                        <select data-link="<?php echo admin_url(); ?>" name="manager-filter" id="zacctmgr_filter"
                                class="ewc-filter-cat">
                            <option value="">Filter by Account Manager...</option>
							<?php
								if ( $users && count( $users ) > 0 ) {
									foreach ( $users as $user ) {
										$user_id    = (int) $user->ID;
										$first_name = get_user_meta( $user_id, 'first_name', true );
										$last_name  = get_user_meta( $user_id, 'last_name', true );

										$name = '-';
										if ( $first_name != '' || $last_name != '' ) {
											$name = $first_name . ' ' . $last_name;
										}

										$selected = $manager_id == $user_id ? 'selected="selected"' : '';
										?>
                                        <option value="<?php echo $user_id; ?>" <?php echo $selected; ?>><?php echo $name; ?></option>
										<?php
									}
								}
							?>
                        </select>
                    </div>
				<?php endif;
			}
		}

		public function prepare_items() {
			$current_page = absint( $this->get_pagenum() );
			$per_page     = 20;

			/**
			 * Init column headers.
			 */
			$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

			add_action( 'pre_user_query', array( $this, 'order_by_last_name' ) );

			/**
			 * Get users.
			 */
			if ( zacctmgr_allow_edit_others_commission() ) {
				$manager_id = 0;
			} else {
				$manager_id = get_current_user_id();
			}

			if ( isset( $_REQUEST['name'] ) ) {
				$name = esc_textarea( $_REQUEST['name'] );
			} else {
				$name = '';
			}

			if ( isset( $_REQUEST['manager_filter'] ) ) {
				$manager_id = (int) $_REQUEST['manager_filter'];
			}

			$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'accountmanager';
			$order   = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';

			$query = zacctmgr_get_customers_query( array(
				'current_page' => $current_page,
				'per_page'     => $per_page,
				'manager_id'   => $manager_id,
				'name'         => $name
			), $orderby, $order );

			$results = $query->get_results();
			//usort($results, array($this, 'usort_reorder'));

			$this->items = $results;

			remove_action( 'pre_user_query', array( $this, 'order_by_last_name' ) );

			/**
			 * Pagination.
			 */
			$this->set_pagination_args(
				array(
					'total_items' => $query->total_users,
					'per_page'    => $per_page,
					'total_pages' => ceil( $query->total_users / $per_page ),
				)
			);
		}
	}

?>
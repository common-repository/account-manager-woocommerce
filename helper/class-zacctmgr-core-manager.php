<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	class ZACCTMGR_Core_Manager extends WP_List_Table {
		public $commissionData = null;
		public $managers = null;
		public $ranges = [];

		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'manager',
					'plural'   => 'managers',
					'ajax'     => false,
				)
			);
			$this->ranges = array(
				'all'        => __( 'All', 'woocommerce' ),
				'year'       => __( 'Year', 'woocommerce' ),
				'last_month' => __( 'Last month', 'woocommerce' ),
				'month'      => __( 'This month', 'woocommerce' ),
				'7day'       => __( 'Last 7 days', 'woocommerce' ),
			);


			$this->managers       = zacctmgr_get_em_users();
			$this->commissionData = zacctmgr_get_total_commission_by_managers( $this->managers );
		}

		public function print_overview() {
			$this->prepare_items();


			echo '<div class="wrap">';
			echo '<h1>Commission</h1>';
			echo '<hr class="wp-header-end"/>';
			echo '<h2 class="screen-reader-text">Account Manager List</h2>';
			?>

            <div class="stats_range">
                <ul>
					<?php
						if ( isset( $_GET['range'] ) ) {
							$current_range = $_GET['range'];
						} else {
							$current_range = 'all';
							$_GET['range'] = 'all';
						}
						foreach ( $this->ranges as $range => $name ) {
							echo '<li class="' . ( $current_range == $range ? 'active' : '' ) . '"><a href="' . esc_url( remove_query_arg( array(
									'start_date',
									'end_date'
								), add_query_arg( 'range', $range ) ) ) . '">' . esc_html( $name ) . '</a></li>';
						}
					?>
                    <li class="custom <?php echo ( 'custom' === $current_range ) ? 'active' : ''; ?>">
						<?php esc_html_e( 'Custom:', 'woocommerce' ); ?>
                        <form method="GET">
                            <div>
								<?php
									// Maintain query string.
									foreach ( $_GET as $key => $value ) {
										if ( is_array( $value ) ) {
											foreach ( $value as $v ) {
												echo '<input type="hidden" name="' . esc_attr( sanitize_text_field( $key ) ) . '[]" value="' . esc_attr( sanitize_text_field( $v ) ) . '" />';
											}
										} else {
											echo '<input type="hidden" name="' . esc_attr( sanitize_text_field( $key ) ) . '" value="' . esc_attr( sanitize_text_field( $value ) ) . '" />';
										}
									}
								?>
                                <input type="hidden" name="range" value="custom"/>
                                <input type="text" size="11" placeholder="yyyy-mm-dd"
                                       value="<?php echo ( ! empty( $_GET['start_date'] ) ) ? esc_attr( wp_unslash( $_GET['start_date'] ) ) : ''; ?>"
                                       name="start_date" class="range_datepicker from" id="start_date_datepicker"
                                       autocomplete="off"/><?php //@codingStandardsIgnoreLine
								?>
                                <span>&ndash;</span>
                                <input type="text" size="11" placeholder="yyyy-mm-dd"
                                       value="<?php echo ( ! empty( $_GET['end_date'] ) ) ? esc_attr( wp_unslash( $_GET['end_date'] ) ) : ''; ?>"
                                       name="end_date" class="range_datepicker to" id="end_date_datepicker"
                                       autocomplete="off"/><?php //@codingStandardsIgnoreLine
								?>
                                <button type="submit" class="button"
                                        value="<?php esc_attr_e( 'Go', 'woocommerce' ); ?>"><?php esc_html_e( 'Go', 'woocommerce' ); ?></button>
								<?php wp_nonce_field( 'custom_range', 'wc_reports_nonce', false ); ?>
                            </div>
                        </form>
                    </li>
                </ul>
            </div>
            <div class="zacctmgr_tab_content" id="zacctmgr_order_report_section" style="margin-top: 10px;">
				<?php if ( $this->commissionData != null ): ?>
                    <div class="zacctmgr_row">
                        <div class="zacctmgr_col_6 zacctmgr_col_colored">
                            <div class="zacctmgr_row" style="margin: 15px 0">
                                <div class="zacctmgr_col_6">
                                    <label><?php echo count( $this->managers ); ?></label>
                                    <span>Account Managers</span>
                                </div>
                                <div class="zacctmgr_col_6" style="border-left: 2px solid white;">
                                    <label>$<?php echo number_format( $this->commissionData['total']['total'], 2 ); ?></label>
                                    <span>Total Commissions Paid</span>
                                </div>
                            </div> <!-- Row End !-->
                        </div> <!-- Col End !-->
                        <div class="zacctmgr_col_6">
                            <div class="zacctmgr_row" style="margin: 15px 0">
                                <div class="zacctmgr_col_6">
                                    <label>$<?php echo number_format( $this->commissionData['total']['new'], 2 ); ?></label>
                                    <span>Total Commission New</span>
                                </div>
                                <div class="zacctmgr_col_6" style="border-left: 2px solid #efefef">
                                    <label>$<?php echo number_format( $this->commissionData['total']['existing'], 2 ); ?></label>
                                    <span>Total Commission Existing</span>
                                </div>
                            </div> <!-- Row End !-->
                        </div> <!-- Col End !-->
                    </div>
				<?php else: ?>
                    <div class="zacctmgr_row" style="margin: 3rem auto; display: block; text-align: center;">
                        <h2 style="font-size: 26px; color: #737373;">Oops, No Data for Time Period Selected</h2>
                        <h3 style="font-size: 20px; color: #737373;">Try another data range</h3>
                    </div> <!-- Col End !-->
				<?php endif; ?>
            </div> <!-- ACM Tab Content End !-->
			<?php
			echo '<form method="post" id="woocommerce_customersCopy">';
			//$this->search_box('Search customers', 'customer_search');
			$this->display();
			echo '</form>';
			echo '</div>';
		}

		public function column_default( $user, $column_name ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';

			$manager_commission_rate = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$user->ID AND order_level=1 ORDER BY timestamp DESC LIMIT 1;" );
			if ( count( $manager_commission_rate ) != 0 ) {
				$commission_rate = $manager_commission_rate[0];
			} else {
				$commission_rate                                             = new StdClass ();
				$commission_rate->no_commission                              = 0;
				$commission_rate->order_level                                = 1;
				$commission_rate->customer_account_level                     = 0;
				$commission_rate->new_order_commission_percentage_type       = 0;
				$commission_rate->new_order_commission_fixed_type            = 1;
				$commission_rate->new_order_commission_value                 = 0;
				$commission_rate->new_order_commission_limit                 = 1;
				$commission_rate->new_order_exclude_coupon_amount            = 0;
				$commission_rate->new_order_exclude_taxes_amount             = 0;
				$commission_rate->new_order_exclude_shipping_costs           = 0;
				$commission_rate->new_order_exclude_shipping_tax_amount      = 0;
				$commission_rate->existing_order_commission_percentage_type  = 0;
				$commission_rate->existing_order_commission_fixed_type       = 1;
				$commission_rate->existing_order_commission_value            = 0;
				$commission_rate->existing_order_exclude_coupon_amount       = 0;
				$commission_rate->existing_order_exclude_taxes_amount        = 0;
				$commission_rate->existing_order_exclude_shipping_costs      = 0;
				$commission_rate->existing_order_exclude_shipping_tax_amount = 0;

			}

			$new_fixed_type      = $commission_rate->new_order_commission_fixed_type;
			$existing_fixed_type = $commission_rate->existing_order_commission_fixed_type;
			$new_value           = $commission_rate->new_order_commission_value;
			$existing_value      = $commission_rate->existing_order_commission_value;


			switch ( $column_name ) {
				case 'manager_name':
					if ( $user->last_name && $user->first_name ) {
						return $user->last_name . ', ' . $user->first_name;
					} else {
						return '-';
					}
				case 'username':
					return $user->user_nicename;
				case 'role':
					global $wp_roles;
					$wp_roles_content = $wp_roles->roles;

					$roles = [];
					if ( $user->roles && count( $user->roles ) > 0 ) {
						foreach ( $user->roles as $role ) {
							if ( isset( $wp_roles_content[ $role ] ) && isset( $wp_roles_content[ $role ]['name'] ) ) {
								$roles[] = $wp_roles_content[ $role ]['name'];
							}
						}
					}

					if ( count( $roles ) > 0 ) {
						return implode( ', ', $roles );
					} else {
						return '';
					}
				case 'new_orders':
					$value = $new_value;
					$type  = $new_fixed_type == 1 ? 'fixed' : 'percentage';

					$string = number_format( $value, 2 );
					if ( $type == 'fixed' ) {
						$string = '$' . $string;
					} else {
						$string = $string . '%';
					}


					return $string;
				case 'existing_orders':
					$value = $existing_value;
					$type  = $existing_fixed_type == 1 ? 'fixed' : 'percentage';

					$string = number_format( $value, 2 );
					if ( $type == 'fixed' ) {
						$string = '$' . $string;
					} else {
						$string = $string . '%';
					}


					return $string;
				case 'total_commissions':
					return '$' . number_format( $this->commissionData['managers'][ $user->ID ]['total']['total'], 2 );
				case 'wc_actions':
					ob_start();
					?><p>
					<?php
					do_action( 'woocommerce_admin_user_actions_start', $user );

					$actions = array();
					if ( zacctmgr_allow_edit_commission() == true ) {

						$actions['edit'] = array(
							'url'    => admin_url( 'admin.php?page=zacctmgr_commission&tab=account_managers&manager_id=' . $user->ID ),
							'name'   => 'Edit',
							'action' => 'edit',
						);
					}

					$actions['view'] = array(
						'url'    => admin_url( 'edit.php?post_type=shop_order&_customer_user=' . $user->ID ),
						'name'   => 'View orders',
						'action' => 'view',
					);

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
				'manager_name'      => 'Name (Last, First)',
				'username'          => 'Username',
				'role'              => 'Role',
				'new_orders'        => 'New Orders',
				'existing_orders'   => 'Existing Orders',
				'total_commissions' => 'Total Commissions',
				'wc_actions'        => 'Actions',
			);

			return $columns;
		}

		public function get_sortable_columns() {
			return [];
		}

		public function order_by_last_name( $query ) {
			global $wpdb;

			$s = ! empty( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : '';

			$query->query_from    .= " LEFT JOIN {$wpdb->usermeta} as meta2 ON ({$wpdb->users}.ID = meta2.user_id) ";
			$query->query_where   .= " AND meta2.meta_key = 'last_name' ";
			$query->query_orderby = ' ORDER BY meta2.meta_value, user_login ASC ';

			if ( $s ) {
				$query->query_from    .= " LEFT JOIN {$wpdb->usermeta} as meta3 ON ({$wpdb->users}.ID = meta3.user_id)";
				$query->query_where   .= " AND ( user_login LIKE '%" . esc_sql( str_replace( '*', '', $s ) ) . "%' OR user_nicename LIKE '%" . esc_sql( str_replace( '*', '', $s ) ) . "%' OR meta3.meta_value LIKE '%" . esc_sql( str_replace( '*', '', $s ) ) . "%' ) ";
				$query->query_orderby = ' GROUP BY ID ' . $query->query_orderby;
			}

			return $query;
		}

		public function extra_tablenav( $which ) {
			if ( $which == 'top' ) {
				$managers = zacctmgr_get_em_users();

				echo '<ul class="subsubsub">';
				echo '<li><a href="/wp-admin/admin.php?page=zacctmgr">All (' . count( $managers ) . ')</a></li>';
				echo '</ul>';
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
			$query = zacctmgr_get_managers_query( array(
				'current_page' => $current_page,
				'per_page'     => $per_page
			) );

			$results = $query->get_results();

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
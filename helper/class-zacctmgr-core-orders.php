<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ZACCTMGR_Core_Order extends WP_List_Table {

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'order',
				'plural'   => 'orders',
				'ajax'     => false,
			)
		);
	}

	public function print_overview() {
		$this->prepare_items();
		echo '<div class="wrap zacctmgr_overview_wrap">';
		echo '<hr class="wp-header-end"/>';
		echo '<h2 class="screen-reader-text">Order List</h2>';
		$this->display();
		echo '</div>';
	}

	public function column_default( $post, $column_name ) {
		$order = wc_get_order( $post->ID );

		if ( $order->get_type() == 'shop_order_refund' ) {
			$orderRefunded = wc_get_order( $order->get_parent_id() );
			switch ( $column_name ) {
				case 'order':
					$buyer = '';

					if ( $orderRefunded->get_billing_first_name() || $orderRefunded->get_billing_last_name() ) {
						/* translators: 1: first name 2: last name */
						$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $orderRefunded->get_billing_first_name(), $orderRefunded->get_billing_last_name() ) );
					} elseif ( $orderRefunded->get_billing_company() ) {
						$buyer = trim( $orderRefunded->get_billing_company() );
					} elseif ( $orderRefunded->get_customer_id() ) {
						$user  = get_user_by( 'id', $orderRefunded->get_customer_id() );
						$buyer = ucwords( $user->display_name );
					}

					if ( $orderRefunded->get_status() === 'trash' ) {
						return '<strong>#' . esc_attr( $orderRefunded->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong>';
					} else {
						return '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $orderRefunded->get_id() ) ) . '&action=edit' ) . '" class="order-view"><strong>#' . esc_attr( $orderRefunded->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong></a>';
					}
				case 'date':
					$order_timestamp = $orderRefunded->get_date_created() ? $orderRefunded->get_date_created()->getTimestamp() : '';

					if ( ! $order_timestamp ) {
						return '&ndash;';
					}

					// Check if the order was created within the last 24 hours, and not in the future.
					if ( $order_timestamp > strtotime( '-1 day', current_time( 'timestamp', true ) ) && $order_timestamp <= current_time( 'timestamp', true ) ) {
						$show_date = sprintf(
						/* translators: %s: human-readable time difference */
							_x( '%s ago', '%s = human-readable time difference', 'woocommerce' ),
							human_time_diff( $orderRefunded->get_date_created()->getTimestamp(), current_time( 'timestamp', true ) )
						);
					} else {
						$show_date = $orderRefunded->get_date_created()->date_i18n( apply_filters( 'woocommerce_admin_order_date_format', __( 'M j, Y', 'woocommerce' ) ) );
					}

					return '<time datetime="' . esc_attr( $orderRefunded->get_date_created()->date( 'c' ) ) . '" title="' . esc_html( $orderRefunded->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) . '">' . esc_html( $show_date ) . '</time>';
				case 'status':
					$tooltip                 = '';
					$comment_count           = get_comment_count( $orderRefunded->get_id() );
					$approved_comments_count = absint( $comment_count['approved'] );

					if ( $approved_comments_count ) {
						$latest_notes = wc_get_order_notes(
							array(
								'order_id' => $orderRefunded->get_id(),
								'limit'    => 1,
								'orderby'  => 'date_created_gmt',
							)
						);

						$latest_note = current( $latest_notes );

						if ( isset( $latest_note->content ) && 1 === $approved_comments_count ) {
							$tooltip = wc_sanitize_tooltip( $latest_note->content );
						} elseif ( isset( $latest_note->content ) ) {
							/* translators: %d: notes count */
							$tooltip = wc_sanitize_tooltip( $latest_note->content . '<br/><small style="display:block">' . sprintf( _n( 'Plus %d other note', 'Plus %d other notes', ( $approved_comments_count - 1 ), 'woocommerce' ), $approved_comments_count - 1 ) . '</small>' );
						} else {
							/* translators: %d: notes count */
							$tooltip = wc_sanitize_tooltip( sprintf( _n( '%d note', '%d notes', $approved_comments_count, 'woocommerce' ), $approved_comments_count ) );
						}
					}

					if ( $tooltip ) {
						return '<mark class="order-status ' . esc_attr( sanitize_html_class( 'status-' . $orderRefunded->get_status() ) ) . ' tips" data-tip="' . wp_kses_post( $tooltip ) . '"><span>' . esc_html( wc_get_order_status_name( $orderRefunded->get_status() ) ) . '</span></mark>';
					} else {
						return '<mark class="order-status ' . esc_attr( sanitize_html_class( 'status-' . $orderRefunded->get_status() ) ) . '"><span>' . esc_html( wc_get_order_status_name( $orderRefunded->get_status() ) ) . '</span></mark>';
					}
				case 'billing':
					$address = $orderRefunded->get_formatted_billing_address();

					if ( $address ) {
						$o = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $address ) );

						if ( $orderRefunded->get_payment_method() ) {
							/* translators: %s: payment method */
							$o .= '<br><span class="description">' . sprintf( __( 'via %s', 'woocommerce' ), esc_html( $orderRefunded->get_payment_method_title() ) ) . '</span>'; // WPCS: XSS ok.
						}

						return $o;
					} else {
						return '&ndash;';
					}
				case 'total':
					if ( $orderRefunded->get_payment_method_title() ) {
						/* translators: %s: method */
						return '<span class="tips" data-tip="' . esc_attr( sprintf( __( 'via %s', 'woocommerce' ), $orderRefunded->get_payment_method_title() ) ) . '">' . wp_kses_post( $orderRefunded->get_formatted_order_total() ) . '</span>';
					} else {
						return wp_kses_post( $orderRefunded->get_formatted_order_total() );
					}
				case 'account_manager':
					if ( get_post_meta( $orderRefunded->get_id(), '_account_manager', true ) != "" ) {
						return '<span>' . get_post_meta( $orderRefunded->get_id(), '_account_manager', true ) . '</span>';
					} else {
						return '<span>No data available</span>';
					}
				case 'commission':
					if ( get_post_meta( $orderRefunded->get_id(), '_commission', true ) != "" ) {
						return '<span> $' . get_post_meta( $orderRefunded->get_id(), '_commission', true ) . '</span>';
					} else {
						return '<span>No data available</span>';
					}
				case 'wc_actions':
					ob_start();
					?><p>
					<?php

					$actions = array();

					$actions['edit'] = array(
						'url'    => admin_url( 'admin.php?page=zacctmgr_commission&tab=orders&edit=' . $orderRefunded->get_id() ),
						'name'   => 'Edit',
						'action' => 'edit',
					);

					foreach ( $actions as $action ) {
						printf( '<a class="button tips %s" href="%s" data-tip="%s">%s</a>', esc_attr( $action['action'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
					}
					?>
                    </p>
					<?php
					$order_actions = ob_get_contents();
					ob_end_clean();

					return $order_actions;
			}

			return '';

		} else {
			switch ( $column_name ) {
				case 'order':
					$buyer = '';

					if ( $order->get_billing_first_name() || $order->get_billing_last_name() ) {
						/* translators: 1: first name 2: last name */
						$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $order->get_billing_first_name(), $order->get_billing_last_name() ) );
					} elseif ( $order->get_billing_company() ) {
						$buyer = trim( $order->get_billing_company() );
					} elseif ( $order->get_customer_id() ) {
						$user  = get_user_by( 'id', $order->get_customer_id() );
						$buyer = ucwords( $user->display_name );
					}

					if ( $order->get_status() === 'trash' ) {
						return '<strong>#' . esc_attr( $order->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong>';
					} else {
						return '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $order->get_id() ) ) . '&action=edit' ) . '" class="order-view"><strong>#' . esc_attr( $order->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong></a>';
					}
				case 'date':
					$order_timestamp = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : '';

					if ( ! $order_timestamp ) {
						return '&ndash;';
					}

					// Check if the order was created within the last 24 hours, and not in the future.
					if ( $order_timestamp > strtotime( '-1 day', current_time( 'timestamp', true ) ) && $order_timestamp <= current_time( 'timestamp', true ) ) {
						$show_date = sprintf(
						/* translators: %s: human-readable time difference */
							_x( '%s ago', '%s = human-readable time difference', 'woocommerce' ),
							human_time_diff( $order->get_date_created()->getTimestamp(), current_time( 'timestamp', true ) )
						);
					} else {
						$show_date = $order->get_date_created()->date_i18n( apply_filters( 'woocommerce_admin_order_date_format', __( 'M j, Y', 'woocommerce' ) ) );
					}

					return '<time datetime="' . esc_attr( $order->get_date_created()->date( 'c' ) ) . '" title="' . esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) . '">' . esc_html( $show_date ) . '</time>';
				case 'status':
					$tooltip                 = '';
					$comment_count           = get_comment_count( $order->get_id() );
					$approved_comments_count = absint( $comment_count['approved'] );

					if ( $approved_comments_count ) {
						$latest_notes = wc_get_order_notes(
							array(
								'order_id' => $order->get_id(),
								'limit'    => 1,
								'orderby'  => 'date_created_gmt',
							)
						);

						$latest_note = current( $latest_notes );

						if ( isset( $latest_note->content ) && 1 === $approved_comments_count ) {
							$tooltip = wc_sanitize_tooltip( $latest_note->content );
						} elseif ( isset( $latest_note->content ) ) {
							/* translators: %d: notes count */
							$tooltip = wc_sanitize_tooltip( $latest_note->content . '<br/><small style="display:block">' . sprintf( _n( 'Plus %d other note', 'Plus %d other notes', ( $approved_comments_count - 1 ), 'woocommerce' ), $approved_comments_count - 1 ) . '</small>' );
						} else {
							/* translators: %d: notes count */
							$tooltip = wc_sanitize_tooltip( sprintf( _n( '%d note', '%d notes', $approved_comments_count, 'woocommerce' ), $approved_comments_count ) );
						}
					}

					if ( $tooltip ) {
						return '<mark class="order-status ' . esc_attr( sanitize_html_class( 'status-' . $order->get_status() ) ) . ' tips" data-tip="' . wp_kses_post( $tooltip ) . '"><span>' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</span></mark>';
					} else {
						return '<mark class="order-status ' . esc_attr( sanitize_html_class( 'status-' . $order->get_status() ) ) . '"><span>' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</span></mark>';
					}
				case 'billing':
					$address = $order->get_formatted_billing_address();

					if ( $address ) {
						$o = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $address ) );

						if ( $order->get_payment_method() ) {
							/* translators: %s: payment method */
							$o .= '<br><span class="description">' . sprintf( __( 'via %s', 'woocommerce' ), esc_html( $order->get_payment_method_title() ) ) . '</span>'; // WPCS: XSS ok.
						}

						return $o;
					} else {
						return '&ndash;';
					}
				case 'total':
					if ( $order->get_payment_method_title() ) {
						/* translators: %s: method */
						return '<span class="tips" data-tip="' . esc_attr( sprintf( __( 'via %s', 'woocommerce' ), $order->get_payment_method_title() ) ) . '">' . wp_kses_post( $order->get_formatted_order_total() ) . '</span>';
					} else {
						return wp_kses_post( $order->get_formatted_order_total() );
					}
				case 'account_manager':
					if ( get_post_meta( $order->get_id(), '_account_manager', true ) != "" ) {
						return '<span>' . get_post_meta( $order->get_id(), '_account_manager', true ) . '</span>';
					} else {
						return '<span>No data available</span>';
					}
				case 'commission':
					if ( get_post_meta( $order->get_id(), '_commission', true ) != "" ) {
						return '<span> $' . get_post_meta( $order->get_id(), '_commission', true ) . '</span>';
					} else {
						return '<span>No data available</span>';
					}
				case 'wc_actions':
					ob_start();
					?><p>
					<?php

					$actions = array();

					$actions['edit'] = array(
						'url'    => admin_url( 'admin.php?page=zacctmgr_commission&tab=orders&edit=' . $order->get_id() ),
						'name'   => 'Edit',
						'action' => 'edit',
					);

					foreach ( $actions as $action ) {
						printf( '<a class="button tips %s" href="%s" data-tip="%s">%s</a>', esc_attr( $action['action'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
					}
					?>
                    </p>
					<?php
					$order_actions = ob_get_contents();
					ob_end_clean();

					return $order_actions;
			}

			return '';
		}
	}

	public function get_columns() {
		$columns = array(
			'order'           => 'Order',
			'date'            => 'Date',
			'status'          => 'Status',
			'billing'         => 'Billing',
			'total'           => 'Total',
			'account_manager' => 'Account Manager',
			'commission'      => 'Commission',
			'wc_actions'      => 'Actions',
		);

		return $columns;
	}

	public function get_sortable_columns() {
		return array(
//				'order' => array( 'order', true ),
//				'date'  => array( 'date', true ),
//				'total' => array( 'total', true )
		);
	}

	public function extra_tablenav( $which ) {
		if ( $which == 'top' ) {

			$total_orders = get_posts( array(
				'numberposts' => - 1,
				'post_type'   => wc_get_order_types(),
				'post_status' => zacctmgr_get_allowed_wc_statuses()
			) );


			echo '<ul class="subsubsub" style="margin:3px 10px;">';
			echo '<li><a href="' . admin_url() . 'admin.php?page=zacctmgr_commission&tab=orders">All (' . count( $total_orders ) . ')</a></li>';
			echo '</ul>';

			if ( isset( $_REQUEST['manager_filter'] ) ) {
				$manager_id = (int) $_REQUEST['manager_filter'];
			}

			$users = zacctmgr_get_em_users();
			?>
            <div class="alignleft actions bulkactions">
                <select data-link="<?php echo admin_url(); ?>" name="manager-filter" id="zacctmgr_order_filter"
                        class="ewc-filter-cat">
                    <option value="all">All</option>
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
							if ( isset( $manager_id ) && $manager_id == $user_id ) {
								$selected = 'selected="selected"';
							} else {
								$selected = '';
							}
							?>
                            <option value="<?php echo $user_id; ?>" <?php echo $selected; ?>><?php echo $name; ?></option>
							<?php
						}
					}
					?>
                </select>
            </div>
			<?php if ( ! isset( $_REQUEST['manager_filter'] ) ): ?>
                <div style="display: inline-block; margin: 0 1rem;" id="zacctmgr_search_order"
                     class="searchform">
                    <label class="screen-reader-text" for="s">Search Order</label>
                    <input type="text" class="search-field"
                           value="<?php echo isset( $_GET['search_order'] ) ? sanitize_text_field( $_GET['search_order'] ) : ''; ?>"
                           data-link="<?php echo admin_url(); ?>"
                           name="zacctmgr_search_order_term" id="zacctmgr_search_order_term"
                           placeholder="Search Order"/>
					<?php submit_button( 'Search', '', 'submit', false, array( 'id' => 'zacctmgr_order_search_submit' ) ); ?>
                </div>
			<?php endif; ?>


			<?php

		}
	}

	public function prepare_items() {
		$current_page = $this->get_pagenum();
		$per_page     = 20;

		/**
		 * Init column headers.
		 */
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$allowed_statuses = zacctmgr_get_allowed_wc_statuses();

		if ( isset( $_REQUEST['search_order'] ) ) {
			$search_term = sanitize_text_field( $_REQUEST['search_order'] );
		}

		if ( isset( $_REQUEST['manager_filter'] ) ) {
			$manager_id = (int) $_REQUEST['manager_filter'];
			$manager    = get_user_by( 'id', $manager_id );


			$args = array(
				'meta_key'       => '_account_manager',
				'meta_value'     => $manager->display_name,
				'post_type'      => wc_get_order_types(),
				'post_status'    => $allowed_statuses,
				'posts_per_page' => $per_page,
				'paged'          => $current_page,
				'orderby'        => 'post_date',
				'order'          => 'DESC'
			);

			$total_args = array(
				'numberposts' => - 1,
				'meta_key'    => '_account_manager',
				'meta_value'  => $manager->display_name,
				'post_type'   => wc_get_order_types(),
				'post_status' => $allowed_statuses
			);
		} else {
			if ( ! isset( $search_term ) ) {
				$args = array(
					'post_type'      => wc_get_order_types(),
					'post_status'    => $allowed_statuses,
					'posts_per_page' => $per_page,
					'paged'          => $current_page,
					'orderby'        => 'post_date',
					'order'          => 'DESC'
				);

				$total_args = array(
					'numberposts' => - 1,
					'post_type'   => wc_get_order_types(),
					'post_status' => $allowed_statuses
				);
			} else {

				$args = array(
					'post_type'      => wc_get_order_types(),
					'post_status'    => $allowed_statuses,
					'posts_per_page' => $per_page,
					'meta_query'     => array(
						'relation' => 'OR',
						array(
							'key'     => '_billing_first_name',
							'value'   => $search_term,
							'compare' => 'LIKE'
						),
						array(
							'key'     => '_billing_last_name',
							'value'   => $search_term,
							'compare' => 'LIKE'
						)
					),
					'paged'          => $current_page,
					'orderby'        => 'post_date',
					'order'          => 'DESC'
				);

				$total_args = array(
					'numberposts' => - 1,
					'post_type'   => wc_get_order_types(),
					'post_status' => $allowed_statuses,
					'meta_query'  => array(
						'relation' => 'OR',
						array(
							'key'     => '_billing_first_name',
							'value'   => $search_term,
							'compare' => 'LIKE'
						),
						array(
							'key'     => '_billing_last_name',
							'value'   => $search_term,
							'compare' => 'LIKE'
						)
					),
				);

			}
		}


		$items      = get_posts( $args );
		$total_post = get_posts( $total_args );

		if ( isset( $search_term ) ) {
			$post = wc_get_order( $search_term );
			if ( $post != false ) {
				$items[]      = $post;
				$total_post[] = $post;
			}
		}


		$this->items = $items;


		/**
		 * Pagination.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => count( $total_post ),
				'per_page'    => $per_page,
				'total_pages' => ceil( count( $total_post ) / $per_page ),
			)
		);
	}
}

?>
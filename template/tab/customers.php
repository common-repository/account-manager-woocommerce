<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	$customer_id      = isset( $_GET['customer_id'] ) ? (int) $_GET['customer_id'] : 0;
	$customer_data    = $customer_id != 0 ? get_user_by( 'ID', $customer_id ) : null;

	if ( ! $customer_data ): // Insights
		$topList = zacctmgr_get_top_customers( 5 );
		?>
        <div class="zacctmgr_tab_content">
            <div class="zacctmgr_row zacctmgr_disabled_hpadding">
                <div class="zacctmgr_col_6">
                    <h3>Select a Customer</h3>
                    <select id="zacctmgr_insights_customers"></select>
                </div> <!-- Col End !-->
                <div class="zacctmgr_col_6" style="border-left: 2px solid #efefef;">
                    <h3 class="zacctmgr_center">Top 5 Customers</h3>
					<?php
						if ( $topList ) {
							echo '<ul id="zacctmgr_top_customer_list">';
							$index = 1;
							foreach ( $topList as $item ) {
								echo '<li><label>' . ( $index ++ ) . '. ' . $item->first_name . ' ' . $item->last_name . '</label> <span>' . $item->billing_company . '</span> <font>' . wc_price( wc_get_customer_total_spent( $item->ID ) ) . '</font></li>';
							}
							echo '</ul>';
						}
					?>
                </div> <!-- Col End !-->
            </div> <!-- Row End !-->
        </div> <!-- Tab Content End !-->
	<?php
	else: // Customer Detail
		$manager_id = (int) $customer_data->zacctmgr_assigned;
		$manager_data = $manager_id != 0 ? get_user_by( 'ID', $manager_id ) : null;
		?>
        <div class="zacctmgr_tab_content">
            <div class="zacctmgr_row zacctmgr_disabled_hpadding">
                <div class="zacctmgr_col_6">
                    <h3>Select a Customer</h3>
                    <select id="zacctmgr_insights_customers"></select>
                </div> <!-- Col End !-->
                <div class="zacctmgr_col_6" style="border-left: 2px solid #efefef;">
                    <h3 class="zacctmgr_center">Customer</h3>
                    <ul id="zacctmgr_customer_info" style="padding: 0;">
                        <li class="zacctmgr_background">Last,
                            First:&nbsp;&nbsp;<b><?php echo $customer_data->last_name . ' ' . $customer_data->first_name; ?></b>
                        </li>
                        <li>Company:&nbsp;&nbsp;<b><?php echo $customer_data->billing_company; ?></b></li>
                        <li class="zacctmgr_background">
                            Email:&nbsp;&nbsp;<b><?php echo $customer_data->user_email; ?></b></li>
                        <li>Phone:&nbsp;&nbsp;<b><?php echo $customer_data->billing_phone; ?></b></li>
                        <li class="zacctmgr_background">
                            Account
                            Manager:&nbsp;&nbsp;<b><?php echo $manager_data ? $manager_data->first_name . ' ' . $manager_data->last_name : 'Not Assigned'; ?></b>
                        </li>
                    </ul>
                </div> <!-- Col End !-->
            </div> <!-- Row End !-->
        </div> <!-- Tab Content End !-->

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
		<?php $report = zacctmgr_get_customer_report( $customer_id ); ?>
        <div class="zacctmgr_tab_content" id="zacctmgr_order_report_section">
            <div class="zacctmgr_row">
                <div class="zacctmgr_col_4 zacctmgr_col_colored">
                    <div class="zacctmgr_row" style="margin: 15px 0">
                        <div class="zacctmgr_col_12">
                            <label><?php echo $report->total_orders; ?></label>
                            <span>Orders</span>
                        </div>
                    </div> <!-- Row End !-->
                </div> <!-- Col End !-->
                <div class="zacctmgr_col_8">
                    <div class="zacctmgr_row" style="margin: 15px 0">
                        <div class="zacctmgr_col_6">
                            <label><?php echo '$' . number_format( $report->total_sales, 2 ); ?></label>
                            <span>Gross Sales</span>
                        </div>
                        <div class="zacctmgr_col_6" style="border-left: 2px solid #efefef">
                            <label><?php echo '$' . number_format( $report->net_sales, 2 ); ?></label>
                            <span>Net Sales</span>
                        </div>
                    </div> <!-- Row End !-->
                </div> <!-- Col End !-->
            </div>
        </div> <!-- Tab Content End !-->

        <div class="zacctmgr_tab_content" id="zacctmgr_money_report_section">
            <div class="zacctmgr_row" style="margin: 15px 0;">
                <div class="zacctmgr_col_4">
                    <label><?php echo '$' . number_format( $report->total_coupons, 2 ); ?></label>
                    <span>coupons</span>
                </div> <!-- Col End !-->
                <div class="zacctmgr_col_4" style="border-left: 2px solid #efefef;">
                    <label><?php echo '$' . number_format( $report->total_refunds, 2 ); ?></label>
                    <span>refunds</span>
                </div> <!-- Col End !-->
                <div class="zacctmgr_col_4" style="border-left: 2px solid #efefef;">
                    <label><?php echo '$' . number_format( $report->total_shipping, 2 ); ?></label>
                    <span>shipping</span>
                </div> <!-- Col End !-->
            </div> <!-- Row End !-->
        </div> <!-- Tab Content End !-->

        <div class="zacctmgr_row" id="zacctmgr_detail_report_section">
            <div class="zacctmgr_col_4">
                <div class="zacctmgr_padding">
                    <p class="zacctmgr_background">Number of Products Purchased:
                        <b><?php echo $report->total_items; ?></b></p>
                    <p>Top Ordered Product:
                        <b><?php echo $report->detailed_order_items ? $report->detailed_order_items[0]->order_item_name : ''; ?></b>
                    </p>
                    <p class="zacctmgr_background">Top Ordered Category:
						<?php
							if ( $report->detailed_order_items ) {
								$product_id = $report->detailed_order_items[0]->product_id;

								$product_categories = zacctmgr_get_product_category_by_id( $product_id );

								if ( count( $product_categories ) > 0 ) {
									echo '<b>' . implode( ', ', $product_categories ) . '</b>';
								}
							}
						?>
                    </p>
                </div> <!-- Tab Content End !-->
            </div> <!-- Col End !-->
            <div class="zacctmgr_col_4">
                <div class="zacctmgr_padding">
                    <p class="zacctmgr_background">Avg Orders Per Month: <b><?php echo $report->avg_order; ?></b></p>
                    <p>Avg Order Size: <b><?php echo '$' . number_format( $report->avg_order_size, 2 ); ?></b></p>
                    <p class="zacctmgr_background">Avg Number of Products Ordered:
                        <b><?php echo $report->avg_order_item; ?></b></p>
                </div> <!-- Tab Content End !-->
            </div> <!-- Col End !-->
            <div class="zacctmgr_col_4">
                <div class="zacctmgr_padding" id="zacctmgr_top_order_list">
					<?php
						$orders = zacctmgr_get_orders_by_customer( $customer_id );

						if ( $orders ) {
							foreach ( $orders as $order ) {
								$post_date = $order->post_date;
								$id        = $order->ID;
								$order     = wc_get_order( $order->ID );
								?>
                                <p>
									<?php echo date( 'M d, Y', strtotime( $post_date ) ); ?>:
                                    <a href="/wp-admin/post.php?post=<?php echo $id; ?>&action=edit">#<?php echo $id; ?>
                                        - <?php echo '$' . number_format( $order->get_total(), 2 ); ?></a>
                                </p>
								<?php
							}

							echo '<div style="text-align:center; margin-top: 10px;"><a href="/wp-admin/edit.php?post_type=shop_order&_customer_user=' . $customer_id . '" style="font-weight: bold;">View All</a></div>';
						} else {
							echo 'No Orders';
						}
					?>
                </div> <!-- Tab Content End !-->
            </div> <!-- Col End !-->
        </div> <!-- Row End !-->
	<?php
	endif;
?>
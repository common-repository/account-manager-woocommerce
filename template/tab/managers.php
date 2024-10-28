<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}
	
	$manager_id         = isset( $_GET['manager_id'] ) ? (int) $_GET['manager_id'] : 0;
	$manager_data       = $manager_id != 0 ? get_user_by( 'ID', $manager_id ) : null;
	
	if ( ! $manager_data ):
		
		$topList = zacctmgr_get_top_managers();
		?>
        <div class="zacctmgr_tab_content">
            <div class="zacctmgr_row zacctmgr_disabled_hpadding">
                <div class="zacctmgr_col_6">
                    <h3>Select a Account Manager</h3>
                    <select id="zacctmgr_insights_managers"></select>
                </div> <!-- Col End !-->
                <!--                <div class="zacctmgr_col_6" style="border-left: 2px solid #efefef;">-->
                <!--                    <table id="zacctmgr_top_manager_list" border="0" cellpadding="10" cellspacing="0">-->
                <!--                        <thead>-->
                <!--                        <tr>-->
                <!--                            <th>Top Account Managers</th>-->
                <!--                            <th># Accounts</th>-->
                <!--                            <th>Top Revenue</th>-->
                <!--                        </tr>-->
                <!--                        </thead>-->
                <!--                        <tbody>-->
                <!--						--><?php
					//							if ( $topList ):
					//								$index = 0;
					//								foreach ( $topList as $manager ):
					//									$object = get_user_by( 'ID', $manager->ID );
					//
				?>
                <!--                                    <tr>-->
                <!--                                        <td>-->
				<?php //echo ( ++ $index ) . '. ' . $object->first_name . ' ' . $object->last_name;
				?><!--</td>-->
                <!--                                        <td>--><?php //echo $manager->child;
				?><!--</td>-->
                <!--                                        <td>-->
				<?php //echo '$' . number_format( $manager->total, 2 );
				?><!--</td>-->
                <!--                                    </tr>-->
                <!--								--><?php
					//								endforeach;
					//							endif;
					//
				?>
                <!--                        </tbody>-->
                <!--                    </table>-->
                <!--                </div>-->
            </div> <!-- Row End !-->
        </div> <!-- Tab Content End !-->
	<?php
	else:
		$report = zacctmgr_get_manager_report( $manager_id );
		$commissionData = zacctmgr_get_total_commission_by_manager( $manager_data );
		$total_accounts = zacctmgr_get_total_accounts_by_manager( $manager_id );
		?>
        <div class="zacctmgr_tab_content">
            <div class="zacctmgr_row zacctmgr_disabled_hpadding">
                <div class="zacctmgr_col_6" style="border-right: 2px solid #efefef;">
                    <h3>Select a Account Manager</h3>
                    <select id="zacctmgr_insights_managers"></select>
                </div> <!-- Col End !-->
                <div class="zacctmgr_col_6" style="text-align: center;" id="zacctmgr_top_manager_info">
                    <div>
                        <h3>Account Manager</h3>
                    </div>
                    <div>
                        <h2 style="font-size:36px;"><?php echo $manager_data->first_name . ' ' . $manager_data->last_name; ?></h2>
                    </div>
                </div> <!-- Col End !-->
            </div> <!-- Row End !-->
        </div> <!-- Tab Content End !-->
        <div class="zacctmgr_tab_content" id="zacctmgr_order_report_section_snapshot">
            <div class="zacctmgr_row zacctmgr_disabled_hpadding">
                <div class="zacctmgr_col_6" style="text-align: center;">
                    <h2 style="font-size: 32px;">Snapshot</h2>
                    <h3>Current Accounts & Revenue Managed</h3>
                    <span style="font-size: 16px; color:#888;"><?php echo current_time( 'Y-m-d' ); ?></span>
                </div> <!-- Col End !-->
                <div class="zacctmgr_col_3">
                    <div class="zacctmgr_row">
                        <div class="zacctmgr_col_12">
                            <label><?php echo $total_accounts['number']; ?></label>
                            <span>Total Accounts</span>
                        </div>
                    </div>
                </div> <!-- Col End !-->
                <div class="zacctmgr_col_3">
                    <div class="zacctmgr_row" style="border-left: 2px solid #e4e4e4;">
                        <div class="zacctmgr_col_12">
                            <label><?php echo '$' . number_format( $total_accounts['total_revenue'], 2 ); ?></label>
                            <span>Managed Revenue</span>
                        </div>
                    </div>
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
        <div class="zacctmgr_tab_content" id="zacctmgr_order_report_section">
            <div class="zacctmgr_row">
                <div class="zacctmgr_col_6">
                    <div class="zacctmgr_row">
                        <div class="zacctmgr_col_12">
                            <label><?php echo $report ? count( $report->customers ) : 0; ?></label>
                            <span>New Assigned Accounts</span>
                        </div>
                    </div> <!-- Row End !-->
                </div> <!-- Col End !-->
                <div class="zacctmgr_col_6">
                    <div class="zacctmgr_row" style="border-left: 2px solid #e4e4e4;">
                        <div class="zacctmgr_col_12">
                            <span style="margin-bottom: 1rem;">New Assigned Accounts</span>
                            <label><?php echo $report ? '$' . number_format( $report->total_sales, 2 ) : '$0.00'; ?></label>
                            <span>Managed Revenue</span>
                        </div>
                    </div> <!-- Row End !-->
                </div> <!-- Col End !-->
            </div>
        </div> <!-- Tab Content End !-->

        <div class="zacctmgr_tab_content" id="zacctmgr_money_report_section">
            <div class="zacctmgr_row" style="margin: 15px 0;">
                <div class="zacctmgr_col_4">
                    <label><?php echo $report ? '$' . number_format( $report->total_coupons, 2 ) : '$0.00'; ?></label>
                    <span>coupons</span>
                </div> <!-- Col End !-->
                <div class="zacctmgr_col_4" style="border-left: 2px solid #efefef;">
                    <label><?php echo $report ? '$' . number_format( $report->total_refunds, 2 ) : '$0.00'; ?></label>
                    <span>refunds</span>
                </div> <!-- Col End !-->
                <div class="zacctmgr_col_4" style="border-left: 2px solid #efefef;">
                    <label><?php echo $report ? '$' . number_format( $report->total_shipping, 2 ) : '$0.00'; ?></label>
                    <span>shipping</span>
                </div> <!-- Col End !-->
            </div> <!-- Row End !-->
        </div> <!-- Tab Content End !-->

        <div class="zacctmgr_row" id="zacctmgr_detail_report_section">
            <div class="zacctmgr_col_4">
                <div class="zacctmgr_padding">
                    <p class="zacctmgr_background"># of Products Ordered:
                        <b><?php echo $report ? $report->total_items : 0; ?></b></p>
                    <p>Top Ordered Product:
                        <b><?php echo ( $report && $report->detailed_order_items ) ? $report->detailed_order_items[0]->order_item_name : ''; ?></b>
                    </p>
                    <p class="zacctmgr_background">Top Ordered Category:
						<?php
							if ( $report && $report->detailed_order_items ):
								$product_id = $report->detailed_order_items[0]->product_id;
								
								$product_categories = zacctmgr_get_product_category_by_id( $product_id );
								
								if ( count( $product_categories ) > 0 ) {
									echo '<b>' . implode( ', ', $product_categories ) . '</b>';
								}
							endif;
						?>
                    </p>

                </div> <!-- Tab Content End !-->
            </div> <!-- Col End !-->
            <div class="zacctmgr_col_4">
                <div class="zacctmgr_padding">
                    <p class="zacctmgr_background">Avg Account Orders Per Month:
                        <b><?php echo $report ? $report->avg_order : 0; ?></b></p>
                    <p>Avg Account Order Size:
                        <b><?php echo $report ? '$' . number_format( $report->avg_order_size, 2 ) : '$0.00'; ?></b></p>
                    <p class="zacctmgr_background">Avg Number of Products Ordered:
                        <b><?php echo $report ? $report->avg_order_item : 0; ?></b></p>
                </div> <!-- Tab Content End !-->
            </div> <!-- Col End !-->
            <div class="zacctmgr_col_4">
                <div class="zacctmgr_padding" id="zacctmgr_top_order_list">
					<?php
						if ( $report ):
							$orders = zacctmgr_get_orders_by_customers( $report->customers );
							
							if ( $orders ):
								foreach ( $orders as $order ):
									$post_date = $order->post_date;
									$id = $order->ID;
									$order = wc_get_order( $order->ID );
									?>
                                    <p>
										<?php echo date( 'M d, Y', strtotime( $post_date ) ); ?>:
                                        <a href="/wp-admin/post.php?post=<?php echo $id; ?>&action=edit">#<?php echo $id; ?>
                                            - <?php echo '$' . number_format( $order->get_total(), 2 ); ?></a>
                                    </p>
								<?php
								endforeach;
								
								echo '<div style="text-align:center; margin-top: 10px;"><a href="/wp-admin/edit.php?post_type=shop_order&post_status=all&zacctmgr_filter_wc=' . $manager_id . '&filter_action=Filter" style="font-weight:bold;">View All</a></div>';
							else:
								echo '<p class="zacctmgr_center" style="background:transparent;">No Orders</p>';
							endif;
						else:
							echo '<p class="zacctmgr_center" style="background:transparent;">No Orders</p>';
						endif;
					?>
                </div> <!-- Tab Content End !-->
            </div> <!-- Col End !-->
        </div> <!-- Row End !-->
	<?php
	endif;
?>
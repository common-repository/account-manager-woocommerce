<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}
	
	$manager_id   = get_current_user_id();
	$manager_data = $manager_id != 0 ? get_user_by( 'ID', $manager_id ) : null;
	
	
	/* order_level, customer_account_level, no_commission */
	
	$value1 = '<input type="text" name="commission_new_value" required value="' . ( isset( $data->zacctmgr_commission_new_value ) ? $data->zacctmgr_commission_new_value : '' ) . '"/>';
	$value2 = '<input type="text" name="commission_existing_value" required value="' . ( isset( $data->zacctmgr_commission_existing_value ) ? $data->zacctmgr_commission_existing_value : '' ) . '"/>';
	
	$type1 = '';
	$type1 .= '<select name="commission_new_type" id="zacctmgr_commission_new_type_select">';
	$type1 .= '<option value="fixed" ' . ( ( isset( $data->zacctmgr_commission_new_type ) && $data->zacctmgr_commission_new_type == 'fixed' ) ? 'selected="selected"' : '' ) . '>Fixed</option>';
	$type1 .= '<option value="percentage" ' . ( ( isset( $data->zacctmgr_commission_new_type ) && $data->zacctmgr_commission_new_type == 'percentage' ) ? 'selected="selected"' : '' ) . '>Percentage</option>';
	$type1 .= '</select>';
	
	$type2 = '';
	$type2 .= '<select name="commission_existing_type" id="zacctmgr_commission_existing_type_select">';
	$type2 .= '<option value="fixed" ' . ( ( isset( $data->zacctmgr_commission_existing_type ) && $data->zacctmgr_commission_existing_type == 'fixed' ) ? 'selected="selected"' : '' ) . '>Fixed</option>';
	$type2 .= '<option value="percentage" ' . ( ( isset( $data->zacctmgr_commission_existing_type ) && $data->zacctmgr_commission_existing_type == 'percentage' ) ? 'selected="selected"' : '' ) . '>Percentage</option>';
	$type2 .= '</select>';
	
	$order_count = ( isset( $data->zacctmgr_commission_order_count ) && (int) $data->zacctmgr_commission_order_count > 1 ) ? (int) $data->zacctmgr_commission_order_count : 1;
	
	$order1 = '<input type="number" name="commission_order_count" value="' . $order_count . '"/>';
	
	/* Apply 1 */
	$apply1 = '';
	
	$style_fixed      = 'style="display: block;"';
	$style_percentage = 'style="display: none;"';
	
	if ( isset( $data->zacctmgr_commission_new_type ) && $data->zacctmgr_commission_new_type == 'percentage' ) {
		$style_fixed      = 'style="display: none;"';
		$style_percentage = 'style="display: block;"';
	}
	
	$zacctmgr_commission_new_exclude_options = isset( $data->zacctmgr_commission_new_exclude_options ) ? $data->zacctmgr_commission_new_exclude_options : [];
	
	$apply1 .= '<div class="zacctmgr_commission_new_type_result" id="zacctmgr_commission_new_type_result_percentage" ' . $style_percentage . '>';
	$apply1 .= '<h4 style="margin-bottom: 5px;">Order Total</h4>';
	
	$apply1 .= '<div style="box-sizing: border-box; padding-left: 20px;">';
	foreach ( ZACCTMGR_EXCLUDE_OPTIONS as $key => $label ) {
		$extra = in_array( $key, $zacctmgr_commission_new_exclude_options ) ? 'checked="checked"' : '';
		
		$apply1 .= '<div style="margin-bottom: 5px;">';
		$apply1 .= '<input style="margin-top: 3px;" type="checkbox" id="cnex_' . $key . '" name="commission_new_exclude_options[]" value="' . $key . '" ' . $extra . '/>';
		$apply1 .= '<label for="cnex_' . $key . '">' . $label . '</label>';
		$apply1 .= '</div>';
	}
	$apply1 .= '</div>';
	$apply1 .= '</div>';
	
	$apply1 .= '<div class="zacctmgr_commission_new_type_result" id="zacctmgr_commission_new_type_result_fixed" ' . $style_fixed . '>';
	$apply1 .= '<h4>Fixed Value Amount</h4>';
	$apply1 .= '</div>';
	/* Apply 1 End */
	
	/* Apply 2 */
	$apply2 = '';
	
	$style_fixed      = 'style="display: block;"';
	$style_percentage = 'style="display: none;"';
	
	if ( isset( $data->zacctmgr_commission_existing_type ) && $data->zacctmgr_commission_existing_type == 'percentage' ) {
		$style_fixed      = 'style="display: none;"';
		$style_percentage = 'style="display: block;"';
	}
	
	$zacctmgr_commission_existing_exclude_options = isset( $data->zacctmgr_commission_existing_exclude_options ) ? $data->zacctmgr_commission_existing_exclude_options : [];
	
	$apply2 .= '<div class="zacctmgr_commission_existing_type_result" id="zacctmgr_commission_existing_type_result_percentage" ' . $style_percentage . '>';
	$apply2 .= '<h4 style="margin-bottom: 5px;">Order Total</h4>';
	
	$apply2 .= '<div style="box-sizing: border-box; padding-left: 20px;">';
	foreach ( ZACCTMGR_EXCLUDE_OPTIONS as $key => $label ) {
		$extra = in_array( $key, $zacctmgr_commission_existing_exclude_options ) ? 'checked="checked"' : '';
		
		$apply2 .= '<div style="margin-bottom: 5px;">';
		$apply2 .= '<input style="margin-top: 3px;" type="checkbox" id="ceex_' . $key . '" name="commission_existing_exclude_options[]" value="' . $key . '" ' . $extra . '/>';
		$apply2 .= '<label for="ceex_' . $key . '">' . $label . '</label>';
		$apply2 .= '</div>';
	}
	$apply2 .= '</div>';
	$apply2 .= '</div>';
	
	$apply2 .= '<div class="zacctmgr_commission_existing_type_result" id="zacctmgr_commission_existing_type_result_fixed" ' . $style_fixed . '>';
	$apply2 .= '<h4>Fixed Value Amount</h4>';
	$apply2 .= '</div>';
	/* Apply 2 End */
	
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'zacctmgr_edit_commission' ) ) {
		echo '<div class="notice updated my-acf-notice is-dismissible" style="margin-top: 10px;"><p>Account Manager commission updated.<br/><a href="admin.php?page=zacctmgr_commission">&#8592; Back to Commission</a></p></div>';
	}
	
	
	echo '<div class="wrap">';
	echo '<hr class="wp-header-end"/>';
	echo '<h2 class="screen-reader-text">Account Manager Commission Edit</h2>';
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';
	
	$last_commission_rate_query = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id AND customer_account_level=0 ORDER BY timestamp DESC LIMIT 1;" );
	
	if ( count( $last_commission_rate_query ) != 0 ) {
		
		$new_value      = trim( $last_commission_rate_query[0]->new_order_commission_value );
		$new_type       = $last_commission_rate_query[0]->new_order_commission_fixed_type == 1 ? 'fixed' : 'percentage';
		$existing_value = trim( $last_commission_rate_query[0]->existing_order_commission_value );
		$existing_type  = $last_commission_rate_query[0]->existing_order_commission_fixed_type == 1 ? 'fixed' : 'percentage';
		$order_limit    = $last_commission_rate_query[0]->new_order_commission_limit;
	} else {
		$new_type       = 'fixed';
		$existing_type  = 'fixed';
		$new_value      = 0;
		$existing_value = 0;
		$order_limit    = 0;
	}
	$report         = zacctmgr_get_manager_report( $manager_id );
	$commissionData = zacctmgr_get_total_commission_by_manager( $manager_data );
	
	$order_level_commission = zacctmgr_get_latest_order_level( $manager_id );
	$accounts_number         = zacctmgr_get_account_type_number( $manager_id );
?>
<div class="zacctmgr_tab_content">
    <div class="zacctmgr_row zacctmgr_disabled_hpadding">
        <div class="zacctmgr_col_12" style="border-left: 2px solid #efefef;" id="zacctmgr_top_manager_info">
            <article>
                <label>Account Manager</label>
                <h2 style="text-align: left; margin-left: 200px;"><?php echo $manager_data->first_name . ' ' . $manager_data->last_name; ?></h2>
            </article>
            <article>
                <label>Commissions</label>
				<?php
					$new_label = $existing_label = '';
					if ( $new_value != '' && $new_type != '' ) {
						if ( $new_type == 'fixed' ) {
							$new_label = '$' . number_format( $new_value, 2 ) . ' new orders';
						} else {
							$new_label = number_format( $new_value, 2 ) . '% new orders';
						}
					}
					if ( $existing_value != '' && $existing_type != '' ) {
						if ( $existing_type == 'fixed' ) {
							$existing_label = '$' . number_format( $existing_value, 2 ) . ' existing orders';
						} else {
							$existing_label = number_format( $existing_value, 2 ) . '% existing orders';
						}
					}
				?>
                <p style="flex: 0 0 auto; margin-left:10px; text-align: center;"><?php echo $accounts_number['order_level']; ?>
                    Accounts
                    <br/><span style="text-align: left; font-size:12px;">Order Level</span></p>
                <p style="flex: 0 0 auto; border-left: 2px solid #efefef; margin-left:60px; text-align: center;"><?php echo $accounts_number['customer_account_level']; ?>
                    Accounts
                    <br/><span style="text-align: left; font-size:12px;">Customer Account Level</span>
                </p>
            </article>
            <article style="padding: 0;">
                <table style="width: 100%; text-align: left; border-spacing: 0;">
                    <tr style="background: #f7f7f7;">
                        <th style="padding: 1rem; padding-left: 5rem;">Order Level Rates</th>
                        <th>Apply to</th>
                        <th>Value</th>
                        <th>Calculated by</th>
                    </tr>
                    <tr>
                        <td style="border-bottom: solid 2px #f7f7f7; padding-left:7rem; width: 15rem;">New Orders</td>
                        <td style="border-bottom: solid 2px #f7f7f7;"><?php echo $order_level_commission->new_order_commission_limit; ?></td>
                        <td style="border-bottom: solid 2px #f7f7f7;"><?php echo $order_level_commission->new_order_commission_value . ( $order_level_commission->new_order_commission_percentage_type == 1 ? '%' : '' ); ?></td>
						<?php
							if ( $order_level_commission->new_order_commission_percentage_type == 1 ) {
								$o = '';
								if ( $order_level_commission->new_order_exclude_coupon_amount == 1 ) {
									$o .= '<span>Exclude Coupon Amount</span><br/>';
								}
								if ( $order_level_commission->new_order_exclude_taxes_amount == 1 ) {
									$o .= '<span>Exclude Taxes Amount</span><br/>';
								}
								if ( $order_level_commission->new_order_exclude_shipping_costs == 1 ) {
									$o .= '<span>Exclude Shipping Costs Amount</span><br/>';
								}
								if ( $order_level_commission->new_order_exclude_shipping_tax_amount == 1 ) {
									$o .= '<span>Exclude Shipping Tax Amount</span><br/>';
								}
								if ( $o == '' ) {
									$o = 'Percentage Value Amount';
								}
							} else {
								if ( $order_level_commission->new_order_commission_fixed_type == 1 ) {
									$o = 'Fixed Value Amount';
								}
							}
						?>
                        <td style="border-bottom: solid 2px #f7f7f7;"><?php echo $o; ?></td>
                    </tr>
                    <hr>
                    <tr>
                        <td style="padding-left:7rem;">Existing Orders</td>
                        <td></td>
                        <td><?php echo $order_level_commission->existing_order_commission_value . ( $order_level_commission->new_order_commission_percentage_type == 1 ? '%' : '' ); ?></td>
						<?php
							if ( $order_level_commission->existing_order_commission_percentage_type == 1 ) {
								$o = '';
								if ( $order_level_commission->existing_order_exclude_coupon_amount == 1 ) {
									$o .= '<span>Exclude Coupon Amount</span><br/>';
								}
								if ( $order_level_commission->existing_order_exclude_taxes_amount == 1 ) {
									$o .= '<span>Exclude Taxes Amount</span><br/>';
								}
								if ( $order_level_commission->existing_order_exclude_shipping_costs == 1 ) {
									$o .= '<span>Exclude Shipping Costs Amount</span><br/>';
								}
								if ( $order_level_commission->existing_order_exclude_shipping_tax_amount == 1 ) {
									$o .= '<span>Exclude Shipping Tax Amount</span><br/>';
								}
								if ( $o == '' ) {
									$o = 'Percentage Value Amount';
								}
							} else {
								if ( $order_level_commission->existing_order_commission_fixed_type == 1 ) {
									$o = 'Fixed Value Amount';
								}
							}
						?>
                        <td><?php echo $o; ?></td>
                    </tr>
                </table>
            </article>
        </div> <!-- Col End !-->
        <div class="zacctmgr_col_6">
        </div> <!-- Row End !-->
    </div>
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
	<?php if ( $commissionData != null ): ?>
        <div class="zacctmgr_row">
            <div class="zacctmgr_col_8">
                <div class="zacctmgr_row" style="margin: 15px 0">
                    <div class="zacctmgr_col_6">
                        <label>$<?php echo number_format( $commissionData['total']['new'], 2 ); ?></label>
                        <span>Total Commission New</span>
                    </div>
                    <div class="zacctmgr_col_6" style="border-left: 2px solid #efefef">
                        <label>$<?php echo number_format( $commissionData['total']['existing'], 2 ); ?></label>
                        <span>Total Commission Existing</span>
                    </div>
                </div>
            </div> <!-- Row End !-->
            <div class="zacctmgr_col_4 zacctmgr_col_colored">
                <div class="zacctmgr_row" style="margin: 15px 0">
                    <div class="zacctmgr_col_12">
                        <label style="color: #000 !important;">$<?php echo number_format( $commissionData['total']['total'], 2 ); ?></label>
                        <span style="color: #000 !important;">Total Commission</span>
                    </div>
                </div>
            </div>
        </div> <!-- Col End !-->
	<?php else: ?>
        <div class="zacctmgr_row" style="margin: 3rem auto; display: block; text-align: center;">
            <h2 style="font-size: 26px; color: #737373;">Oops, No Data for Time Period Selected</h2>
            <h3 style="font-size: 20px; color: #737373;">Try another data range</h3>
        </div> <!-- Col End !-->
	<?php endif; ?>
</div> <!-- Tab Content End !-->
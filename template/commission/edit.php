<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}
	
	$manager_id   = isset( $_GET['manager_id'] ) ? (int) $_GET['manager_id'] : 0;
	$manager_data = $manager_id != 0 ? get_user_by( 'ID', $manager_id ) : null;
	
	
	if ( zacctmgr_allow_edit_commission() == false ) {
		$o = '<div class="zacctmgr_not_allowed_wrap">';
		$o .= '<p>Sorry, you are not allowed to access this page!</p>';
		$o .= '</div>';
		wp_die( $o, 403 );
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';
	
	$manager_commission_rate                = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id AND customer_id IS NULL ORDER BY timestamp DESC LIMIT 1;" );
	$manager_order_level_commission_rate    = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id AND order_level=1 AND customer_account_level=0 AND customer_id IS NULL ORDER BY timestamp DESC LIMIT 1;" );
	$manager_customer_level_commission_rate = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id AND customer_id IS NULL AND customer_account_level=1 ORDER BY timestamp DESC LIMIT 1;" );
	$manager_no_commission_rate             = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id AND customer_id IS NULL AND customer_account_level=0 AND no_commission=1 ORDER BY timestamp DESC LIMIT 1;" );
	
	if ( count( $manager_commission_rate ) != 0 ) {
		$current_commission_rate = $manager_commission_rate[0];
		if ( count( $manager_order_level_commission_rate ) != 0 ) {
			if ( count( $manager_customer_level_commission_rate ) != 0 ) {
				if ( $manager_order_level_commission_rate[0]->timestamp > $manager_customer_level_commission_rate[0]->timestamp ) {
					if ( count( $manager_no_commission_rate ) != 0 ) {
						if ( $manager_order_level_commission_rate[0]->timestamp > $manager_no_commission_rate[0]->timestamp ) {
							$commission_rate = $manager_order_level_commission_rate[0];
						} else {
							$commission_rate = $manager_no_commission_rate[0];
						}
					} else {
						$commission_rate = $manager_order_level_commission_rate[0];
					}
				} else {
					if ( count( $manager_no_commission_rate ) != 0 ) {
						if ( $manager_customer_level_commission_rate[0]->timestamp > $manager_no_commission_rate[0]->timestamp ) {
							$commission_rate = $manager_customer_level_commission_rate[0];
						} else {
							$commission_rate = $manager_no_commission_rate[0];
						}
					} else {
						$commission_rate = $manager_customer_level_commission_rate[0];
					}
				}
			} else {
				if ( count( $manager_no_commission_rate ) != 0 ) {
					if ( $manager_order_level_commission_rate[0]->timestamp > $manager_no_commission_rate[0]->timestamp ) {
						$commission_rate = $manager_order_level_commission_rate[0];
					} else {
						$commission_rate = $manager_no_commission_rate[0];
					}
				} else {
					$commission_rate = $manager_order_level_commission_rate[0];
				}
			}
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
		
		$current_commission_rate = $commission_rate;
		
	}
	
	$new_exclude = [];
	if ( $commission_rate->new_order_commission_percentage_type == 1 ) {
		if ( $commission_rate->new_order_exclude_coupon_amount == 1 ) {
			array_push( $new_exclude, 'coupon' );
		}
		if ( $commission_rate->new_order_exclude_taxes_amount == 1 ) {
			array_push( $new_exclude, 'tax' );
		}
		if ( $commission_rate->new_order_exclude_shipping_costs == 1 ) {
			array_push( $new_exclude, 'shipping' );
		}
		if ( $commission_rate->new_order_exclude_shipping_tax_amount == 1 ) {
			array_push( $new_exclude, 'shipping_tax' );
		}
	}
	
	$existing_exclude = [];
	if ( $commission_rate->existing_order_commission_percentage_type == 1 ) {
		
		if ( $commission_rate->existing_order_exclude_coupon_amount == 1 ) {
			array_push( $existing_exclude, 'coupon' );
		}
		if ( $commission_rate->existing_order_exclude_taxes_amount == 1 ) {
			array_push( $existing_exclude, 'tax' );
		}
		if ( $commission_rate->existing_order_exclude_shipping_costs == 1 ) {
			array_push( $existing_exclude, 'shipping' );
		}
		if ( $commission_rate->existing_order_exclude_shipping_tax_amount == 1 ) {
			array_push( $existing_exclude, 'shipping_tax' );
		}
	}
	
	if ( $commission_rate->order_level == 1 ) {
		$commission_type = 'order_level';
	} elseif ( $commission_rate->customer_account_level == 1 ) {
		$commission_type = 'customer_account_level';
	} else {
		$commission_type = 'no_commission';
	}
	
	if ( $current_commission_rate->order_level == 1 ) {
		$current_commission_type = 'order_level';
	} elseif ( $current_commission_rate->customer_account_level == 1 ) {
		$current_commission_type = 'customer_account_level';
	} else {
		$current_commission_type = 'no_commission';
	}
	
	
	$new_fixed_type           = $commission_rate->new_order_commission_fixed_type;
	$existing_fixed_type      = $commission_rate->existing_order_commission_fixed_type;
	$new_percentage_type      = $commission_rate->new_order_commission_percentage_type;
	$existing_percentage_type = $commission_rate->existing_order_commission_percentage_type;
	$order_count              = $commission_rate->new_order_commission_limit;
	$new_value                = $commission_rate->new_order_commission_value;
	$existing_value           = $commission_rate->existing_order_commission_value;
	
	
	/* order_level, customer_account_level, no_commission */
	$zacctmgr_commission_type = $data->zacctmgr_commission_type ? $data->zacctmgr_commission_type : 'order_level';
	
	$value1 = '<input type="number" step="0.01"  min="0"' . ( $new_fixed_type == 1 ? 'max="1000000"' : 'max="100"' ) . 'id="commission_new_value" name="commission_new_value" required value="' . $new_value . '" style="width: 60%;"/>';
	$value2 = '<input type="number" step="0.01" min="0"' . ( $existing_fixed_type == 1 ? 'max="1000000"' : 'max="100"' ) . 'id="commission_existing_value" name="commission_existing_value" required value="' . $existing_value . '" style="width: 60%;"/>';
	
	$type1 = '';
	$type1 .= '<select name="commission_new_type" id="zacctmgr_commission_new_type_select">';
	$type1 .= '<option value="fixed" ' . ( $new_fixed_type == 1 ? 'selected="selected"' : '' ) . '>Fixed</option>';
	$type1 .= '<option value="percentage" ' . ( $new_percentage_type == 1 ? 'selected="selected"' : '' ) . '>Percentage</option>';
	$type1 .= '</select>';
	
	$type2 = '';
	$type2 .= '<select name="commission_existing_type" id="zacctmgr_commission_existing_type_select">';
	$type2 .= '<option value="fixed" ' . ( $existing_fixed_type == 1 ? 'selected="selected"' : '' ) . '>Fixed</option>';
	$type2 .= '<option value="percentage" ' . ( $existing_percentage_type == 1 ? 'selected="selected"' : '' ) . '>Percentage</option>';
	$type2 .= '</select>';
	
	$order1 = '<input type="number" name="commission_order_count" value="' . $order_count . '"/>';
	
	/* Apply 1 */
	$apply1 = '';
	
	$style_fixed      = 'style="display: block;"';
	$style_percentage = 'style="display: none;"';
	
	
	if ( $new_percentage_type == 1 ) {
		$style_fixed      = 'style="display: none;"';
		$style_percentage = 'style="display: block;"';
	}
	
	$zacctmgr_commission_new_exclude_options = $new_exclude;
	
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
	
	if ( $existing_percentage_type == 1 ) {
		$style_fixed      = 'style="display: none;"';
		$style_percentage = 'style="display: block;"';
	}
	
	$zacctmgr_commission_existing_exclude_options = $existing_exclude;
	
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

//	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'zacctmgr_edit_commission' ) ) {
//		echo '<div class="notice updated my-acf-notice is-dismissible" style="margin-top: 10px;"><p>Account Manager commission updated.<br/><a href="admin.php?page=zacctmgr_commission&tab=account_managers">&#8592; Back to Commission</a></p></div>';
//	}
	
	
	echo '<div class="wrap">';
	echo '<h1>Edit Commission <a href="admin.php?page=zacctmgr_commission&tab=account_managers" class="zacctmgr_goback_btn"><span class="dashicons dashicons-undo"></span></a></h1>';
	echo '<hr class="wp-header-end"/>';
	echo '<h2 class="screen-reader-text">Account Manager Commission Edit</h2>';
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';
	
	$report         = zacctmgr_get_manager_report( $manager_id );
	$new_type       = $new_fixed_type == 1 ? 'fixed' : 'percentage';
	$existing_type  = $existing_fixed_type == 1 ? 'fixed' : 'percentage';
	$commissionData = zacctmgr_get_total_commission_by_manager( $manager_data );
	
	
	$current_commission_calculation_type = $current_commission_type;
	$current_new_order_limit             = $commission_rate->new_order_commission_limit;
	$current_new_order_type              = $commission_rate->new_order_commission_fixed_type == 1 ? 'fixed' : 'percentage';
	$current_existing_order_type         = $commission_rate->existing_order_commission_fixed_type == 1 ? 'fixed' : 'percentage';
	$current_new_value                   = $commission_rate->new_order_commission_value;
	$current_existing_value              = $commission_rate->existing_order_commission_value;
	$current_new_exclude                 = $new_exclude;
	$current_existing_exclude            = $existing_exclude;
	
	
	$audit_info = '<input type="hidden" name="current_commission_calculation_type" value="' . $current_commission_calculation_type . '"/>';
	$audit_info .= '<input type="hidden" name="current_new_order_limit" value="' . $current_new_order_limit . '"/>';
	$audit_info .= '<input type="hidden" name="current_new_order_type" value="' . $current_new_order_type . '"/>';
	$audit_info .= '<input type="hidden" name="current_existing_order_type" value="' . $current_existing_order_type . '"/>';
	$audit_info .= '<input type="hidden" name="current_new_value" value="' . $current_new_value . '"/>';
	$audit_info .= '<input type="hidden" name="current_existing_value" value="' . $current_existing_value . '"/>';
	foreach ( $current_new_exclude as $item ) {
		$audit_info .= '<input type="hidden" name="current_new_exclude[]" value="' . $item . '">';
	}
	foreach ( $current_existing_exclude as $item ) {
		$audit_info .= '<input type="hidden" name="current_existing_exclude[]" value="' . $item . '">';
	}
	
	
	$order_level_commission = zacctmgr_get_latest_order_level( $manager_id );
	$accounts_number        = zacctmgr_get_account_type_number( $manager_id );
?>
    <div class="zacctmgr_tab_content">
        <div class="zacctmgr_row zacctmgr_disabled_hpadding">
            <div class="zacctmgr_col_12" style="border-left: 2px solid #efefef;" id="zacctmgr_top_manager_info">
                <article>
                    <label>Account Manager</label>
                    <h2 style="text-align: left; margin-left: 200px;"><?php echo $manager_data->first_name . ' ' . $manager_data->last_name; ?></h2>
                </article>
                <article>
                    <label>Commission Rates</label>
					<?php
						$new_label = $existing_label = '';
						if ( $new_value != '' && $new_type != '' ) {
							if ( $new_type == 'fixed' ) {
								$new_label = '$' . number_format( $new_value, 2, '.', ',' ) . ' new orders';
							} else {
								$new_label = number_format( $new_value, 2 ) . '% new orders';
							}
						}
						if ( $existing_value != '' && $existing_type != '' ) {
							if ( $existing_type == 'fixed' ) {
								$existing_label = '$' . number_format( $existing_value, 2, '.', ',' ) . ' existing orders';
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
                            <td style="border-bottom: solid 2px #f7f7f7; padding-left:7rem; width: 15rem;">New Orders
                            </td>
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
                            <label>$<?php echo number_format( $commissionData['total']['new'], 2, '.', ',' ); ?></label>
                            <span>Total Commission New</span>
                        </div>
                        <div class="zacctmgr_col_6" style="border-left: 2px solid #efefef">
                            <label>$<?php echo number_format( $commissionData['total']['existing'], 2, '.', ',' ); ?></label>
                            <span>Total Commission Existing</span>
                        </div>
                    </div>
                </div> <!-- Row End !-->
                <div class="zacctmgr_col_4 zacctmgr_col_colored">
                    <div class="zacctmgr_row" style="margin: 15px 0">
                        <div class="zacctmgr_col_12">
                            <label style="color: #000 !important;">$<?php echo number_format( $commissionData['total']['total'], 2, '.', ',' ); ?></label>
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
<?php
	echo '<form method="post" action="' . admin_url( 'admin-post.php' ) . '" id="zacctmgr_edit_commission">';
	echo '<input type="hidden" name="action" value="zacctmgr_edit_commission"/>';
	echo '<input type="hidden" name="manager_id" value="' . $manager_id . '"/>';
	echo $audit_info;
	
	echo wp_nonce_field( 'zacctmgr_edit_commission' );
	echo '<div class="zacctmgr_edit_commission_table">';
	echo '<table class="form-table" cellpadding="0" style="padding:0; border-spacing: 0">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Commission Calculations</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	echo '<tr>';
	echo '<td>';
	echo '<div class="zacctmgr_edit_commission_flex_wrap">';
	echo '<div class="zacctmgr_edit_commission_flex_item">';
	echo '<input type="radio" id="zacctmgr_commission_type1" name="zacctmgr_commission_type" value="order_level" ' . ( $current_commission_type == 'order_level' ? 'checked="checked"' : '' ) . '/>';
	echo '<label for="zacctmgr_commission_type1">Order Level</label>';
	echo '</div>';
	
	echo '<div class="zacctmgr_edit_commission_flex_item">';
	echo '<input type="radio" id="zacctmgr_commission_type2" name="zacctmgr_commission_type" value="customer_account_level" ' . ( $current_commission_type == 'customer_account_level' ? 'checked="checked"' : '' ) . '/>';
	echo '<label for="zacctmgr_commission_type2">Customer Account Level</label>';
	echo '</div>';
	
	echo '<div class="zacctmgr_edit_commission_flex_item">';
	echo '<input type="radio" id="zacctmgr_commission_type3" name="zacctmgr_commission_type" value="no_commission" ' . ( $current_commission_type == 'no_commission' ? 'checked="checked"' : '' ) . '/>';
	echo '<label for="zacctmgr_commission_type3">No Commission</label>';
	echo '</div>';
	echo '</div>';
	echo '</td>';
	echo '</tr>';
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	
	echo '<div class="zacctmgr_edit_commission_table" id="zacctmgr_edit_commission_table_manager">';
	echo '<table class="form-table" cellpadding="0" style="padding:0; border-spacing: 0">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Commission Rates</th>';
	echo '<th style="text-align: center;">Apply to</th>';
	echo '<th style="text-align: center;">Value</th>';
	echo '<th style="text-align: center;">Type</th>';
	echo '<th style="text-align: center;">Calculate By</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	echo '<tr>';
	echo '<td>New Orders</td>';
	echo '<td style="text-align: center;">' . $order1 . '</td>';
	echo '<td style="text-align: center;">' . $value1 . '</td>';
	echo '<td style="text-align: center;">' . $type1 . '</td>';
	echo '<td style="text-align: left;">' . $apply1 . '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td>Existing Orders</td>';
	echo '<td style="text-align: center;"></td>';
	echo '<td style="text-align: center;">' . $value2 . '</td>';
	echo '<td style="text-align: center;">' . $type2 . '</td>';
	echo '<td style="text-align: left;">' . $apply2 . '</td>';
	echo '</tr>';
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	
	echo '<p class="submit">';
	echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="Update User"/>';
	echo '</p>';
	echo '</form>';
	echo '</div>';
?>
    <h3 style="margin:1rem;">Audit Log</h3>
    <div class="zacctmgr_tab_content">
		<?php
			if ( ! class_exists( 'ZACCTMGR_Core_Audit_Manager_Commission' ) ) {
				require_once( ZACCTMGR_PLUGIN_DIR . 'helper/class-zacctmgr-core-audit-manager-commission.php' );
			}
			$manager_audit = new ZACCTMGR_Core_Audit_Manager_Commission( $manager_id );
			$manager_audit->print_overview();
		?>
    </div>
<?php
?>
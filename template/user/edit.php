<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	$zacctmgr_allowed_no = zacctmgr_get_allowed_no_manager();
	$users               = zacctmgr_get_em_users();
	$default_manager     = zacctmgr_get_default_manager();

	$manager_id = 0;
	if ( $user && is_object( $user ) ) {
		$manager_id = zacctmgr_get_manager_id( $user->ID );
	} else {
		$user = null;
	}

	$manager_data = $manager_id != 0 ? get_user_by( 'ID', $manager_id ) : null;

	global $wpdb;
	$table_name = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';

	$customer_commission_rate = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id AND customer_id=$user->ID AND customer_account_level=1 ORDER BY timestamp DESC LIMIT 1;" );
	$manager_commission_rate  = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id AND order_level=1 AND customer_account_level=0 ORDER BY timestamp DESC LIMIT 1;" );
	$customer_account_rate    = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id AND customer_id IS NULL AND customer_account_level=1 ORDER BY timestamp DESC LIMIT 1;" );

	if ( count( $customer_commission_rate ) == 0 ) {
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
		$own_custom_commission_rate = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id AND customer_account_level=1 AND customer_id IS NULL ORDER BY timestamp DESC LIMIT 1;" );
		if ( count( $own_custom_commission_rate ) != 0 && $own_custom_commission_rate[0]->timestamp > $manager_commission_rate[0]->timestamp ) {
			$commission_rate->customer_account_level = 1;
			$commission_rate->order_level            = 0;
		}
	} else {
		if ( $customer_commission_rate[0]->timestamp >= $manager_commission_rate[0]->timestamp ) {
			$commission_rate = $customer_commission_rate[0];
		} else {
			if ( count( $customer_account_rate ) != 0 && $customer_account_rate[0]->timestamp >= $manager_commission_rate[0]->timestamp ) {
				$commission_rate = $customer_account_rate[0];
			} else {
				$commission_rate = $manager_commission_rate[0];
			}
		}
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

	if ( $commission_rate->customer_account_level == 1 && $commission_rate->no_commission == 1 ) {
		$customer_commission_type = 'no_commission';
	} elseif ( $commission_rate->customer_account_level == 1 && $commission_rate->order_level == 1 ) {
		$customer_commission_type = 'order_level';
	} elseif ( $commission_rate->customer_account_level == 1 ) {
		$customer_commission_type = 'customer_account_level';
	} else {
		$customer_commission_type = 'order_level';
	}

	$new_fixed_type           = $commission_rate->new_order_commission_fixed_type;
	$existing_fixed_type      = $commission_rate->existing_order_commission_fixed_type;
	$new_percentage_type      = $commission_rate->new_order_commission_percentage_type;
	$existing_percentage_type = $commission_rate->existing_order_commission_percentage_type;
	$order_count              = $commission_rate->new_order_commission_limit;
	$new_value                = $commission_rate->new_order_commission_value;
	$existing_value           = $commission_rate->existing_order_commission_value;
?>
<table class="form-table">
    <tr class="form-field">
        <th scope="row"><label for="zacctmgr_select">Account Manager</label></th>
        <td>
            <span id="zacctmgr_current_manager" style="display: none;"><?php echo $manager_id; ?></span>
            <select name="zacctmgr_select" id="zacctmgr_select" class="zacctmgr_select_account_manager_user_page">
				<?php
					if ( $zacctmgr_allowed_no == 1 ) {
						echo '<option value="">No Account Manager</option>';
					}

					if ( $users && count( $users ) > 0 ) {
						foreach ( $users as $tuser ) {
							$user_id    = (int) $tuser->ID;
							$first_name = $tuser->first_name;
							$last_name  = $tuser->last_name;

							$name = '-';
							if ( $first_name != '' || $last_name != '' ) {
								$name = $first_name . ' ' . $last_name;
							}

							$selected = '';
							if ( $manager_id != 0 ) {
								if ( $user_id == $manager_id ) {
									$selected = 'selected="selected"';
								}
							} else {
								if ( $user_id == $default_manager && $zacctmgr_allowed_no == 0 ) {
									$selected = 'selected="selected"';
								}
							}
							?>
                            <option value="<?php echo $user_id; ?>" <?php echo $selected; ?>><?php echo $name; ?></option>
							<?php
						}
					}
				?>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row"><label class="zacctmgr_commission_calculation_user_page">Commission Calculations</label></th>
        <td class="zacctmgr_commission_calculation_user_page">
			<?php if ( $commission_rate->customer_account_level != 1 || zacctmgr_can_edit_customer_commission( $_GET['user_id'] ) == false ) { ?>
                <label><?php echo ucwords( str_replace( '_', ' ', $commission_type ) ); ?></label>
			<?php } else { ?>
                <div class="zacctmgr_edit_commission_flex_wrap">
                    <div class="zacctmgr_edit_commission_flex_item">
                        <input type="radio" id="zacctmgr_commission_type1" name="zacctmgr_commission_type"
                               value="order_level" <?php echo $customer_commission_type == 'order_level' ? 'checked="checked"' : ''; ?>/>
                        <label for="zacctmgr_commission_type1">Order Level</label>
                    </div>
                    <div class="zacctmgr_edit_commission_flex_item">
                        <input type="radio" id="zacctmgr_commission_type2" name="zacctmgr_commission_type"
                               value="customer_account_level" <?php echo $customer_commission_type == 'customer_account_level' ? 'checked="checked"' : ''; ?>/>
                        <label for="zacctmgr_commission_type2">Customer Account Level</label>
                    </div>
                    <div class="zacctmgr_edit_commission_flex_item">
                        <input type="radio" id="zacctmgr_commission_type3" name="zacctmgr_commission_type"
                               value="no_commission" <?php echo $customer_commission_type == 'no_commission' ? 'checked="checked"' : ''; ?>/>
                        <label for="zacctmgr_commission_type3">No Commission</label>
                    </div>
                </div>
			<?php } ?>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row"></th>
        <td class="zacctmgr_commission_calculation_user_page">
			<?php if ( $commission_rate->customer_account_level == 1 && $manager_data != null && zacctmgr_can_edit_customer_commission( $_GET['user_id'] ) == true ) { ?>
				<?php
				$order1 = '<input type="number" name="commission_order_count" value="' . $order_count . '"/>';

				$value1 = '<input type="text" name="commission_new_value" required value="' . $new_value . '"/>';
				$value2 = '<input type="text" name="commission_existing_value" required value="' . $existing_value . '"/>';

				if ( zacctmgr_can_edit_customer_commission( $_GET['user_id'] ) == true ) {


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

					$style_fixed      = 'style="display: block;"';
					$style_percentage = 'style="display: none;"';

					if ( $new_percentage_type == 1 ) {
						$style_fixed      = 'style="display: none;"';
						$style_percentage = 'style="display: block;"';
					}

					$apply1 = '';

					$apply1 .= '<div class="zacctmgr_commission_new_type_result" id="zacctmgr_commission_new_type_result_percentage" ' . $style_percentage . '>';
					$apply1 .= '<h4 style="margin-bottom: 5px;">Order Total</h4>';

					$zacctmgr_commission_new_exclude_options = $new_exclude;

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

					$style_fixed      = 'style="display: block;"';
					$style_percentage = 'style="display: none;"';

					if ( $existing_percentage_type == 1 ) {
						$style_fixed      = 'style="display: none;"';
						$style_percentage = 'style="display: block;"';
					}

					$apply2 = '';

					$apply2 .= '<div class="zacctmgr_commission_existing_type_result" id="zacctmgr_commission_existing_type_result_percentage" ' . $style_percentage . '>';
					$apply2 .= '<h4 style="margin-bottom: 5px;">Order Total</h4>';

					$zacctmgr_commission_existing_exclude_options = $existing_exclude;

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
				}
				?>
                <div class="zacctmgr_edit_customer_commission_table">
                    <table class="form-table" cellpadding="0" style="padding:0; border-spacing: 0">
                        <thead>
                        <tr>
                            <th>Commission Rates</th>
                            <th style="text-align: center;">Apply to</th>
                            <th style="text-align: center;">Value</th>
                            <th style="text-align: center;">Type</th>
                            <th style="text-align: center;">Calculate By</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>New Orders</td>
                            <td style="text-align: center;"><?php echo $order1; ?></td>
                            <td style="text-align: center;"><?php echo $value1; ?></td>
                            <td style="text-align: center;"><?php echo $type1; ?></td>
                            <td style="text-align: left;"><?php echo $apply1; ?></td>
                        </tr>
                        <tr>
                            <td>Existing Orders</td>
                            <td style="text-align: center;"></td>
                            <td style="text-align: center;"><?php echo $value2; ?></td>
                            <td style="text-align: center;"><?php echo $type2; ?></td>
                            <td style="text-align: left;"><?php echo $apply2; ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
			<?php } elseif ( ( $commission_type == 'order_level' && $manager_data != null ) || ( $commission_type == 'customer_account_level' && $manager_data != null && zacctmgr_can_edit_customer_commission( $_GET['user_id'] ) == false ) ) { ?>
                <div class="zacctmgr_edit_customer_commission_table">
                    <table class="form-table" cellpadding="0" style="padding:0; border-spacing: 0">
                        <thead>
                        <tr>
                            <th>Commission Rates</th>
                            <th style="text-align: center;">Apply to</th>
                            <th style="text-align: center;">Value</th>
                            <th style="text-align: center;">Type</th>
                            <th style="text-align: center;">Calculate By</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>New Orders</td>
                            <td style="text-align: center;">
								<?php echo $order_count; ?>
                            </td>
                            <td style="text-align: center;"><?php echo $new_value; ?></td>
                            <td style="text-align: center;"><?php echo $new_percentage_type == 1 ? 'Percentage' : 'Fixed'; ?></td>
                            <td style="text-align: left;">
								<?php
									if ( $new_percentage_type == 1 ) {
										if ( $new_exclude ) {
											foreach ( $new_exclude as $key ) {
												echo ZACCTMGR_EXCLUDE_OPTIONS[ $key ] . '<br/>';
											}
										} else {
											echo 'Order Total';
										}
									} else {
										echo 'Fixed Value Amount';
									}
								?>
                            </td>
                        </tr>
                        <tr>
                            <td>Existing Orders</td>
                            <td style="text-align: center;"></td>
                            <td style="text-align: center;"><?php echo $existing_value; ?></td>
                            <td style="text-align: center;"><?php echo $existing_percentage_type == 1 ? 'Percentage' : 'Fixed'; ?></td>
                            <td style="text-align: left;">
								<?php
									if ( $existing_percentage_type == 1 ) {
										if ( $existing_exclude ) {
											foreach ( $existing_exclude as $key ) {
												echo ZACCTMGR_EXCLUDE_OPTIONS[ $key ] . '<br/>';
											}
										} else {
											echo 'Order Total';
										}
									} else {
										echo 'Fixed Value Amount';
									}
								?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
			<?php } ?>
        </td>
    </tr>
</table>
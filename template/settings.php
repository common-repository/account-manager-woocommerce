<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	if ( $this->zacctmgr_allow_edit_settings() == false ) {
		echo '<div class="zacctmgr_not_allowed_wrap">';
		echo '<p>Sorry, you are not allowed to access this page!</p>';
		echo '</div>';
		exit;
	}

	$roles                    = zacctmgr_get_roles();
	$selected_roles           = zacctmgr_get_selected_roles();
	$users                    = zacctmgr_get_em_users();
	$allowed_users            = zacctmgr_get_allowed_edit_commission_users();
	$allowed_edit_other_users = zacctmgr_get_allowed_edit_others_commission_users();
	$allowed_edit_order_users = zacctmgr_get_allowed_edit_order_commission_users();

	$manager_id = zacctmgr_get_default_manager();

	$statuses         = wc_get_order_statuses();
	$allowed_statuses = zacctmgr_get_allowed_wc_statuses();

	$zacctmgr_refund_commission_setting                 = zacctmgr_refund_commission_setting();
	$zacctmgr_allowed_no                                = zacctmgr_get_allowed_no_manager();
	$zacctmgr_hide_settings_in_menu                     = zacctmgr_get_hide_settings_in_menu();
	$zacctmgr_order_recalculate_commission              = zacctmgr_order_recalculate_commission();
	$zacctmgr_user_access_settings                      = zacctmgr_get_user_access_settings();
	$zacctmgr_user_allow_edit_commission_setting        = zacctmgr_get_user_allow_edit_commission_setting();
	$zacctmgr_user_allow_edit_others_commission_setting = zacctmgr_get_user_allow_edit_others_commission_setting();
	$zacctmgr_user_allow_edit_order_commission_setting  = zacctmgr_get_user_allow_edit_order_commission_setting();

	echo '<div class="wrap">';
	echo '<h1>Settings</h1>';
	echo '<hr class="wp-header-end"/>';

	echo '<h2 class="screen-reader-text">Account Manager Settings</h2>';

	echo '<div id="zacctmgr_factory_reset_wrap">';
	echo '<form style="margin-top:1rem;" method="post" action="' . admin_url( 'admin-post.php' ) . '" id="zacctmgr_factory_reset_form">';
	echo '<input type="hidden" name="action" value="zacctmgr_factory_reset"/>';
	echo wp_nonce_field( 'zacctmgr_factory_reset', 'zacctmgr_factory_reset_nonce' );
	echo '<label style="margin-top: 2rem;"><b>Reset your plugin data</b></label>';
	echo '<p style="margin: 1rem 0 10px 0;">This action will reset your plugin data. In order to make a correct reset, after clicking the Reset button, you will need to Deactivate and Reactivate the plugin Manually. </p>';
	echo '<input type="submit" class="button button-primary" name="factory_reset" id="factory_reset" value="Reset">';
	echo '</div>';
	echo '</form>';


	echo '<form method="post" action="' . admin_url( 'admin-post.php' ) . '" id="zacctmgr_edit_settings_form">';
	echo '<input type="hidden" name="action" value="zacctmgr_edit_settings"/>';
	echo '<input type="hidden" id="current_default_manager" value="' . $manager_id . '"/>';

	echo wp_nonce_field( 'zacctmgr_edit_settings' );

	echo '<div id="zacctmgr_edit_settings_wrap">';
	echo '<label><b>Users with access to Account Manager Settings</b></label>';
	echo '<div style="margin-top: 10px; margin-bottom: 20px; margin-left:10px;">';
	echo '<div style="margin-bottom: 20px;">';
	echo '<input type="radio" id="zacctmgr_user_access_settings_administrators" name="zacctmgr_user_access_settings" value="administrators"' . checked( 'administrators', $zacctmgr_user_access_settings, false ) . '>Administrators';
	echo '<span><br><i>All Administrators can manage settings</i><br></span>';
	echo '</div>';
	echo '<div style="margin-bottom: 20px;">';
	echo '<input type="radio" id="zacctmgr_user_access_settings_manage_options" name="zacctmgr_user_access_settings" value="manage_options"' . checked( 'manage_options', $zacctmgr_user_access_settings, false ) . '>Anyone with "manage_options" capability';
	echo '<span><br><i>By default only Administrators have this capability.</i><br></span>';
	echo '</div>';
	echo '<div style="margin-bottom: 20px;">';
	echo '<input type="radio" id="zacctmgr_user_access_settings_current_user" name="zacctmgr_user_access_settings" value="' . get_current_user_id() . '"' . checked( get_current_user_id(), $zacctmgr_user_access_settings, false ) . '>Only the current user';
	echo '<span><br><i>Login: ' . get_current_user() . ', user ID: ' . get_current_user_id() . '</i><br></span>';
	echo '</div>';
	echo '<div style="margin-top: 10px; margin-bottom: 20px;">';
	$extra = $zacctmgr_hide_settings_in_menu == 1 ? 'checked="checked"' : '';
	echo '<input type="checkbox" id="zacctmgr_hide_settings_in_menu" name="zacctmgr_hide_settings_in_menu" value="1" ' . $extra . '/>';
	echo '<label for="zacctmgr_hide_settings_in_menu" style="display: inline-block; margin-top: -5px;">Hide the "Settings" entry on the sub menu of Account Manager from other users</label>';
	echo '</div>';
	echo '</div>';

	echo '<label><b>Default Account Manager for New Customers</b></label>';
	echo '<select id="zacctmgr_default" name="zacctmgr_default">';
	echo '<option value="">Select...</option>';
	if ( $users ) {
		foreach ( $users as $user ) {
			$extra = $user->ID == $manager_id ? 'selected="selected"' : '';
			echo '<option value="' . $user->ID . '" ' . $extra . '>' . $user->first_name . ' ' . $user->last_name . '</option>';
		}
	}
	echo '</select>';

	echo '<label><b>Customer Accounts Allow No Account Manager Assignment</b></label>';
	echo '<div style="margin-top: 10px; margin-bottom: 20px;">';
	$extra = $zacctmgr_allowed_no == 1 ? 'checked="checked"' : '';
	echo '<input type="checkbox" id="zacctmgr_allowed_no" name="zacctmgr_allowed_no" value="1" ' . $extra . '/>';
	echo '<label for="zacctmgr_allowed_no" style="display: inline-block; margin-top: -5px;">Enabled</label>';
	echo '</div>';

	echo '<label style="margin-bottom: 10px;"><b>Allowed Woocommerce Statuses</b><br/>for Order Commission Calculations</label>';
	echo '<select id="zacctmgr_allowed_woo_status_list" name="zacctmgr_allowed_woo_status[]" multiple="multiple">';
	if ( $statuses ) {
		foreach ( $statuses as $key => $label ) {
			$extra = '';
			if ( in_array( $key, $allowed_statuses ) ) {
				$extra = 'selected="selected"';
			}

			echo '<option value="' . $key . '" ' . $extra . '>' . $label . '</option>';
		}
	}
	echo '</select>';

	echo '<label style="margin-top: 20px;"><b>Select Role Types for Account Manager Functionality</b></label>';
	echo '<div id="zacctmgr_roles_wrap">';
	foreach ( $roles as $key => $value ) {
		$extra = in_array( $key, $selected_roles ) ? 'checked="checked"' : '';

		echo '<div class="zacctmgr_edit_settings_flex_wrap">';
		echo '<input type="checkbox" id="zacctmgr_role_' . $key . '" class="zacctmgr_roles_selection" name="zacctmgr_roles[]" value="' . $key . '" ' . $extra . '/>';
		echo '<label for="zacctmgr_role_' . $key . '">' . $value['name'] . '</label>';
		echo '</div>';
	}
	echo '</div>';

	echo '<label style="margin: 10px 0 10px 0;"><b>Users Allowed to Edit Order Assignments and Commission Values</b></label>';
	echo '<div style=" margin-top: 10px; margin-bottom: 20px; margin-left:10px;">';
	echo '<div style="display:inline; margin-right: 10px;">';
	echo '<input type="radio" id="zacctmgr_user_allow_edit_order_commission_setting_administrators" name="zacctmgr_user_allow_edit_order_commission_setting" value="administrators"' . checked( 'administrators', $zacctmgr_user_allow_edit_order_commission_setting, false ) . '>All Account Managers';
	echo '</div>';
	echo '<div style="display:inline; margin-right: 10px;">';
	echo '<input type="radio" id="zacctmgr_user_allow_edit_order_commission_setting_users" name="zacctmgr_user_allow_edit_order_commission_setting" value="users"' . checked( 'users', $zacctmgr_user_allow_edit_order_commission_setting, false ) . '>Select Account Managers';
	echo '</div>';
	echo '</div>';

	echo '<div id="zacctmgr_allowed_users_to_edit_order_commission_list_container">';
	echo '<select id="zacctmgr_users_edit_order_commission_list" name="zacctmgr_allowed_edit_order_commission_users[]" multiple="multiple">';
	if ( $users ) {
		foreach ( $users as $user ) {
			$extra = '';
			if ( in_array( $user->ID, $allowed_edit_order_users ) ) {
				$extra = 'selected="selected"';
			}
			echo '<option value="' . $user->ID . '" ' . $extra . '>' . $user->display_name . '</option>';
		}
	}
	echo '</select>';
	echo '</div>';

	echo '<label style="margin: 10px 0 10px 0;"><b>Users Allowed to Edit Commissions</b></label>';
	echo '<div style=" margin-top: 10px; margin-bottom: 20px; margin-left:10px;">';
	echo '<div style="display:inline; margin-right: 10px;">';
	echo '<input type="radio" id="zacctmgr_user_allow_edit_commission_setting_administrators" name="zacctmgr_user_allow_edit_commission_setting" value="administrators"' . checked( 'administrators', $zacctmgr_user_allow_edit_commission_setting, false ) . '>All Account Managers';
	echo '</div>';
	echo '<div style="display:inline; margin-right: 10px;">';
	echo '<input type="radio" id="zacctmgr_user_allow_edit_commission_setting_users" name="zacctmgr_user_allow_edit_commission_setting" value="users"' . checked( 'users', $zacctmgr_user_allow_edit_commission_setting, false ) . '>Select Account Managers';
	echo '</div>';
	echo '</div>';

	echo '<div id="zacctmgr_allowed_users_to_edit_commission_list_container">';
	echo '<select id="zacctmgr_allowed_users_to_edit_commission_list" name="zacctmgr_allowed_edit_commission_users[]" multiple="multiple">';
	if ( $users ) {
		foreach ( $users as $user ) {
			$extra = '';
			if ( in_array( $user->ID, $allowed_users ) ) {
				$extra = 'selected="selected"';
			}
			echo '<option value="' . $user->ID . '" ' . $extra . '>' . $user->display_name . '</option>';
		}
	}
	echo '</select>';
	echo '</div>';


	echo '<label style="margin: 10px 0 10px 0;"><b>Users Allowed to Edit Customers</b></label>';
	echo '<div style=" margin-top: 10px; margin-bottom: 20px; margin-left:10px;">';
	echo '<div style="display:inline; margin-right: 10px;">';
	echo '<input type="radio" id="zacctmgr_user_allow_edit_others_commission_setting_administrators" name="zacctmgr_user_allow_edit_others_commission_setting" value="administrators"' . checked( 'administrators', $zacctmgr_user_allow_edit_others_commission_setting, false ) . '>All Account Managers';
	echo '</div>';
	echo '<div style="display:inline; margin-right: 10px;">';
	echo '<input type="radio" id="zacctmgr_user_allow_edit_others_commission_setting_users" name="zacctmgr_user_allow_edit_others_commission_setting" value="users"' . checked( 'users', $zacctmgr_user_allow_edit_others_commission_setting, false ) . '>Select Account Managers';
	echo '</div>';
	echo '</div>';
	echo '<div id="zacctmgr_allowed_users_to_edit_others_commission_list_container">';
	echo '<select id="zacctmgr_allowed_users_to_edit_others_commission_list" name="zacctmgr_allowed_edit_others_commission_users[]" multiple="multiple">';
	if ( $users ) {
		foreach ( $users as $user ) {
			$extra = '';
			if ( in_array( $user->ID, $allowed_edit_other_users ) ) {
				$extra = 'selected="selected"';
			}
			echo '<option value="' . $user->ID . '" ' . $extra . '>' . $user->display_name . '</option>';
		}
	}
	echo '</select>';
	echo '</div>';

	echo '<label style="margin: 10px 0 10px 0;"><b>Commission action on refunded orders</b></label>';
	echo '<div style=" margin-top: 10px; margin-bottom: 20px; margin-left:10px;">';
	echo '<div style="display:block; margin-bottom: 10px;">';
	echo '<input type="radio" id="zacctmgr_refund_commission_setting_no_change" name="zacctmgr_refund_commission_setting" value="no_change"' . checked( 'no_change', $zacctmgr_refund_commission_setting, false ) . '>No change to commission value on the order';
	echo '</div>';
	echo '<div style="display:block; margin-right: 10px;">';
	echo '<input type="radio" id="zacctmgr_refund_commission_setting_zero" name="zacctmgr_refund_commission_setting" value="zero"' . checked( 'zero', $zacctmgr_refund_commission_setting, false ) . '>Automatically change commission value to 0';
	echo '</div>';
	echo '</div>';

	echo '<label style="margin: 10px 0 10px 0;"><b>Commission action on updating orders</b></label>';
	echo '<div style="margin-top: 10px; margin-bottom: 10px; margin-left: 10px;">';
	$extra = $zacctmgr_order_recalculate_commission == 'no' ? 'checked="checked"' : '';
	echo '<input type="radio" id="zacctmgr_order_recalculate_commission_no" name="zacctmgr_order_recalculate_commission" value="no" ' . $extra . '/>';
	echo '<label for="zacctmgr_order_recalculate_commission_no" style="display: inline-block; margin-top: -5px;">Modified order values don’t recalculate commission</label>';
	echo '</div>';
	echo '<div style="margin-top: 10px; margin-bottom: 10px; margin-left: 10px;">';
	$extra = $zacctmgr_order_recalculate_commission == 'yes' ? 'checked="checked"' : '';
	echo '<input type="radio" id="zacctmgr_order_recalculate_commission_yes" name="zacctmgr_order_recalculate_commission" value="yes" ' . $extra . '/>';
	echo '<label for="zacctmgr_order_recalculate_commission_yes" style="display: inline-block; margin-top: -5px;">Modified order values recalculate commission.</label>';
	echo '</div>';
	echo '<div style="margin-top: 10px; margin-bottom: 10px; margin-left: 10px;">';
	$extra = $zacctmgr_order_recalculate_commission == 'override' ? 'checked="checked"' : '';
	echo '<input type="radio" id="zacctmgr_order_recalculate_commission_override" name="zacctmgr_order_recalculate_commission" value="override" ' . $extra . '/>';
	echo '<label for="zacctmgr_order_recalculate_commission_override" style="display: inline-block; margin-top: -5px;">Modified order values recalculate commission and override any manual commission edits</label>';
	echo '</div>';


	$v2_install_date = get_option( 'zacctmgr_v2_install_date' );
	echo '<label style="margin: 2rem 0 10px 0;">Account Manager for WooCommerce Version 2 date of installation: ' . $v2_install_date . '</label>';
	echo '<label style="margin: 10px 0 10px 0;">Reports prior to this date are not supported, Thank you for understanding!</label>';

	echo '<p class="submit">';
	echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="Update Settings"/>';
	echo '</p>';
	echo '</div>';
	echo '</form>';
	echo '</div>';
?>
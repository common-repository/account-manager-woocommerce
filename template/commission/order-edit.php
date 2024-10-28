<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	if ( zacctmgr_allow_edit_order() == false ) {
		$o = '<div class="zacctmgr_not_allowed_wrap">';
		$o .= '<p>Sorry, you are not allowed to access this page!</p>';
		$o .= '</div>';
		wp_die( $o, 403 );
	}
	global $wpdb;
	$table_name_commission = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';

	$order              = wc_get_order( $_GET['edit'] );
	$account_manager    = get_post_meta( $order->get_id(), '_account_manager', true );
	$current_commission = (float) str_replace( ',', '', get_post_meta( $order->get_id(), '_commission', true ) );
	$customer           = $order->get_user();
	$order_timestamp    = $order->get_date_created()->date( 'Y-m-d H:i:s' );
	$user_query         = new WP_User_Query( array(
		'search'         => $account_manager,
		'search_columns' => array( 'display_name' ),
		'number'         => 1
	) );

	$results          = $user_query->get_results();
	$manager          = $results[0];
	$allowed_statuses = zacctmgr_get_allowed_wc_statuses();


	$manager_commission_rate_query = $wpdb->get_results( "SELECT * FROM $table_name_commission WHERE manager_id=$manager->ID AND customer_id IS NULL AND timestamp <= '$order_timestamp' ORDER BY timestamp DESC LIMIT 1;" );

	if ( count( $manager_commission_rate_query ) != 0 ) {
		if ( $manager_commission_rate_query[0]->no_commission == 1 ) {
			return 0;
		}
		if ( $manager_commission_rate_query[0]->order_level == 1 ) {
			$commission_rate = $manager_commission_rate_query[0];
		}
		if ( $manager_commission_rate_query[0]->customer_account_level == 1 ) {
			$customer_commission_rate_query = $wpdb->get_results( "SELECT * FROM $table_name_commission WHERE manager_id=$manager->ID AND customer_id=$customer->ID AND customer_account_level=1  AND timestamp <= '$order_timestamp' ORDER BY timestamp DESC LIMIT 1;" );
			if ( count( $customer_commission_rate_query ) != 0 ) {
				if ( $customer_commission_rate_query[0]->order_level == 0 ) {
					$commission_rate = $customer_commission_rate_query[0];
				} else {
					$manager_customer_account_level_commission_rate_query = $wpdb->get_results( "SELECT * FROM $table_name_commission WHERE manager_id=$manager->ID AND customer_id IS NULL AND customer_account_level=1  AND timestamp <= '$order_timestamp' ORDER BY timestamp DESC LIMIT 1;" );
					if ( count( $manager_customer_account_level_commission_rate_query ) != 0 ) {
						$commission_rate = $manager_customer_account_level_commission_rate_query[0];
					}
				}
			} else {
				$manager_customer_account_level_commission_rate_query = $wpdb->get_results( "SELECT * FROM $table_name_commission WHERE manager_id=$manager->ID AND customer_id IS NULL AND customer_account_level=1  AND timestamp <= '$order_timestamp' ORDER BY timestamp DESC LIMIT 1;" );
				if ( count( $manager_customer_account_level_commission_rate_query ) != 0 ) {
					$commission_rate = $manager_customer_account_level_commission_rate_query[0];
				}
			}
		}
	} else {
		$new_commission = $existing_commission = 0;
	}

	if ( $commission_rate->no_commission == 1 ) {
		$new_commission = $existing_commission = 0;
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

		if ( $commission_rate->new_order_commission_limit != 0 ) {
			$isNew = false;
			if ( count( $orders ) > 0 ) {
				foreach ( $orders as $item ) {
					if ( $order->get_id() == $item->ID ) {
						$isNew = true;
						break;
					}
				}
			}
		} else {
			$isNew = false;
		}

		if ( $isNew ) {
			$order_new_commission_type = get_post_meta( $order->get_id(), '_new_commission_type', true );
			if ( $order_new_commission_type != "" ) {
				if ( $order_new_commission_type == 1 ) {

					$new_commission      = $current_commission;
					$existing_commission = 0.00;
				} else {

					$existing_commission = $current_commission;
					$new_commission      = 0.00;
				}
			} else {

				$new_commission      = $current_commission;
				$existing_commission = 0.00;
			}
		} else {
			$order_new_commission_type = get_post_meta( $order->get_id(), '_new_commission_type', true );
			if ( $order_new_commission_type != "" ) {
				if ( $order_new_commission_type == 1 ) {

					$new_commission      = $current_commission;
					$existing_commission = 0.00;
				} else {

					$existing_commission = $current_commission;
					$new_commission      = 0.00;
				}
			} else {
				$existing_commission = $current_commission;
				$new_commission      = 0.00;
			}
		}
	}

	$current_new_commission      = $new_commission;
	$current_existing_commission = $existing_commission;
	$account_managers            = zacctmgr_get_em_users();

?>
<div class="wrap">
    <h1><b>Edit Order Commission </b><a href="admin.php?page=zacctmgr_commission&tab=orders"
                                        class="zacctmgr_goback_btn"><span class="dashicons dashicons-undo"></span></a>
    </h1>
    <hr class="wp-header-end"/>
    <h2 class="screen-reader-text">Edit Order Commission</h2>

    <div class="zacctmgr_tab_content">
        <div class="zacctmgr_row zacctmgr_disabled_hpadding">
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>"
                  id="zacctmgr_edit_order_commission" style="width: 100%;">
                <input type="hidden" id="zacctmgr_order_id" name="zacctmgr_order_id"
                       value="<?php echo $order->get_id(); ?>">
                <input type="hidden" name="zacctmgr_current_new_commission"
                       value="<?php echo $current_new_commission; ?>">
                <input type="hidden" name="zacctmgr_current_existing_commission"
                       value="<?php echo $current_existing_commission; ?>">
                <div class="zacctmgr_col_12" style="border-left: 2px solid #efefef; wid">
                    <p id="zacctmgr_edit_order_commisson_error"
                       style="color:red; font-weight: bold; text-align: center; font-size: 15px;"></p>
                    <div class="zacctmgr_row">
                        <input type="hidden" name="action" value="zacctmgr_edit_order_commission"/>
						<?php echo wp_nonce_field( 'zacctmgr_edit_order_commission' ); ?>
                        <div class="zacctmgr_col_3" style="text-align: center; border: solid #eee 1px; margin:1rem;">
                            <label for="zacctmgr_order_account_manager" style="font-size: 16px; font-weight: 300;">
                                <b>Account Manager</b></label><br>
                            <select id="zacctmgr_order_account_manager" name="zacctmgr_order_account_manager"
                                    style="margin: 1rem;">
                                <option value="<?php echo $account_manager; ?>"><?php echo $account_manager; ?></option>
								<?php
									if ( $account_managers ) {
										foreach ( $account_managers as $user ) {
											if ( $user->display_name != $account_manager ) {
												$extra = $user->ID == $manager_id ? 'selected="selected"' : '';
												echo '<option value="' . $user->display_name . '" ' . $extra . '>' . $user->display_name . '</option>';
											}
										}
									}
								?>
                            </select>
                        </div>
                        <div class="zacctmgr_col_3" style="text-align: center; border: solid #eee 1px; margin:1rem;">
                            <label for="zacctmgr_order_commission_new" style="font-size: 16px; font-weight: 300;"><b>Commission
                                    Value NEW</b></label><br>
                            <input type="number" step="0.01" min="0" max="1000000"
                                   value="<?php echo $new_commission; ?>"
                                   id="zacctmgr_order_commission_new"
                                   name="zacctmgr_order_commission_new"
                                   style="margin: 1rem;">
                        </div>
                        <div class="zacctmgr_col_3" style="text-align: center; border: solid #eee 1px; margin:1rem;">
                            <label for="zacctmgr_order_commission_existing"
                                   style="font-size: 16px; font-weight: 300;"><b>Commission Value
                                    EXISTING</b></label><br>
                            <input type="number" step="0.01" min="0" max="1000000"
                                   value="<?php echo $existing_commission; ?>"
                                   id="zacctmgr_order_commission_existing"
                                   name="zacctmgr_order_commission_existing"
                                   style="margin: 1rem;">
                        </div>
                        <div style="text-align: center; width: 12%; flex: 0 0 12%; box-sizing: border-box; padding: 15px;">
                            <div class="zacctmgr_row">
                                <div class="zacctmgr_col_12">
                                    <input id="zacctmgr_manual_calculate_button" type="submit" value="Update Manually"
                                           class="button button-primary"
                                           style="margin-bottom: 1rem;">
                                    <a id="zacctmgr_recalculate_button"
                                       href="<?php echo admin_url( 'admin-post.php' ) . '?action=zacctmgr_recalculate_order_commission&order_id=' . $order->get_id() . '&account_manager=' . $account_manager; ?>"
                                       class="button">Recalculate by Account Manager</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="zacctmgr_tab_content">
		<?php $meta_box_order_data = new WC_Meta_Box_Order_Data();
			$meta_box_order_data->init_address_fields();
		?>

        <style type="text/css">
            #post-body-content, #titlediv {
                display: none
            }
        </style>
        <div class="panel-wrap woocommerce">
            <div id="order_data" class="panel woocommerce-order-data">
                <h2 class="woocommerce-order-data__heading">
					<?php

						/* translators: 1: order type 2: order number */
						printf(
							esc_html__( '#%1$s details', 'woocommerce' ),
							esc_html( $order->get_order_number() )
						);

					?>
                </h2>
                <p class="woocommerce-order-data__meta order_number">
					<?php
						$payment_method = $order->get_payment_method();

						$meta_list = array();

						if ( $payment_method ) {
							/* translators: %s: payment method */
							$payment_method_string = sprintf(
								__( 'Payment via %s', 'woocommerce' ),
								esc_html( $payment_method )
							);

							$meta_list[] = $payment_method_string;
						}

						if ( $order->get_date_paid() ) {
							/* translators: 1: date 2: time */
							$meta_list[] = sprintf(
								__( 'Paid on %1$s @ %2$s', 'woocommerce' ),
								wc_format_datetime( $order->get_date_paid() ),
								wc_format_datetime( $order->get_date_paid(), get_option( 'time_format' ) )
							);
						}

						if ( $ip_address = $order->get_customer_ip_address() ) {
							/* translators: %s: IP address */
							$meta_list[] = sprintf(
								__( 'Customer IP: %s', 'woocommerce' ),
								'<span class="woocommerce-Order-customerIP">' . esc_html( $ip_address ) . '</span>'
							);
						}

						echo wp_kses_post( implode( '. ', $meta_list ) );

					?>
                </p>
                <div class="order_data_column_container">
                    <div class="order_data_column">
                        <h3><?php esc_html_e( 'General', 'woocommerce' ); ?></h3>

                        <p class="form-field form-field-wide">
                            <label for="order_date"><?php _e( 'Date created:', 'woocommerce' ); ?></label>
                            <input type="text" style="color:#000;" disabled class="date-picker" name="order_date"
                                   maxlength="10"
                                   value="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( $order->get_date_created() ) ) ); ?>"
                                   pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>"/>@
                            &lrm;
                            <input type="number" style="color:#000;" disabled class="hour"
                                   placeholder="<?php esc_attr_e( 'h', 'woocommerce' ); ?>"
                                   name="order_date_hour" min="0" max="23" step="1"
                                   value="<?php echo esc_attr( date_i18n( 'H', strtotime( $order->get_date_created() ) ) ); ?>"
                                   pattern="([01]?[0-9]{1}|2[0-3]{1})"/>:
                            <input type="number" style="color:#000;" disabled class="minute"
                                   placeholder="<?php esc_attr_e( 'm', 'woocommerce' ); ?>"
                                   name="order_date_minute" min="0" max="59" step="1"
                                   value="<?php echo esc_attr( date_i18n( 'i', strtotime( $order->get_date_created() ) ) ); ?>"
                                   pattern="[0-5]{1}[0-9]{1}"/>
                            <input type="hidden" name="order_date_second"
                                   value="<?php echo esc_attr( date_i18n( 's', strtotime( $order->get_date_created() ) ) ); ?>"/>
                        </p>

                        <p class="form-field form-field-wide wc-order-status">
                            <label for="order_status">
								<?php
									_e( 'Status:', 'woocommerce' );
								?>
                            </label>
                            <input type="text" style="color:#000;" name="order_status" id="order_status"
                                   value="<?php echo ucfirst( esc_attr( $order->get_status() ) ); ?>" disabled/>
                        </p>

                        <p class="form-field form-field-wide wc-customer-user">
                            <!--email_off--> <!-- Disable CloudFlare email obfuscation -->
                            <label for="customer_user">
								<?php
									_e( 'Customer:', 'woocommerce' );
									if ( $order->get_user_id( 'edit' ) ) {
										$args = array(
											'post_status'    => 'all',
											'post_type'      => 'shop_order',
											'_customer_user' => $order->get_user_id( 'edit' ),
										);
									}
								?>
                            </label>
							<?php
								$user_string = '';
								$user_id     = '';
								if ( $order->get_user_id() ) {
									$user_id = absint( $order->get_user_id() );
									$user    = get_user_by( 'id', $user_id );
									/* translators: 1: user display name 2: user ID 3: user email */
									$user_string = sprintf(
										esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ),
										$user->display_name,
										absint( $user->ID ),
										$user->user_email
									);
								} else {
									$user_string = sprintf(
										esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ),
										'Guest',
										'',
										$order->get_billing_email()
									);
								}
							?>
                            <input id="customer_user" name="customer_user" type="text" disabled style="color:#000;"
                                   value="<?php echo esc_attr( $user_string ); ?>"/>
                            <!--/email_off-->
                        </p>
                    </div>
                    <div class="order_data_column">
                        <h3>
							<?php esc_html_e( 'Billing', 'woocommerce' ); ?>
                        </h3>
                        <div class="address">
							<?php

								// Display values.
								if ( $order->get_formatted_billing_address() ) {
									echo '<p>' . wp_kses( $order->get_formatted_billing_address(), array( 'br' => array() ) ) . '</p>';
								} else {
									echo '<p class="none_set"><strong>' . __( 'Address:', 'woocommerce' ) . '</strong> ' . __( 'No billing address set.', 'woocommerce' ) . '</p>';
								}
							?>
                        </div>
                    </div>
                    <div class="order_data_column">
                        <h3>
							<?php esc_html_e( 'Shipping', 'woocommerce' ); ?>
                        </h3>
                        <div class="address">
							<?php

								// Display values.
								if ( $order->get_formatted_shipping_address() ) {
									echo '<p>' . wp_kses( $order->get_formatted_shipping_address(), array( 'br' => array() ) ) . '</p>';
								} else {
									echo '<p class="none_set"><strong>' . __( 'Address:', 'woocommerce' ) . '</strong> ' . __( 'No shipping address set.', 'woocommerce' ) . '</p>';
								}
							?>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>

    <div id="woocommerce-order-items" class="postbox">
        <div class="inside">
			<?php
				global $wpdb;

				$payment_gateway     = wc_get_payment_gateway_by_order( $order );
				$line_items          = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
				$discounts           = $order->get_items( 'discount' );
				$line_items_fee      = $order->get_items( 'fee' );
				$line_items_shipping = $order->get_items( 'shipping' );

				if ( wc_tax_enabled() ) {
					$order_taxes      = $order->get_taxes();
					$tax_classes      = WC_Tax::get_tax_classes();
					$classes_options  = wc_get_product_tax_class_options();
					$show_tax_columns = count( $order_taxes ) === 1;
				}
			?>
            <div class="woocommerce_order_items_wrapper wc-order-items-editable">
                <table cellpadding="0" cellspacing="0" class="woocommerce_order_items">
                    <thead>
                    <tr>
                        <th class="item sortable" colspan="2"
                            data-sort="string-ins"><?php esc_html_e( 'Item', 'woocommerce' ); ?></th>
                        <th class="item_cost sortable"
                            data-sort="float"><?php esc_html_e( 'Cost', 'woocommerce' ); ?></th>
                        <th class="quantity sortable"
                            data-sort="int"><?php esc_html_e( 'Qty', 'woocommerce' ); ?></th>
                        <th class="line_cost sortable"
                            data-sort="float"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
						<?php
							if ( ! empty( $order_taxes ) ) :
								foreach ( $order_taxes as $tax_id => $tax_item ) :
									$tax_class = wc_get_tax_class_by_tax_id( $tax_item['rate_id'] );
									$tax_class_name = isset( $classes_options[ $tax_class ] ) ? $classes_options[ $tax_class ] : __( 'Tax', 'woocommerce' );
									$column_label = ! empty( $tax_item['label'] ) ? $tax_item['label'] : __( 'Tax', 'woocommerce' );
									/* translators: %1$s: tax item name %2$s: tax class name  */
									$column_tip = sprintf( esc_html__( '%1$s (%2$s)', 'woocommerce' ), $tax_item['name'], $tax_class_name );
									?>
                                    <th class="line_tax tips" data-tip="<?php echo esc_attr( $column_tip ); ?>">
										<?php echo esc_attr( $column_label ); ?>
                                        <input type="hidden" class="order-tax-id"
                                               name="order_taxes[<?php echo esc_attr( $tax_id ); ?>]"
                                               value="<?php echo esc_attr( $tax_item['rate_id'] ); ?>">
                                        <a class="delete-order-tax" href="#"
                                           data-rate_id="<?php echo esc_attr( $tax_id ); ?>"></a>
                                    </th>
								<?php
								endforeach;
							endif;
						?>
                        <th class="wc-order-edit-line-item" width="1%">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody id="order_line_items">
					<?php
						foreach ( $line_items as $item_id => $item ) {
							$product      = $item->get_product();
							$product_link = $product ? admin_url( 'post.php?post=' . $item->get_product_id() . '&action=edit' ) : '';
							$thumbnail    = $product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item_id, $item ) : '';
							$row_class    = apply_filters( 'woocommerce_admin_html_order_item_class', ! empty( $class ) ? $class : '', $item, $order );
							?>
                            <tr class="item <?php echo esc_attr( $row_class ); ?>"
                                data-order_item_id="<?php echo esc_attr( $item_id ); ?>">
                                <td class="thumb">
									<?php echo '<div class="wc-order-item-thumbnail">' . wp_kses_post( $thumbnail ) . '</div>'; ?>
                                </td>
                                <td class="name" data-sort-value="<?php echo esc_attr( $item->get_name() ); ?>">
									<?php
										echo $product_link ? '<a href="' . esc_url( $product_link ) . '" class="wc-order-item-name">' . wp_kses_post( $item->get_name() ) . '</a>' : '<div class="wc-order-item-name">' . wp_kses_post( $item->get_name() ) . '</div>';

										if ( $product && $product->get_sku() ) {
											echo '<div class="wc-order-item-sku"><strong>' . esc_html__( 'SKU:', 'woocommerce' ) . '</strong> ' . esc_html( $product->get_sku() ) . '</div>';
										}

										if ( $item->get_variation_id() ) {
											echo '<div class="wc-order-item-variation"><strong>' . esc_html__( 'Variation ID:', 'woocommerce' ) . '</strong> ';
											if ( 'product_variation' === get_post_type( $item->get_variation_id() ) ) {
												echo esc_html( $item->get_variation_id() );
											} else {
												/* translators: %s: variation id */
												printf( esc_html__( '%s (No longer exists)', 'woocommerce' ), $item->get_variation_id() );
											}
											echo '</div>';
										}
									?>
                                    <input type="hidden" class="order_item_id" name="order_item_id[]"
                                           value="<?php echo esc_attr( $item_id ); ?>"/>
                                    <input type="hidden"
                                           name="order_item_tax_class[<?php echo absint( $item_id ); ?>]"
                                           value="<?php echo esc_attr( $item->get_tax_class() ); ?>"/>

									<?php
										$hidden_order_itemmeta = apply_filters(
											'woocommerce_hidden_order_itemmeta', array(
												'_qty',
												'_tax_class',
												'_product_id',
												'_variation_id',
												'_line_subtotal',
												'_line_subtotal_tax',
												'_line_total',
												'_line_tax',
												'method_id',
												'cost',
												'_reduced_stock',
											)
										);
									?>
                                    <div class="view">
										<?php if ( $meta_data = $item->get_formatted_meta_data( '' ) ) : ?>
                                            <table cellspacing="0" class="display_meta">
												<?php
													foreach ( $meta_data as $meta_id => $meta ) :
														if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
															continue;
														}
														?>
                                                        <tr>
                                                            <th><?php echo wp_kses_post( $meta->display_key ); ?>:
                                                            </th>
                                                            <td><?php echo wp_kses_post( force_balance_tags( $meta->display_value ) ); ?></td>
                                                        </tr>
													<?php endforeach; ?>
                                            </table>
										<?php endif; ?>
                                    </div>
                                    <div class="edit" style="display: none;">
                                        <table class="meta" cellspacing="0">
                                            <tbody class="meta_items">
											<?php if ( $meta_data = $item->get_formatted_meta_data( '' ) ) : ?>
												<?php
												foreach ( $meta_data as $meta_id => $meta ) :
													if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
														continue;
													}
													?>
                                                    <tr data-meta_id="<?php echo esc_attr( $meta_id ); ?>">
                                                        <td>
                                                            <input type="text" maxlength="255"
                                                                   placeholder="<?php esc_attr_e( 'Name (required)', 'woocommerce' ); ?>"
                                                                   name="meta_key[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $meta_id ); ?>]"
                                                                   value="<?php echo esc_attr( $meta->key ); ?>"/>
                                                            <textarea
                                                                    placeholder="<?php esc_attr_e( 'Value (required)', 'woocommerce' ); ?>"
                                                                    name="meta_value[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $meta_id ); ?>]"><?php echo esc_textarea( rawurldecode( $meta->value ) ); ?></textarea>
                                                        </td>
                                                        <td width="1%">
                                                            <button class="remove_order_item_meta button">&times;
                                                            </button>
                                                        </td>
                                                    </tr>
												<?php endforeach; ?>
											<?php endif; ?>
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="4">
                                                    <button class="add_order_item_meta button"><?php esc_html_e( 'Add&nbsp;meta', 'woocommerce' ); ?></button>
                                                </td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </td>

                                <td class="item_cost" width="1%"
                                    data-sort-value="<?php echo esc_attr( $order->get_item_subtotal( $item, false, true ) ); ?>">
                                    <div class="view">
										<?php
											echo wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $order->get_currency() ) );

											if ( $item->get_subtotal() !== $item->get_total() ) {
												echo '<span class="wc-order-item-discount">-' . wc_price( wc_format_decimal( $order->get_item_subtotal( $item, false, false ) - $order->get_item_total( $item, false, false ), '' ), array( 'currency' => $order->get_currency() ) ) . '</span>';
											}
										?>
                                    </div>
                                </td>
                                <td class="quantity" width="1%">
                                    <div class="view">
										<?php
											echo '<small class="times">&times;</small> ' . esc_html( $item->get_quantity() );

											if ( $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) ) {
												echo '<small class="refunded">-' . ( $refunded_qty * - 1 ) . '</small>';
											}
										?>
                                    </div>
                                    <div class="edit" style="display: none;">
                                        <input type="number"
                                               step="<?php echo esc_attr( apply_filters( 'woocommerce_quantity_input_step', '1', $product ) ); ?>"
                                               min="0" autocomplete="off"
                                               name="order_item_qty[<?php echo absint( $item_id ); ?>]"
                                               placeholder="0"
                                               value="<?php echo esc_attr( $item->get_quantity() ); ?>"
                                               data-qty="<?php echo esc_attr( $item->get_quantity() ); ?>" size="4"
                                               class="quantity"/>
                                    </div>
                                    <div class="refund" style="display: none;">
                                        <input type="number"
                                               step="<?php echo esc_attr( apply_filters( 'woocommerce_quantity_input_step', '1', $product ) ); ?>"
                                               min="0" max="<?php echo absint( $item->get_quantity() ); ?>"
                                               autocomplete="off"
                                               name="refund_order_item_qty[<?php echo absint( $item_id ); ?>]"
                                               placeholder="0" size="4" class="refund_order_item_qty"/>
                                    </div>
                                </td>
                                <td class="line_cost" width="1%"
                                    data-sort-value="<?php echo esc_attr( $item->get_total() ); ?>">
                                    <div class="view">
										<?php
											echo wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );

											if ( $item->get_subtotal() !== $item->get_total() ) {
												echo '<span class="wc-order-item-discount">-' . wc_price( wc_format_decimal( $item->get_subtotal() - $item->get_total(), '' ), array( 'currency' => $order->get_currency() ) ) . '</span>';
											}

											if ( $refunded = $order->get_total_refunded_for_item( $item_id ) ) {
												echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
											}
										?>
                                    </div>
                                    <div class="edit" style="display: none;">
                                        <div class="split-input">
                                            <div class="input">
                                                <label><?php esc_attr_e( 'Pre-discount:', 'woocommerce' ); ?></label>
                                                <input type="text"
                                                       name="line_subtotal[<?php echo absint( $item_id ); ?>]"
                                                       placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                                       value="<?php echo esc_attr( wc_format_localized_price( $item->get_subtotal() ) ); ?>"
                                                       class="line_subtotal wc_input_price"
                                                       data-subtotal="<?php echo esc_attr( wc_format_localized_price( $item->get_subtotal() ) ); ?>"/>
                                            </div>
                                            <div class="input">
                                                <label><?php esc_attr_e( 'Total:', 'woocommerce' ); ?></label>
                                                <input type="text"
                                                       name="line_total[<?php echo absint( $item_id ); ?>]"
                                                       placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                                       value="<?php echo esc_attr( wc_format_localized_price( $item->get_total() ) ); ?>"
                                                       class="line_total wc_input_price"
                                                       data-tip="<?php esc_attr_e( 'After pre-tax discounts.', 'woocommerce' ); ?>"
                                                       data-total="<?php echo esc_attr( wc_format_localized_price( $item->get_total() ) ); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="refund" style="display: none;">
                                        <input type="text"
                                               name="refund_line_total[<?php echo absint( $item_id ); ?>]"
                                               placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                               class="refund_line_total wc_input_price"/>
                                    </div>
                                </td>

								<?php
									if ( ( $tax_data = $item->get_taxes() ) && wc_tax_enabled() ) {
										foreach ( $order_taxes as $tax_item ) {
											$tax_item_id       = $tax_item->get_rate_id();
											$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
											$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : '';
											?>
                                            <td class="line_tax" width="1%">
                                                <div class="view">
													<?php
														if ( '' !== $tax_item_total ) {
															echo wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order->get_currency() ) );
														} else {
															echo '&ndash;';
														}

														if ( $item->get_subtotal() !== $item->get_total() ) {
															if ( '' === $tax_item_total ) {
																echo '<span class="wc-order-item-discount">&ndash;</span>';
															} else {
																echo '<span class="wc-order-item-discount">-' . wc_price( wc_round_tax_total( $tax_item_subtotal - $tax_item_total ), array( 'currency' => $order->get_currency() ) ) . '</span>';
															}
														}

														if ( $refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id ) ) {
															echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
														}
													?>
                                                </div>
                                                <div class="edit" style="display: none;">
                                                    <div class="split-input">
                                                        <div class="input">
                                                            <label><?php esc_attr_e( 'Pre-discount:', 'woocommerce' ); ?></label>
                                                            <input type="text"
                                                                   name="line_subtotal_tax[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]"
                                                                   placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                                                   value="<?php echo esc_attr( wc_format_localized_price( $tax_item_subtotal ) ); ?>"
                                                                   class="line_subtotal_tax wc_input_price"
                                                                   data-subtotal_tax="<?php echo esc_attr( wc_format_localized_price( $tax_item_subtotal ) ); ?>"
                                                                   data-tax_id="<?php echo esc_attr( $tax_item_id ); ?>"/>
                                                        </div>
                                                        <div class="input">
                                                            <label><?php esc_attr_e( 'Total:', 'woocommerce' ); ?></label>
                                                            <input type="text"
                                                                   name="line_tax[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]"
                                                                   placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                                                   value="<?php echo esc_attr( wc_format_localized_price( $tax_item_total ) ); ?>"
                                                                   class="line_tax wc_input_price"
                                                                   data-total_tax="<?php echo esc_attr( wc_format_localized_price( $tax_item_total ) ); ?>"
                                                                   data-tax_id="<?php echo esc_attr( $tax_item_id ); ?>"/>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="refund" style="display: none;">
                                                    <input type="text"
                                                           name="refund_line_tax[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]"
                                                           placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                                           class="refund_line_tax wc_input_price"
                                                           data-tax_id="<?php echo esc_attr( $tax_item_id ); ?>"/>
                                                </div>
                                            </td>
											<?php
										}
									}
								?>
                                <td class="wc-order-edit-line-item" width="1%">

                                </td>
                            </tr>
							<?php

						}
					?>
                    </tbody>
                    <tbody id="order_shipping_line_items">
					<?php
						$shipping_methods = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();
						foreach ( $line_items_shipping as $item_id => $item ) {
							?>
                            <tr class="shipping <?php echo ( ! empty( $class ) ) ? esc_attr( $class ) : ''; ?>"
                                data-order_item_id="<?php echo esc_attr( $item_id ); ?>">
                                <td class="thumb">
                                    <div></div>
                                </td>

                                <td class="name">
                                    <div class="view">
										<?php echo esc_html( $item->get_name() ? $item->get_name() : __( 'Shipping', 'woocommerce' ) ); ?>
                                    </div>
                                    <div class="edit" style="display: none;">
                                        <input type="hidden" name="shipping_method_id[]"
                                               value="<?php echo esc_attr( $item_id ); ?>"/>
                                        <input type="text" class="shipping_method_name"
                                               placeholder="<?php esc_attr_e( 'Shipping name', 'woocommerce' ); ?>"
                                               name="shipping_method_title[<?php echo esc_attr( $item_id ); ?>]"
                                               value="<?php echo esc_attr( $item->get_name() ); ?>"/>
                                        <select class="shipping_method"
                                                name="shipping_method[<?php echo esc_attr( $item_id ); ?>]">
                                            <optgroup
                                                    label="<?php esc_attr_e( 'Shipping method', 'woocommerce' ); ?>">
                                                <option value=""><?php esc_html_e( 'N/A', 'woocommerce' ); ?></option>
												<?php
													$found_method = false;

													foreach ( $shipping_methods as $method ) {
														$current_method = ( 0 === strpos( $item->get_method_id(), $method->id ) ) ? $item->get_method_id() : $method->id;

														echo '<option value="' . esc_attr( $current_method ) . '" ' . selected( $item->get_method_id() === $current_method, true, false ) . '>' . esc_html( $method->get_method_title() ) . '</option>';

														if ( $item->get_method_id() === $current_method ) {
															$found_method = true;
														}
													}

													if ( ! $found_method && $item->get_method_id() ) {
														echo '<option value="' . esc_attr( $item->get_method_id() ) . '" selected="selected">' . esc_html__( 'Other', 'woocommerce' ) . '</option>';
													} else {
														echo '<option value="other">' . esc_html__( 'Other', 'woocommerce' ) . '</option>';
													}
												?>
                                            </optgroup>
                                        </select>
                                    </div>
									<?php
										$hidden_order_itemmeta = apply_filters(
											'woocommerce_hidden_order_itemmeta', array(
												'_qty',
												'_tax_class',
												'_product_id',
												'_variation_id',
												'_line_subtotal',
												'_line_subtotal_tax',
												'_line_total',
												'_line_tax',
												'method_id',
												'cost',
												'_reduced_stock',
											)
										);
									?>
                                    <div class="view">
										<?php if ( $meta_data = $item->get_formatted_meta_data( '' ) ) : ?>
                                            <table cellspacing="0" class="display_meta">
												<?php
													foreach ( $meta_data as $meta_id => $meta ) :
														if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
															continue;
														}
														?>
                                                        <tr>
                                                            <th><?php echo wp_kses_post( $meta->display_key ); ?>:
                                                            </th>
                                                            <td><?php echo wp_kses_post( force_balance_tags( $meta->display_value ) ); ?></td>
                                                        </tr>
													<?php endforeach; ?>
                                            </table>
										<?php endif; ?>
                                    </div>
                                    <div class="edit" style="display: none;">
                                        <table class="meta" cellspacing="0">
                                            <tbody class="meta_items">
											<?php if ( $meta_data = $item->get_formatted_meta_data( '' ) ) : ?>
												<?php
												foreach ( $meta_data as $meta_id => $meta ) :
													if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
														continue;
													}
													?>
                                                    <tr data-meta_id="<?php echo esc_attr( $meta_id ); ?>">
                                                        <td>
                                                            <input type="text" maxlength="255"
                                                                   placeholder="<?php esc_attr_e( 'Name (required)', 'woocommerce' ); ?>"
                                                                   name="meta_key[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $meta_id ); ?>]"
                                                                   value="<?php echo esc_attr( $meta->key ); ?>"/>
                                                            <textarea
                                                                    placeholder="<?php esc_attr_e( 'Value (required)', 'woocommerce' ); ?>"
                                                                    name="meta_value[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $meta_id ); ?>]"><?php echo esc_textarea( rawurldecode( $meta->value ) ); ?></textarea>
                                                        </td>
                                                        <td width="1%">
                                                            <button class="remove_order_item_meta button">&times;
                                                            </button>
                                                        </td>
                                                    </tr>
												<?php endforeach; ?>
											<?php endif; ?>
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="4">
                                                    <button class="add_order_item_meta button"><?php esc_html_e( 'Add&nbsp;meta', 'woocommerce' ); ?></button>
                                                </td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </td>


                                <td class="item_cost" width="1%">&nbsp;</td>
                                <td class="quantity" width="1%">&nbsp;</td>

                                <td class="line_cost" width="1%">
                                    <div class="view">
										<?php
											echo wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );
											$refunded = $order->get_total_refunded_for_item( $item_id, 'shipping' );
											if ( $refunded ) {
												echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
											}
										?>
                                    </div>
                                    <div class="edit" style="display: none;">
                                        <input type="text" name="shipping_cost[<?php echo esc_attr( $item_id ); ?>]"
                                               placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                               value="<?php echo esc_attr( wc_format_localized_price( $item->get_total() ) ); ?>"
                                               class="line_total wc_input_price"/>
                                    </div>
                                    <div class="refund" style="display: none;">
                                        <input type="text"
                                               name="refund_line_total[<?php echo absint( $item_id ); ?>]"
                                               placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                               class="refund_line_total wc_input_price"/>
                                    </div>
                                </td>
								<?php
									if ( ( $tax_data = $item->get_taxes() ) && wc_tax_enabled() ) {
										foreach ( $order_taxes as $tax_item ) {
											$tax_item_id    = $tax_item->get_rate_id();
											$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
											?>
                                            <td class="line_tax" width="1%">
                                                <div class="view">
													<?php
														echo ( '' !== $tax_item_total ) ? wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order->get_currency() ) ) : '&ndash;';
														$refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id, 'shipping' );
														if ( $refunded ) {
															echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
														}
													?>
                                                </div>
                                                <div class="edit" style="display: none;">
                                                    <input type="text"
                                                           name="shipping_taxes[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]"
                                                           placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                                           value="<?php echo ( isset( $tax_item_total ) ) ? esc_attr( wc_format_localized_price( $tax_item_total ) ) : ''; ?>"
                                                           class="line_tax wc_input_price"/>
                                                </div>
                                                <div class="refund" style="display: none;">
                                                    <input type="text"
                                                           name="refund_line_tax[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]"
                                                           placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                                           class="refund_line_tax wc_input_price"
                                                           data-tax_id="<?php echo esc_attr( $tax_item_id ); ?>"/>
                                                </div>
                                            </td>
											<?php
										}
									}
								?>
                                <td class="wc-order-edit-line-item">

                                </td>
                            </tr>
							<?php
						}
					?>
                    </tbody>
                    <tbody id="order_fee_line_items">
					<?php
						foreach ( $line_items_fee as $item_id => $item ) {
							?>
                            <tr class="fee <?php echo ( ! empty( $class ) ) ? esc_attr( $class ) : ''; ?>"
                                data-order_item_id="<?php echo esc_attr( $item_id ); ?>">
                                <td class="thumb">
                                    <div></div>
                                </td>

                                <td class="name">
                                    <div class="view">
										<?php echo esc_html( $item->get_name() ? $item->get_name() : __( 'Fee', 'woocommerce' ) ); ?>
                                    </div>
                                    <div class="edit" style="display: none;">
                                        <input type="text"
                                               placeholder="<?php esc_attr_e( 'Fee name', 'woocommerce' ); ?>"
                                               name="order_item_name[<?php echo absint( $item_id ); ?>]"
                                               value="<?php echo ( $item->get_name() ) ? esc_attr( $item->get_name() ) : ''; ?>"/>
                                        <input type="hidden" class="order_item_id" name="order_item_id[]"
                                               value="<?php echo esc_attr( $item_id ); ?>"/>
                                        <input type="hidden"
                                               name="order_item_tax_class[<?php echo absint( $item_id ); ?>]"
                                               value="<?php echo esc_attr( $item->get_tax_class() ); ?>"/>
                                    </div>
									<?php do_action( 'woocommerce_after_order_fee_item_name', $item_id, $item, null ); ?>
                                </td>
								<?php do_action( 'woocommerce_admin_order_item_values', null, $item, absint( $item_id ) ); ?>
                                <td class="item_cost" width="1%">&nbsp;</td>
                                <td class="quantity" width="1%">&nbsp;</td>

                                <td class="line_cost" width="1%">
                                    <div class="view">
										<?php
											echo wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );

											if ( $refunded = $order->get_total_refunded_for_item( $item_id, 'fee' ) ) {
												echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
											}
										?>
                                    </div>
                                    <div class="edit" style="display: none;">
                                        <input type="text" name="line_total[<?php echo absint( $item_id ); ?>]"
                                               placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                               value="<?php echo esc_attr( wc_format_localized_price( $item->get_total() ) ); ?>"
                                               class="line_total wc_input_price"/>
                                    </div>
                                    <div class="refund" style="display: none;">
                                        <input type="text"
                                               name="refund_line_total[<?php echo absint( $item_id ); ?>]"
                                               placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                               class="refund_line_total wc_input_price"/>
                                    </div>
                                </td>
								<?php
									if ( ( $tax_data = $item->get_taxes() ) && wc_tax_enabled() ) {
										foreach ( $order_taxes as $tax_item ) {
											$tax_item_id    = $tax_item->get_rate_id();
											$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
											?>
                                            <td class="line_tax" width="1%">
                                                <div class="view">
													<?php
														echo ( '' !== $tax_item_total ) ? wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order->get_currency() ) ) : '&ndash;';

														if ( $refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id, 'fee' ) ) {
															echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
														}
													?>
                                                </div>
                                                <div class="edit" style="display: none;">
                                                    <input type="text"
                                                           name="line_tax[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]"
                                                           placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                                           value="<?php echo ( isset( $tax_item_total ) ) ? esc_attr( wc_format_localized_price( $tax_item_total ) ) : ''; ?>"
                                                           class="line_tax wc_input_price"/>
                                                </div>
                                                <div class="refund" style="display: none;">
                                                    <input type="text"
                                                           name="refund_line_tax[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]"
                                                           placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>"
                                                           class="refund_line_tax wc_input_price"
                                                           data-tax_id="<?php echo esc_attr( $tax_item_id ); ?>"/>
                                                </div>
                                            </td>
											<?php
										}
									}
								?>
                                <td class="wc-order-edit-line-item">

                                </td>
                            </tr>
							<?php
						}
					?>
                    </tbody>
                    <tbody id="order_refunds">
					<?php
						$refunds = $order->get_refunds();

						if ( $refunds ) {
							foreach ( $refunds as $refund ) {
								$who_refunded = new WP_User( $refund->get_refunded_by() );
								?>
                                <tr class="refund <?php echo ( ! empty( $class ) ) ? esc_attr( $class ) : ''; ?>"
                                    data-order_refund_id="<?php echo esc_attr( $refund->get_id() ); ?>">
                                    <td class="thumb">
                                        <div></div>
                                    </td>
                                    <td class="name">
										<?php
											if ( $who_refunded->exists() ) {
												printf(
												/* translators: 1: refund id 2: refund date 3: username */
													esc_html__( 'Refund #%1$s - %2$s by %3$s', 'woocommerce' ),
													esc_html( $refund->get_id() ),
													esc_html( wc_format_datetime( $refund->get_date_created(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) ),
													sprintf(
														'<abbr class="refund_by" title="%1$s">%2$s</abbr>',
														/* translators: 1: ID who refunded */
														sprintf( esc_attr__( 'ID: %d', 'woocommerce' ), absint( $who_refunded->ID ) ),
														esc_html( $who_refunded->display_name )
													)
												);
											} else {
												printf(
												/* translators: 1: refund id 2: refund date */
													esc_html__( 'Refund #%1$s - %2$s', 'woocommerce' ),
													esc_html( $refund->get_id() ),
													esc_html( wc_format_datetime( $refund->get_date_created(), get_option( 'date_format' ) . ', ' . get_option( 'time_format' ) ) )
												);
											}
										?>
										<?php if ( $refund->get_reason() ) : ?>
                                            <p class="description"><?php echo esc_html( $refund->get_reason() ); ?></p>
										<?php endif; ?>
                                        <input type="hidden" class="order_refund_id" name="order_refund_id[]"
                                               value="<?php echo esc_attr( $refund->get_id() ); ?>"/>
                                    </td>
									<?php do_action( 'woocommerce_admin_order_item_values', null, $refund, $refund->get_id() ); ?>
                                    <td class="item_cost" width="1%">&nbsp;</td>
                                    <td class="quantity" width="1%">&nbsp;</td>
                                    <td class="line_cost" width="1%">
                                        <div class="view">
											<?php
												echo wp_kses_post(
													wc_price( '-' . $refund->get_amount(), array( 'currency' => $refund->get_currency() ) )
												);
											?>
                                        </div>
                                    </td>
									<?php
										if ( wc_tax_enabled() ) :
											$total_taxes = count( $order_taxes );
											?>
											<?php for ( $i = 0; $i < $total_taxes; $i ++ ) : ?>
                                            <td class="line_tax" width="1%"></td>
										<?php endfor; ?>
										<?php endif; ?>

                                    <td class="wc-order-edit-line-item">
                                    </td>
                                </tr>
								<?php
							}
						}
					?>
                    </tbody>
                </table>
            </div>
            <div class="wc-order-data-row wc-order-totals-items wc-order-items-editable">
				<?php
					$coupons = $order->get_items( 'coupon' );
					if ( $coupons ) :?>
                        <div class="wc-used-coupons">
                            <ul class="wc_coupon_list">
                                <li><strong><?php esc_html_e( 'Coupon(s)', 'woocommerce' ); ?></strong></li>
								<?php
									foreach ( $coupons as $item_id => $item ) :
										$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' LIMIT 1;", $item->get_code() ) );
										$class = $order->is_editable() ? 'code editable' : 'code';
										?>
                                        <li class="<?php echo esc_attr( $class ); ?>">
											<?php if ( $post_id ) : ?>
												<?php
												$post_url = apply_filters( 'woocommerce_admin_order_item_coupon_url', add_query_arg(
													array(
														'post'   => $post_id,
														'action' => 'edit',
													),
													admin_url( 'post.php' )
												), $item, $order );
												?>
                                                <a href="<?php echo esc_url( $post_url ); ?>" class="tips"
                                                   data-tip="<?php echo esc_attr( wc_price( $item->get_discount(), array( 'currency' => $order->get_currency() ) ) ); ?>">
                                                    <span><?php echo esc_html( $item->get_code() ); ?></span>
                                                </a>
											<?php else : ?>
                                                <span class="tips"
                                                      data-tip="<?php echo esc_attr( wc_price( $item->get_discount(), array( 'currency' => $order->get_currency() ) ) ); ?>">
								<span><?php echo esc_html( $item->get_code() ); ?></span>
							</span>
											<?php endif; ?>
											<?php if ( $order->is_editable() ) : ?>
                                                <a class="remove-coupon" href="javascript:void(0)" aria-label="Remove"
                                                   data-code="<?php echo esc_attr( $item->get_code() ); ?>"></a>
											<?php endif; ?>
                                        </li>
									<?php endforeach; ?>
                            </ul>
                        </div>
					<?php endif; ?>
                <table class="wc-order-totals">
					<?php if ( 0 < $order->get_total_discount() ) : ?>
                        <tr>
                            <td class="label"><?php esc_html_e( 'Discount:', 'woocommerce' ); ?></td>
                            <td width="1%"></td>
                            <td class="total">
								<?php echo wc_price( $order->get_total_discount(), array( 'currency' => $order->get_currency() ) ); // WPCS: XSS ok. ?>
                            </td>
                        </tr>
					<?php endif; ?>
					<?php if ( $order->get_shipping_methods() ) : ?>
                        <tr>
                            <td class="label"><?php esc_html_e( 'Shipping:', 'woocommerce' ); ?></td>
                            <td width="1%"></td>
                            <td class="total">
								<?php
									$refunded = $order->get_total_shipping_refunded();
									if ( $refunded > 0 ) {
										echo '<del>' . strip_tags( wc_price( $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) ) ) . '</del> <ins>' . wc_price( $order->get_shipping_total() - $refunded, array( 'currency' => $order->get_currency() ) ) . '</ins>'; // WPCS: XSS ok.
									} else {
										echo wc_price( $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) ); // WPCS: XSS ok.
									}
								?>
                            </td>
                        </tr>
					<?php endif; ?>
					<?php if ( wc_tax_enabled() ) : ?>
						<?php foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
                            <tr>
                                <td class="label"><?php echo esc_html( $tax->label ); ?>:</td>
                                <td width="1%"></td>
                                <td class="total">
									<?php
										$refunded = $order->get_total_tax_refunded_by_rate_id( $tax->rate_id );
										if ( $refunded > 0 ) {
											echo '<del>' . strip_tags( $tax->formatted_amount ) . '</del> <ins>' . wc_price( WC_Tax::round( $tax->amount, wc_get_price_decimals() ) - WC_Tax::round( $refunded, wc_get_price_decimals() ), array( 'currency' => $order->get_currency() ) ) . '</ins>'; // WPCS: XSS ok.
										} else {
											echo wp_kses_post( $tax->formatted_amount );
										}
									?>
                                </td>
                            </tr>
						<?php endforeach; ?>
					<?php endif; ?>
                    <tr>
                        <td class="label"><?php esc_html_e( 'Total', 'woocommerce' ); ?>:</td>
                        <td width="1%"></td>
                        <td class="total">
							<?php echo $order->get_formatted_order_total(); // WPCS: XSS ok. ?>
                        </td>
                    </tr>
					<?php if ( $order->get_total_refunded() ) : ?>
                        <tr>
                            <td class="label refunded-total"><?php esc_html_e( 'Refunded', 'woocommerce' ); ?>:</td>
                            <td width="1%"></td>
                            <td class="total refunded-total">
                                -<?php echo wc_price( $order->get_total_refunded(), array( 'currency' => $order->get_currency() ) ); // WPCS: XSS ok. ?></td>
                        </tr>
					<?php endif; ?>
                </table>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <h3 style="margin:1rem;">Audit Log</h3>
    <div class="zacctmgr_tab_content">
		<?php
			if ( ! class_exists( 'ZACCTMGR_Core_Audit_Order' ) ) {
				require_once( ZACCTMGR_PLUGIN_DIR . 'helper/class-zacctmgr-core-audit-orders.php' );
			}
			$order_audit = new ZACCTMGR_Core_Audit_Order( $order );
			$order_audit->print_overview();
		?>
    </div>
</div>
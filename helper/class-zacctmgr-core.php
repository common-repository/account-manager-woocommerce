<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	class ZACCTMGR_Core {
		private static $initiated = false;

		public static function init() {
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', function () {
					?>
                    <div class="notice notice-error is-dismissible">
                        <p>Account Manager WooCommerce require WooCommerce</p>
                    </div>
					<?php
				} );

				return;
			}


			if ( ! self::$initiated ) {
				self::init_hooks();
			}
		}

		private static function init_hooks() {
		}

		public static function plugin_activation() {

			self::zacctmgr_set_v2_installation_date();
			self::zacctmgr_create_acm_assignments_mapping_table();
			self::zacctmgr_create_acm_commissions_mapping_table();
			self::zacctmgr_create_acm_order_audit_mapping_table();
			self::zacctmgr_create_acm_manager_commission_audit_mapping();
			self::zacctmgr_initialize_customer_assignments_table();
			self::zacctmgr_initialize_commissions_table();
		}

		public static function plugin_deactivation() {
		}

		public static function zacctmgr_set_v2_installation_date() {

			$option = get_option( 'zacctmgr_v2_install_date' );
			if ( $option == false ) {
				update_option( 'zacctmgr_v2_install_date', current_time( 'mysql' ) );
			}
		}

		public static function zacctmgr_create_acm_assignments_mapping_table() {
			global $wpdb;
			global $zacctmgr_acm_assignments_mapping_version;

			$zacctmgr_acm_assignments_mapping_version = "1.0";


			$table_name_assignments = $wpdb->prefix . 'zacctmgr_acm_assignments_mapping';


			$charset_collate = $wpdb->get_charset_collate();

			$sql_assignments = "CREATE TABLE $table_name_assignments (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				timestamp datetime NOT NULL,
				manager_id mediumint(9) NOT NULL,
				customer_id mediumint(9) NOT NULL,
				PRIMARY KEY  (id)
			)$charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql_assignments );

			add_option( 'zacctmgr_acm_assignments_mapping_version', $zacctmgr_acm_assignments_mapping_version );
		}

		public static function zacctmgr_create_acm_commissions_mapping_table() {
			global $wpdb;
			global $zacctmgr_acm_commissions_mapping_version;

			$zacctmgr_acm_commissions_mapping_version = "1.0";

			$table_name_commissions = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';

			$charset_collate = $wpdb->get_charset_collate();

			$sql_commissions = "CREATE TABLE $table_name_commissions (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				timestamp datetime NOT NULL,
				manager_id mediumint(9) NOT NULL,
				no_commission tinyint(1) NOT NULL,
				order_level tinyint(1) NOT NULL,
				customer_account_level tinyint(1) NOT NULL,
				customer_id mediumint(9) DEFAULT NULL,
				new_order_commission_percentage_type tinyint(1) NOT NULL,
				new_order_commission_fixed_type tinyint(1) NOT NULL,
				new_order_commission_value float(9,2) NOT NULL,
				new_order_commission_limit mediumint(9) NOT NULL,
				new_order_exclude_coupon_amount tinyint(1),
				new_order_exclude_taxes_amount tinyint(1),
				new_order_exclude_shipping_costs tinyint(1),
				new_order_exclude_shipping_tax_amount tinyint(1),
				existing_order_commission_percentage_type tinyint(1) NOT NULL,
				existing_order_commission_fixed_type tinyint(1) NOT NULL,
				existing_order_commission_value float(9,2) NOT NULL,
				existing_order_exclude_coupon_amount tinyint(1),
				existing_order_exclude_taxes_amount tinyint(1),
				existing_order_exclude_shipping_costs tinyint(1),
				existing_order_exclude_shipping_tax_amount tinyint(1),
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql_commissions );

			add_option( 'zacctmgr_acm_commissions_mapping_version', $zacctmgr_acm_commissions_mapping_version );
		}

		public static function zacctmgr_create_acm_order_audit_mapping_table() {
			global $wpdb;
			global $zacctmgr_acm_order_audit_mapping_version;

			$zacctmgr_acm_order_audit_mapping_version = "1.0";


			$table_name_order_audit = $wpdb->prefix . 'zacctmgr_acm_order_audit_mapping';


			$charset_collate = $wpdb->get_charset_collate();

			$sql_assignments = "CREATE TABLE $table_name_order_audit (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				timestamp datetime NOT NULL,
				user_id mediumint(9) NOT NULL,
				order_id mediumint(9) NOT NULL,
				old_value text NOT NULL,
				new_value text NOT NULL,
				action text NOT NULL,
				is_commission_change tinyint(1) NOT NULL,
				is_manual_change tinyint(1) DEFAULT 0 NOT NULL,
				PRIMARY KEY  (id)
			)$charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql_assignments );

			add_option( 'zacctmgr_acm_order_audit_mapping_version', $zacctmgr_acm_order_audit_mapping_version );
		}

		public static function zacctmgr_create_acm_manager_commission_audit_mapping() {
			global $wpdb;
			global $zacctmgr_acm_manager_commission_audit_mapping;

			$zacctmgr_acm_manager_commission_audit_mapping = "1.0";


			$table_name_order_audit = $wpdb->prefix . 'zacctmgr_acm_manager_commission_audit_mapping';


			$charset_collate = $wpdb->get_charset_collate();

			$sql_assignments = "CREATE TABLE $table_name_order_audit (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				timestamp datetime NOT NULL,
				user_id mediumint(9) NOT NULL,
				manager_id mediumint(9) NOT NULL,
				old_value text NOT NULL,
				new_value text NOT NULL,
				action text NOT NULL,
				is_commission_rate tinyint(1) NOT NULL,
				PRIMARY KEY  (id)
			)$charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql_assignments );

			add_option( 'zacctmgr_acm_manager_commission_audit_mapping', $zacctmgr_acm_manager_commission_audit_mapping );
		}

		public static function zacctmgr_initialize_customer_assignments_table() {
			global $wpdb;

			$table_name_assignments = $wpdb->prefix . 'zacctmgr_acm_assignments_mapping';

			$customers = new WP_User_Query( array(
				'role__not_in' => zacctmgr_get_selected_roles()
			) );


			foreach ( $customers->get_results() as $customer ) {
				$manager_id = get_user_meta( $customer->ID, 'zacctmgr_assigned', true );

				if ( $manager_id != "" ) {

					$result = $wpdb->get_results( "SELECT COUNT(*) AS exist FROM $table_name_assignments WHERE manager_id=$manager_id AND customer_id=$customer->ID ORDER BY timestamp ASC LIMIT 1;" );

					if ( $result[0]->exist == 0 ) {
						zacctmgr_insert_account_manager_assignment( array(
							'customer_id' => $customer->ID,
							'manager_id'  => $manager_id,
							'timestamp'   => current_time( 'mysql' )
						) );
					}
				}

			}

		}

		public static function zacctmgr_initialize_commissions_table() {
			global $wpdb;

			$table_name = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';

			$managers = zacctmgr_get_em_users();

			foreach ( $managers as $manager ) {
				if ( $manager->zacctmgr_commission_type == '' ) {
					$timestamp                                  = current_time( 'mysql' );
					$manager_id                                 = $manager->ID;
					$customer_id                                = null;
					$no_commission                              = 0;
					$order_level                                = 1;
					$customer_account_level                     = 0;
					$new_order_commission_percentage_type       = 0;
					$new_order_commission_fixed_type            = 0;
					$new_order_commission_value                 = 0;
					$new_order_commission_limit                 = 1;
					$new_order_exclude_coupon_amount            = 0;
					$new_order_exclude_taxes_amount             = 0;
					$new_order_exclude_shipping_costs           = 0;
					$new_order_exclude_shipping_tax_amount      = 0;
					$existing_order_commission_percentage_type  = 0;
					$existing_order_commission_fixed_type       = 0;
					$existing_order_commission_value            = 0;
					$existing_order_exclude_coupon_amount       = 0;
					$existing_order_exclude_taxes_amount        = 0;
					$existing_order_exclude_shipping_costs      = 0;
					$existing_order_exclude_shipping_tax_amount = 0;

					$result = $wpdb->get_results( "SELECT COUNT(*) AS exist FROM $table_name WHERE
							timestamp=$timestamp AND 
							manager_id=$manager_id AND 
							customer_id=$customer_id AND 
							no_commission=$no_commission AND 
							order_level=$order_level AND 
							customer_account_level=$customer_account_level AND 
							new_order_commission_percentage_type=$new_order_commission_percentage_type AND 
							new_order_commission_fixed_type=$new_order_commission_fixed_type AND 
							new_order_commission_value=$new_order_commission_value AND
							new_order_commission_limit=$new_order_commission_limit AND 
							new_order_exclude_coupon_amount=$new_order_exclude_coupon_amount AND 
							new_order_exclude_taxes_amount=$new_order_exclude_taxes_amount AND 
							new_order_exclude_shipping_costs=$new_order_exclude_shipping_costs AND 
							new_order_exclude_shipping_tax_amount=$new_order_exclude_shipping_tax_amount AND 
							existing_order_commission_percentage_type=$existing_order_commission_percentage_type AND 
							existing_order_commission_fixed_type=$existing_order_commission_fixed_type AND 
							existing_order_commission_value=$existing_order_commission_value AND 
							existing_order_exclude_coupon_amount=$existing_order_exclude_coupon_amount AND 
							existing_order_exclude_taxes_amount=$existing_order_exclude_taxes_amount AND 
							existing_order_exclude_shipping_costs=$existing_order_exclude_shipping_costs AND
							existing_order_exclude_shipping_tax_amount=$existing_order_exclude_shipping_tax_amount
							ORDER BY timestamp ASC LIMIT 1;" );

					if ( $result[0]->exist == 0 ) {
						$commission_entry = array(
							'timestamp'                                  => $timestamp,
							'manager_id'                                 => $manager_id,
							'customer_id'                                => $customer_id,
							'no_commission'                              => $no_commission,
							'order_level'                                => $order_level,
							'customer_account_level'                     => $customer_account_level,
							'new_order_commission_percentage_type'       => $new_order_commission_percentage_type,
							'new_order_commission_fixed_type'            => $new_order_commission_fixed_type,
							'new_order_commission_value'                 => $new_order_commission_value,
							'new_order_commission_limit'                 => $new_order_commission_limit,
							'new_order_exclude_coupon_amount'            => $new_order_exclude_coupon_amount,
							'new_order_exclude_taxes_amount'             => $new_order_exclude_taxes_amount,
							'new_order_exclude_shipping_costs'           => $new_order_exclude_shipping_costs,
							'new_order_exclude_shipping_tax_amount'      => $new_order_exclude_shipping_tax_amount,
							'existing_order_commission_percentage_type'  => $existing_order_commission_percentage_type,
							'existing_order_commission_fixed_type'       => $existing_order_commission_fixed_type,
							'existing_order_commission_value'            => $existing_order_commission_value,
							'existing_order_exclude_coupon_amount'       => $existing_order_exclude_coupon_amount,
							'existing_order_exclude_taxes_amount'        => $existing_order_exclude_taxes_amount,
							'existing_order_exclude_shipping_costs'      => $existing_order_exclude_shipping_costs,
							'existing_order_exclude_shipping_tax_amount' => $existing_order_exclude_shipping_tax_amount
						);

						zacctmgr_insert_commission_entry( $commission_entry );
					}
				}
				if ( $manager->zacctmgr_commission_type == 'no_commission' ) {
					$timestamp                                  = current_time( 'mysql' );
					$manager_id                                 = $manager->ID;
					$customer_id                                = null;
					$no_commission                              = 1;
					$order_level                                = 0;
					$customer_account_level                     = 0;
					$new_order_commission_percentage_type       = 0;
					$new_order_commission_fixed_type            = 0;
					$new_order_commission_value                 = 0;
					$new_order_commission_limit                 = 0;
					$new_order_exclude_coupon_amount            = 0;
					$new_order_exclude_taxes_amount             = 0;
					$new_order_exclude_shipping_costs           = 0;
					$new_order_exclude_shipping_tax_amount      = 0;
					$existing_order_commission_percentage_type  = 0;
					$existing_order_commission_fixed_type       = 0;
					$existing_order_commission_value            = 0;
					$existing_order_exclude_coupon_amount       = 0;
					$existing_order_exclude_taxes_amount        = 0;
					$existing_order_exclude_shipping_costs      = 0;
					$existing_order_exclude_shipping_tax_amount = 0;

					$result = $wpdb->get_results( "SELECT COUNT(*) AS exist FROM $table_name WHERE
							timestamp=$timestamp AND 
							manager_id=$manager_id AND 
							customer_id=$customer_id AND 
							no_commission=$no_commission AND 
							order_level=$order_level AND 
							customer_account_level=$customer_account_level AND 
							new_order_commission_percentage_type=$new_order_commission_percentage_type AND 
							new_order_commission_fixed_type=$new_order_commission_fixed_type AND 
							new_order_commission_value=$new_order_commission_value AND
							new_order_commission_limit=$new_order_commission_limit AND 
							new_order_exclude_coupon_amount=$new_order_exclude_coupon_amount AND 
							new_order_exclude_taxes_amount=$new_order_exclude_taxes_amount AND 
							new_order_exclude_shipping_costs=$new_order_exclude_shipping_costs AND 
							new_order_exclude_shipping_tax_amount=$new_order_exclude_shipping_tax_amount AND 
							existing_order_commission_percentage_type=$existing_order_commission_percentage_type AND 
							existing_order_commission_fixed_type=$existing_order_commission_fixed_type AND 
							existing_order_commission_value=$existing_order_commission_value AND 
							existing_order_exclude_coupon_amount=$existing_order_exclude_coupon_amount AND 
							existing_order_exclude_taxes_amount=$existing_order_exclude_taxes_amount AND 
							existing_order_exclude_shipping_costs=$existing_order_exclude_shipping_costs AND
							existing_order_exclude_shipping_tax_amount=$existing_order_exclude_shipping_tax_amount
							ORDER BY timestamp ASC LIMIT 1;" );

					if ( $result[0]->exist == 0 ) {
						$commission_entry = array(
							'timestamp'                                  => $timestamp,
							'manager_id'                                 => $manager_id,
							'customer_id'                                => $customer_id,
							'no_commission'                              => $no_commission,
							'order_level'                                => $order_level,
							'customer_account_level'                     => $customer_account_level,
							'new_order_commission_percentage_type'       => $new_order_commission_percentage_type,
							'new_order_commission_fixed_type'            => $new_order_commission_fixed_type,
							'new_order_commission_value'                 => $new_order_commission_value,
							'new_order_commission_limit'                 => $new_order_commission_limit,
							'new_order_exclude_coupon_amount'            => $new_order_exclude_coupon_amount,
							'new_order_exclude_taxes_amount'             => $new_order_exclude_taxes_amount,
							'new_order_exclude_shipping_costs'           => $new_order_exclude_shipping_costs,
							'new_order_exclude_shipping_tax_amount'      => $new_order_exclude_shipping_tax_amount,
							'existing_order_commission_percentage_type'  => $existing_order_commission_percentage_type,
							'existing_order_commission_fixed_type'       => $existing_order_commission_fixed_type,
							'existing_order_commission_value'            => $existing_order_commission_value,
							'existing_order_exclude_coupon_amount'       => $existing_order_exclude_coupon_amount,
							'existing_order_exclude_taxes_amount'        => $existing_order_exclude_taxes_amount,
							'existing_order_exclude_shipping_costs'      => $existing_order_exclude_shipping_costs,
							'existing_order_exclude_shipping_tax_amount' => $existing_order_exclude_shipping_tax_amount
						);

						zacctmgr_insert_commission_entry( $commission_entry );
					}


				}
				if ( $manager->zacctmgr_commission_type == 'order_level' ) {
					$timestamp                                  = current_time( 'mysql' );
					$manager_id                                 = $manager->ID;
					$customer_id                                = null;
					$no_commission                              = 0;
					$order_level                                = 1;
					$customer_account_level                     = 0;
					$new_order_commission_percentage_type       = $manager->zacctmgr_commission_new_type == 'percentage' ? 1 : 0;
					$new_order_commission_fixed_type            = $manager->zacctmgr_commission_new_type == 'fixed' ? 1 : 0;
					$new_order_commission_value                 = $manager->zacctmgr_commission_new_value;
					$new_order_commission_limit                 = $manager->zacctmgr_commission_order_count;
					$new_order_exclude_coupon_amount            = in_array( 'coupon', $manager->zacctmgr_commission_new_exclude_options );
					$new_order_exclude_taxes_amount             = in_array( 'tax', $manager->zacctmgr_commission_new_exclude_options );
					$new_order_exclude_shipping_costs           = in_array( 'shipping', $manager->zacctmgr_commission_new_exclude_options );
					$new_order_exclude_shipping_tax_amount      = in_array( 'shipping_tax', $manager->zacctmgr_commission_new_exclude_options );
					$existing_order_commission_percentage_type  = $manager->zacctmgr_commission_existing_type == 'percentage' ? 1 : 0;
					$existing_order_commission_fixed_type       = $manager->zacctmgr_commission_existing_type == 'fixed' ? 1 : 0;
					$existing_order_commission_value            = $manager->zacctmgr_commission_existing_value;
					$existing_order_exclude_coupon_amount       = in_array( 'coupon', $manager->zacctmgr_commission_existing_exclude_options );
					$existing_order_exclude_taxes_amount        = in_array( 'tax', $manager->zacctmgr_commission_existing_exclude_options );
					$existing_order_exclude_shipping_costs      = in_array( 'shipping', $manager->zacctmgr_commission_existing_exclude_options );
					$existing_order_exclude_shipping_tax_amount = in_array( 'shipping_tax', $manager->zacctmgr_commission_existing_exclude_options );

					$result = $wpdb->get_results( "SELECT COUNT(*) AS exist FROM $table_name WHERE
							timestamp=$timestamp AND 
							manager_id=$manager_id AND 
							customer_id=$customer_id AND 
							no_commission=$no_commission AND 
							order_level=$order_level AND 
							customer_account_level=$customer_account_level AND 
							new_order_commission_percentage_type=$new_order_commission_percentage_type AND 
							new_order_commission_fixed_type=$new_order_commission_fixed_type AND 
							new_order_commission_value=$new_order_commission_value AND
							new_order_commission_limit=$new_order_commission_limit AND 
							new_order_exclude_coupon_amount=$new_order_exclude_coupon_amount AND 
							new_order_exclude_taxes_amount=$new_order_exclude_taxes_amount AND 
							new_order_exclude_shipping_costs=$new_order_exclude_shipping_costs AND 
							new_order_exclude_shipping_tax_amount=$new_order_exclude_shipping_tax_amount AND 
							existing_order_commission_percentage_type=$existing_order_commission_percentage_type AND 
							existing_order_commission_fixed_type=$existing_order_commission_fixed_type AND 
							existing_order_commission_value=$existing_order_commission_value AND 
							existing_order_exclude_coupon_amount=$existing_order_exclude_coupon_amount AND 
							existing_order_exclude_taxes_amount=$existing_order_exclude_taxes_amount AND 
							existing_order_exclude_shipping_costs=$existing_order_exclude_shipping_costs AND
							existing_order_exclude_shipping_tax_amount=$existing_order_exclude_shipping_tax_amount
							ORDER BY timestamp ASC LIMIT 1;" );

					if ( $result[0]->exist == 0 ) {
						$commission_entry = array(
							'timestamp'                                  => $timestamp,
							'manager_id'                                 => $manager_id,
							'customer_id'                                => $customer_id,
							'no_commission'                              => $no_commission,
							'order_level'                                => $order_level,
							'customer_account_level'                     => $customer_account_level,
							'new_order_commission_percentage_type'       => $new_order_commission_percentage_type,
							'new_order_commission_fixed_type'            => $new_order_commission_fixed_type,
							'new_order_commission_value'                 => $new_order_commission_value,
							'new_order_commission_limit'                 => $new_order_commission_limit,
							'new_order_exclude_coupon_amount'            => $new_order_exclude_coupon_amount,
							'new_order_exclude_taxes_amount'             => $new_order_exclude_taxes_amount,
							'new_order_exclude_shipping_costs'           => $new_order_exclude_shipping_costs,
							'new_order_exclude_shipping_tax_amount'      => $new_order_exclude_shipping_tax_amount,
							'existing_order_commission_percentage_type'  => $existing_order_commission_percentage_type,
							'existing_order_commission_fixed_type'       => $existing_order_commission_fixed_type,
							'existing_order_commission_value'            => $existing_order_commission_value,
							'existing_order_exclude_coupon_amount'       => $existing_order_exclude_coupon_amount,
							'existing_order_exclude_taxes_amount'        => $existing_order_exclude_taxes_amount,
							'existing_order_exclude_shipping_costs'      => $existing_order_exclude_shipping_costs,
							'existing_order_exclude_shipping_tax_amount' => $existing_order_exclude_shipping_tax_amount
						);

						zacctmgr_insert_commission_entry( $commission_entry );
					}
				} elseif ( $manager->zacctmgr_commission_type == 'customer_account_level' ) {
					$manager_id    = $manager->ID;
					$customers_ids = zacctmgr_get_customer_list_by_manager( array(
						'manager_id' => $manager_id,
						'start_date' => date( '1970-01-01 00:00:00' ),
						'end_date'   => date( 'Y-m-d 23:59:59' )
					) );
					foreach ( $customers_ids as $customers_id ) {
						$customer = get_user_by( 'id', $customers_id );
						if ( $customer->zacctmgr_commission_type == '' ) {
							$timestamp                                  = current_time( 'mysql' );
							$manager_id                                 = $manager->ID;
							$customer_id                                = $customers_id;
							$no_commission                              = 1;
							$order_level                                = 0;
							$customer_account_level                     = 1;
							$new_order_commission_percentage_type       = 0;
							$new_order_commission_fixed_type            = 0;
							$new_order_commission_value                 = 0;
							$new_order_commission_limit                 = 1;
							$new_order_exclude_coupon_amount            = 0;
							$new_order_exclude_taxes_amount             = 0;
							$new_order_exclude_shipping_costs           = 0;
							$new_order_exclude_shipping_tax_amount      = 0;
							$existing_order_commission_percentage_type  = 0;
							$existing_order_commission_fixed_type       = 0;
							$existing_order_commission_value            = 0;
							$existing_order_exclude_coupon_amount       = 0;
							$existing_order_exclude_taxes_amount        = 0;
							$existing_order_exclude_shipping_costs      = 0;
							$existing_order_exclude_shipping_tax_amount = 0;

							$result = $wpdb->get_results( "SELECT COUNT(*) AS exist FROM $table_name WHERE
							timestamp=$timestamp AND 
							manager_id=$manager_id AND 
							customer_id=$customer_id AND 
							no_commission=$no_commission AND 
							order_level=$order_level AND 
							customer_account_level=$customer_account_level AND 
							new_order_commission_percentage_type=$new_order_commission_percentage_type AND 
							new_order_commission_fixed_type=$new_order_commission_fixed_type AND 
							new_order_commission_value=$new_order_commission_value AND
							new_order_commission_limit=$new_order_commission_limit AND 
							new_order_exclude_coupon_amount=$new_order_exclude_coupon_amount AND 
							new_order_exclude_taxes_amount=$new_order_exclude_taxes_amount AND 
							new_order_exclude_shipping_costs=$new_order_exclude_shipping_costs AND 
							new_order_exclude_shipping_tax_amount=$new_order_exclude_shipping_tax_amount AND 
							existing_order_commission_percentage_type=$existing_order_commission_percentage_type AND 
							existing_order_commission_fixed_type=$existing_order_commission_fixed_type AND 
							existing_order_commission_value=$existing_order_commission_value AND 
							existing_order_exclude_coupon_amount=$existing_order_exclude_coupon_amount AND 
							existing_order_exclude_taxes_amount=$existing_order_exclude_taxes_amount AND 
							existing_order_exclude_shipping_costs=$existing_order_exclude_shipping_costs AND
							existing_order_exclude_shipping_tax_amount=$existing_order_exclude_shipping_tax_amount
							ORDER BY timestamp ASC LIMIT 1;" );

							if ( $result[0]->exist == 0 ) {
								$commission_entry = array(
									'timestamp'                                  => $timestamp,
									'manager_id'                                 => $manager_id,
									'customer_id'                                => $customer_id,
									'no_commission'                              => $no_commission,
									'order_level'                                => $order_level,
									'customer_account_level'                     => $customer_account_level,
									'new_order_commission_percentage_type'       => $new_order_commission_percentage_type,
									'new_order_commission_fixed_type'            => $new_order_commission_fixed_type,
									'new_order_commission_value'                 => $new_order_commission_value,
									'new_order_commission_limit'                 => $new_order_commission_limit,
									'new_order_exclude_coupon_amount'            => $new_order_exclude_coupon_amount,
									'new_order_exclude_taxes_amount'             => $new_order_exclude_taxes_amount,
									'new_order_exclude_shipping_costs'           => $new_order_exclude_shipping_costs,
									'new_order_exclude_shipping_tax_amount'      => $new_order_exclude_shipping_tax_amount,
									'existing_order_commission_percentage_type'  => $existing_order_commission_percentage_type,
									'existing_order_commission_fixed_type'       => $existing_order_commission_fixed_type,
									'existing_order_commission_value'            => $existing_order_commission_value,
									'existing_order_exclude_coupon_amount'       => $existing_order_exclude_coupon_amount,
									'existing_order_exclude_taxes_amount'        => $existing_order_exclude_taxes_amount,
									'existing_order_exclude_shipping_costs'      => $existing_order_exclude_shipping_costs,
									'existing_order_exclude_shipping_tax_amount' => $existing_order_exclude_shipping_tax_amount
								);

								zacctmgr_insert_commission_entry( $commission_entry );
							}
						}
						if ( $customer->zacctmgr_commission_type == 'no_commission' ) {
							$timestamp                                  = current_time( 'mysql' );
							$manager_id                                 = $manager->ID;
							$customer_id                                = $customers_id;
							$no_commission                              = 1;
							$order_level                                = 0;
							$customer_account_level                     = 1;
							$new_order_commission_percentage_type       = 0;
							$new_order_commission_fixed_type            = 0;
							$new_order_commission_value                 = 0;
							$new_order_commission_limit                 = 1;
							$new_order_exclude_coupon_amount            = 0;
							$new_order_exclude_taxes_amount             = 0;
							$new_order_exclude_shipping_costs           = 0;
							$new_order_exclude_shipping_tax_amount      = 0;
							$existing_order_commission_percentage_type  = 0;
							$existing_order_commission_fixed_type       = 0;
							$existing_order_commission_value            = 0;
							$existing_order_exclude_coupon_amount       = 0;
							$existing_order_exclude_taxes_amount        = 0;
							$existing_order_exclude_shipping_costs      = 0;
							$existing_order_exclude_shipping_tax_amount = 0;

							$result = $wpdb->get_results( "SELECT COUNT(*) AS exist FROM $table_name WHERE
							timestamp=$timestamp AND 
							manager_id=$manager_id AND 
							customer_id=$customer_id AND 
							no_commission=$no_commission AND 
							order_level=$order_level AND 
							customer_account_level=$customer_account_level AND 
							new_order_commission_percentage_type=$new_order_commission_percentage_type AND 
							new_order_commission_fixed_type=$new_order_commission_fixed_type AND 
							new_order_commission_value=$new_order_commission_value AND
							new_order_commission_limit=$new_order_commission_limit AND 
							new_order_exclude_coupon_amount=$new_order_exclude_coupon_amount AND 
							new_order_exclude_taxes_amount=$new_order_exclude_taxes_amount AND 
							new_order_exclude_shipping_costs=$new_order_exclude_shipping_costs AND 
							new_order_exclude_shipping_tax_amount=$new_order_exclude_shipping_tax_amount AND 
							existing_order_commission_percentage_type=$existing_order_commission_percentage_type AND 
							existing_order_commission_fixed_type=$existing_order_commission_fixed_type AND 
							existing_order_commission_value=$existing_order_commission_value AND 
							existing_order_exclude_coupon_amount=$existing_order_exclude_coupon_amount AND 
							existing_order_exclude_taxes_amount=$existing_order_exclude_taxes_amount AND 
							existing_order_exclude_shipping_costs=$existing_order_exclude_shipping_costs AND
							existing_order_exclude_shipping_tax_amount=$existing_order_exclude_shipping_tax_amount
							ORDER BY timestamp ASC LIMIT 1;" );

							if ( $result[0]->exist == 0 ) {
								$commission_entry = array(
									'timestamp'                                  => $timestamp,
									'manager_id'                                 => $manager_id,
									'customer_id'                                => $customer_id,
									'no_commission'                              => $no_commission,
									'order_level'                                => $order_level,
									'customer_account_level'                     => $customer_account_level,
									'new_order_commission_percentage_type'       => $new_order_commission_percentage_type,
									'new_order_commission_fixed_type'            => $new_order_commission_fixed_type,
									'new_order_commission_value'                 => $new_order_commission_value,
									'new_order_commission_limit'                 => $new_order_commission_limit,
									'new_order_exclude_coupon_amount'            => $new_order_exclude_coupon_amount,
									'new_order_exclude_taxes_amount'             => $new_order_exclude_taxes_amount,
									'new_order_exclude_shipping_costs'           => $new_order_exclude_shipping_costs,
									'new_order_exclude_shipping_tax_amount'      => $new_order_exclude_shipping_tax_amount,
									'existing_order_commission_percentage_type'  => $existing_order_commission_percentage_type,
									'existing_order_commission_fixed_type'       => $existing_order_commission_fixed_type,
									'existing_order_commission_value'            => $existing_order_commission_value,
									'existing_order_exclude_coupon_amount'       => $existing_order_exclude_coupon_amount,
									'existing_order_exclude_taxes_amount'        => $existing_order_exclude_taxes_amount,
									'existing_order_exclude_shipping_costs'      => $existing_order_exclude_shipping_costs,
									'existing_order_exclude_shipping_tax_amount' => $existing_order_exclude_shipping_tax_amount
								);

								zacctmgr_insert_commission_entry( $commission_entry );
							}


						} elseif ( $customer->zacctmgr_commission_type == 'customer_account_level' ) {
							$timestamp                            = current_time( 'mysql' );
							$manager_id                           = $manager->ID;
							$customer_id                          = $customers_id;
							$no_commission                        = 0;
							$order_level                          = 0;
							$customer_account_level               = 1;
							$new_order_commission_percentage_type = $customer->zacctmgr_commission_new_type == 'percentage' ? 1 : 0;
							$new_order_commission_fixed_type      = $customer->zacctmgr_commission_new_type == 'fixed' ? 1 : 0;
							$new_order_commission_value           = $customer->zacctmgr_commission_new_value;
							$new_order_commission_limit           = $customer->zacctmgr_commission_order_count;
							if ( is_string( $customer->zacctmgr_commission_new_exclude_options ) ) {
								$new_order_exclude_coupon_amount       = strpos( $customer->zacctmgr_commission_new_exclude_options, 'coupon' ) != false ? 1 : 0;
								$new_order_exclude_taxes_amount        = strpos( $customer->zacctmgr_commission_new_exclude_options, 'tax' ) != false ? 1 : 0;
								$new_order_exclude_shipping_costs      = strpos( $customer->zacctmgr_commission_new_exclude_options, 'shipping' ) != false ? 1 : 0;
								$new_order_exclude_shipping_tax_amount = strpos( $customer->zacctmgr_commission_new_exclude_options, 'shipping_tax' ) != false ? 1 : 0;
							} else if ( is_array( $customer->zacctmgr_commission_new_exclude_options ) ) {
								$new_order_exclude_coupon_amount       = in_array( 'coupon', $customer->zacctmgr_commission_new_exclude_options );
								$new_order_exclude_taxes_amount        = in_array( 'tax', $customer->zacctmgr_commission_new_exclude_options );
								$new_order_exclude_shipping_costs      = in_array( 'shipping', $customer->zacctmgr_commission_new_exclude_options );
								$new_order_exclude_shipping_tax_amount = in_array( 'shipping_tax', $customer->zacctmgr_commission_new_exclude_options );

							}
							$existing_order_commission_percentage_type = $customer->zacctmgr_commission_existing_type == 'percentage' ? 1 : 0;
							$existing_order_commission_fixed_type      = $customer->zacctmgr_commission_existing_type == 'fixed' ? 1 : 0;
							$existing_order_commission_value           = $customer->zacctmgr_commission_existing_value;
							if ( is_string( $customer->zacctmgr_commission_existing_exclude_options ) ) {
								$existing_order_exclude_coupon_amount       = strpos( $customer->zacctmgr_commission_existing_exclude_options, 'coupon' ) != false ? 1 : 0;
								$existing_order_exclude_taxes_amount        = strpos( $customer->zacctmgr_commission_existing_exclude_options, 'tax' ) != false ? 1 : 0;
								$existing_order_exclude_shipping_costs      = strpos( $customer->zacctmgr_commission_existing_exclude_options, 'shipping' ) != false ? 1 : 0;
								$existing_order_exclude_shipping_tax_amount = strpos( $customer->zacctmgr_commission_existing_exclude_options, 'shipping_tax' ) != false ? 1 : 0;
							} else if ( is_array( $customer->zacctmgr_commission_existing_exclude_options ) ) {
								$existing_order_exclude_coupon_amount       = in_array( 'coupon', $customer->zacctmgr_commission_existing_exclude_options );
								$existing_order_exclude_taxes_amount        = in_array( 'tax', $customer->zacctmgr_commission_existing_exclude_options );
								$existing_order_exclude_shipping_costs      = in_array( 'shipping', $customer->zacctmgr_commission_existing_exclude_options );
								$existing_order_exclude_shipping_tax_amount = in_array( 'shipping_tax', $customer->zacctmgr_commission_existing_exclude_options );

							}
							$result = $wpdb->get_results( "SELECT COUNT(*) AS exist FROM $table_name WHERE
							timestamp=$timestamp AND 
							manager_id=$manager_id AND 
							customer_id=$customer_id AND 
							no_commission=$no_commission AND 
							order_level=$order_level AND 
							customer_account_level=$customer_account_level AND 
							new_order_commission_percentage_type=$new_order_commission_percentage_type AND 
							new_order_commission_fixed_type=$new_order_commission_fixed_type AND 
							new_order_commission_value=$new_order_commission_value AND
							new_order_commission_limit=$new_order_commission_limit AND 
							new_order_exclude_coupon_amount=$new_order_exclude_coupon_amount AND 
							new_order_exclude_taxes_amount=$new_order_exclude_taxes_amount AND 
							new_order_exclude_shipping_costs=$new_order_exclude_shipping_costs AND 
							new_order_exclude_shipping_tax_amount=$new_order_exclude_shipping_tax_amount AND 
							existing_order_commission_percentage_type=$existing_order_commission_percentage_type AND 
							existing_order_commission_fixed_type=$existing_order_commission_fixed_type AND 
							existing_order_commission_value=$existing_order_commission_value AND 
							existing_order_exclude_coupon_amount=$existing_order_exclude_coupon_amount AND 
							existing_order_exclude_taxes_amount=$existing_order_exclude_taxes_amount AND 
							existing_order_exclude_shipping_costs=$existing_order_exclude_shipping_costs AND
							existing_order_exclude_shipping_tax_amount=$existing_order_exclude_shipping_tax_amount
							ORDER BY timestamp ASC LIMIT 1;" );

							if ( $result[0]->exist == 0 ) {
								$commission_entry = array(
									'timestamp'                                  => $timestamp,
									'manager_id'                                 => $manager_id,
									'customer_id'                                => $customer_id,
									'no_commission'                              => $no_commission,
									'order_level'                                => $order_level,
									'customer_account_level'                     => $customer_account_level,
									'new_order_commission_percentage_type'       => $new_order_commission_percentage_type,
									'new_order_commission_fixed_type'            => $new_order_commission_fixed_type,
									'new_order_commission_value'                 => $new_order_commission_value,
									'new_order_commission_limit'                 => $new_order_commission_limit,
									'new_order_exclude_coupon_amount'            => $new_order_exclude_coupon_amount,
									'new_order_exclude_taxes_amount'             => $new_order_exclude_taxes_amount,
									'new_order_exclude_shipping_costs'           => $new_order_exclude_shipping_costs,
									'new_order_exclude_shipping_tax_amount'      => $new_order_exclude_shipping_tax_amount,
									'existing_order_commission_percentage_type'  => $existing_order_commission_percentage_type,
									'existing_order_commission_fixed_type'       => $existing_order_commission_fixed_type,
									'existing_order_commission_value'            => $existing_order_commission_value,
									'existing_order_exclude_coupon_amount'       => $existing_order_exclude_coupon_amount,
									'existing_order_exclude_taxes_amount'        => $existing_order_exclude_taxes_amount,
									'existing_order_exclude_shipping_costs'      => $existing_order_exclude_shipping_costs,
									'existing_order_exclude_shipping_tax_amount' => $existing_order_exclude_shipping_tax_amount
								);

								zacctmgr_insert_commission_entry( $commission_entry );
							}
						} elseif ( $customer->zacctmgr_commission_type == 'order_level' ) {
							$last_order_level_rates = $wpdb->get_results( "SELECT * FROM $table_name WHERE
								manager_id=$manager_id AND 
								customer_id IS NULL AND
								no_commission=0 AND 
								order_level=1 AND 
								customer_account_level=0 
								ORDER BY timestamp DESC LIMIT 1;" );
							if ( count( $last_order_level_rates ) != 0 ) {
								$timestamp                                  = current_time( 'mysql' );
								$manager_id                                 = $manager->ID;
								$customer_id                                = $customers_id;
								$no_commission                              = 0;
								$order_level                                = 1;
								$customer_account_level                     = 1;
								$new_order_commission_percentage_type       = $last_order_level_rates[0]->new_order_commission_percentage_type;
								$new_order_commission_fixed_type            = $last_order_level_rates[0]->new_order_commission_fixed_type;
								$new_order_commission_value                 = $last_order_level_rates[0]->new_order_commission_value;
								$new_order_commission_limit                 = $last_order_level_rates[0]->new_order_commission_limit;
								$new_order_exclude_coupon_amount            = $last_order_level_rates[0]->new_order_exclude_coupon_amount;
								$new_order_exclude_taxes_amount             = $last_order_level_rates[0]->new_order_exclude_taxes_amount;
								$new_order_exclude_shipping_costs           = $last_order_level_rates[0]->new_order_exclude_shipping_costs;
								$new_order_exclude_shipping_tax_amount      = $last_order_level_rates[0]->new_order_exclude_shipping_tax_amount;
								$existing_order_commission_percentage_type  = $last_order_level_rates[0]->existing_order_commission_percentage_type;
								$existing_order_commission_fixed_type       = $last_order_level_rates[0]->existing_order_commission_fixed_type;
								$existing_order_commission_value            = $last_order_level_rates[0]->existing_order_commission_value;
								$existing_order_exclude_coupon_amount       = $last_order_level_rates[0]->existing_order_exclude_coupon_amount;
								$existing_order_exclude_taxes_amount        = $last_order_level_rates[0]->existing_order_exclude_taxes_amount;
								$existing_order_exclude_shipping_costs      = $last_order_level_rates[0]->existing_order_exclude_shipping_costs;
								$existing_order_exclude_shipping_tax_amount = $last_order_level_rates[0]->existing_order_exclude_shipping_tax_amount;

								$result = $wpdb->get_results( "SELECT COUNT(*) AS exist FROM $table_name WHERE
									timestamp=$timestamp AND 
									manager_id=$manager_id AND 
									customer_id=$customer_id AND 
									no_commission=$no_commission AND 
									order_level=$order_level AND 
									customer_account_level=$customer_account_level AND 
									new_order_commission_percentage_type=$new_order_commission_percentage_type AND 
									new_order_commission_fixed_type=$new_order_commission_fixed_type AND 
									new_order_commission_value=$new_order_commission_value AND
									new_order_commission_limit=$new_order_commission_limit AND 
									new_order_exclude_coupon_amount=$new_order_exclude_coupon_amount AND 
									new_order_exclude_taxes_amount=$new_order_exclude_taxes_amount AND 
									new_order_exclude_shipping_costs=$new_order_exclude_shipping_costs AND 
									new_order_exclude_shipping_tax_amount=$new_order_exclude_shipping_tax_amount AND 
									existing_order_commission_percentage_type=$existing_order_commission_percentage_type AND 
									existing_order_commission_fixed_type=$existing_order_commission_fixed_type AND 
									existing_order_commission_value=$existing_order_commission_value AND 
									existing_order_exclude_coupon_amount=$existing_order_exclude_coupon_amount AND 
									existing_order_exclude_taxes_amount=$existing_order_exclude_taxes_amount AND 
									existing_order_exclude_shipping_costs=$existing_order_exclude_shipping_costs AND
									existing_order_exclude_shipping_tax_amount=$existing_order_exclude_shipping_tax_amount
									ORDER BY timestamp ASC LIMIT 1;" );

								if ( $result[0]->exist == 0 ) {
									$commission_entry = array(
										'timestamp'                                  => $timestamp,
										'manager_id'                                 => $manager_id,
										'customer_id'                                => $customer_id,
										'no_commission'                              => $no_commission,
										'order_level'                                => $order_level,
										'customer_account_level'                     => $customer_account_level,
										'new_order_commission_percentage_type'       => $new_order_commission_percentage_type,
										'new_order_commission_fixed_type'            => $new_order_commission_fixed_type,
										'new_order_commission_value'                 => $new_order_commission_value,
										'new_order_commission_limit'                 => $new_order_commission_limit,
										'new_order_exclude_coupon_amount'            => $new_order_exclude_coupon_amount,
										'new_order_exclude_taxes_amount'             => $new_order_exclude_taxes_amount,
										'new_order_exclude_shipping_costs'           => $new_order_exclude_shipping_costs,
										'new_order_exclude_shipping_tax_amount'      => $new_order_exclude_shipping_tax_amount,
										'existing_order_commission_percentage_type'  => $existing_order_commission_percentage_type,
										'existing_order_commission_fixed_type'       => $existing_order_commission_fixed_type,
										'existing_order_commission_value'            => $existing_order_commission_value,
										'existing_order_exclude_coupon_amount'       => $existing_order_exclude_coupon_amount,
										'existing_order_exclude_taxes_amount'        => $existing_order_exclude_taxes_amount,
										'existing_order_exclude_shipping_costs'      => $existing_order_exclude_shipping_costs,
										'existing_order_exclude_shipping_tax_amount' => $existing_order_exclude_shipping_tax_amount
									);

									zacctmgr_insert_commission_entry( $commission_entry );
								} else {
									$timestamp                                  = current_time( 'mysql' );
									$manager_id                                 = $manager->ID;
									$customer_id                                = $customers_id;
									$no_commission                              = 0;
									$order_level                                = 1;
									$customer_account_level                     = 1;
									$new_order_commission_percentage_type       = 0;
									$new_order_commission_fixed_type            = 0;
									$new_order_commission_value                 = 0;
									$new_order_commission_limit                 = 1;
									$new_order_exclude_coupon_amount            = 0;
									$new_order_exclude_taxes_amount             = 0;
									$new_order_exclude_shipping_costs           = 0;
									$new_order_exclude_shipping_tax_amount      = 0;
									$existing_order_commission_percentage_type  = 0;
									$existing_order_commission_fixed_type       = 0;
									$existing_order_commission_value            = 0;
									$existing_order_exclude_coupon_amount       = 0;
									$existing_order_exclude_taxes_amount        = 0;
									$existing_order_exclude_shipping_costs      = 0;
									$existing_order_exclude_shipping_tax_amount = 0;

									$result = $wpdb->get_results( "SELECT COUNT(*) AS exist FROM $table_name WHERE
									timestamp=$timestamp AND 
									manager_id=$manager_id AND 
									customer_id=$customer_id AND 
									no_commission=$no_commission AND 
									order_level=$order_level AND 
									customer_account_level=$customer_account_level AND 
									new_order_commission_percentage_type=$new_order_commission_percentage_type AND 
									new_order_commission_fixed_type=$new_order_commission_fixed_type AND 
									new_order_commission_value=$new_order_commission_value AND
									new_order_commission_limit=$new_order_commission_limit AND 
									new_order_exclude_coupon_amount=$new_order_exclude_coupon_amount AND 
									new_order_exclude_taxes_amount=$new_order_exclude_taxes_amount AND 
									new_order_exclude_shipping_costs=$new_order_exclude_shipping_costs AND 
									new_order_exclude_shipping_tax_amount=$new_order_exclude_shipping_tax_amount AND 
									existing_order_commission_percentage_type=$existing_order_commission_percentage_type AND 
									existing_order_commission_fixed_type=$existing_order_commission_fixed_type AND 
									existing_order_commission_value=$existing_order_commission_value AND 
									existing_order_exclude_coupon_amount=$existing_order_exclude_coupon_amount AND 
									existing_order_exclude_taxes_amount=$existing_order_exclude_taxes_amount AND 
									existing_order_exclude_shipping_costs=$existing_order_exclude_shipping_costs AND
									existing_order_exclude_shipping_tax_amount=$existing_order_exclude_shipping_tax_amount
									ORDER BY timestamp ASC LIMIT 1;" );

									if ( $result[0]->exist == 0 ) {
										$commission_entry = array(
											'timestamp'                                  => $timestamp,
											'manager_id'                                 => $manager_id,
											'customer_id'                                => $customer_id,
											'no_commission'                              => $no_commission,
											'order_level'                                => $order_level,
											'customer_account_level'                     => $customer_account_level,
											'new_order_commission_percentage_type'       => $new_order_commission_percentage_type,
											'new_order_commission_fixed_type'            => $new_order_commission_fixed_type,
											'new_order_commission_value'                 => $new_order_commission_value,
											'new_order_commission_limit'                 => $new_order_commission_limit,
											'new_order_exclude_coupon_amount'            => $new_order_exclude_coupon_amount,
											'new_order_exclude_taxes_amount'             => $new_order_exclude_taxes_amount,
											'new_order_exclude_shipping_costs'           => $new_order_exclude_shipping_costs,
											'new_order_exclude_shipping_tax_amount'      => $new_order_exclude_shipping_tax_amount,
											'existing_order_commission_percentage_type'  => $existing_order_commission_percentage_type,
											'existing_order_commission_fixed_type'       => $existing_order_commission_fixed_type,
											'existing_order_commission_value'            => $existing_order_commission_value,
											'existing_order_exclude_coupon_amount'       => $existing_order_exclude_coupon_amount,
											'existing_order_exclude_taxes_amount'        => $existing_order_exclude_taxes_amount,
											'existing_order_exclude_shipping_costs'      => $existing_order_exclude_shipping_costs,
											'existing_order_exclude_shipping_tax_amount' => $existing_order_exclude_shipping_tax_amount
										);

										zacctmgr_insert_commission_entry( $commission_entry );
									}
								}
							}
						}
					}

				}
			}

		}

	}

?>
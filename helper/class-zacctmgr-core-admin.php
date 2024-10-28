<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	class ZACCTMGR_Core_Admin {
		private $initiated = false;
		public $ranges = [];

		public function __construct() {
			if ( ! $this->initiated ) {
				$this->init_hooks();
			}
			$this->ranges = array(
				'all'        => __( 'All', 'woocommerce' ),
				'year'       => __( 'Year', 'woocommerce' ),
				'last_month' => __( 'Last month', 'woocommerce' ),
				'month'      => __( 'This month', 'woocommerce' ),
				'7day'       => __( 'Last 7 days', 'woocommerce' ),
			);
		}


		public function init_hooks() {
			$this->initiated = true;

			add_action( 'admin_init', array( $this, 'admin_init' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'load_resources' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) ); // Add Menu Item
			add_action( 'user_new_form', array(
				$this,
				'custom_user_profile_fields'
			) ); // Custom Fields in New User Form
			add_action( 'edit_user_profile', array( $this, 'custom_user_profile_fields' ) ); // User Profile View

			add_action( 'user_edit_form_tag', array( $this, 'edit_others_commission' ) ); // User Profile View

			add_action( 'user_register', array(
				$this,
				'save_custom_user_fields_new_user'
			) ); // User Registration Workflow
			add_action( 'edit_user_profile_update', array( $this, 'save_custom_user_fields' ) ); // User Profile Update

			add_filter( 'manage_users_columns', array( $this, 'modify_user_table' ) ); // All Users Page
			add_filter( 'manage_users_custom_column', array( $this, 'new_modify_user_table_row' ), 10, 3 );
			add_filter( 'user_row_actions', array( $this, 'modify_user_actions' ), 10, 2 );

			/* Ajax Setting */
			add_action( 'wp_ajax_search_customer', array( $this, 'ajax_search_customer' ) );
			add_action( 'wp_ajax_search_manager', array( $this, 'ajax_search_manager' ) );
			add_action( 'wp_ajax_get_em_users', array( $this, 'ajax_get_eligible_managers' ) );

			/* Form Post */
			add_action( 'admin_post_zacctmgr_edit_commission', array( $this, 'edit_commission' ) );
			add_action( 'admin_post_zacctmgr_edit_settings', array( $this, 'edit_settings' ) );
			add_action( 'admin_post_zacctmgr_factory_reset', array( $this, 'factory_reset' ) );
			add_action( 'admin_post_zacctmgr_export_overview', array( $this, 'export_overview' ) );
			add_action( 'admin_post_zacctmgr_edit_order_commission', array( $this, 'edit_order_commission' ) );
			add_action( 'admin_post_zacctmgr_recalculate_order_commission', array(
				$this,
				'recalculate_order_commission'
			) );


			add_action( 'restrict_manage_posts', array( $this, 'account_manager_order_filters' ) );
			add_action( 'pre_get_posts', array( $this, 'apply_account_manager_order_filters' ) );

		}

		public function admin_init() {
		}

		public function load_resources() {
			global $wp_scripts;

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			wp_enqueue_script( 'jquery-ui-datepicker' );

			// You need styling for the datepicker. For simplicity I've linked to Google's hosted jQuery UI CSS.
			wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'jquery-ui' );
			// Register admin styles.
			if ( class_exists( 'WooCommerce' ) ) {
				wp_register_style( 'woocommerce_admin_menu_styles', WC()->plugin_url() . '/assets/css/menu.css', array(), WC_VERSION );
				wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
				wp_register_style( 'jquery-ui-style', WC()->plugin_url() . '/assets/css/jquery-ui/jquery-ui.min.css', array(), WC_VERSION );
				wp_register_style( 'woocommerce_admin_dashboard_styles', WC()->plugin_url() . '/assets/css/dashboard.css', array(), WC_VERSION );
				wp_register_style( 'woocommerce_admin_print_reports_styles', WC()->plugin_url() . '/assets/css/reports-print.css', array(), WC_VERSION, 'print' );
			}
			wp_register_style( 'select2_style', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css', array(), '1.0.0' );
			wp_register_style( 'zacctmgr_style', plugins_url( 'css/style.css', ZACCTMGR_BASE_FILE ), array(), '1.0.1' );

			// Add RTL support for admin styles.
			wp_style_add_data( 'woocommerce_admin_menu_styles', 'rtl', 'replace' );
			wp_style_add_data( 'woocommerce_admin_styles', 'rtl', 'replace' );
			wp_style_add_data( 'woocommerce_admin_dashboard_styles', 'rtl', 'replace' );
			wp_style_add_data( 'woocommerce_admin_print_reports_styles', 'rtl', 'replace' );

			// Sitewide menu CSS.
			wp_enqueue_style( 'woocommerce_admin_menu_styles' );

			// Admin styles for WC pages only.
			if ( in_array( $screen_id, [ 'toplevel_page_zacctmgr', 'account-manager_page_zacctmgr_commission' ] ) ) {
				wp_enqueue_style( 'woocommerce_admin_styles' );
				wp_enqueue_style( 'jquery-ui-style' );
				wp_enqueue_style( 'wp-color-picker' );

				wp_enqueue_style( 'woocommerce_admin_dashboard_styles' );
				wp_enqueue_style( 'woocommerce_admin_print_reports_styles' );
			}

			// @deprecated 2.3.
			if ( has_action( 'woocommerce_admin_css' ) ) {
				do_action( 'woocommerce_admin_css' );
				wc_deprecated_function( 'The woocommerce_admin_css action', '2.3', 'admin_enqueue_scripts' );
			}

			wp_enqueue_style( 'select2_style' );
			wp_enqueue_style( 'zacctmgr_style' );

			$register_scripts = array(
				'select2_script'  => array(
					'src'     => plugins_url( 'js/select2.js', ZACCTMGR_BASE_FILE ),
					'deps'    => array( 'jquery' ),
					'version' => '1.0.0'
				),
				'zacctmgr_script' => array(
					'src'     => plugins_url( 'js/script.js', ZACCTMGR_BASE_FILE ),
					'deps'    => array( 'jquery' ),
					'version' => '1.0.1',
				)
			);

			foreach ( $register_scripts as $name => $props ) {
				wp_register_script( $name, $props['src'], $props['deps'], $props['version'], true );
			}
			wp_enqueue_script( 'select2_script' );
			wp_enqueue_script( 'zacctmgr_script' );

			wp_localize_script( 'ajax-script', 'ajax_object', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'we_value' => 1234
			) );
		}

		public function admin_menu() {
			add_menu_page( 'Account Manager', 'Account Manager', 'manage_options', 'zacctmgr', array(
				$this,
				'show_zacctmgr'
			), 'dashicons-image-filter', 70 );
			add_submenu_page( 'zacctmgr', 'Account Manager', 'Overview', 'manage_options', 'zacctmgr', array(
				$this,
				'show_zacctmgr'
			) );
			add_submenu_page( 'zacctmgr', 'Insights', 'Insights', 'manage_options', 'zacctmgr_insights', array(
				$this,
				'show_zacctmgr_insights'
			) );
			add_submenu_page( 'zacctmgr', 'Commission', 'Commission', 'manage_options', 'zacctmgr_commission', array(
				$this,
				'show_zacctmgr_commission'
			) );
			if ( $this->zacctmgr_show_settings_menu() ) {
				add_submenu_page( 'zacctmgr', 'Settings', 'Settings', 'manage_options', 'zacctmgr_settings', array(
					$this,
					'show_zacctmgr_settings'
				) );
			}
		}

		public function zacctmgr_show_settings_menu() {
			$zacctmgr_hide_settings_in_menu = zacctmgr_get_hide_settings_in_menu();
			$zacctmgr_user_access_settings  = zacctmgr_get_user_access_settings();
			if ( $zacctmgr_hide_settings_in_menu == 0 ) {
				return true;
			}

			if ( $zacctmgr_user_access_settings == 'administrators' ) {
				return current_user_can( 'administrator' );
			}

			if ( $zacctmgr_user_access_settings == 'manage_options' ) {
				return current_user_can( 'manage_options' );
			}

			if ( is_numeric( $zacctmgr_user_access_settings ) ) {
				return get_current_user_id() == $zacctmgr_user_access_settings;
			}
		}

		public function zacctmgr_allow_edit_settings() {
			$zacctmgr_user_access_settings = zacctmgr_get_user_access_settings();

			if ( $zacctmgr_user_access_settings == 'administrators' ) {
				return current_user_can( 'administrator' );
			}

			if ( $zacctmgr_user_access_settings == 'manage_options' ) {
				return current_user_can( 'manage_options' );
			}

			if ( is_numeric( $zacctmgr_user_access_settings ) ) {
				return get_current_user_id() == $zacctmgr_user_access_settings;
			}
		}


		public function show_zacctmgr() { // Overview Page
			include_once( ZACCTMGR_PLUGIN_DIR . 'template/overview.php' );
		}

		public function show_zacctmgr_commission() { // Commission Page
			include_once( ZACCTMGR_PLUGIN_DIR . 'template/commissions.php' );
		}

		public function show_zacctmgr_settings() { // Settings Page
			include_once( ZACCTMGR_PLUGIN_DIR . 'template/settings.php' );
		}

		public function show_zacctmgr_insights() { // Insights Page
			include_once( ZACCTMGR_PLUGIN_DIR . 'template/insights.php' );
		}

		public function show_filters() {
			if ( ! isset( $_GET['post_type'] ) || $_GET['post_type'] != 'shop_order' ) {
				return false;
			}

			$manager_id   = isset( $_REQUEST['zacctmgr_filter_wc'] ) ? (int) $_REQUEST['zacctmgr_filter_wc'] : 0;
			$manager_data = $manager_id != 0 ? get_user_by( 'id', $manager_id ) : null;

			$output = '';

			$output .= '<select name="zacctmgr_filter_wc" id="zacctmgr_filter_wc">';
			if ( $manager_data ) {
				$output .= '<option value="' . $manager_data->ID . '" selected="selected">' . $manager_data->first_name . ' ' . $manager_data->last_name . '<option>';
			}
			$output .= '</select>';

			echo $output;
		}

		public function account_manager_order_filters( $post_type ) {

			if ( $post_type == 'shop_order' ) {
				$manager_id   = isset( $_REQUEST['zacctmgr_filter'] ) ? (int) $_REQUEST['zacctmgr_filter'] : 0;
				$manager_data = $manager_id != 0 ? get_user_by( 'id', $manager_id ) : null;

				// Add your filter input here. Make sure the input name matches the $_GET value you are checking above.
				echo '<select name="zacctmgr_filter" id="zacctmgr_filter_wc">';

				echo '<option value>Select an Account Manager</option>';
				if ( $manager_data ) {
					echo '<option value="' . $manager_data->ID . '" selected="selected">' . $manager_data->first_name . ' ' . $manager_data->last_name . '<option>';
				}

				echo '</select>';

			}
		}

		function apply_account_manager_order_filters( $query ) {

			global $pagenow;


			// Ensure it is an edit.php admin page, the filter exists and has a value, and that it's the orders page
			if ( $query->is_admin && $pagenow == 'edit.php' && isset( $_GET['zacctmgr_filter'] ) && $_GET['zacctmgr_filter'] != '' && $_GET['post_type'] == 'shop_order' ) {

				$manager = get_user_by( 'id', $_GET['zacctmgr_filter'] );

				$meta_key_query = array(
					array(
						'key'     => '_account_manager',
						'value'   => $manager->display_name,
						'compare' => '='
					)
				);
				$query->set( 'meta_query', $meta_key_query );

			}

		}

		public function recalculate_order_commission() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			if ( isset( $_GET['order_id'] ) ) {
				$order    = wc_get_order( $_GET['order_id'] );
				$status   = $order->get_status();
				$order_id = (int) $order->get_id();

				if ( $status != 'auto-draft' ) {
					global $wpdb;

					$current_manager = get_post_meta( $order_id, '_account_manager', true );
					$new_manager     = $_GET['account_manager'];

					if ( $current_manager != $new_manager ) {
						update_post_meta( $order_id, '_account_manager', $new_manager );
					}

					$table_name = $wpdb->prefix . 'zacctmgr_acm_order_audit_mapping';

					$data     = zacctmgr_get_total_commission_by_order( $order );
					$old_data = str_replace( ',', '', get_post_meta( $order_id, '_commission', true ) );

					if ( $data != $old_data ) {
						$query = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_id=$order_id AND old_value=$old_data AND new_value=$data AND is_commission_change=1 ORDER BY timestamp DESC LIMIT 1;" );
						if ( count( $query ) == 0 ) {
							$wpdb->insert(
								$table_name,
								array(
									'timestamp'            => current_time( 'mysql' ),
									'user_id'              => get_current_user_id(),
									'order_id'             => $order_id,
									'old_value'            => $old_data,
									'new_value'            => $data,
									'action'               => 'Automatically recalculated commission',
									'is_commission_change' => 1,
									'is_manual_change'     => 0
								)
							);
						}
					}

					update_post_meta( $order_id, '_commission', $data );
				}
			}

			wp_redirect( 'admin.php?page=zacctmgr_commission&tab=orders&edit=' . $order_id );
			exit();
		}

		public function show_zacctmgr_insights_tab( $current ) {
			$tabs = array(
				'customers'       => 'Customers',
				'account_manager' => 'Account Manager'
			);

			$html = '<h2 class="nav-tab-wrapper">';
			foreach ( $tabs as $tab => $name ) {
				$class = ( $tab == $current ) ? 'nav-tab-active' : '';
				$html  .= '<a class="nav-tab ' . $class . '" href="?page=zacctmgr_insights&tab=' . $tab . '">' . $name . '</a>';
			}
			$html .= '</h2>';

			echo $html;
		}

		public function show_zacctmgr_commissions_tab( $current ) {

			if ( zacctmgr_allow_edit_commission() && zacctmgr_allow_edit_order() ) {
				$tabs = array(
					'my_commission'    => 'My Commission',
					'orders'           => 'Orders',
					'account_managers' => 'Account Managers',
				);
			}

			if ( zacctmgr_allow_edit_commission() && ! zacctmgr_allow_edit_order() ) {
				$tabs = array(
					'my_commission'    => 'My Commission',
					'account_managers' => 'Account Managers'
				);
			}

			if ( ! zacctmgr_allow_edit_commission() && zacctmgr_allow_edit_order() ) {
				$tabs = array(
					'my_commission' => 'My Commission',
					'orders'        => 'Orders'
				);
			}
			if ( ! zacctmgr_allow_edit_commission() && ! zacctmgr_allow_edit_order() ) {
				$tabs = array(
					'my_commission' => 'My Commission'
				);
			}


			$html = '<h2 class="nav-tab-wrapper">';
			foreach ( $tabs as $tab => $name ) {
				$class = ( $tab == $current ) ? 'nav-tab-active' : '';
				$html  .= '<a class="nav-tab ' . $class . '" href="?page=zacctmgr_commission&tab=' . $tab . '">' . $name . '</a>';
			}
			$html .= '</h2>';

			echo $html;
		}

		public function edit_others_commission() {
			if ( get_current_screen()->id != 'profile' ) {
				if ( zacctmgr_can_view_customer( $_GET['user_id'] ) == false ) {
					$o = '<div class="zacctmgr_not_allowed_wrap">';
					$o .= '<p>Sorry, you are not allowed to edit this user.</p>';
					$o .= '</div>';
					wp_die( $o, 403 );
				}
			}
		}

		public function custom_user_profile_fields( $user ) {
			include_once( ZACCTMGR_PLUGIN_DIR . 'template/user/edit.php' );
		}

		public function modify_user_table( $column ) {
			$n_column = array();

			foreach ( $column as $key => $value ) {
				if ( $key == 'posts' ) {
					$n_column['zacctmgr'] = 'Account Manager';
				}

				$n_column[ $key ] = $value;
			}


			return $n_column;
		}

		public function new_modify_user_table_row( $val, $column_name, $user_id ) {
			switch ( $column_name ) {
				case 'zacctmgr':
					$manager_id = zacctmgr_get_manager_id( $user_id );
					if ( $manager_id != 0 ) {
						$first_name = get_user_meta( $manager_id, 'first_name', true );
						$last_name  = get_user_meta( $manager_id, 'last_name', true );

						if ( $first_name == '' && $last_name == '' ) {
							return '-';
						} else {
							return $first_name . ' ' . $last_name;
						}
					}

					return '';
					break;
				default:
			}

			return $val;
		}

		public function modify_user_actions( $actions, $user ) {
			if ( zacctmgr_can_edit_customer_commission( $user->ID ) == true ) {
				return $actions;
			}

			return array();
		}

		public function save_custom_user_fields( $user_id ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}


			if ( isset( $_POST['zacctmgr_commission_type'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_type', sanitize_text_field( $_POST['zacctmgr_commission_type'] ) );
			}

			if ( isset( $_POST['commission_new_value'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_new_value', (float) $_POST['commission_new_value'] );
			}
			if ( isset( $_POST['commission_existing_value'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_existing_value', (float) $_POST['commission_existing_value'] );
			}

			if ( isset( $_POST['commission_new_type'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_new_type', sanitize_text_field( $_POST['commission_new_type'] ) );
			}
			if ( isset( $_POST['commission_existing_type'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_existing_type', sanitize_text_field( $_POST['commission_existing_type'] ) );
			}

			if ( isset( $_POST['commission_order_count'] ) && (int) $_POST['commission_order_count'] > 0 ) {
				update_user_meta( $user_id, 'zacctmgr_commission_order_count', (int) $_POST['commission_order_count'] );
			}

			if ( isset( $_POST['commission_new_exclude_options'] ) && is_array( $_POST['commission_new_exclude_options'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_new_exclude_options', zacctmgr_sanitize_array( $_POST['commission_new_exclude_options'] ) );
			}
			if ( isset( $_POST['commission_existing_exclude_options'] ) && is_array( $_POST['commission_existing_exclude_options'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_existing_exclude_options', zacctmgr_sanitize_array( $_POST['commission_existing_exclude_options'] ) );
			}

			$current_manager_id = zacctmgr_get_manager_id( $user_id );

			if ( $current_manager_id == $_POST['zacctmgr_select'] ) {
				if ( isset( $_POST['zacctmgr_commission_type'] ) ) {
					$manager_id = zacctmgr_get_manager_id( $user_id );

					$manager = get_user_by( 'id', $manager_id );

					if ( $_POST['zacctmgr_commission_type'] == 'no_commission' ) {
						$commission_entry = array(
							'timestamp'                                  => current_time( 'mysql' ),
							'manager_id'                                 => $manager_id,
							'customer_id'                                => $user_id,
							'no_commission'                              => 1,
							'order_level'                                => 0,
							'customer_account_level'                     => 1,
							'new_order_commission_percentage_type'       => 0,
							'new_order_commission_fixed_type'            => 0,
							'new_order_commission_value'                 => 0,
							'new_order_commission_limit'                 => 1,
							'new_order_exclude_coupon_amount'            => 0,
							'new_order_exclude_taxes_amount'             => 0,
							'new_order_exclude_shipping_costs'           => 0,
							'new_order_exclude_shipping_tax_amount'      => 0,
							'existing_order_commission_percentage_type'  => 0,
							'existing_order_commission_fixed_type'       => 0,
							'existing_order_commission_value'            => 0,
							'existing_order_exclude_coupon_amount'       => 0,
							'existing_order_exclude_taxes_amount'        => 0,
							'existing_order_exclude_shipping_costs'      => 0,
							'existing_order_exclude_shipping_tax_amount' => 0
						);
						zacctmgr_insert_commission_entry( $commission_entry );
					} else {
						if ( $_POST['zacctmgr_commission_type'] == 'order_level' ) {

							global $wpdb;
							$table_name = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';

							$manager_rate = $wpdb->get_results( "SELECT * FROM $table_name WHERE manager_id=$manager_id AND customer_id IS NULL AND customer_account_level=1 ORDER BY timestamp DESC;" );

							if ( count( $manager_rate ) != 0 ) {
								$commission_entry = array(
									'timestamp'                                  => current_time( 'mysql' ),
									'manager_id'                                 => $manager_id,
									'customer_id'                                => $user_id,
									'no_commission'                              => 0,
									'order_level'                                => 1,
									'customer_account_level'                     => 1,
									'new_order_commission_percentage_type'       => $manager_rate[0]->new_order_commission_percentage_type,
									'new_order_commission_fixed_type'            => $manager_rate[0]->new_order_commission_fixed_type,
									'new_order_commission_value'                 => $manager_rate[0]->new_order_commission_value,
									'new_order_commission_limit'                 => $manager_rate[0]->new_order_commission_limit,
									'new_order_exclude_coupon_amount'            => $manager_rate[0]->new_order_exclude_coupon_amount,
									'new_order_exclude_taxes_amount'             => $manager_rate[0]->new_order_exclude_taxes_amount,
									'new_order_exclude_shipping_costs'           => $manager_rate[0]->new_order_exclude_shipping_costs,
									'new_order_exclude_shipping_tax_amount'      => $manager_rate[0]->new_order_exclude_shipping_tax_amount,
									'existing_order_commission_percentage_type'  => $manager_rate[0]->existing_order_commission_percentage_type,
									'existing_order_commission_fixed_type'       => $manager_rate[0]->existing_order_commission_fixed_type,
									'existing_order_commission_value'            => $manager_rate[0]->existing_order_commission_value,
									'existing_order_exclude_coupon_amount'       => $manager_rate[0]->existing_order_exclude_coupon_amount,
									'existing_order_exclude_taxes_amount'        => $manager_rate[0]->existing_order_exclude_taxes_amount,
									'existing_order_exclude_shipping_costs'      => $manager_rate[0]->existing_order_exclude_shipping_costs,
									'existing_order_exclude_shipping_tax_amount' => $manager_rate[0]->existing_order_exclude_shipping_tax_amount,
								);
								zacctmgr_insert_commission_entry( $commission_entry );
							}
						} else {
							$commission_entry = array(
								'timestamp'                                  => current_time( 'mysql' ),
								'manager_id'                                 => $manager_id,
								'customer_id'                                => $user_id,
								'no_commission'                              => 0,
								'order_level'                                => 0,
								'customer_account_level'                     => 1,
								'new_order_commission_percentage_type'       => $_POST['commission_new_type'] == 'percentage' ? 1 : 0,
								'new_order_commission_fixed_type'            => $_POST['commission_new_type'] == 'fixed' ? 1 : 0,
								'new_order_commission_value'                 => (float) $_POST['commission_new_value'],
								'new_order_commission_limit'                 => (int) $_POST['commission_order_count'],
								'new_order_exclude_coupon_amount'            => in_array( 'coupon', $_POST['commission_new_exclude_options'] ) ? 1 : 0,
								'new_order_exclude_taxes_amount'             => in_array( 'tax', $_POST['commission_new_exclude_options'] ) ? 1 : 0,
								'new_order_exclude_shipping_costs'           => in_array( 'shipping', $_POST['commission_new_exclude_options'] ) ? 1 : 0,
								'new_order_exclude_shipping_tax_amount'      => in_array( 'shipping_tax', $_POST['commission_new_exclude_options'] ) ? 1 : 0,
								'existing_order_commission_percentage_type'  => $_POST['commission_existing_type'] == 'percentage' ? 1 : 0,
								'existing_order_commission_fixed_type'       => $_POST['commission_existing_type'] == 'fixed' ? 1 : 0,
								'existing_order_commission_value'            => (float) $_POST['commission_existing_value'],
								'existing_order_exclude_coupon_amount'       => in_array( 'coupon', $_POST['commission_existing_exclude_options'] ) ? 1 : 0,
								'existing_order_exclude_taxes_amount'        => in_array( 'tax', $_POST['commission_existing_exclude_options'] ) ? 1 : 0,
								'existing_order_exclude_shipping_costs'      => in_array( 'shipping', $_POST['commission_existing_exclude_options'] ) ? 1 : 0,
								'existing_order_exclude_shipping_tax_amount' => in_array( 'shipping-tax', $_POST['commission_existing_exclude_options'] ) ? 1 : 0,
							);
							zacctmgr_insert_commission_entry( $commission_entry );
						}
					}
				}
			}

			if ( isset( $_POST['zacctmgr_select'] ) ) {
				zacctmgr_set_manager_id( $user_id, (int) $_POST['zacctmgr_select'] );
			}

		}

		public function save_custom_user_fields_new_user( $user_id ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			if ( isset( $_POST['zacctmgr_select'] ) ) {
				zacctmgr_set_manager_id( $user_id, (int) $_POST['zacctmgr_select'] );
			}

			if ( isset( $_POST['zacctmgr_commission_type'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_type', sanitize_text_field( $_POST['zacctmgr_commission_type'] ) );
			}

			if ( isset( $_POST['commission_new_value'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_new_value', (float) $_POST['commission_new_value'] );
			}
			if ( isset( $_POST['commission_existing_value'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_existing_value', (float) $_POST['commission_existing_value'] );
			}

			if ( isset( $_POST['commission_new_type'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_new_type', sanitize_text_field( $_POST['commission_new_type'] ) );
			}
			if ( isset( $_POST['commission_existing_type'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_existing_type', sanitize_text_field( $_POST['commission_existing_type'] ) );
			}

			if ( isset( $_POST['commission_order_count'] ) && (int) $_POST['commission_order_count'] > 0 ) {
				update_user_meta( $user_id, 'zacctmgr_commission_order_count', (int) $_POST['commission_order_count'] );
			}

			if ( isset( $_POST['commission_new_exclude_options'] ) && is_array( $_POST['commission_new_exclude_options'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_new_exclude_options', zacctmgr_sanitize_array( $_POST['commission_new_exclude_options'] ) );
			}
			if ( isset( $_POST['commission_existing_exclude_options'] ) && is_array( $_POST['commission_existing_exclude_options'] ) ) {
				update_user_meta( $user_id, 'zacctmgr_commission_existing_exclude_options', zacctmgr_sanitize_array( $_POST['commission_existing_exclude_options'] ) );
			}

			$allowed_roles = zacctmgr_get_selected_roles();
			if ( in_array( $_POST['role'], $allowed_roles ) ) {
				$commission_entry = array(
					'timestamp'                                  => current_time( 'mysql' ),
					'manager_id'                                 => $user_id,
					'customer_id'                                => null,
					'no_commission'                              => 0,
					'order_level'                                => 1,
					'customer_account_level'                     => 0,
					'new_order_commission_percentage_type'       => 0,
					'new_order_commission_fixed_type'            => 0,
					'new_order_commission_value'                 => 0,
					'new_order_commission_limit'                 => 1,
					'new_order_exclude_coupon_amount'            => 0,
					'new_order_exclude_taxes_amount'             => 0,
					'new_order_exclude_shipping_costs'           => 0,
					'new_order_exclude_shipping_tax_amount'      => 0,
					'existing_order_commission_percentage_type'  => 0,
					'existing_order_commission_fixed_type'       => 0,
					'existing_order_commission_value'            => 0,
					'existing_order_exclude_coupon_amount'       => 0,
					'existing_order_exclude_taxes_amount'        => 0,
					'existing_order_exclude_shipping_costs'      => 0,
					'existing_order_exclude_shipping_tax_amount' => 0
				);
				zacctmgr_insert_commission_entry( $commission_entry );
			}


		}

		public function edit_settings() {
			if ( ! $this->zacctmgr_allow_edit_settings() ) {
				return false;
			}

			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'zacctmgr_edit_settings' ) ) {
				wp_redirect( 'admin.php?page=zacctmgr_settings' );
				exit();
			}

			if ( isset( $_POST['zacctmgr_roles'] ) ) {
				$selected_roles = $_POST['zacctmgr_roles'];
				if ( $selected_roles && is_array( $selected_roles ) && count( $selected_roles ) > 0 ) {
					update_option( 'zacctmgr_selected_roles', zacctmgr_sanitize_array( $selected_roles ) );
				}
			}

			if ( isset( $_POST['zacctmgr_default'] ) ) {
				update_option( 'zacctmgr_default', (int) $_POST['zacctmgr_default'] );
			}

			if ( isset( $_POST['zacctmgr_order_recalculate_commission'] ) ) {
				update_option( 'zacctmgr_order_recalculate_commission', $_POST['zacctmgr_order_recalculate_commission'] );
			}


			if ( isset( $_POST['zacctmgr_refund_commission_setting'] ) ) {
				update_option( 'zacctmgr_refund_commission_setting', $_POST['zacctmgr_refund_commission_setting'] );
			}

			if ( isset( $_POST['zacctmgr_allowed_woo_status'] ) && is_array( $_POST['zacctmgr_allowed_woo_status'] ) ) {
				update_option( 'zacctmgr_allowed_woo_statuses', zacctmgr_sanitize_array( $_POST['zacctmgr_allowed_woo_status'] ) );
			}

			if ( isset( $_POST['zacctmgr_allowed_edit_order_commission_users'] ) && is_array( $_POST['zacctmgr_allowed_edit_order_commission_users'] ) ) {
				update_option( 'zacctmgr_allowed_edit_order_commission_users', zacctmgr_sanitize_array( $_POST['zacctmgr_allowed_edit_order_commission_users'] ) );
			}

			if ( isset ( $_POST['zacctmgr_user_allow_edit_order_commission_setting'] ) ) {
				update_option( 'zacctmgr_user_allow_edit_order_commission_setting', $_POST['zacctmgr_user_allow_edit_order_commission_setting'] );
			}

			if ( isset( $_POST['zacctmgr_allowed_edit_commission_users'] ) && is_array( $_POST['zacctmgr_allowed_edit_commission_users'] ) ) {
				update_option( 'zacctmgr_allowed_edit_commission_users', zacctmgr_sanitize_array( $_POST['zacctmgr_allowed_edit_commission_users'] ) );
			}

			if ( isset ( $_POST['zacctmgr_user_allow_edit_commission_setting'] ) ) {
				update_option( 'zacctmgr_user_allow_edit_commission_setting', $_POST['zacctmgr_user_allow_edit_commission_setting'] );
			}

			if ( isset( $_POST['zacctmgr_allowed_edit_others_commission_users'] ) && is_array( $_POST['zacctmgr_allowed_edit_others_commission_users'] ) ) {
				update_option( 'zacctmgr_allowed_edit_others_commission_users', zacctmgr_sanitize_array( $_POST['zacctmgr_allowed_edit_others_commission_users'] ) );
			}

			if ( isset ( $_POST['zacctmgr_user_allow_edit_others_commission_setting'] ) ) {
				update_option( 'zacctmgr_user_allow_edit_others_commission_setting', $_POST['zacctmgr_user_allow_edit_others_commission_setting'] );
			}

			if ( isset( $_POST['zacctmgr_allowed_no'] ) ) {
				update_option( 'zacctmgr_allowed_no', 1 );
			} else {
				update_option( 'zacctmgr_allowed_no', 0 );
			}

			if ( isset( $_POST['zacctmgr_hide_settings_in_menu'] ) ) {
				update_option( 'zacctmgr_hide_settings_in_menu', 1 );
			} else {
				update_option( 'zacctmgr_hide_settings_in_menu', 0 );
			}

			if ( isset( $_POST['zacctmgr_user_access_settings'] ) ) {
				update_option( 'zacctmgr_user_access_settings', $_POST['zacctmgr_user_access_settings'] );
			}

			wp_redirect( 'admin.php?page=zacctmgr_settings&_wpnonce=' . $_POST['_wpnonce'] );
			exit();
		}

		public function edit_commission() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'zacctmgr_edit_commission' ) ) {
				wp_redirect( 'admin.php?page=zacctmgr_commission' );
				exit();
			}

			if ( ! isset( $_POST['manager_id'] ) || ! isset( $_POST['commission_new_value'] ) || ! isset( $_POST['commission_existing_value'] ) || ! isset( $_POST['commission_new_type'] ) || ! isset( $_POST['commission_existing_type'] ) ) {
				wp_redirect( 'admin.php?page=zacctmgr_commission' );
				exit();
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'zacctmgr_acm_manager_commission_audit_mapping';

			$manager_id = (int) $_POST['manager_id'];

			$commission_type           = sanitize_text_field( $_POST['zacctmgr_commission_type'] );
			$commission_new_value      = (float) $_POST['commission_new_value'];
			$commission_existing_value = (float) $_POST['commission_existing_value'];

			$commission_new_type      = sanitize_text_field( $_POST['commission_new_type'] );
			$commission_existing_type = sanitize_text_field( $_POST['commission_existing_type'] );

			if ( $commission_new_type == 'percentage' && $commission_new_value > 100 ) {
				$commission_new_value = 0;
			}

			if ( $commission_existing_type == 'percentage' && $commission_existing_value > 100 ) {
				$commission_existing_value = 0;
			}

			$commission_order_count = (int) $_POST['commission_order_count'];
			if ( $commission_order_count < 0 ) {
				$commission_order_count = 0;
			}

			if ( $manager_id <= 0 || $commission_new_value < 0 || $commission_existing_value < 0 || $commission_new_type == '' || $commission_existing_type == '' ) {
				wp_redirect( 'admin.php?page=zacctmgr_commission' );
				exit();
			}

			update_user_meta( $manager_id, 'zacctmgr_commission_type', $commission_type );

			update_user_meta( $manager_id, 'zacctmgr_commission_new_value', $commission_new_value );
			update_user_meta( $manager_id, 'zacctmgr_commission_existing_value', $commission_existing_value );

			update_user_meta( $manager_id, 'zacctmgr_commission_new_type', $commission_new_type );
			update_user_meta( $manager_id, 'zacctmgr_commission_existing_type', $commission_existing_type );

			update_user_meta( $manager_id, 'zacctmgr_commission_order_count', $commission_order_count );

			$commission_new_exclude_options = [];
			if ( isset( $_POST['commission_new_exclude_options'] ) && is_array( $_POST['commission_new_exclude_options'] ) ) {
				$commission_new_exclude_options = zacctmgr_sanitize_array( $_POST['commission_new_exclude_options'] );
			}

			$commission_existing_exclude_options = [];
			if ( isset( $_POST['commission_existing_exclude_options'] ) && is_array( $_POST['commission_existing_exclude_options'] ) ) {
				$commission_existing_exclude_options = zacctmgr_sanitize_array( $_POST['commission_existing_exclude_options'] );
			}

			update_user_meta( $manager_id, 'zacctmgr_commission_new_exclude_options', $commission_new_exclude_options );
			update_user_meta( $manager_id, 'zacctmgr_commission_existing_exclude_options', $commission_existing_exclude_options );


			$commission_entry = array(
				'timestamp'                                  => current_time( 'mysql' ),
				'manager_id'                                 => $manager_id,
				'no_commission'                              => $commission_type == 'no_commission' ? 1 : 0,
				'order_level'                                => $commission_type == 'order_level' ? 1 : 0,
				'customer_account_level'                     => $commission_type == 'customer_account_level' ? 1 : 0,
				'new_order_commission_percentage_type'       => $commission_new_type == 'percentage' ? 1 : 0,
				'new_order_commission_fixed_type'            => $commission_new_type == 'fixed' ? 1 : 0,
				'new_order_commission_value'                 => $commission_new_value,
				'new_order_commission_limit'                 => $commission_order_count,
				'new_order_exclude_coupon_amount'            => in_array( 'coupon', $commission_new_exclude_options ) ? 1 : 0,
				'new_order_exclude_taxes_amount'             => in_array( 'tax', $commission_new_exclude_options ) ? 1 : 0,
				'new_order_exclude_shipping_costs'           => in_array( 'shipping', $commission_new_exclude_options ) ? 1 : 0,
				'new_order_exclude_shipping_tax_amount'      => in_array( 'shipping_tax', $commission_new_exclude_options ) ? 1 : 0,
				'existing_order_commission_percentage_type'  => $commission_existing_type == 'percentage' ? 1 : 0,
				'existing_order_commission_fixed_type'       => $commission_existing_type == 'fixed' ? 1 : 0,
				'existing_order_commission_value'            => $commission_existing_value,
				'existing_order_exclude_coupon_amount'       => in_array( 'coupon', $commission_existing_exclude_options ) ? 1 : 0,
				'existing_order_exclude_taxes_amount'        => in_array( 'tax', $commission_existing_exclude_options ) ? 1 : 0,
				'existing_order_exclude_shipping_costs'      => in_array( 'shipping', $commission_existing_exclude_options ) ? 1 : 0,
				'existing_order_exclude_shipping_tax_amount' => in_array( 'shipping_tax', $commission_existing_exclude_options ) ? 1 : 0,
			);

			zacctmgr_insert_commission_entry( $commission_entry );

			$current_commission_calculation_type = $_POST['current_commission_calculation_type'];
			$current_new_order_limit             = $_POST['current_new_order_limit'];
			$current_new_order_type              = $_POST['current_new_order_type'];
			$current_existing_order_type         = $_POST['current_existing_order_type'];
			$current_new_value                   = $_POST['current_new_value'];
			$current_existing_value              = $_POST['current_existing_value'];
			$current_new_exclude                 = $_POST['current_new_exclude'];
			$current_existing_exclude            = $_POST['current_existing_exclude'];


			if ( $current_commission_calculation_type != $commission_type ) {
				$old_value = ucfirst( str_replace( '_', ' ', $current_commission_calculation_type ) ) . ' ';
				$new_value = ucfirst( str_replace( '_', ' ', $commission_type ) ) . ' ';

				$result = $wpdb->get_results( "SELECT * FROM $table_name WHERE is_commission_rate=0 AND manager_id=$manager_id AND old_value=$old_value AND new_value=$new_value ORDER BY timestamp DESC LIMIT 1;" );
				if ( count( $result ) == 0 ) {
					$wpdb->insert( $table_name, array(
						'timestamp'          => current_time( 'mysql' ),
						'user_id'            => get_current_user_id(),
						'manager_id'         => $manager_id,
						'old_value'          => $old_value,
						'new_value'          => $new_value,
						'action'             => 'Edit Commission Calculation Method',
						'is_commission_rate' => 0
					) );
				} else {
					$result = $wpdb->get_results( "SELECT * FROM $table_name WHERE is_commission_rate=0 AND manager_id=$manager_id ORDER BY timestamp DESC LIMIT 1;" );
					if ( $result->old_value == $old_value && $result->new_value == $new_value ) {

					} else {
						$wpdb->insert( $table_name, array(
							'timestamp'          => current_time( 'mysql' ),
							'user_id'            => get_current_user_id(),
							'manager_id'         => $manager_id,
							'old_value'          => $old_value,
							'new_value'          => $new_value,
							'action'             => 'Edit Commission Calculation Method',
							'is_commission_rate' => 0
						) );
					}
				}
			}

			if ( $commission_type != 'no_commission' ) {

				$old_value = '';
				$new_value = '';
				$new_diff  = false;

				if ( $current_new_order_limit != $commission_order_count ) {
					$old_value .= 'New order limit: ' . $current_new_order_limit . ' ';
					$new_value .= 'New order limit: ' . $commission_order_count . ' ';
					$new_diff  = true;
				}

				if ( $current_new_value != $commission_new_value ) {
					$old_value .= 'Value: ' . $current_new_value . ' ';
					$new_value .= 'Value: ' . $commission_new_value . ' ';
					$new_diff  = true;
				}

				if ( $current_new_order_type != $commission_new_type ) {
					$old_value .= 'Type: ' . $current_new_order_type . ' ';
					$new_value .= 'Type: ' . $commission_new_type . ' ';
					$new_diff  = true;
				}

				if ( $current_new_order_type == 'percentage' && $commission_new_type == 'percentage' ) {
					if ( count( array_diff( $current_new_exclude, $commission_new_exclude_options ) ) != 0 || count( array_diff( $commission_new_exclude_options, $current_new_exclude ) ) != 0 ) {
						if ( isset( $current_new_exclude ) && count( $current_new_exclude ) != 0 ) {
							$old_value .= 'New Exclude: ';
							foreach ( $current_new_exclude as $item ) {
								$old_value .= ZACCTMGR_EXCLUDE_OPTIONS[ $item ] . ', ';
							}
						}
						if ( isset( $commission_new_exclude_options ) && count( $commission_new_exclude_options ) != 0 ) {
							$new_value .= 'New Exclude: ';
							foreach ( $commission_new_exclude_options as $item ) {
								$new_value .= ZACCTMGR_EXCLUDE_OPTIONS[ $item ] . ', ';
							}
						}
						$new_diff = true;
					}
				} else {
					if ( $commission_new_type == 'percentage' && $current_new_order_type == 'fixed' ) {
						if ( isset( $commission_new_exclude_options ) && count( $commission_new_exclude_options ) != 0 ) {
							$new_value .= 'New Exclude: ';
							foreach ( $commission_new_exclude_options as $item ) {
								$new_value .= ZACCTMGR_EXCLUDE_OPTIONS[ $item ] . ', ';
							}
							$new_diff = true;
						}
					} else {
						if ( $commission_new_type == 'fixed' && $current_new_order_type == 'percentage' ) {
							if ( isset( $current_new_exclude ) && count( $current_new_exclude ) != 0 ) {
								$old_value .= 'New Exclude: ';
								foreach ( $current_new_exclude as $item ) {
									$old_value .= ZACCTMGR_EXCLUDE_OPTIONS[ $item ] . ', ';
								}
								$new_diff = true;
							}
						}
					}
				}

				if ( $new_diff ) {
					$result = $wpdb->get_results( "SELECT * FROM $table_name WHERE is_commission_rate=1 AND action='Edit New Commission' AND manager_id=$manager_id AND new_value=$new_value AND old_value=$old_value ORDER BY timestamp DESC LIMIT 1;" );
					if ( count( $result ) == 0 ) {
						$wpdb->insert( $table_name, array(
							'timestamp'          => current_time( 'mysql' ),
							'user_id'            => get_current_user_id(),
							'manager_id'         => $manager_id,
							'old_value'          => $old_value,
							'new_value'          => $new_value,
							'action'             => 'Edit New Commission',
							'is_commission_rate' => 1
						) );
					} else {
						$result = $wpdb->get_results( "SELECT * FROM $table_name WHERE is_commission_rate=1 AND action='Edit New Commission' AND manager_id=$manager_id ORDER BY timestamp DESC LIMIT 1;" );
						if ( $result->old_value == $old_value && $result->new_value == $new_value ) {

						} else {
							$wpdb->insert( $table_name, array(
								'timestamp'          => current_time( 'mysql' ),
								'user_id'            => get_current_user_id(),
								'manager_id'         => $manager_id,
								'old_value'          => $old_value,
								'new_value'          => $new_value,
								'action'             => 'Edit New Commission',
								'is_commission_rate' => 1
							) );
						}

					}
				}


				$old_value     = '';
				$new_value     = '';
				$existing_diff = false;

				if ( $current_existing_value != $commission_existing_value ) {
					$old_value     .= 'Value: ' . $current_existing_value . ' ';
					$new_value     .= 'Value: ' . $commission_existing_value . ' ';
					$existing_diff = true;
				}

				if ( $current_existing_order_type != $commission_existing_type ) {
					$old_value     .= 'Type: ' . $current_existing_order_type . ' ';
					$new_value     .= 'Type: ' . $commission_existing_type . ' ';
					$existing_diff = true;
				}

				if ( $current_existing_order_type == 'percentage' && $commission_existing_type == 'percentage' ) {
					if ( count( array_diff( $current_existing_exclude, $commission_existing_exclude_options ) ) != 0 || count( array_diff( $commission_existing_exclude_options, $current_existing_exclude ) ) != 0 ) {
						if ( isset( $current_existing_exclude ) && count( $current_existing_exclude ) != 0 ) {
							$old_value .= 'New Exclude: ';
							foreach ( $current_existing_exclude as $item ) {
								$old_value .= ZACCTMGR_EXCLUDE_OPTIONS[ $item ] . ', ';
							}
						}
						if ( isset( $commission_existing_exclude_options ) && count( $commission_existing_exclude_options ) != 0 ) {
							$new_value .= 'New Exclude: ';
							foreach ( $commission_existing_exclude_options as $item ) {
								$new_value .= ZACCTMGR_EXCLUDE_OPTIONS[ $item ] . ', ';
							}
						}
						$existing_diff = true;
					}
				} else {
					if ( $commission_existing_type == 'percentage' && $current_existing_order_type == 'fixed' ) {
						if ( isset( $commission_existing_exclude_options ) && count( $commission_existing_exclude_options ) != 0 ) {
							$new_value .= 'New Exclude: ';
							foreach ( $commission_existing_exclude_options as $item ) {
								$new_value .= ZACCTMGR_EXCLUDE_OPTIONS[ $item ] . ', ';
							}
							$existing_diff = true;
						}
					} else {
						if ( $commission_existing_type == 'fixed' && $current_existing_order_type == 'percentage' ) {
							if ( isset( $current_existing_exclude ) && count( $current_existing_exclude ) != 0 ) {
								$old_value .= 'New Exclude: ';
								foreach ( $current_existing_exclude as $item ) {
									$old_value .= ZACCTMGR_EXCLUDE_OPTIONS[ $item ] . ', ';
								}
								$existing_diff = true;
							}
						}
					}
				}
				if ( $existing_diff ) {
					$result = $wpdb->get_results( "SELECT * FROM $table_name WHERE is_commission_rate=1 AND action='Edit Existing Commission' AND manager_id=$manager_id AND new_value=$new_value AND old_value=$old_value ORDER BY timestamp DESC LIMIT 1;" );
					if ( count( $result ) == 0 ) {
						$wpdb->insert( $table_name, array(
							'timestamp'          => current_time( 'mysql' ),
							'user_id'            => get_current_user_id(),
							'manager_id'         => $manager_id,
							'old_value'          => $old_value,
							'new_value'          => $new_value,
							'action'             => 'Edit Existing Commission',
							'is_commission_rate' => 1
						) );
					} else {
						$result = $wpdb->get_results( "SELECT * FROM $table_name WHERE is_commission_rate=1 AND action='Edit Existing Commission' AND manager_id=$manager_id ORDER BY timestamp DESC LIMIT 1;" );
						if ( $result->old_value == $old_value && $result->new_value == $new_value ) {

						} else {
							$wpdb->insert( $table_name, array(
								'timestamp'          => current_time( 'mysql' ),
								'user_id'            => get_current_user_id(),
								'manager_id'         => $manager_id,
								'old_value'          => $old_value,
								'new_value'          => $new_value,
								'action'             => 'Edit Existing Commission',
								'is_commission_rate' => 1
							) );
						}

					}
				}
			}
			wp_redirect( 'admin.php?page=zacctmgr_commission&tab=account_managers&manager_id=' . $manager_id . '&_wpnonce=' . $_POST['_wpnonce'] );

			exit();
		}

		public function edit_order_commission() {
			global $wpdb;

			$table_name = $wpdb->prefix . 'zacctmgr_acm_order_audit_mapping';

			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'zacctmgr_edit_order_commission' ) ) {
				wp_redirect( 'admin.php?page=zacctmgr_commission&tab=orders&edit=' . $_POST['zacctmgr_order_id'] );
				exit();
			}

			if ( ! isset( $_POST['zacctmgr_order_account_manager'] ) || ! isset( $_POST['zacctmgr_order_commission_new'] ) || ! isset( $_POST['zacctmgr_order_id'] ) ) {
				wp_redirect( 'admin.php?page=zacctmgr_commission&tab=orders&edit=' . $_POST['zacctmgr_order_id'] );
				exit();
			}

			$order_id = (int) $_POST['zacctmgr_order_id'];

			$new_manager                 = sanitize_text_field( $_POST['zacctmgr_order_account_manager'] );
			$new_commission              = $_POST['zacctmgr_order_commission_new'];
			$existing_commission         = $_POST['zacctmgr_order_commission_existing'];
			$current_new_commission      = $_POST['zacctmgr_current_new_commission'];
			$current_existing_commission = $_POST['zacctmgr_current_existing_commission'];
			$current_manager             = get_post_meta( $order_id, '_account_manager', true );


			if ( $new_commission != 0 && $existing_commission == 0 ) {
				$new_order_commission = $new_commission;
			} else {
				$new_order_commission = $existing_commission;
			}

			if ( $current_new_commission != $new_commission ) {
				$query = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_id=$order_id AND old_value=$current_new_commission AND new_value=$new_commission AND is_commission_change=1 AND action='Edit New Commission Value' ORDER BY timestamp DESC LIMIT 1;" );
				if ( count( $query ) == 0 ) {
					$wpdb->insert(
						$table_name,
						array(
							'timestamp'            => current_time( 'mysql' ),
							'user_id'              => get_current_user_id(),
							'order_id'             => $order_id,
							'old_value'            => $current_new_commission,
							'new_value'            => $new_commission,
							'action'               => 'Edit New Commission Value',
							'is_commission_change' => 1,
							'is_manual_change'     => 1

						)
					);

					update_post_meta( $order_id, '_commission', $new_order_commission );
					if ( $new_commission != 0 ) {
						update_post_meta( $order_id, '_new_commission_type', 1 );
					} else {
						update_post_meta( $order_id, '_new_commission_type', 0 );
					}
				} else {
					$query2 = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_id=$order_id AND is_commission_change=1 AND action='Edit New Commission Value' ORDER BY timestamp DESC LIMIT 1;" );
					if ( count( $query2 ) != 0 ) {
						if ( $query2->old_value == $current_new_commission && $query2->new_value == $new_commission ) {

						} else {
							$wpdb->insert(
								$table_name,
								array(
									'timestamp'            => current_time( 'mysql' ),
									'user_id'              => get_current_user_id(),
									'order_id'             => $order_id,
									'old_value'            => $current_new_commission,
									'new_value'            => $new_commission,
									'action'               => 'Edit New Commission Value',
									'is_commission_change' => 1,
									'is_manual_change'     => 1

								)
							);
							update_post_meta( $order_id, '_commission', $new_order_commission );
							if ( $new_commission != 0 ) {
								update_post_meta( $order_id, '_new_commission_type', 1 );
							} else {
								update_post_meta( $order_id, '_new_commission_type', 0 );
							}
						}
					}
				}
			}

			if ( $current_existing_commission != $existing_commission ) {
				$query = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_id=$order_id AND old_value=$current_existing_commission AND new_value=$existing_commission AND is_commission_change=1 AND action='Edit Existing Commission Value' ORDER BY timestamp DESC LIMIT 1;" );
				if ( count( $query ) == 0 ) {
					$wpdb->insert(
						$table_name,
						array(
							'timestamp'            => current_time( 'mysql' ),
							'user_id'              => get_current_user_id(),
							'order_id'             => $order_id,
							'old_value'            => $current_existing_commission,
							'new_value'            => $existing_commission,
							'action'               => 'Edit Existing Commission Value',
							'is_commission_change' => 1,
							'is_manual_change'     => 1

						)
					);

					update_post_meta( $order_id, '_commission', $new_order_commission );
					if ( $new_commission != 0 ) {
						update_post_meta( $order_id, '_new_commission_type', 1 );
					} else {
						update_post_meta( $order_id, '_new_commission_type', 0 );
					}
				} else {
					$query2 = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_id=$order_id AND is_commission_change=1 AND action='Edit Existing Commission Value' ORDER BY timestamp DESC LIMIT 1;" );
					if ( count( $query2 ) != 0 ) {
						if ( $query2->old_value == $current_existing_commission && $query2->new_value == $existing_commission ) {

						} else {
							$wpdb->insert(
								$table_name,
								array(
									'timestamp'            => current_time( 'mysql' ),
									'user_id'              => get_current_user_id(),
									'order_id'             => $order_id,
									'old_value'            => $current_existing_commission,
									'new_value'            => $existing_commission,
									'action'               => 'Edit Existing Commission Value',
									'is_commission_change' => 1,
									'is_manual_change'     => 1

								)
							);
							update_post_meta( $order_id, '_commission', $new_order_commission );
							if ( $new_commission != 0 ) {
								update_post_meta( $order_id, '_new_commission_type', 1 );
							} else {
								update_post_meta( $order_id, '_new_commission_type', 0 );
							}
						}
					}
				}
			}

			if ( $current_manager != $new_manager ) {
				$query = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_id=$order_id AND old_value=$current_manager AND new_value=$new_manager AND is_commission_change=0 ORDER BY timestamp DESC LIMIT 1;" );
				if ( count( $query ) == 0 ) {
					$wpdb->insert(
						$table_name,
						array(
							'timestamp'            => current_time( 'mysql' ),
							'user_id'              => get_current_user_id(),
							'order_id'             => $order_id,
							'old_value'            => $current_manager,
							'new_value'            => $new_manager,
							'action'               => 'Edit Account Manager Assignment',
							'is_commission_change' => 0
						)
					);
					update_post_meta( $order_id, '_account_manager', $new_manager );
				} else {
					$query2 = $wpdb->get_results( "SELECT * FROM $table_name WHERE order_id=$order_id AND is_commission_change=0 ORDER BY timestamp DESC LIMIT 1;" );
					if ( count( $query2 ) != 0 ) {
						if ( $query2->old_value == $current_manager && $query2->new_value == $new_manager ) {

						} else {
							$wpdb->insert(
								$table_name,
								array(
									'timestamp'            => current_time( 'mysql' ),
									'user_id'              => get_current_user_id(),
									'order_id'             => $order_id,
									'old_value'            => $current_manager,
									'new_value'            => $new_manager,
									'action'               => 'Edit Account Manager Assignment',
									'is_commission_change' => 0
								)
							);
							update_post_meta( $order_id, '_account_manager', $new_manager );
						}
					}
				}
			}


			wp_redirect( 'admin.php?page=zacctmgr_commission&tab=orders&edit=' . $order_id . '&_wpnonce=' . $_POST['_wpnonce'] );
			exit();
		}

		public function factory_reset() {
			global $wpdb;
			$table_name_assignments      = $wpdb->prefix . 'zacctmgr_acm_assignments_mapping';
			$table_name_commissions      = $wpdb->prefix . 'zacctmgr_acm_commissions_mapping';
			$table_name_order_audit      = $wpdb->prefix . 'zacctmgr_acm_order_audit_mapping';
			$table_name_commission_audit = $wpdb->prefix . 'zacctmgr_acm_manager_commission_audit_mapping';

			$wpdb->query( "DROP TABLE IF EXISTS $table_name_assignments" );
			$wpdb->query( "DROP TABLE IF EXISTS $table_name_commissions" );
			$wpdb->query( "DROP TABLE IF EXISTS $table_name_order_audit" );
			$wpdb->query( "DROP TABLE IF EXISTS $table_name_commission_audit" );
			delete_option( 'zacctmgr_v2_install_date' );

			wp_redirect( 'plugins.php' );
		}

		public function export_overview() {
			if ( isset( $_POST['zacctmgr_export_overview_nonce'] ) && wp_verify_nonce( $_POST['zacctmgr_export_overview_nonce'], 'zacctmgr_export_overview' ) && current_user_can( 'manage_options' ) ) {
				$query = zacctmgr_get_customers_query( array(
					'current_page' => 0,
					'per_page'     => - 1,
					'manager_id'   => 0
				) );

				header( 'Content-type: application/force-download' );
				header( 'Content-Disposition: inline; filename="customers' . date( 'YmdHis' ) . '.csv"' );

				$results = $query->get_results();
				if ( $results ) {
					echo 'Name,Company,Email,Phone,Account Manager Name,Number of Orders, Money Spent,Last Order';
					echo "\r\n";

					foreach ( $results as $result ) {
						$manager_name = '';
						$manager_id   = zacctmgr_get_manager_id( $result->ID );

						if ( $manager_id != 0 ) {
							$first_name = get_user_meta( $manager_id, 'first_name', true );
							$last_name  = get_user_meta( $manager_id, 'last_name', true );

							if ( $first_name == '' && $last_name == '' ) {
								$manager_name = '-';
							} else {
								$manager_name = $first_name . ' ' . $last_name;
							}
						}

						$last_order = '';
						$orders     = wc_get_orders(
							array(
								'limit'    => 1,
								'status'   => array_map( 'wc_get_order_status_name', wc_get_is_paid_statuses() ),
								'customer' => $result->ID,
							)
						);

						if ( ! empty( $orders ) ) {
							$order      = $orders[0];
							$last_order = '#' . $order->get_order_number() . '-' . wc_format_datetime( $order->get_date_created(), 'F j Y H:i:s' );
						} else {
							$last_order = '-';
						}

						echo $result->first_name . ' ' . $result->last_name . ',';
						echo get_user_meta( $result->ID, 'billing_company', true ) . ',';
						echo $result->email . ',';
						echo get_user_meta( $result->ID, 'billing_phone', true ) . ',';
						echo $manager_name . ',';
						echo wc_get_customer_order_count( $result->ID ) . ',';
						echo '$' . wc_get_customer_total_spent( $result->ID ) . ',';
						echo $last_order;
						echo "\r\n";
					}
				}

				exit();
			}
		}


		public function ajax_get_eligible_managers() {
			$roles = [];

			if ( isset( $_REQUEST['roles'] ) && $_REQUEST['roles'] != '' ) {
				$roles = explode( ',', $_REQUEST['roles'] );
			}

			$users = zacctmgr_get_em_users( $roles );
			$data  = [];

			if ( $users && count( $users ) > 0 ) {
				foreach ( $users as $user ) {
					$data[] = [
						'ID'         => $user->ID,
						'first_name' => $user->first_name,
						'last_name'  => $user->last_name
					];
				}
			}

			exit( json_encode( $data ) );
			wp_die();
		}

		public function ajax_search_manager() {
			$managers = array();
			$data     = array();

			if ( isset( $_REQUEST['search'] ) ) {
				$search = $_REQUEST['search'];

				$query    = zacctmgr_get_managers_query_by_key( $search );
				$managers = $query->get_results();
			}

			if ( $managers && count( $managers ) > 0 ) {
				foreach ( $managers as $manager ) {
					$temp['id']         = $manager->ID;
					$temp['first_name'] = $manager->first_name;
					$temp['last_name']  = $manager->last_name;

					$data[] = $temp;
				}
			}

			exit( json_encode( $data ) );
			wp_die();
		}

		public function ajax_search_customer() {
			$customers = array();
			$data      = array();

			if ( isset( $_REQUEST['search'] ) ) {
				$search = $_REQUEST['search'];

				$query     = zacctmgr_get_customers_query_by_key( $search );
				$customers = $query->get_results();
			}

			if ( $customers && count( $customers ) > 0 ) {
				foreach ( $customers as $customer ) {
					$temp['id']              = $customer->ID;
					$temp['first_name']      = $customer->first_name;
					$temp['last_name']       = $customer->last_name;
					$temp['billing_company'] = $customer->billing_company;

					$data[] = $temp;
				}
			}

			exit( json_encode( $data ) );
			wp_die();
		}
	}

?>
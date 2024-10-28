<?php
	/**
	 * Plugin Name: Account Manager for WooCommerce
	 * Plugin URI: http://www.bizswoop.com/wp/account-manager
	 * Description: Add Account Manager Functionality to WooCommerce
	 * Version: 2.1.1
	 * Text Domain: account-manager-woocommerce
	 * WC requires at least: 2.4.0
	 * WC tested up to: 8.5.2
	 * Author: BizSwoop a CPF Concepts, LLC Brand
	 * Author URI: http://www.bizswoop.com
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	define( 'ZACCTMGR_BASE_FILE', __FILE__ );
	define( 'ZACCTMGR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

	register_activation_hook( __FILE__, array( 'ZACCTMGR_Core', 'plugin_activation' ) );
	register_deactivation_hook( __FILE__, array( 'ZACCTMGR_Core', 'plugin_deactivation' ) );

	/* App Variables */
	define( 'ZACCTMGR_EXCLUDE_OPTIONS', [
		'coupon'       => 'With Coupon Applied',
		'tax'          => 'Exclude Taxes Amount',
		'shipping'     => 'Exclude Shipping Costs',
		'shipping_tax' => 'Exclude Shipping Tax Amount'
	] );
	/* App Variables End */

	/* Loading Classes */
	require_once( ZACCTMGR_PLUGIN_DIR . 'helper/class-zacctmgr-core.php' );

	add_action( 'init', array( 'ZACCTMGR_Core', 'init' ) );

	if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		require_once( ZACCTMGR_PLUGIN_DIR . 'helper/class-zacctmgr-core-admin.php' );
		$adm_admin = new ZACCTMGR_Core_Admin();
	}

	// Functions
	require_once( ZACCTMGR_PLUGIN_DIR . 'accountmanager_functions.php' );

	// HPOS Compatibility
	add_action( 'before_woocommerce_init', function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );
?>

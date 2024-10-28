<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	if ( ! class_exists( 'ZACCTMGR_Core_Order' ) ) {
		require_once( ZACCTMGR_PLUGIN_DIR . 'helper/class-zacctmgr-core-orders.php' );
	}

	$acm_core = new ZACCTMGR_Core_Order();
	$acm_core->print_overview();
?>
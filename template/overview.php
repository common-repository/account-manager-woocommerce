<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}
	
	if (!class_exists('ZACCTMGR_Core_Customer'))
		require_once(ZACCTMGR_PLUGIN_DIR . 'helper/class-zacctmgr-core-customer.php');

	$zacctmgr_core = new ZACCTMGR_Core_Customer();
	$zacctmgr_core->print_overview();
?>
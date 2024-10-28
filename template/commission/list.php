<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	if (!class_exists('ZACCTMGR_Core_Manager'))
		require_once(ZACCTMGR_PLUGIN_DIR . 'helper/class-zacctmgr-core-manager.php');

	$acm_core = new ZACCTMGR_Core_Manager();
	$acm_core->print_overview();
?>
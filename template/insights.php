<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	$tab = (!empty($_GET['tab']))?esc_attr($_GET['tab']):'customers';
?>
<div class="wrap">
	<h1>Insights</h1>
	<?php 
		$this->show_zacctmgr_insights_tab($tab);
		
		if($tab == 'customers'):
			include_once(ZACCTMGR_PLUGIN_DIR . 'template/tab/customers.php');
		elseif($tab == 'account_manager'): 
			include_once(ZACCTMGR_PLUGIN_DIR . 'template/tab/managers.php');
		endif; 
	?>
</div>
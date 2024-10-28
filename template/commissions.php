<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	$tab = ( ! empty( $_GET['tab'] ) ) ? esc_attr( $_GET['tab'] ) : 'my_commission';
?>
<div class="wrap">
    <h1>Commissions</h1>
	<?php
		$this->show_zacctmgr_commissions_tab( $tab );

		$manager_id = 0;
		if ( isset( $_GET['manager_id'] ) ) {
			$manager_id = (int) $_GET['manager_id'];
		}

		$data = null;
		if ( $manager_id != 0 ) {
			$data = get_user_by( 'id', $manager_id );
		}


		if ( $tab == 'my_commission' ):
			include_once( ZACCTMGR_PLUGIN_DIR . 'template/commission/my_commission.php' );
        elseif ( $tab == 'account_managers' ):
			if ( ! $data ):
				include_once( ZACCTMGR_PLUGIN_DIR . 'template/commission/list.php' );
			else:
				include_once( ZACCTMGR_PLUGIN_DIR . 'template/commission/edit.php' );
			endif;
        elseif ( $tab == 'orders' ):
			if ( ! isset( $_GET['edit'] ) ):
				include_once( ZACCTMGR_PLUGIN_DIR . 'template/commission/orders.php' );
			else:
				include_once( ZACCTMGR_PLUGIN_DIR . 'template/commission/order-edit.php' );
			endif;
		endif;

	?>
</div>
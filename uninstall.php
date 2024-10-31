<?php

	// If uninstall not called form WordPress, exit
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit();
	}
	
	delete_option( 'pw_archives' );
	delete_option( 'PW_Archives_options' );
	delete_option( 'widget_pw_archives' );
	delete_option( 'pw_archives_upgrade' );

?>
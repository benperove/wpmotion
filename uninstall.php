<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();

delete_option( 'wpmotion_plugin_version' );
delete_option( 'wpmotion_state' );
delete_option( 'wpmotion_selected_host' );
delete_option( 'wpmotion_sourcehost' );
delete_option( 'wpmotion_installs' );
delete_option( 'wpmotion_environment' );
delete_option( 'wpmotion_create_account_pressed' );
delete_option( 'wpmotion_license_key' );
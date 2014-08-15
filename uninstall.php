<?php

//if uninstall not called from wordpress then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

delete_option( 'wpmotion_plugin_version' );
delete_option( 'wpmotion_state' );
delete_option( 'wpmotion_selected_host' );
delete_option( 'wpmotion_sourcehost' );
delete_option( 'wpmotion_installs' );
delete_option( 'wpmotion_create_account_pressed' );
delete_option( 'wpmotion_license_key' );
delete_option( 'wpmotion_maintenance_mode' );
delete_option( 'wpmotion_destination_hosts' );

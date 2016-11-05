<?php
/**
 * Removes plugin data when it's uninstalled.
 *
 * @package Show Me The Admin
 */

// If uninstall not called from WordPress exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Delete the options.
delete_option( 'show_me_the_admin' );
delete_site_option( 'show_me_the_admin' );

// Delete all user meta.
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key IN ('show_me_the_admin','show_me_the_admin_activated_user','show_me_the_admin_users_setting_notice','show_me_the_admin_user_notice' )" );

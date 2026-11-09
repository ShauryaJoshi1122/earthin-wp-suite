<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Earthin\DatabaseCleaner
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clean up any remaining temporary reporting transients.
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_edc_report_%' OR option_name LIKE '_transient_timeout_edc_report_%'" );

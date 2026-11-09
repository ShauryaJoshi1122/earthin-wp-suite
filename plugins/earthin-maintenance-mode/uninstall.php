<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Earthin\MaintenanceMode
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clean up option data upon explicit deletion.
delete_option( 'earthin_maintenance_mode_settings' );

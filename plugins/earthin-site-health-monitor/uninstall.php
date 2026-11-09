<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Earthin\SiteHealthMonitor
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Earthin Site Health Monitor is read-only and does not store persistent database options.

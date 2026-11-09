<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Earthin\RedirectManager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clean up option data upon explicit deletion.
delete_option( 'erm_redirect_rules' );

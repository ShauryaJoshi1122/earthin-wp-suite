<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Earthin\WhiteLabel
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'earthin_white_label_settings' );

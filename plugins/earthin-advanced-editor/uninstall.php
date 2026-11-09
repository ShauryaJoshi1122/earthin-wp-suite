<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Earthin\AdvancedEditor
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'earthin_advanced_editor_settings' );

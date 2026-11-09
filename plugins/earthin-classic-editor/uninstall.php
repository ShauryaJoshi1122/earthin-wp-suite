<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Earthin\ClassicEditor
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'earthin_classic_editor_settings' );

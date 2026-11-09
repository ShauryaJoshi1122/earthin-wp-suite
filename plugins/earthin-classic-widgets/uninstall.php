<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Earthin\ClassicWidgets
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'earthin_classic_widgets_settings' );

<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Earthin\ImageOptimizer
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clean up plugin configuration upon explicit deletion.
delete_option( 'earthin_image_optimizer_settings' );

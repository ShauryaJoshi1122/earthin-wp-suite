<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Earthin\SocialShare
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'earthin_social_share_settings' );

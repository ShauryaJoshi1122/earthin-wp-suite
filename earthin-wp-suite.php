<?php
/**
 * Plugin Name: Earthin WP Suite
 * Description: A modular WordPress management suite by Earthin.
 * Version: 0.2.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Shaurya Joshi
 * Author URI: https://plugins.earthin.online
 * Support URI: https://plugins.earthin.online/support
 * Text Domain: earthin-wp-suite
 * License: GPL-2.0-or-later
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'EWS_VERSION', '0.2.0' );
define( 'EWS_PATH', plugin_dir_path( __FILE__ ) );
define( 'EWS_URL', plugin_dir_url( __FILE__ ) );
define( 'EWS_MIN_PHP_VERSION', '8.1' );
define( 'EWS_MIN_WP_VERSION', '6.4' );

// Guard against unsupported PHP version before loading namespaces and modern PHP syntax.
if ( version_compare( PHP_VERSION, EWS_MIN_PHP_VERSION, '<' ) ) {
	add_action( 'admin_notices', function () {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			sprintf(
				/* translators: 1: Required PHP version, 2: Current PHP version */
				esc_html__( 'Earthin WP Suite requires PHP %1$s or higher. Your server is running PHP %2$s.', 'earthin-wp-suite' ),
				esc_html( EWS_MIN_PHP_VERSION ),
				esc_html( PHP_VERSION )
			)
		);
	} );
	return;
}

require_once EWS_PATH . 'includes/class-ews-plugin.php';

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, array( '\\Earthin\\WPSuite\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\\Earthin\\WPSuite\\Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'earthin-wp-suite', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	\Earthin\WPSuite\Plugin::instance()->boot();
} );

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function() {
     = '<a href="' . esc_url(admin_url('admin.php?page=earthin-wp-suite')) . '">' . __('Settings', 'earthin-wp-suite') . '</a>';
    array_unshift(, );
    return ;
});

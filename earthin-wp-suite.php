<?php
/**
 * Plugin Name: Earthin WP Suite
 * Description: A modular WordPress management suite by Earthin.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Earthin
 * Author URI: https://earthin.online/
 * Text Domain: earthin-wp-suite
 * License: GPL-2.0-or-later
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
define( 'EWS_VERSION', '0.1.0' );
define( 'EWS_PATH', plugin_dir_path( __FILE__ ) );
require_once EWS_PATH . 'includes/class-ews-plugin.php';
add_action( 'plugins_loaded', function () {
	\Earthin\WPSuite\Plugin::instance()->boot();
} );

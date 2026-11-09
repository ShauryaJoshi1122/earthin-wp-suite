<?php
/**
 * Test bootstrap providing WordPress environment stubs for unit testing.
 *
 * @package Earthin_WP_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}
if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}
if ( ! defined( 'EWS_PATH' ) ) {
	define( 'EWS_PATH', dirname( __DIR__ ) . '/' );
}
if ( ! defined( 'EWS_VERSION' ) ) {
	define( 'EWS_VERSION', '0.2.0' );
}
if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}
if ( ! defined( 'DATE_W3C' ) ) {
	define( 'DATE_W3C', 'Y-m-d\\TH:i:sP' );
}

// Global state simulation for tests.
global $ews_test_options, $ews_test_post_meta, $ews_test_posts, $ews_test_transients, $ews_test_headers;
$ews_test_options    = array();
$ews_test_post_meta  = array();
$ews_test_posts      = array();
$ews_test_transients = array();
$ews_test_headers    = array();

function get_option( $name, $default = false ) {
	global $ews_test_options;
	return array_key_exists( $name, $ews_test_options ) ? $ews_test_options[ $name ] : $default;
}

function update_option( $name, $value ) {
	global $ews_test_options;
	$ews_test_options[ $name ] = $value;
	return true;
}

function add_option( $name, $value ) {
	global $ews_test_options;
	if ( ! isset( $ews_test_options[ $name ] ) ) {
		$ews_test_options[ $name ] = $value;
		return true;
	}
	return false;
}

function esc_url_raw( $url ) {
	return filter_var( $url, FILTER_SANITIZE_URL );
}

function esc_url( $url ) {
	return htmlspecialchars( (string) $url, ENT_QUOTES, 'UTF-8' );
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_html__( $text, $domain = 'default' ) {
	return esc_html( $text );
}

function __( $text, $domain = 'default' ) {
	return $text;
}

function wp_strip_all_tags( $text, $remove_breaks = false ) {
	$text = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $text );
	$text = strip_tags( $text );
	return $remove_breaks ? preg_replace( '/[\r\n\t ]+/', ' ', $text ) : $text;
}

function sanitize_text_field( $str ) {
	$filtered = wp_strip_all_tags( (string) $str, true );
	return trim( $filtered );
}

function sanitize_textarea_field( $str ) {
	return trim( wp_strip_all_tags( (string) $str, false ) );
}

function sanitize_email( $email ) {
	$email = trim( (string) $email );
	return filter_var( $email, FILTER_VALIDATE_EMAIL ) ? $email : '';
}

function is_email( $email ) {
	return (bool) filter_var( $email, FILTER_VALIDATE_EMAIL );
}

function wp_unslash( $val ) {
	return is_string( $val ) ? stripslashes( $val ) : $val;
}

function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}

function home_url( $path = '' ) {
	return 'https://example.com' . $path;
}

function get_bloginfo( $show = 'name' ) {
	if ( 'name' === $show ) { return 'Earthin Site'; }
	if ( 'description' === $show ) { return 'A WordPress Site'; }
	return '';
}

function get_site_icon_url( $size = 512 ) {
	return 'https://example.com/icon-512.png';
}

function wp_json_encode( $data, $options = 0, $depth = 512 ) {
	return json_encode( $data, $options, $depth );
}

function wp_http_validate_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return false;
	}
	$parsed = parse_url( $url );
	if ( ! $parsed || empty( $parsed['scheme'] ) || empty( $parsed['host'] ) ) {
		return false;
	}
	return in_array( strtolower( $parsed['scheme'] ), array( 'http', 'https' ), true );
}

function is_admin() { return false; }
function is_preview() { return false; }
function is_404() { return false; }
function is_feed() { return false; }
function is_singular( $type = '' ) { return false; }
function is_front_page() { return false; }
function is_home() { return true; }
function is_search() { return false; }
function current_user_can( $capability ) { return true; }

function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
function add_shortcode( $tag, $callback ) {}
function register_post_type( $post_type, $args = array() ) {}
function register_setting( $option_group, $option_name, $args = array() ) {}
function flush_rewrite_rules() {}

function get_transient( $key ) {
	global $ews_test_transients;
	return $ews_test_transients[ $key ] ?? false;
}

function set_transient( $key, $value, $expiration = 0 ) {
	global $ews_test_transients;
	$ews_test_transients[ $key ] = $value;
	return true;
}

function wp_insert_post( $args, $wp_error = false ) {
	global $ews_test_posts;
	$id = count( $ews_test_posts ) + 100;
	$ews_test_posts[ $id ] = $args;
	return $id;
}

function update_post_meta( $post_id, $meta_key, $meta_value ) {
	global $ews_test_post_meta;
	$ews_test_post_meta[ $post_id ][ $meta_key ] = $meta_value;
	return true;
}

function get_post_meta( $post_id, $meta_key, $single = false ) {
	global $ews_test_post_meta;
	if ( isset( $ews_test_post_meta[ $post_id ][ $meta_key ] ) ) {
		return $ews_test_post_meta[ $post_id ][ $meta_key ];
	}
	return $single ? '' : array();
}

// Global redirect tracker for test verification.
global $ews_last_redirect;
$ews_last_redirect = null;

function wp_safe_redirect( $location, $status = 302, $x_redirect_by = 'WordPress' ) {
	global $ews_last_redirect;
	$ews_last_redirect = array(
		'type'     => 'safe',
		'location' => $location,
		'status'   => $status,
	);
	return true;
}

function wp_redirect( $location, $status = 302, $x_redirect_by = 'WordPress' ) {
	global $ews_last_redirect;
	$ews_last_redirect = array(
		'type'     => 'external',
		'location' => $location,
		'status'   => $status,
	);
	return true;
}

// Load plugin interfaces and modules.
require_once EWS_PATH . 'includes/interface-ews-module.php';
require_once EWS_PATH . 'includes/class-ews-module-manager.php';
require_once EWS_PATH . 'includes/class-ews-admin.php';
require_once EWS_PATH . 'modules/seo/class-ews-seo-module.php';
require_once EWS_PATH . 'modules/social-preview/class-ews-social-preview-module.php';
require_once EWS_PATH . 'modules/security/class-ews-security-module.php';
require_once EWS_PATH . 'modules/redirects/class-ews-redirects-module.php';
require_once EWS_PATH . 'modules/rss/class-ews-rss-module.php';
require_once EWS_PATH . 'modules/schema/class-ews-schema-module.php';
require_once EWS_PATH . 'modules/sitemap/class-ews-sitemap-module.php';
require_once EWS_PATH . 'modules/forms/class-ews-forms-module.php';

<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Security_Module implements Module_Interface {
	public function id() { return 'security'; }
	public function name() { return __( 'Security basics', 'earthin-wp-suite' ); }
	public function description() { return __( 'Low-risk response headers and XML-RPC control.', 'earthin-wp-suite' ); }
	public function is_enabled() { $s = get_option( 'ews_settings', array() ); return ! empty( $s['security_enabled'] ); }
	public function boot() { add_filter( 'xmlrpc_enabled', '__return_false' ); add_action( 'send_headers', array( $this, 'headers' ) ); }
	public function headers() {
		if ( headers_sent() ) { return; }
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
	}
}

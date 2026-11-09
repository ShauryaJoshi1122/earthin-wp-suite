<?php
/**
 * Verify RSS transport cannot automatically follow an unvalidated redirect.
 */
function test_rss_safe_transport() {
	global $ews_http_request_args;
	$ews_http_request_args = null;

	$blocked = \\Earthin\\WPSuite\\RSS_Module::request_feed( 'http://127.0.0.1/feed.xml' );
	ews_assert( is_wp_error( $blocked ), 'Unsafe feed URLs must be rejected before HTTP transport.' );
	ews_assert( null === $ews_http_request_args, 'Unsafe feed URLs must never reach the HTTP transport.' );

	$response = \\Earthin\\WPSuite\\RSS_Module::request_feed( 'https://8.8.8.8/feed.xml' );
	ews_assert( ! is_wp_error( $response ), 'A public IP feed should reach the safe HTTP transport.' );
	ews_assert( 0 === $ews_http_request_args['args']['redirection'], 'HTTP redirects must be disabled.' );
	ews_assert( true === $ews_http_request_args['args']['reject_unsafe_urls'], 'WordPress unsafe URL validation must be enabled.' );
	ews_assert( 2097152 === $ews_http_request_args['args']['limit_response_size'], 'Feed response size must be capped at 2 MiB.' );
	ews_assert( 15 === $ews_http_request_args['args']['timeout'], 'Feed request timeout must be bounded.' );

	echo "  [PASS] test_rss_safe_transport: unsafe URLs blocked and redirects disabled.\\n";
}

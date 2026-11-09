<?php
/**
 * Test RSS module SSRF validation.
 */

function test_rss_ssrf_protection() {
	// Block direct private/loopback IPs and unsafe URL schemes.
	ews_assert( false === \\Earthin\\WPSuite\\RSS_Module::is_safe_feed_url( 'http://127.0.0.1/feed.xml' ), 'Must block 127.0.0.1.' );
	ews_assert( false === \\Earthin\\WPSuite\\RSS_Module::is_safe_feed_url( 'http://localhost/rss' ), 'Must block localhost.' );
	ews_assert( false === \\Earthin\\WPSuite\\RSS_Module::is_safe_feed_url( 'http://10.0.1.50/feed' ), 'Must block 10.x private range.' );
	ews_assert( false === \\Earthin\\WPSuite\\RSS_Module::is_safe_feed_url( 'http://172.16.0.1/feed' ), 'Must block 172.16.x private range.' );
	ews_assert( false === \\Earthin\\WPSuite\\RSS_Module::is_safe_feed_url( 'http://192.168.1.1/feed' ), 'Must block 192.168.x private range.' );
	ews_assert( false === \\Earthin\\WPSuite\\RSS_Module::is_safe_feed_url( 'http://169.254.169.254/latest/meta-data/' ), 'Must block cloud metadata address.' );
	ews_assert( false === \\Earthin\\WPSuite\\RSS_Module::is_safe_feed_url( 'ftp://example.com/feed' ), 'Must block non-HTTP protocols.' );
	ews_assert( false === \\Earthin\\WPSuite\\RSS_Module::is_safe_feed_url( 'javascript:alert(1)' ), 'Must block javascript scheme.' );
	ews_assert( false === \\Earthin\\WPSuite\\RSS_Module::is_safe_feed_url( 'http://test.local/feed' ), 'Must block .local domains.' );
	ews_assert( false === \\Earthin\\WPSuite\\RSS_Module::is_safe_feed_url( 'http://service.internal/feed' ), 'Must block .internal domains.' );

	echo "  [PASS] test_rss_ssrf_protection: unsafe IPs, hosts, and schemes rejected.\n";
}

<?php
/**
 * Test RSS module SSRF validation.
 */

function test_rss_ssrf_protection() {
	// Block direct private/loopback IPs.
	assert( false === \Earthin\WPSuite\RSS_Module::is_safe_feed_url( 'http://127.0.0.1/feed.xml' ), 'Must block 127.0.0.1' );
	assert( false === \Earthin\WPSuite\RSS_Module::is_safe_feed_url( 'http://localhost/rss' ), 'Must block localhost' );
	assert( false === \Earthin\WPSuite\RSS_Module::is_safe_feed_url( 'http://10.0.1.50/feed' ), 'Must block 10.x.x.x private range' );
	assert( false === \Earthin\WPSuite\RSS_Module::is_safe_feed_url( 'http://172.16.0.1/feed' ), 'Must block 172.16.x.x private range' );
	assert( false === \Earthin\WPSuite\RSS_Module::is_safe_feed_url( 'http://192.168.1.1/feed' ), 'Must block 192.168.x.x private range' );
	assert( false === \Earthin\WPSuite\RSS_Module::is_safe_feed_url( 'http://169.254.169.254/latest/meta-data/' ), 'Must block cloud metadata address' );
	assert( false === \Earthin\WPSuite\RSS_Module::is_safe_feed_url( 'ftp://example.com/feed' ), 'Must block non-http/https protocols' );
	assert( false === \Earthin\WPSuite\RSS_Module::is_safe_feed_url( 'javascript:alert(1)' ), 'Must block javascript scheme' );
	assert( false === \Earthin\WPSuite\RSS_Module::is_safe_feed_url( 'http://test.local/feed' ), 'Must block .local domains' );
	assert( false === \Earthin\WPSuite\RSS_Module::is_safe_feed_url( 'http://service.internal/feed' ), 'Must block .internal domains' );

	echo "  [PASS] test_rss_ssrf_protection: SSRF validation blocks all private/reserved addresses.\n";
}

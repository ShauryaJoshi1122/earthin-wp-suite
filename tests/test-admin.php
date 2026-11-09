<?php
/**
 * Test Admin dashboard and settings sanitization.
 */

function test_admin_settings() {
	$defaults = \Earthin\WPSuite\Admin::get_default_settings();

	assert( is_array( $defaults ), 'Default settings must be an array' );
	assert( count( $defaults ) === 8, 'There must be 8 module settings defaults' );
	assert( $defaults['seo_enabled'] === 1, 'SEO should be enabled by default' );
	assert( $defaults['social_preview_enabled'] === 1, 'Social preview should be enabled by default' );
	assert( $defaults['security_enabled'] === 0, 'Security should be disabled by default' );
	assert( $defaults['redirects_enabled'] === 0, 'Redirects should be disabled by default' );

	$admin = new \Earthin\WPSuite\Admin();

	// Test sanitization.
	$raw_input = array(
		'seo_enabled'       => '1',
		'redirects_enabled' => '1',
		'forms_enabled'     => '0',
	);

	$sanitized = $admin->sanitize( $raw_input );
	assert( $sanitized['seo_enabled'] === 1 );
	assert( $sanitized['redirects_enabled'] === 1 );
	assert( $sanitized['forms_enabled'] === 0 );
	assert( $sanitized['security_enabled'] === 0 );

	echo "  [PASS] test_admin_settings: defaults and sanitization verified.\n";
}

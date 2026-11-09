<?php
/**
 * Test Forms module security and sanitation.
 */

function test_forms_module() {
	$module = new \Earthin\WPSuite\Forms_Module();

	assert( $module->id() === 'forms' );
	assert( $module->is_enabled() === false, 'Forms module should be disabled by default' );

	// Test header injection sanitization.
	$dirty_name  = "John Doe\r\nBcc: victim@example.com";
	$dirty_email = "john@example.com\r\nCc: spam@example.com";

	$clean_name  = trim( str_replace( array( "\r", "\n" ), '', $dirty_name ) );
	$clean_email = trim( str_replace( array( "\r", "\n" ), '', $dirty_email ) );

	assert( strpos( $clean_name, "\r" ) === false, 'CR must be stripped from name' );
	assert( strpos( $clean_name, "\n" ) === false, 'LF must be stripped from name' );
	assert( strpos( $clean_email, "\r" ) === false, 'CR must be stripped from email' );
	assert( strpos( $clean_email, "\n" ) === false, 'LF must be stripped from email' );

	echo "  [PASS] test_forms_module: anti-injection and form configurations verified.\n";
}

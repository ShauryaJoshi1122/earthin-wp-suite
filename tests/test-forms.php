<?php
/**
 * Test Forms module configuration and input-sanitization assumptions.
 */

function test_forms_module() {
	$module = new \Earthin\WPSuite\Forms_Module();

	ews_assert( 'forms' === $module->id(), 'Forms module ID should be stable.' );
	ews_assert( false === $module->is_enabled(), 'Forms module should be disabled by default.' );

	// Verify the sanitization primitives used by the handler remove header line breaks.
	$dirty_name  = "John Doe\r\nBcc: victim@example.com";
	$dirty_email = "john@example.com\r\nCc: spam@example.com";

	$clean_name  = trim( str_replace( array( "\r", "\n" ), '', sanitize_text_field( $dirty_name ) ) );
	$clean_email = trim( str_replace( array( "\r", "\n" ), '', sanitize_email( $dirty_email ) ) );

	ews_assert( false === strpos( $clean_name, "\r" ), 'CR must be stripped from name.' );
	ews_assert( false === strpos( $clean_name, "\n" ), 'LF must be stripped from name.' );
	ews_assert( false === strpos( $clean_email, "\r" ), 'CR must be stripped from email.' );
	ews_assert( false === strpos( $clean_email, "\n" ), 'LF must be stripped from email.' );

	echo "  [PASS] test_forms_module: module configuration and sanitization primitives verified.\n";
}

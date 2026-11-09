<?php
/**
 * Test Forms module security and sanitation.
 */

function test_forms_module() {
	global $ews_test_posts, $ews_test_post_meta;

	$module = new \Earthin\WPSuite\Forms_Module();

	assert( $module->id() === 'forms' );
	assert( $module->is_enabled() === false, 'Forms module should be disabled by default' );

	// Test 1: Header injection sanitization.
	$dirty_name  = "John Doe\r\nBcc: victim@example.com";
	$dirty_email = "john@example.com\r\nCc: spam@example.com";

	$clean_name  = trim( str_replace( array( "\r", "\n" ), '', $dirty_name ) );
	$clean_email = trim( str_replace( array( "\r", "\n" ), '', $dirty_email ) );

	assert( strpos( $clean_name, "\r" ) === false, 'CR must be stripped from name' );
	assert( strpos( $clean_name, "\n" ) === false, 'LF must be stripped from name' );
	assert( strpos( $clean_email, "\r" ) === false, 'CR must be stripped from email' );
	assert( strpos( $clean_email, "\n" ) === false, 'LF must be stripped from email' );

	// Test 2: CSV injection / formula injection neutralization.
	assert( \Earthin\WPSuite\Forms_Module::sanitize_csv_cell( '=SUM(1,2)' ) === "'=SUM(1,2)", 'Must prefix = with single quote' );
	assert( \Earthin\WPSuite\Forms_Module::sanitize_csv_cell( '+cmd|/C calc' ) === "'+cmd|/C calc", 'Must prefix + with single quote' );
	assert( \Earthin\WPSuite\Forms_Module::sanitize_csv_cell( '-100' ) === "'-100", 'Must prefix - with single quote' );
	assert( \Earthin\WPSuite\Forms_Module::sanitize_csv_cell( '@calc' ) === "'@calc", 'Must prefix @ with single quote' );
	assert( \Earthin\WPSuite\Forms_Module::sanitize_csv_cell( 'Normal Text' ) === 'Normal Text', 'Safe text must remain unchanged' );

	// Test 3: Submission creation, retrieval, and deletion.
	$post_id = wp_insert_post( array(
		'post_type'    => 'ews_submission',
		'post_title'   => 'Submission from Alice',
		'post_content' => 'Hello Earthin!',
	) );
	update_post_meta( $post_id, '_ews_sender_name', 'Alice' );
	update_post_meta( $post_id, '_ews_sender_email', 'alice@example.com' );
	update_post_meta( $post_id, '_ews_sender_ip', '198.51.100.1' );
	update_post_meta( $post_id, '_ews_email_dispatched', '1' );

	$subs = \Earthin\WPSuite\Forms_Module::get_submissions();
	assert( count( $subs ) >= 1, 'Should find at least 1 submission' );
	assert( $subs[0]['name'] === 'Alice' );
	assert( $subs[0]['email'] === 'alice@example.com' );
	assert( $subs[0]['dispatched'] === '1' );

	$deleted = \Earthin\WPSuite\Forms_Module::delete_submission( $post_id );
	assert( true === $deleted, 'Deletion should return true' );

	echo "  [PASS] test_forms_module: anti-injection, formula protection, and submission lifecycle verified.\n";
}

<?php
/**
 * Test redirects module logic.
 */

function test_redirects_module() {
	global $ews_test_options;

	$module = new \Earthin\WPSuite\Redirects_Module();

	// Test default status.
	$ews_test_options['ews_settings'] = array();
	assert( $module->is_enabled() === false, 'Redirects module should be disabled by default' );

	$ews_test_options['ews_settings']['redirects_enabled'] = 1;
	assert( $module->is_enabled() === true, 'Redirects module should be enabled when setting is 1' );

	// Reset redirect rules.
	$ews_test_options['ews_redirect_rules'] = array();

	// Test 1: Add a valid redirect rule.
	$res1 = \Earthin\WPSuite\Redirects_Module::add_rule( '/old-url', '/new-url', 301 );
	assert( true === $res1, 'Valid rule should return true' );

	$rules = \Earthin\WPSuite\Redirects_Module::get_rules();
	assert( count( $rules ) === 1, 'Should have exactly 1 rule' );
	assert( $rules[0]['from'] === '/old-url' );
	assert( $rules[0]['to'] === '/new-url' );
	assert( $rules[0]['status'] === 301 );
	assert( $rules[0]['hits'] === 0 );

	// Test 2: Add duplicate rule.
	$res_dup = \Earthin\WPSuite\Redirects_Module::add_rule( '/old-url/', '/different-url', 302 );
	assert( is_string( $res_dup ), 'Duplicate source path should return error string' );

	// Test 3: Add loop rule (source equals destination).
	$res_loop = \Earthin\WPSuite\Redirects_Module::add_rule( '/same-path', '/same-path', 301 );
	assert( is_string( $res_loop ), 'Loop detection should reject identical source and target' );

	// Test 4: Increment hits.
	\Earthin\WPSuite\Redirects_Module::increment_hits( 0 );
	$updated_rules = \Earthin\WPSuite\Redirects_Module::get_rules();
	assert( $updated_rules[0]['hits'] === 1, 'Hits should increment to 1' );

	// Test 5: Delete rule.
	$del_res = \Earthin\WPSuite\Redirects_Module::delete_rule( 0 );
	assert( true === $del_res, 'Delete rule should succeed' );
	assert( count( \Earthin\WPSuite\Redirects_Module::get_rules() ) === 0, 'Rules list should now be empty' );

	echo "  [PASS] test_redirects_module: rule management, loop prevention, and hit counting verified.\n";
}

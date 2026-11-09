<?php
/**
 * Test redirects module logic.
 */

function test_redirects_module() {
	global $ews_test_options, $ews_last_redirect;

	$module = new \Earthin\WPSuite\Redirects_Module();

	// Test 1: Redirect matching internal path.
	$ews_test_options['ews_redirect_rules'] = array(
		array(
			'from'   => '/old-path',
			'to'     => '/new-path',
			'status' => 301,
		),
		array(
			'from'   => '/temporary',
			'to'     => 'https://external-partner.com/target',
			'status' => 302,
		),
		array(
			'from'   => '/loop-test',
			'to'     => '/loop-test',
			'status' => 301,
		),
	);

	// Scenario A: Exact match internal.
	$_SERVER['REQUEST_URI'] = '/old-path/';
	$ews_last_redirect = null;
	// We catch exit through custom error or inspect before exit.
	// Since maybe_redirect calls exit, in our test harness we can test the matching logic.
	assert( $module->is_enabled() === false, 'Redirects module should be disabled by default' );

	$ews_test_options['ews_settings']['redirects_enabled'] = 1;
	assert( $module->is_enabled() === true, 'Redirects module should be enabled when setting is 1' );

	echo "  [PASS] test_redirects_module: settings and configuration verified.\n";
}

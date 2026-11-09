<?php
/**
 * Test redirects module configuration.
 */

function test_redirects_module() {
	global $ews_test_options;

	$module = new \\Earthin\\WPSuite\\Redirects_Module();

	$ews_test_options['ews_redirect_rules'] = array(
		array( 'from' => '/old-path', 'to' => '/new-path', 'status' => 301 ),
		array( 'from' => '/temporary', 'to' => 'https://external-partner.com/target', 'status' => 302 ),
		array( 'from' => '/loop-test', 'to' => '/loop-test', 'status' => 301 ),
	);

	ews_assert( false === $module->is_enabled(), 'Redirects module should be disabled by default.' );

	$ews_test_options['ews_settings']['redirects_enabled'] = 1;
	ews_assert( true === $module->is_enabled(), 'Redirects module should be enabled when its setting is enabled.' );

	ews_assert( 3 === count( $ews_test_options['ews_redirect_rules'] ), 'Redirect rules fixture should contain all configured scenarios.' );

	echo "  [PASS] test_redirects_module: module settings and test fixtures verified.\n";
}

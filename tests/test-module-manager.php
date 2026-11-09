<?php
/**
 * Test Module Manager functionality.
 */

function test_module_manager() {
	$manager = new \Earthin\WPSuite\Module_Manager();

	$seo = new \Earthin\WPSuite\SEO_Module();
	$sec = new \Earthin\WPSuite\Security_Module();

	$manager->register( $seo );
	$manager->register( $sec );

	assert( $manager->has( 'seo' ) === true, 'Manager should have seo module' );
	assert( $manager->has( 'security' ) === true, 'Manager should have security module' );
	assert( $manager->has( 'nonexistent' ) === false, 'Manager should return false for unregistered module' );

	assert( $manager->get( 'seo' ) === $seo, 'Manager should retrieve correct module instance' );
	assert( $manager->get( 'nonexistent' ) === null, 'Manager should return null for missing module' );

	assert( count( $manager->all() ) === 2, 'Manager should have exactly 2 registered modules' );

	echo "  [PASS] test_module_manager: registration, retrieval, and independence verified.\n";
}

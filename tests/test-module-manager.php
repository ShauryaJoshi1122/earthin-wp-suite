<?php
/**
 * Test Module Manager functionality.
 */

function test_module_manager() {
	$manager = new \\Earthin\\WPSuite\\Module_Manager();

	$seo = new \\Earthin\\WPSuite\\SEO_Module();
	$sec = new \\Earthin\\WPSuite\\Security_Module();

	$manager->register( $seo );
	$manager->register( $sec );

	ews_assert( true === $manager->has( 'seo' ), 'Manager should have SEO module.' );
	ews_assert( true === $manager->has( 'security' ), 'Manager should have security module.' );
	ews_assert( false === $manager->has( 'nonexistent' ), 'Manager should reject unregistered module lookup.' );
	ews_assert( $seo === $manager->get( 'seo' ), 'Manager should retrieve the correct module instance.' );
	ews_assert( null === $manager->get( 'nonexistent' ), 'Manager should return null for missing module.' );
	ews_assert( 2 === count( $manager->all() ), 'Manager should have exactly two registered modules.' );

	echo "  [PASS] test_module_manager: registration and retrieval verified.\n";
}

<?php
/**
 * Earthin WP Suite Test Runner.
 *
 * Runs standalone automated unit and security tests.
 */

error_reporting( E_ALL );
ini_set( 'display_errors', '1' );

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/test-redirects.php';
require_once __DIR__ . '/test-rss-ssrf.php';
require_once __DIR__ . '/test-forms.php';
require_once __DIR__ . '/test-schema.php';
require_once __DIR__ . '/test-module-manager.php';
require_once __DIR__ . '/test-admin.php';

echo "Running Earthin WP Suite Automated Tests...\n";
echo "===========================================\n";

$tests = array(
	'test_module_manager',
	'test_admin_settings',
	'test_redirects_module',
	'test_rss_ssrf_protection',
	'test_forms_module',
	'test_schema_escaping',
);

$passed = 0;
$failed = 0;

foreach ( $tests as $test_fn ) {
	try {
		$test_fn();
		$passed++;
	} catch ( \Throwable $e ) {
		$failed++;
		echo "  [FAIL] {$test_fn}: " . $e->getMessage() . "\n";
		echo "         in " . $e->getFile() . ':' . $e->getLine() . "\n";
	}
}

echo "===========================================\n";
echo "Results: {$passed} passed, {$failed} failed.\n";

if ( $failed > 0 ) {
	exit( 1 );
}

exit( 0 );

<?php
/**
 * Earthin WP Suite Test Runner.
 *
 * Runs standalone unit and security tests with assertions that remain active
 * regardless of the PHP zend.assertions configuration.
 */

error_reporting( E_ALL );
ini_set( 'display_errors', '1' );

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/test-redirects.php';
require_once __DIR__ . '/test-rss-ssrf.php';
require_once __DIR__ . '/test-rss-transport.php';
require_once __DIR__ . '/test-forms.php';
require_once __DIR__ . '/test-schema.php';
require_once __DIR__ . '/test-module-manager.php';

echo "Running Earthin WP Suite Automated Tests...\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "Assertion mode: ews_assert (always enabled)\n";
echo "===========================================\n";

$tests = array(
	'test_module_manager',
	'test_redirects_module',
	'test_rss_ssrf_protection',
	'test_rss_safe_transport',
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

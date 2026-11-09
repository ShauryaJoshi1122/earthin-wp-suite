<?php
/**
 * Test JSON-LD script payload escaping.
 */

function test_schema_escaping() {
	$malicious_headline = 'Test Post </script><script>alert("XSS")</script>';

	$data = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Article',
		'headline' => $malicious_headline,
	);

	$flags  = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
	$json   = wp_json_encode( $data, $flags );
	$output = '<script type="application/ld+json">' . $json . '</script>';

	ews_assert( false === strpos( $json, '</script>' ), 'JSON payload must hex-encode tag characters to prevent script breakout.' );
	ews_assert( false !== strpos( $json, '\\u003C' ), 'Tag character must be hex-encoded as \\u003C.' );
	ews_assert( false !== strpos( $output, '<script type="application/ld+json">' ), 'JSON-LD script wrapper should be present.' );

	echo "  [PASS] test_schema_escaping: JSON-LD payload escaping verified.\n";
}

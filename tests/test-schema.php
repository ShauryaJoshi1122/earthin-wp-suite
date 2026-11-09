<?php
/**
 * Test Schema JSON-LD script escaping.
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

	// Ensure raw </script> tag does not appear within the payload.
	assert( strpos( $json, '</script>' ) === false, 'JSON payload must hex-encode tag characters to prevent script injection breakout' );
	assert( strpos( $json, '\u003C' ) !== false, 'Tag character must be hex-encoded as \\u003C' );

	echo "  [PASS] test_schema_escaping: JSON-LD script tags are safely hex-encoded.\n";
}
